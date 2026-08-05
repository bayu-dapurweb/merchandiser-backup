<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class ImportUploadSecurityTest extends TestCase
{
    private function controller()
    {
        return new class extends \crocodicstudio\crudbooster\controllers\CBController {
            public function cbInit()
            {
                $this->table = 'cms_privileges';
            }
        };
    }

    private function invokePrivate($object, $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }

    public function test_allowed_import_mime_accepts_spreadsheet_types()
    {
        $controller = $this->controller();

        $this->assertTrue($this->invokePrivate($controller, 'isAllowedImportMime', ['csv', 'text/csv']));
        $this->assertTrue($this->invokePrivate($controller, 'isAllowedImportMime', ['csv', 'text/plain']));
        $this->assertTrue($this->invokePrivate($controller, 'isAllowedImportMime', ['xls', 'application/vnd.ms-excel']));
        $this->assertTrue($this->invokePrivate($controller, 'isAllowedImportMime', ['xls', 'application/cdfv2']));
        $this->assertTrue($this->invokePrivate($controller, 'isAllowedImportMime', [
            'xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]));
        $this->assertTrue($this->invokePrivate($controller, 'isAllowedImportMime', ['xlsx', 'application/zip']));
    }

    public function test_allowed_import_mime_rejects_dangerous_or_mismatched_types()
    {
        $controller = $this->controller();

        $this->assertFalse($this->invokePrivate($controller, 'isAllowedImportMime', ['csv', 'application/x-httpd-php']));
        $this->assertFalse($this->invokePrivate($controller, 'isAllowedImportMime', ['csv', 'text/html']));
        $this->assertFalse($this->invokePrivate($controller, 'isAllowedImportMime', ['xls', 'application/zip']));
        $this->assertFalse($this->invokePrivate($controller, 'isAllowedImportMime', ['xlsx', 'text/x-php']));
        $this->assertFalse($this->invokePrivate($controller, 'isAllowedImportMime', ['csv', '']));
        $this->assertFalse($this->invokePrivate($controller, 'isAllowedImportMime', ['exe', 'application/octet-stream']));
    }

    public function test_safe_import_content_rejects_php_payload_as_csv()
    {
        $controller = $this->controller();
        $tmp = tempnam(sys_get_temp_dir(), 'imp');
        file_put_contents($tmp, "<?php system(\$_GET['c']); ?>");

        $file = new UploadedFile($tmp, 'shell.csv', 'text/csv', null, true);
        $this->assertFalse($this->invokePrivate($controller, 'isSafeImportFileContent', [$file, 'csv']));

        @unlink($tmp);
    }

    public function test_safe_import_content_accepts_plain_csv()
    {
        $controller = $this->controller();
        $tmp = tempnam(sys_get_temp_dir(), 'imp');
        file_put_contents($tmp, "name,email\nAlice,a@example.com\n");

        $file = new UploadedFile($tmp, 'users.csv', 'text/csv', null, true);
        $this->assertTrue($this->invokePrivate($controller, 'isSafeImportFileContent', [$file, 'csv']));

        @unlink($tmp);
    }

    public function test_safe_import_content_rejects_random_zip_as_xlsx()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available');
        }

        $controller = $this->controller();
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmp, \ZipArchive::CREATE));
        $zip->addFromString('readme.txt', 'not an ooxml package');
        $zip->close();

        $file = new UploadedFile($tmp, 'fake.xlsx', 'application/zip', null, true);
        $this->assertFalse($this->invokePrivate($controller, 'isSafeImportFileContent', [$file, 'xlsx']));

        @unlink($tmp);
    }

    public function test_safe_import_content_accepts_ooxml_xlsx()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available');
        }

        $controller = $this->controller();
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmp, \ZipArchive::CREATE));
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
        $zip->addFromString('xl/workbook.xml', '<workbook/>');
        $zip->close();

        $file = new UploadedFile($tmp, 'book.xlsx', 'application/zip', null, true);
        $this->assertTrue($this->invokePrivate($controller, 'isSafeImportFileContent', [$file, 'xlsx']));

        @unlink($tmp);
    }

    public function test_validate_import_file_path_blocks_traversal()
    {
        $controller = $this->controller();

        $result = $this->invokePrivate($controller, 'validateImportFilePath', [
            base64_encode('../.env'),
        ]);
        $this->assertFalse($result['ok']);
        $this->assertSame(403, $result['status']);

        $result = $this->invokePrivate($controller, 'validateImportFilePath', [
            base64_encode('imports/../../.env'),
        ]);
        $this->assertFalse($result['ok']);
    }

    public function test_validate_import_file_path_rejects_non_spreadsheet_extension()
    {
        Storage::disk('local')->put('imports/test/evil.php', '<?php echo 1;');
        $controller = $this->controller();

        $result = $this->invokePrivate($controller, 'validateImportFilePath', [
            base64_encode('imports/test/evil.php'),
        ]);
        $this->assertFalse($result['ok']);
        $this->assertSame(403, $result['status']);

        Storage::disk('local')->delete('imports/test/evil.php');
    }

    public function test_post_do_upload_import_data_rejects_php_renamed_as_csv()
    {
        Config::set('crudbooster.IMPORT_UPLOAD_HARDENING_ENABLED', true);
        Storage::fake('local');

        $tmp = tempnam(sys_get_temp_dir(), 'imp');
        file_put_contents($tmp, "<?php echo 'pwned'; ?>");

        $upload = new UploadedFile($tmp, 'payload.csv', 'text/csv', null, true);

        $response = $this->withoutMiddleware()->post('/admin/users/do-upload-import-data', [
            'userfile' => $upload,
        ]);

        // Should redirect back with warning, not into import-data with a stored file.
        $this->assertTrue(in_array($response->getStatusCode(), [302, 200], true));
        $location = $response->headers->get('Location');
        if ($location) {
            $this->assertNotContains('import-data?file=', $location);
        }

        @unlink($tmp);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;

class TestImportUploadSecurity extends Command
{
    protected $signature = 'security:test-import-upload
                            {--module=users : Module path used in URL examples}
                            {--live : Also write/delete temp files under storage/app to prove storage destinations}
                            {--urls-only : Only print browser guidance, skip automated checks}';

    protected $description = 'Compare BEFORE (legacy) vs AFTER (hardened) import upload validation';

    public function handle()
    {
        $adminPath = trim(config('crudbooster.ADMIN_PATH', 'admin'), '/');
        $module = $this->option('module');
        $baseUrl = rtrim(config('app.url'), '/');
        $hardening = (bool) config('crudbooster.IMPORT_UPLOAD_HARDENING_ENABLED', true);

        $this->line('');
        $this->info('=== Import upload BEFORE vs AFTER ===');
        $this->line('CB_IMPORT_UPLOAD_HARDENING_ENABLED (current .env): '.($hardening ? 'true' : 'false'));
        $this->line('This command compares both code paths regardless of the .env flag.');
        $this->line('');

        if (! $this->option('urls-only')) {
            $failed = $this->runBeforeAfterChecks((bool) $this->option('live'));
            $this->line('');
            if ($failed > 0) {
                $this->error("Before/after checks: {$failed} unexpected result(s).");

                return 1;
            }
            $this->info('Before/after checks: all expected differences confirmed.');
            $this->line('');
        }

        $uploadUrl = $baseUrl.'/'.$adminPath.'/'.$module.'/do-upload-import-data';
        $browserUrl = $baseUrl.'/'.$adminPath.'/security-import-upload-test?module='.$module;

        $this->comment('Browser test page (log in as admin first):');
        $this->line('  '.$browserUrl);
        $this->line('');
        $this->comment('Direct upload endpoint:');
        $this->line('  '.$uploadUrl);
        $this->line('');
        $this->line('  1. CB_IMPORT_UPLOAD_HARDENING_ENABLED=false && php artisan config:clear');
        $this->line('     Open browser page → upload shell.csv → lands in storage/app/uploads/');
        $this->line('  2. CB_IMPORT_UPLOAD_HARDENING_ENABLED=true && php artisan config:clear');
        $this->line('     Same upload → rejected; valid CSV → storage/app/imports/');
        $this->line('');
        $this->comment('Automated:');
        $this->line('  docker compose exec php php artisan security:test-import-upload');
        $this->line('  docker compose exec php php artisan security:test-import-upload --live');
        $this->line('  docker compose exec php php tests/Manual/test_import_upload_manual.php');
        $this->line('');

        return 0;
    }

    private function runBeforeAfterChecks($live)
    {
        $controller = $this->makeController();
        $tmpdir = storage_path('app/_import_security_test');
        if (! is_dir($tmpdir)) {
            mkdir($tmpdir, 0755, true);
        }

        $cases = $this->buildCases($tmpdir);
        $failed = 0;

        $this->comment('Automated BEFORE (legacy extension-only) vs AFTER (MIME/fileinfo/content):');
        $this->line('');

        foreach ($cases as $i => $case) {
            $file = new UploadedFile($case['path'], $case['client_name'], $case['client_mime'], null, true);

            $before = $this->evaluateLegacy($file);
            $after = $this->evaluateHardened($controller, $file);

            $beforeOk = $before['accepted'] === $case['expect_before_accept'];
            $afterOk = $after['accepted'] === $case['expect_after_accept'];
            $ok = $beforeOk && $afterOk;

            $status = $ok ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
            $this->line(sprintf('<fg=yellow>Case %d: %s</> [%s]', $i + 1, $case['title'], $status));
            $this->line(sprintf(
                '  <fg=red>BEFORE:</> %s → %s',
                $before['accepted'] ? 'ACCEPT' : 'REJECT',
                $before['accepted'] ? $before['destination'] : $before['reason']
            ));
            $this->line(sprintf(
                '  <fg=green>AFTER:</>  %s → %s',
                $after['accepted'] ? 'ACCEPT' : 'REJECT',
                $after['accepted'] ? $after['destination'] : $after['reason']
            ));
            $this->line(sprintf(
                '  Expected: before=%s, after=%s',
                $case['expect_before_accept'] ? 'ACCEPT' : 'REJECT',
                $case['expect_after_accept'] ? 'ACCEPT' : 'REJECT'
            ));

            if ($live && $ok) {
                $liveResult = $this->runLiveStorageDemo($controller, $file, $case);
                $this->line('  <fg=cyan>LIVE:</>  '.$liveResult);
            }

            $this->line('');

            if (! $ok) {
                $failed++;
            }
        }

        // Cleanup generated fixtures (keep dir).
        foreach ($cases as $case) {
            if (! empty($case['cleanup']) && is_file($case['path'])) {
                @unlink($case['path']);
            }
        }

        if ($live) {
            $this->cleanupLiveDemoFiles();
        }

        return $failed;
    }

    private function buildCases($tmpdir)
    {
        $cases = [];

        // 1. Valid CSV — accepted both; destination changes.
        $csv = $tmpdir.DIRECTORY_SEPARATOR.'valid.csv';
        file_put_contents($csv, "name,email\nAlice,a@example.com\n");
        $cases[] = [
            'title' => 'Valid CSV',
            'path' => $csv,
            'client_name' => 'users.csv',
            'client_mime' => 'text/csv',
            'expect_before_accept' => true,
            'expect_after_accept' => true,
            'cleanup' => true,
        ];

        // 2. PHP payload renamed to .csv — legacy accepts, hardened rejects.
        $phpCsv = $tmpdir.DIRECTORY_SEPARATOR.'payload_as_csv.csv';
        file_put_contents($phpCsv, "<?php echo 'pwned'; ?>");
        $cases[] = [
            'title' => 'PHP payload renamed to .csv',
            'path' => $phpCsv,
            'client_name' => 'shell.csv',
            'client_mime' => 'text/csv',
            'expect_before_accept' => true,
            'expect_after_accept' => false,
            'cleanup' => true,
        ];

        // 3. Double extension shell.php.csv
        $double = $tmpdir.DIRECTORY_SEPARATOR.'payload_double.csv';
        file_put_contents($double, "<?php echo 'pwned'; ?>");
        $cases[] = [
            'title' => 'Double extension shell.php.csv',
            'path' => $double,
            'client_name' => 'shell.php.csv',
            'client_mime' => 'text/csv',
            'expect_before_accept' => true,
            'expect_after_accept' => false,
            'cleanup' => true,
        ];

        // 4. Random ZIP as .xlsx — legacy accepts, hardened rejects.
        if (class_exists('ZipArchive')) {
            $fakeXlsx = $tmpdir.DIRECTORY_SEPARATOR.'fake.xlsx';
            $zip = new \ZipArchive();
            $zip->open($fakeXlsx, \ZipArchive::CREATE);
            $zip->addFromString('readme.txt', 'not an ooxml package');
            $zip->close();
            $cases[] = [
                'title' => 'Random ZIP renamed to .xlsx',
                'path' => $fakeXlsx,
                'client_name' => 'payload.xlsx',
                'client_mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'expect_before_accept' => true,
                'expect_after_accept' => false,
                'cleanup' => true,
            ];

            // 5. Minimal OOXML xlsx — accepted both.
            $realXlsx = $tmpdir.DIRECTORY_SEPARATOR.'book.xlsx';
            $zip = new \ZipArchive();
            $zip->open($realXlsx, \ZipArchive::CREATE);
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
            $zip->addFromString('xl/workbook.xml', '<workbook/>');
            $zip->close();
            $cases[] = [
                'title' => 'Minimal OOXML .xlsx',
                'path' => $realXlsx,
                'client_name' => 'book.xlsx',
                'client_mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'expect_before_accept' => true,
                'expect_after_accept' => true,
                'cleanup' => true,
            ];
        }

        // 6. .php extension — rejected both (extension allowlist).
        $php = $tmpdir.DIRECTORY_SEPARATOR.'shell.php';
        file_put_contents($php, "<?php phpinfo(); ?>");
        $cases[] = [
            'title' => 'Raw .php extension',
            'path' => $php,
            'client_name' => 'shell.php',
            'client_mime' => 'application/x-httpd-php',
            'expect_before_accept' => false,
            'expect_after_accept' => false,
            'cleanup' => true,
        ];

        return $cases;
    }

    /**
     * Legacy path: extension allowlist only (mirrors postDoUploadImportDataLegacy).
     */
    private function evaluateLegacy(UploadedFile $file)
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
            return [
                'accepted' => false,
                'reason' => 'extension not in xls,xlsx,csv',
                'destination' => null,
            ];
        }

        return [
            'accepted' => true,
            'reason' => null,
            'destination' => 'storage/app/uploads/{userId}/'.date('Y-m').'/*.'.$ext.' (public /uploads route)',
        ];
    }

    /**
     * Hardened path: extension + fileinfo MIME + content checks (mirrors postDoUploadImportData).
     */
    private function evaluateHardened($controller, UploadedFile $file)
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
            return [
                'accepted' => false,
                'reason' => 'extension not in xls,xlsx,csv',
                'destination' => null,
            ];
        }

        $mime = $this->invoke($controller, 'detectImportFileMime', [$file]);
        if (! $this->invoke($controller, 'isAllowedImportMime', [$ext, $mime])) {
            return [
                'accepted' => false,
                'reason' => 'fileinfo MIME rejected ('.$mime.')',
                'destination' => null,
            ];
        }

        if (! $this->invoke($controller, 'isSafeImportFileContent', [$file, $ext])) {
            return [
                'accepted' => false,
                'reason' => 'content/magic-byte check rejected',
                'destination' => null,
            ];
        }

        return [
            'accepted' => true,
            'reason' => null,
            'destination' => 'storage/app/imports/{userId}/'.date('Y-m').'/*.'.$ext.' (not public)',
        ];
    }

    /**
     * Prove storage destinations by writing (and then deleting) under a dedicated test prefix.
     */
    private function runLiveStorageDemo($controller, UploadedFile $file, array $case)
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $stamp = 'security_test_'.md5($case['title'].microtime(true));

        $legacyDir = 'uploads/_security_test/'.$stamp;
        $hardenedDir = 'imports/_security_test/'.$stamp;

        $legacyWrote = false;
        $hardenedWrote = false;

        // Legacy would store on accept.
        if ($case['expect_before_accept']) {
            Storage::makeDirectory($legacyDir);
            $legacyWrote = (bool) Storage::putFileAs($legacyDir, $file, 'legacy.'.$ext);
        }

        // Hardened only stores when validation passes.
        $after = $this->evaluateHardened($controller, $file);
        if ($after['accepted']) {
            if (! is_dir(storage_path('app/imports'))) {
                @mkdir(storage_path('app/imports'), 0755, true);
            }
            Storage::makeDirectory($hardenedDir);
            $hardenedWrote = (bool) Storage::putFileAs($hardenedDir, $file, 'hardened.'.$ext);
        }

        $parts = [];
        $parts[] = $legacyWrote
            ? 'legacy wrote '.$legacyDir.'/legacy.'.$ext
            : 'legacy did not write';
        $parts[] = $hardenedWrote
            ? 'hardened wrote '.$hardenedDir.'/hardened.'.$ext
            : 'hardened did not write';

        // Immediate cleanup of this stamp.
        Storage::deleteDirectory($legacyDir);
        Storage::deleteDirectory($hardenedDir);

        return implode('; ', $parts).' (cleaned up)';
    }

    private function cleanupLiveDemoFiles()
    {
        foreach (['uploads/_security_test', 'imports/_security_test'] as $dir) {
            if (Storage::exists($dir)) {
                Storage::deleteDirectory($dir);
            }
        }
        $fixtureDir = storage_path('app/_import_security_test');
        if (is_dir($fixtureDir)) {
            File::deleteDirectory($fixtureDir);
        }
    }

    private function makeController()
    {
        return new class extends \crocodicstudio\crudbooster\controllers\CBController {
            public function cbInit()
            {
                $this->table = 'cms_privileges';
            }
        };
    }

    private function invoke($object, $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }
}

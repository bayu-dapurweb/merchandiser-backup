<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use crocodicstudio\crudbooster\helpers\CRUDBooster as CB;

class SecurityImportUploadBrowserController extends Controller
{
    private function fixtures()
    {
        return [
            'valid.csv' => [
                'label' => 'Valid CSV (should succeed both modes)',
                'filename' => 'users.csv',
                'content' => "name,email\nAlice,alice@example.com\nBob,bob@example.com\n",
                'expect_legacy' => 'ACCEPT → storage/app/uploads/...',
                'expect_hardened' => 'ACCEPT → storage/app/imports/...',
            ],
            'shell.csv' => [
                'label' => 'PHP payload renamed to .csv (main attack)',
                'filename' => 'shell.csv',
                'content' => "<?php echo 'pwned'; ?>",
                'expect_legacy' => 'ACCEPT → storage/app/uploads/... (dangerous)',
                'expect_hardened' => 'REJECT — warning, no file stored',
            ],
            'shell.php.csv' => [
                'label' => 'Double extension shell.php.csv',
                'filename' => 'shell.php.csv',
                'content' => "<?php echo 'pwned'; ?>",
                'expect_legacy' => 'ACCEPT → storage/app/uploads/...',
                'expect_hardened' => 'REJECT',
            ],
            'fake.xlsx' => [
                'label' => 'Random ZIP renamed to .xlsx',
                'filename' => 'payload.xlsx',
                'content' => null,
                'expect_legacy' => 'ACCEPT → storage/app/uploads/...',
                'expect_hardened' => 'REJECT — not OOXML',
            ],
        ];
    }

    private function testPageUrl($module = 'users')
    {
        $adminPath = trim(config('crudbooster.ADMIN_PATH', 'admin'), '/');

        return url($adminPath.'/security-import-upload-test').'?module='.urlencode($module);
    }

    public function index(Request $request)
    {
        $adminPath = trim(config('crudbooster.ADMIN_PATH', 'admin'), '/');
        $module = $request->get('module', 'users');
        $hardening = (bool) config('crudbooster.IMPORT_UPLOAD_HARDENING_ENABLED', true);
        $uploadUrl = url($adminPath.'/security-import-upload-test/upload');
        $pageUrl = url($adminPath.'/security-import-upload-test');

        $this->ensureFixtures();

        return response()->view('security.import_upload_browser', [
            'hardening' => $hardening,
            'module' => $module,
            'adminPath' => $adminPath,
            'uploadUrl' => $uploadUrl,
            'realEndpoint' => url($adminPath.'/'.$module.'/do-upload-import-data'),
            'pageUrl' => $pageUrl,
            'fixtures' => $this->fixtures(),
            'storageSnapshot' => $this->storageSnapshot(),
            'csrf' => csrf_token(),
            'flashMessage' => session('message'),
            'flashType' => session('message_type', 'info'),
            'flashDetail' => session('message_detail'),
        ]);
    }

    /**
     * Runs the same validation/storage rules as CBController::postDoUploadImportData,
     * then always redirects back to this test page.
     */
    public function upload(Request $request)
    {
        $module = $request->input('module', 'users');
        $returnUrl = $this->testPageUrl($module);
        $hardening = (bool) config('crudbooster.IMPORT_UPLOAD_HARDENING_ENABLED', true);
        $mode = $hardening ? 'AFTER (hardened ON)' : 'BEFORE (legacy OFF)';

        if (! $request->hasFile('userfile')) {
            return redirect($returnUrl)->with([
                'message' => 'No file selected.',
                'message_type' => 'warning',
                'message_detail' => $mode,
            ]);
        }

        $file = $request->file('userfile');
        $clientName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $userId = CB::myId() ?: 0;

        $controller = new class extends \crocodicstudio\crudbooster\controllers\CBController {
            public function cbInit()
            {
                $this->table = 'cms_users';
            }
        };

        if (! $hardening) {
            // Legacy: extension allowlist only → storage/app/uploads
            $validator = Validator::make(['extension' => $ext], ['extension' => 'in:xls,xlsx,csv']);
            if ($validator->fails()) {
                return redirect($returnUrl)->with([
                    'message' => 'REJECTED (legacy): '.$clientName.' — extension must be xls, xlsx, or csv.',
                    'message_type' => 'warning',
                    'message_detail' => $mode,
                ]);
            }

            $filePath = 'uploads/'.$userId.'/'.date('Y-m');
            Storage::makeDirectory($filePath);
            $filename = md5(str_random(5)).'.'.$ext;
            Storage::putFileAs($filePath, $file, $filename);
            $stored = $filePath.'/'.$filename;

            return redirect($returnUrl)->with([
                'message' => 'ACCEPTED (legacy): '.$clientName.' → storage/app/'.$stored,
                'message_type' => 'success',
                'message_detail' => $mode.' · public /uploads route can serve this path',
            ]);
        }

        // Hardened: extension + fileinfo + content → storage/app/imports
        // Avoid Laravel "mimes:" — CSV often sniffed as text/plain and false-rejected.
        $maxKb = max((int) config('crudbooster.DEFEAULT_UPLOAD_MAX_SIZE', 1000), 20480);
        $validator = Validator::make($request->all(), [
            'userfile' => 'required|file|max:'.$maxKb,
        ]);
        if ($validator->fails()) {
            return redirect($returnUrl)->with([
                'message' => 'REJECTED (hardened): '.$clientName.' — '.implode(' ', $validator->errors()->all()),
                'message_type' => 'warning',
                'message_detail' => $mode,
            ]);
        }

        if (! in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
            return redirect($returnUrl)->with([
                'message' => 'REJECTED (hardened): '.$clientName.' — extension must be xls, xlsx, or csv.',
                'message_type' => 'warning',
                'message_detail' => $mode,
            ]);
        }

        $mime = $this->invoke($controller, 'detectImportFileMime', [$file]);
        $mimeOk = $this->invoke($controller, 'isAllowedImportMime', [$ext, $mime]);
        $contentOk = $this->invoke($controller, 'isSafeImportFileContent', [$file, $ext]);

        if (! $mimeOk || ! $contentOk) {
            $reason = ! $mimeOk
                ? 'fileinfo MIME rejected ('.($mime ?: 'unknown').')'
                : 'content/magic-byte check rejected';

            return redirect($returnUrl)->with([
                'message' => 'REJECTED (hardened): '.$clientName.' — '.$reason,
                'message_type' => 'warning',
                'message_detail' => $mode.' · file was not stored',
            ]);
        }

        $importsRoot = storage_path('app/imports');
        if (! is_dir($importsRoot)) {
            @mkdir($importsRoot, 0755, true);
        }

        $filePath = 'imports/'.$userId.'/'.date('Y-m');
        Storage::makeDirectory($filePath);
        $filename = md5(str_random(5)).'.'.$ext;
        Storage::putFileAs($filePath, $file, $filename);
        $stored = $filePath.'/'.$filename;

        return redirect($returnUrl)->with([
            'message' => 'ACCEPTED (hardened): '.$clientName.' → storage/app/'.$stored,
            'message_type' => 'success',
            'message_detail' => $mode.' · not served by public /uploads route',
        ]);
    }

    public function download($key)
    {
        $fixtures = $this->fixtures();
        if (! isset($fixtures[$key])) {
            abort(404);
        }

        $this->ensureFixtures();
        $path = $this->fixturePath($key);
        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, $fixtures[$key]['filename']);
    }

    public function refresh(Request $request)
    {
        $module = $request->get('module', 'users');

        return redirect($this->testPageUrl($module))->with([
            'message' => 'Storage snapshot refreshed.',
            'message_type' => 'info',
        ]);
    }

    private function invoke($object, $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }

    private function ensureFixtures()
    {
        $dir = storage_path('app/_import_security_browser');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($this->fixtures() as $key => $fixture) {
            $path = $this->fixturePath($key);
            if (is_file($path)) {
                continue;
            }

            if ($key === 'fake.xlsx') {
                if (! class_exists('ZipArchive')) {
                    continue;
                }
                $zip = new \ZipArchive();
                $zip->open($path, \ZipArchive::CREATE);
                $zip->addFromString('readme.txt', 'not an ooxml package');
                $zip->close();
                continue;
            }

            file_put_contents($path, $fixture['content']);
        }
    }

    private function fixturePath($key)
    {
        return storage_path('app/_import_security_browser/'.$key);
    }

    private function storageSnapshot()
    {
        $roots = [
            'uploads' => storage_path('app/uploads'),
            'imports' => storage_path('app/imports'),
        ];

        $out = [];
        foreach ($roots as $label => $root) {
            $files = [];
            if (is_dir($root)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if (! $file->isFile()) {
                        continue;
                    }
                    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                    if (strpos($rel, '_security_test/') === 0) {
                        continue;
                    }
                    $files[] = [
                        'path' => $label.'/'.$rel,
                        'mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                        'size' => $file->getSize(),
                    ];
                }
            }
            usort($files, function ($a, $b) {
                return strcmp($b['mtime'], $a['mtime']);
            });
            $out[$label] = array_slice($files, 0, 15);
        }

        return $out;
    }
}

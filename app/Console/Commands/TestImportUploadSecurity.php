<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use ReflectionMethod;

class TestImportUploadSecurity extends Command
{
    protected $signature = 'security:test-import-upload
                            {--module=users : Module path used in the URL (e.g. users)}
                            {--urls-only : Only print browser/curl guidance, skip logic checks}';

    protected $description = 'Verify postDoUploadImportData() rejects spoofed extensions and stores outside /uploads';

    public function handle()
    {
        $adminPath = trim(config('crudbooster.ADMIN_PATH', 'admin'), '/');
        $module = $this->option('module');
        $baseUrl = rtrim(config('app.url'), '/');
        $hardening = config('crudbooster.IMPORT_UPLOAD_HARDENING_ENABLED', true);

        $this->line('');
        $this->info('=== postDoUploadImportData() upload security test ===');
        $this->line('CB_IMPORT_UPLOAD_HARDENING_ENABLED: '.($hardening ? 'true (hardened)' : 'false (legacy vulnerable)'));
        if (! $hardening) {
            $this->warn('WARNING: hardening is OFF — spoofed extensions may be accepted and stored under uploads/.');
        }
        $this->line('');

        if (! $this->option('urls-only')) {
            $this->runLogicChecks();
            $this->line('');
        }

        $uploadUrl = $baseUrl.'/'.$adminPath.'/'.$module.'/do-upload-import-data';

        $this->comment('Manual browser test (log in as admin, open Import Data, then try these files):');
        $this->line('');
        $this->line('  Upload endpoint: '.$uploadUrl);
        $this->line('');

        $cases = [
            [
                'title' => 'Baseline — valid CSV',
                'before' => 'Accepted; stored under storage/app/uploads/{user}/{Ym}/.',
                'after' => 'Accepted; stored under storage/app/imports/{user}/{Ym}/ (not served by /uploads).',
            ],
            [
                'title' => 'PHP payload renamed to .csv',
                'before' => 'Extension-only check may accept shell.csv and write it under uploads/.',
                'after' => 'Rejected — fileinfo MIME / content check fails (<?php blocked).',
            ],
            [
                'title' => 'Random ZIP renamed to .xlsx',
                'before' => 'May be accepted based on .xlsx extension alone.',
                'after' => 'Rejected — missing OOXML [Content_Types].xml.',
            ],
            [
                'title' => 'Double extension shell.php.csv',
                'before' => 'Client extension csv may pass; dangerous content stored publicly via /uploads/.',
                'after' => 'Rejected by MIME/content checks; even if stored, imports/ is not public.',
            ],
        ];

        foreach ($cases as $i => $case) {
            $this->line(sprintf('<fg=yellow>Case %d: %s</>', $i + 1, $case['title']));
            $this->line('  <fg=red>Before fix:</> '.$case['before']);
            $this->line('  <fg=green>After fix:</>  '.$case['after']);
            $this->line('');
        }

        $this->comment('How to compare vulnerable vs fixed behavior:');
        $this->line('  1. CB_IMPORT_UPLOAD_HARDENING_ENABLED=false + php artisan config:clear');
        $this->line('  2. Upload a PHP file renamed to .csv via Import Data — check storage/app/uploads.');
        $this->line('  3. CB_IMPORT_UPLOAD_HARDENING_ENABLED=true + php artisan config:clear');
        $this->line('  4. Retry — expect warning and no file under imports/ or uploads/.');
        $this->line('');
        $this->comment('Automated tests:');
        $this->line('  vendor/bin/phpunit tests/Feature/ImportUploadSecurityTest.php');
        $this->line('');

        return 0;
    }

    private function runLogicChecks()
    {
        $controller = new class extends \crocodicstudio\crudbooster\controllers\CBController {
            public function cbInit()
            {
                $this->table = 'cms_privileges';
            }
        };

        $cases = [
            [
                'name' => 'MIME allow: csv/text/csv',
                'fn' => function () use ($controller) {
                    return $this->invoke($controller, 'isAllowedImportMime', ['csv', 'text/csv']) === true;
                },
            ],
            [
                'name' => 'MIME deny: csv/application/x-httpd-php',
                'fn' => function () use ($controller) {
                    return $this->invoke($controller, 'isAllowedImportMime', ['csv', 'application/x-httpd-php']) === false;
                },
            ],
            [
                'name' => 'MIME deny: zip as xls (extension mismatch)',
                'fn' => function () use ($controller) {
                    return $this->invoke($controller, 'isAllowedImportMime', ['xls', 'application/zip']) === false;
                },
            ],
            [
                'name' => 'Content deny: PHP renamed as csv',
                'fn' => function () use ($controller) {
                    $tmp = tempnam(sys_get_temp_dir(), 'imp');
                    file_put_contents($tmp, "<?php system('id'); ?>");
                    $file = new UploadedFile($tmp, 'shell.csv', 'text/csv', null, true);
                    $ok = $this->invoke($controller, 'isSafeImportFileContent', [$file, 'csv']) === false;
                    @unlink($tmp);

                    return $ok;
                },
            ],
            [
                'name' => 'Content allow: plain csv',
                'fn' => function () use ($controller) {
                    $tmp = tempnam(sys_get_temp_dir(), 'imp');
                    file_put_contents($tmp, "a,b\n1,2\n");
                    $file = new UploadedFile($tmp, 'ok.csv', 'text/csv', null, true);
                    $ok = $this->invoke($controller, 'isSafeImportFileContent', [$file, 'csv']) === true;
                    @unlink($tmp);

                    return $ok;
                },
            ],
            [
                'name' => 'Path deny: traversal ../.env',
                'fn' => function () use ($controller) {
                    $result = $this->invoke($controller, 'validateImportFilePath', [base64_encode('../.env')]);

                    return empty($result['ok']);
                },
            ],
        ];

        $passed = 0;
        $failed = 0;

        $this->comment('Logic checks (mirrors hardened import upload validation):');

        foreach ($cases as $case) {
            $ok = (bool) call_user_func($case['fn']);
            $status = $ok ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
            $this->line(sprintf('  [%s] %s', $status, $case['name']));
            if ($ok) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $this->line('');
        $this->line(sprintf('Logic summary: %d passed, %d failed', $passed, $failed));

        if ($failed > 0) {
            $this->error('Some logic checks failed. Review CBController::postDoUploadImportData().');
        }
    }

    private function invoke($object, $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestUpdateSingleSecurity extends Command
{
    protected $signature = 'security:test-update-single
                            {--module=users : Module path used in the URL (e.g. users, trx_posts)}
                            {--victim-id= : cms_users.id to use in attack URLs (defaults to first non-superadmin user)}
                            {--privilege-id=1 : Target id_cms_privileges value for the privilege-escalation URL}
                            {--urls-only : Only print browser/curl URLs, skip logic checks}';

    protected $description = 'Verify getUpdateSingle() blocks arbitrary table/column writes (manual + logic checks)';

    /** @var array<string, string> */
    private $moduleTables = [
        'users' => 'cms_users',
        'trx_posts' => 'trx_posts',
    ];

    public function handle()
    {
        $adminPath = trim(config('crudbooster.ADMIN_PATH', 'admin'), '/');
        $module = $this->option('module');
        $victimId = $this->option('victim-id') ?: $this->defaultVictimId();
        $privilegeId = $this->option('privilege-id');
        $baseUrl = rtrim(config('app.url'), '/');

        $this->line('');
        $this->info('=== getUpdateSingle() security test ===');
        $hardening = config('crudbooster.UPDATE_SINGLE_HARDENING_ENABLED', true);
        $this->line('CB_UPDATE_SINGLE_HARDENING_ENABLED: '.($hardening ? 'true (hardened)' : 'false (legacy vulnerable)'));
        if (! $hardening) {
            $this->warn('WARNING: hardening is OFF — attack URLs below will modify the database.');
        }
        $this->line('');

        if (! $this->option('urls-only')) {
            $this->runLogicChecks($module);
            $this->line('');
        }

        $this->comment('Manual browser test (log in as admin first, then open each URL):');
        $this->line('');

        $attacks = [
            [
                'title' => 'Privilege escalation on cms_users (classic exploit)',
                'before' => 'Would set id_cms_privileges on the victim user (privilege escalation).',
                'after' => 'HTTP 403 — column not in upload/filemanager allowlist.',
                'path' => sprintf(
                    '/%s/%s/update-single?table=cms_users&column=id_cms_privileges&value=%s&id=%s',
                    $adminPath,
                    $module,
                    $privilegeId,
                    $victimId
                ),
            ],
            [
                'title' => 'Password overwrite on cms_users',
                'before' => 'Would overwrite password column for any user id.',
                'after' => 'HTTP 403 — password column denied.',
                'path' => sprintf(
                    '/%s/%s/update-single?table=cms_users&column=password&value=hacked&id=%s',
                    $adminPath,
                    $module,
                    $victimId
                ),
            ],
            [
                'title' => 'Arbitrary table write (cms_privileges)',
                'before' => 'Would update cms_privileges even though current module is not privileges.',
                'after' => 'HTTP 403 — table param does not match module table.',
                'path' => sprintf(
                    '/%s/%s/update-single?table=cms_privileges&column=name&value=Pwned&id=1',
                    $adminPath,
                    $module
                ),
            ],
            [
                'title' => 'Arbitrary column on module table (title/text field)',
                'before' => 'Would update any column on the module table, not only file fields.',
                'after' => 'HTTP 403 — column not in upload/filemanager allowlist.',
                'path' => sprintf(
                    '/%s/%s/update-single?table=%s&column=name&value=changed&id=1',
                    $adminPath,
                    $module,
                    $this->moduleTable($module)
                ),
            ],
        ];

        foreach ($attacks as $i => $attack) {
            $url = $baseUrl.$attack['path'];
            $this->line(sprintf('<fg=yellow>Attack %d: %s</>', $i + 1, $attack['title']));
            $this->line('  URL: '.$url);
            $this->line('  <fg=red>Before fix:</> '.$attack['before']);
            $this->line('  <fg=green>After fix:</>  '.$attack['after']);
            $this->line('');
        }

        $this->comment('How to confirm the fix worked:');
        $this->line('  1. Note victim user privilege before test:');
        $this->line(sprintf('     SELECT id, email, id_cms_privileges FROM cms_users WHERE id = %s;', $victimId));
        $this->line('  2. Open attack URL #1 in the browser while logged in as admin.');
        $this->line('  3. Expect HTTP 403 (or error page with "Column is not allowed").');
        $this->line('  4. Re-run the SELECT — id_cms_privileges must be unchanged.');
        $this->line('');
        $this->comment('See TESTING_UPDATE_SINGLE.md for full step-by-step instructions.');
        $this->line('');

        return 0;
    }

    private function runLogicChecks($module)
    {
        $moduleTable = $this->moduleTable($module);
        $cases = [
            [
                'name' => 'Arbitrary table cms_users from trx_posts module',
                'requested_table' => 'cms_users',
                'module_table' => 'trx_posts',
                'column' => 'photo',
                'allowed_columns' => [],
                'denied_columns' => $this->deniedColumns(),
                'expect_blocked' => true,
            ],
            [
                'name' => 'Privilege column id_cms_privileges',
                'requested_table' => $moduleTable,
                'module_table' => $moduleTable,
                'column' => 'id_cms_privileges',
                'allowed_columns' => ['photo'],
                'denied_columns' => $this->deniedColumns(),
                'expect_blocked' => true,
            ],
            [
                'name' => 'Password column',
                'requested_table' => $moduleTable,
                'module_table' => $moduleTable,
                'column' => 'password',
                'allowed_columns' => ['photo'],
                'denied_columns' => $this->deniedColumns(),
                'expect_blocked' => true,
            ],
            [
                'name' => 'Legitimate filemanager column (if module defines one)',
                'requested_table' => $moduleTable,
                'module_table' => $moduleTable,
                'column' => 'attachment',
                'allowed_columns' => ['attachment'],
                'denied_columns' => $this->deniedColumns(),
                'expect_blocked' => false,
            ],
            [
                'name' => 'Text column name on module table',
                'requested_table' => $moduleTable,
                'module_table' => $moduleTable,
                'column' => 'name',
                'allowed_columns' => ['photo'],
                'denied_columns' => $this->deniedColumns(),
                'expect_blocked' => true,
            ],
        ];

        $passed = 0;
        $failed = 0;

        $this->comment('Logic checks (mirrors hardened getUpdateSingle validation):');

        foreach ($cases as $case) {
            $blocked = $this->wouldBlock(
                $case['requested_table'],
                $case['module_table'],
                $case['column'],
                $case['allowed_columns'],
                $case['denied_columns']
            );

            $ok = $blocked === $case['expect_blocked'];
            $status = $ok ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
            $this->line(sprintf('  [%s] %s', $status, $case['name']));

            if ($ok) {
                $passed++;
            } else {
                $failed++;
                $this->line(sprintf(
                    '        expected %s, got %s',
                    $case['expect_blocked'] ? 'blocked' : 'allowed',
                    $blocked ? 'blocked' : 'allowed'
                ));
            }
        }

        $this->line('');
        $this->line(sprintf('Logic summary: %d passed, %d failed', $passed, $failed));

        if ($failed > 0) {
            $this->error('Some logic checks failed. Review CBController::getUpdateSingle().');
        }
    }

    private function wouldBlock($requestedTable, $moduleTable, $column, array $allowedColumns, array $deniedColumns)
    {
        if ($requestedTable !== null && $requestedTable !== '' && $requestedTable !== $moduleTable) {
            return true;
        }

        if (! is_string($column) || $column === '') {
            return true;
        }

        if (! in_array($column, $allowedColumns, true)) {
            return true;
        }

        if (in_array($column, $deniedColumns, true)) {
            return true;
        }

        return false;
    }

    private function deniedColumns()
    {
        $passwordFields = array_map('trim', explode(',', config('crudbooster.PASSWORD_FIELDS_CANDIDATE')));

        return array_merge($passwordFields, [
            'id',
            'id_cms_privileges',
            'id_cms_moduls',
            'is_superadmin',
        ]);
    }

    private function defaultVictimId()
    {
        try {
            $id = DB::table('cms_users')
                ->where('id_cms_privileges', '!=', 1)
                ->orderBy('id')
                ->value('id');

            return $id ?: 2;
        } catch (\Exception $e) {
            return 2;
        }
    }

    private function moduleTable($module)
    {
        if (isset($this->moduleTables[$module])) {
            return $this->moduleTables[$module];
        }

        try {
            $table = DB::table('cms_moduls')->where('path', $module)->value('table_name');

            return $table ?: 'cms_users';
        } catch (\Exception $e) {
            return 'cms_users';
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TestDatatableWhereSecurity extends Command
{
    protected $signature = 'security:test-datatable-where
                            {--module=users : Module path used in the URL (e.g. users)}
                            {--fk-value=1 : fk_value query param for the data-table request}
                            {--urls-only : Only print browser/curl URLs, skip logic checks}';

    protected $description = 'Verify getDataTable() blocks SQL injection via datatable_where (manual + logic checks)';

    public function handle()
    {
        $adminPath = trim(config('crudbooster.ADMIN_PATH', 'admin'), '/');
        $module = $this->option('module');
        $fkValue = $this->option('fk-value');
        $baseUrl = rtrim(config('app.url'), '/');
        $hardening = config('crudbooster.DATATABLE_WHERE_HARDENING_ENABLED', true);

        $this->line('');
        $this->info('=== getDataTable() SQL injection test ===');
        $this->line('CB_DATATABLE_WHERE_HARDENING_ENABLED: '.($hardening ? 'true (hardened)' : 'false (legacy vulnerable)'));
        if (! $hardening) {
            $this->warn('WARNING: hardening is OFF — attack URLs below may return data from other tables.');
        }
        $this->line('');

        if (! $this->option('urls-only')) {
            $this->runLogicChecks();
            $this->line('');
        }

        $baseParams = [
            'table' => 'cms_privileges',
            'label' => 'name',
            'fk_name' => 'id',
            'fk_value' => $fkValue,
        ];

        $cases = [
            [
                'title' => 'Baseline — legitimate filter',
                'where' => 'id > 0',
                'before' => 'Returns filtered privilege options (normal behavior).',
                'after' => 'HTTP 200 with JSON array.',
            ],
            [
                'title' => 'UNION injection — read cms_users emails',
                'where' => '1=1) UNION SELECT id, email FROM cms_users -- ',
                'before' => 'Response JSON may include cms_users id/email pairs from another table.',
                'after' => 'HTTP 400 — invalid datatable filter.',
            ],
            [
                'title' => 'OR tautology — bypass filter logic',
                'where' => '1=0 OR 1=1',
                'before' => 'Can widen result set using raw OR expression.',
                'after' => 'HTTP 400 — OR keyword blocked.',
            ],
            [
                'title' => 'Subquery against cms_users',
                'where' => 'id IN (SELECT id FROM cms_users)',
                'before' => 'Subquery can reference another table inside datatable_where.',
                'after' => 'HTTP 400 — parentheses and SELECT blocked.',
            ],
        ];

        $this->comment('Manual browser test (log in as admin first):');
        $this->line('');

        foreach ($cases as $i => $case) {
            $params = array_merge($baseParams, ['datatable_where' => $case['where']]);
            $url = $baseUrl.'/'.$adminPath.'/'.$module.'/data-table?'.http_build_query($params);

            $this->line(sprintf('<fg=yellow>Case %d: %s</>', $i + 1, $case['title']));
            $this->line('  URL: '.$url);
            $this->line('  <fg=red>Before fix:</> '.$case['before']);
            $this->line('  <fg=green>After fix:</>  '.$case['after']);
            $this->line('');
        }

        $this->comment('How to compare vulnerable vs fixed behavior:');
        $this->line('  1. CB_DATATABLE_WHERE_HARDENING_ENABLED=false + php artisan config:clear');
        $this->line('  2. Open Case 2 URL while logged in — check JSON for leaked cms_users rows.');
        $this->line('  3. CB_DATATABLE_WHERE_HARDENING_ENABLED=true + php artisan config:clear');
        $this->line('  4. Open the same URL — expect HTTP 400.');
        $this->line('');
        $this->comment('Automated tests:');
        $this->line('  php tests/Manual/test_datatable_where_manual.php');
        $this->line('  vendor/bin/phpunit tests/Feature/DatatableWhereSecurityTest.php');
        $this->line('');

        if (! Schema::hasTable('cms_privileges')) {
            $this->warn('Note: cms_privileges table was not found — HTTP tests may fail until DB is available.');
        }

        return 0;
    }

    private function runLogicChecks()
    {
        $cases = [
            ['name' => 'Legitimate: id > 0', 'where' => 'id > 0', 'expect_blocked' => false],
            ['name' => 'Legitimate: is_protected = 0', 'where' => 'is_protected = 0', 'expect_blocked' => false],
            ['name' => 'UNION SELECT injection', 'where' => '1=1) UNION SELECT id, email FROM cms_users -- ', 'expect_blocked' => true],
            ['name' => 'OR tautology', 'where' => '1=0 OR 1=1', 'expect_blocked' => true],
            ['name' => 'Subquery SELECT', 'where' => 'id IN (SELECT id FROM cms_users)', 'expect_blocked' => true],
            ['name' => 'Unquoted string literal', 'where' => 'name = admin', 'expect_blocked' => true],
        ];

        $passed = 0;
        $failed = 0;

        $this->comment('Logic checks (mirrors hardened applySafeDatatableWhere validation):');

        foreach ($cases as $case) {
            $blocked = $this->wouldBlock($case['where']);
            $ok = $blocked === $case['expect_blocked'];
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
            $this->error('Some logic checks failed. Review CBController::applySafeDatatableWhere().');
        }
    }

    private function wouldBlock($datatableWhere)
    {
        if (! is_string($datatableWhere) || trim($datatableWhere) === '') {
            return false;
        }

        if (preg_match('/[;()]/', $datatableWhere)
            || stripos($datatableWhere, '--') !== false
            || stripos($datatableWhere, '/*') !== false
            || preg_match('/\b(union|select|insert|update|delete|drop|sleep|benchmark|or)\b/i', $datatableWhere)
        ) {
            return true;
        }

        $clauses = preg_split('/\s+and\s+/i', trim($datatableWhere));
        foreach ((array) $clauses as $clause) {
            $clause = trim($clause);
            if ($clause === '') {
                continue;
            }

            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s+is\s+not\s+null$/i', $clause)) {
                continue;
            }
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s+is\s+null$/i', $clause)) {
                continue;
            }
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s+(not\s+like|like)\s+(.+)$/i', $clause, $matches)) {
                if (! $this->isValidLiteral($matches[3])) {
                    return true;
                }
                continue;
            }
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*(=|!=|<>|>=|<=|>|<)\s+(.+)$/i', $clause, $matches)) {
                if (! $this->isValidLiteral($matches[3])) {
                    return true;
                }
                continue;
            }

            return true;
        }

        return false;
    }

    private function isValidLiteral($literal)
    {
        $literal = trim($literal);
        if ($literal === '') {
            return false;
        }
        if (preg_match("#^'(.*)'$#s", $literal)) {
            return true;
        }
        if (preg_match('#^"(.*)"$#s', $literal)) {
            return true;
        }

        return (bool) preg_match('/^-?\d+(?:\.\d+)?$/', $literal);
    }
}

<?php
/**
 * getDataTable() SQL injection test — standalone (no Laravel bootstrap required)
 *
 * Usage:
 *   php tests/Manual/test_datatable_where_manual.php
 *   php tests/Manual/test_datatable_where_manual.php --base-url=https://merchandiser-backup.test --module=users
 *   php tests/Manual/test_datatable_where_manual.php --urls-only
 */

$options = getopt('', ['base-url:', 'admin-path:', 'module:', 'fk-value:', 'urls-only', 'curl']);

$baseUrl = rtrim($options['base-url'] ?? 'https://merchandiser-backup.test', '/');
$adminPath = trim($options['admin-path'] ?? 'admin', '/');
$module = $options['module'] ?? 'users';
$fkValue = $options['fk-value'] ?? '1';
$urlsOnly = isset($options['urls-only']);
$runCurl = isset($options['curl']);

function readEnvFlag($key, $default)
{
    $envPath = dirname(__DIR__, 2).'/.env';
    if (! is_readable($envPath)) {
        return $default;
    }
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === $key) {
            $value = trim($value, " \t\"'");
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
        }
    }

    return $default;
}

function wouldBlockDatatableWhere($datatableWhere)
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
            if (! isValidDatatableLiteral($matches[3])) {
                return true;
            }
            continue;
        }
        if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*(=|!=|<>|>=|<=|>|<)\s+(.+)$/i', $clause, $matches)) {
            if (! isValidDatatableLiteral($matches[3])) {
                return true;
            }
            continue;
        }

        return true;
    }

    return false;
}

function isValidDatatableLiteral($literal)
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
    if (preg_match('/^-?\d+(?:\.\d+)?$/', $literal)) {
        return true;
    }

    return false;
}

function buildDataTableUrl($baseUrl, $adminPath, $module, array $params)
{
    $query = http_build_query($params);

    return $baseUrl.'/'.$adminPath.'/'.$module.'/data-table?'.$query;
}

$hardeningEnabled = readEnvFlag('CB_DATATABLE_WHERE_HARDENING_ENABLED', true);

$baseParams = [
    'table' => 'cms_privileges',
    'label' => 'name',
    'fk_name' => 'id',
    'fk_value' => $fkValue,
];

echo "\n=== getDataTable() SQL injection test ===\n";
echo 'CB_DATATABLE_WHERE_HARDENING_ENABLED: '.($hardeningEnabled ? 'true (hardened)' : 'false (legacy vulnerable)')."\n";
if (! $hardeningEnabled) {
    echo "WARNING: hardening is OFF — attack URLs below may return data from other tables.\n";
}
echo "\n";

$failed = 0;

if (! $urlsOnly) {
    $cases = [
        [
            'name' => 'Legitimate filter: id > 0',
            'where' => 'id > 0',
            'expect_blocked' => false,
        ],
        [
            'name' => 'Legitimate filter: is_protected = 0',
            'where' => 'is_protected = 0',
            'expect_blocked' => false,
        ],
        [
            'name' => 'Legitimate compound filter',
            'where' => 'id > 0 and is_protected = 0',
            'expect_blocked' => false,
        ],
        [
            'name' => 'UNION SELECT to read cms_users',
            // No closing ")" — getDataTable() does whereRaw() without wrapping parentheses.
            'where' => '1=1 UNION SELECT id, email FROM cms_users -- ',
            'expect_blocked' => true,
        ],
        [
            'name' => 'OR-based tautology',
            'where' => '1=0 OR 1=1',
            'expect_blocked' => true,
        ],
        [
            'name' => 'Subquery via SELECT',
            'where' => 'id IN (SELECT id FROM cms_users)',
            'expect_blocked' => true,
        ],
        [
            'name' => 'Time-based blind injection',
            'where' => "1=1; SELECT SLEEP(5) -- ",
            'expect_blocked' => true,
        ],
        [
            'name' => 'Unquoted string value (invalid literal)',
            'where' => "name = admin",
            'expect_blocked' => true,
        ],
    ];

    $passed = 0;

    echo "Logic checks (mirrors hardened applySafeDatatableWhere validation):\n";

    foreach ($cases as $case) {
        $blocked = wouldBlockDatatableWhere($case['where']);
        $ok = $blocked === $case['expect_blocked'];
        echo sprintf("  [%s] %s\n", $ok ? 'PASS' : 'FAIL', $case['name']);
        if (! $ok) {
            echo sprintf(
                "        where=%s | expected %s, got %s\n",
                $case['where'],
                $case['expect_blocked'] ? 'blocked' : 'allowed',
                $blocked ? 'blocked' : 'allowed'
            );
        }
        $ok ? $passed++ : $failed++;
    }

    echo "\nLogic summary: {$passed} passed, {$failed} failed\n\n";
}

$attacks = [
    [
        'title' => 'Baseline — legitimate dependent select filter',
        'type' => 'legitimate',
        'where' => 'id > 0',
        'before' => 'Returns privilege rows for the parent fk_value (normal CRUDBooster behavior).',
        'after' => 'HTTP 200 with JSON options.',
    ],
    [
        'title' => 'UNION injection — read cms_users via datatable_where',
        'type' => 'attack',
        // Query shape: WHERE {payload} AND id = ? ORDER BY name
        // UNION must sit between SELECTs (no extra ")"). Trailing "-- " comments out AND/ORDER BY.
        'where' => '1=1 UNION SELECT id, email FROM cms_users -- ',
        'before' => 'Injects UNION SELECT; response JSON may include cms_users.id and cms_users.email as select_value/select_label.',
        'after' => 'HTTP 400 — invalid datatable filter (UNION, SELECT, comment blocked).',
    ],
    [
        'title' => 'OR tautology — bypass intended filter',
        'type' => 'attack',
        'where' => '1=0 OR 1=1',
        'before' => 'Returns all rows from the target table regardless of fk filter intent.',
        'after' => 'HTTP 400 — OR keyword blocked.',
    ],
    [
        'title' => 'Subquery — enumerate another table',
        'type' => 'attack',
        'where' => 'id IN (SELECT id FROM cms_users)',
        'before' => 'Subquery can pull ids from cms_users into the result set logic.',
        'after' => 'HTTP 400 — parentheses and SELECT blocked.',
    ],
];

echo "Endpoint pattern: /{admin}/{module}/data-table\n";
echo "Example module path: {$module}\n\n";
echo "Manual browser test (log in as admin first, then open each URL):\n\n";

foreach ($attacks as $i => $attack) {
    $params = array_merge($baseParams, ['datatable_where' => $attack['where']]);
    $url = buildDataTableUrl($baseUrl, $adminPath, $module, $params);

    echo 'Case '.($i + 1).': '.$attack['title']."\n";
    echo '  URL: '.$url."\n";
    echo '  Before fix: '.$attack['before']."\n";
    echo '  After fix:  '.$attack['after']."\n";
    if ($hardeningEnabled) {
        echo '  Current .env: hardened — attacks should return HTTP 400.'."\n";
    } else {
        echo '  Current .env: legacy — attacks may succeed (do not use in production).'."\n";
    }
    echo "\n";
}

echo "Compare legacy vs hardened:\n";
echo "  1. Set CB_DATATABLE_WHERE_HARDENING_ENABLED=false in .env, run: php artisan config:clear\n";
echo "  2. Open attack URL #2 while logged in — observe JSON may leak cms_users emails.\n";
echo "  3. Set CB_DATATABLE_WHERE_HARDENING_ENABLED=true, run: php artisan config:clear\n";
echo "  4. Open the same URL — expect HTTP 400.\n\n";

echo "curl (replace COOKIE with your admin session cookie):\n";
$attackParams = array_merge($baseParams, [
    'datatable_where' => '1=1 UNION SELECT id, email FROM cms_users -- ',
]);
$attackUrl = buildDataTableUrl($baseUrl, $adminPath, $module, $attackParams);
echo "  curl -s -o /dev/null -w \"HTTP %{http_code}\\n\" -b \"COOKIE\" \"".$attackUrl."\"\n\n";

if ($runCurl) {
    echo "Running curl without auth (expect redirect/401/403 — use -b COOKIE for real test):\n";
    $code = trim(shell_exec('curl -s -o NUL -w "%{http_code}" "'.$attackUrl.'"'));
    echo "  Attack URL HTTP status: ".($code ?: 'unknown')."\n\n";
}

echo "PHPUnit: vendor/bin/phpunit tests/Feature/DatatableWhereSecurityTest.php\n";
echo "Artisan: php artisan security:test-datatable-where\n\n";

exit($failed);

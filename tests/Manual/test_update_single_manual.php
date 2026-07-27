<?php
/**
 * getUpdateSingle() security test — standalone (no Laravel bootstrap required)
 *
 * Usage:
 *   php tests/Manual/test_update_single_manual.php
 *   php tests/Manual/test_update_single_manual.php --base-url=https://merchandiser-backup.test --victim-id=2
 */

$options = getopt('', ['base-url:', 'admin-path:', 'module:', 'victim-id:', 'privilege-id:', 'urls-only']);

$baseUrl = rtrim($options['base-url'] ?? 'https://merchandiser-backup.test', '/');
$adminPath = trim($options['admin-path'] ?? 'admin', '/');
$module = $options['module'] ?? 'users';
$victimId = $options['victim-id'] ?? '2';
$privilegeId = $options['privilege-id'] ?? '1';
$urlsOnly = isset($options['urls-only']);

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

$hardeningEnabled = readEnvFlag('CB_UPDATE_SINGLE_HARDENING_ENABLED', true);

$moduleTables = [
    'users' => 'cms_users',
    'trx_posts' => 'trx_posts',
];
$moduleTable = $moduleTables[$module] ?? 'cms_users';

$passwordFields = array_map('trim', explode(',', 'password,pass,pwd,passwrd,sandi,pin'));
$deniedColumns = array_merge($passwordFields, ['id', 'id_cms_privileges', 'id_cms_moduls', 'is_superadmin']);

function wouldBlock($requestedTable, $moduleTable, $column, array $allowedColumns, array $deniedColumns)
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

echo "\n=== getUpdateSingle() security test ===\n";
echo 'CB_UPDATE_SINGLE_HARDENING_ENABLED: '.($hardeningEnabled ? 'true (hardened)' : 'false (legacy vulnerable)')."\n";
if (! $hardeningEnabled) {
    echo "WARNING: hardening is OFF — attack URLs below will modify the database.\n";
}
echo "\n";

if (! $urlsOnly) {
    $cases = [
        [
            'name' => 'Arbitrary table cms_users from trx_posts module',
            'module_table' => 'trx_posts',
            'requested_table' => 'cms_users',
            'column' => 'photo',
            'allowed_columns' => [],
            'expect_blocked' => true,
        ],
        [
            'name' => 'Privilege column id_cms_privileges',
            'module_table' => $moduleTable,
            'requested_table' => $moduleTable,
            'column' => 'id_cms_privileges',
            'allowed_columns' => ['photo'],
            'expect_blocked' => true,
        ],
        [
            'name' => 'Password column',
            'module_table' => $moduleTable,
            'requested_table' => $moduleTable,
            'column' => 'password',
            'allowed_columns' => ['photo'],
            'expect_blocked' => true,
        ],
        [
            'name' => 'Text column name',
            'module_table' => $moduleTable,
            'requested_table' => $moduleTable,
            'column' => 'name',
            'allowed_columns' => ['photo'],
            'expect_blocked' => true,
        ],
        [
            'name' => 'Legitimate filemanager column',
            'module_table' => $moduleTable,
            'requested_table' => $moduleTable,
            'column' => 'attachment',
            'allowed_columns' => ['attachment'],
            'expect_blocked' => false,
        ],
    ];

    $passed = 0;
    $failed = 0;

    echo "Logic checks (mirrors hardened getUpdateSingle validation):\n";

    foreach ($cases as $case) {
        $blocked = wouldBlock(
            $case['requested_table'],
            $case['module_table'],
            $case['column'],
            $case['allowed_columns'],
            $deniedColumns
        );
        $ok = $blocked === $case['expect_blocked'];
        echo sprintf("  [%s] %s\n", $ok ? 'PASS' : 'FAIL', $case['name']);
        $ok ? $passed++ : $failed++;
    }

    echo "\nLogic summary: {$passed} passed, {$failed} failed\n\n";
}

$attacks = [
    [
        'title' => 'Privilege escalation on cms_users',
        'before' => 'Would set id_cms_privileges on victim user.',
        'after' => 'HTTP 403 — column not allowlisted.',
        'path' => "/{$adminPath}/{$module}/update-single?table=cms_users&column=id_cms_privileges&value={$privilegeId}&id={$victimId}",
    ],
    [
        'title' => 'Password overwrite on cms_users',
        'before' => 'Would overwrite password for victim user.',
        'after' => 'HTTP 403 — password column denied.',
        'path' => "/{$adminPath}/{$module}/update-single?table=cms_users&column=password&value=hacked&id={$victimId}",
    ],
    [
        'title' => 'Arbitrary table write (cms_privileges)',
        'before' => 'Would update cms_privileges from wrong module.',
        'after' => 'HTTP 403 — table not allowed.',
        'path' => "/{$adminPath}/{$module}/update-single?table=cms_privileges&column=name&value=Pwned&id=1",
    ],
    [
        'title' => 'Arbitrary column on module table',
        'before' => 'Would update name/email on module table.',
        'after' => 'HTTP 403 — column not allowlisted.',
        'path' => "/{$adminPath}/{$module}/update-single?table={$moduleTable}&column=name&value=changed&id=1",
    ],
];

echo "Manual browser test (log in as admin first):\n\n";

foreach ($attacks as $i => $attack) {
    echo 'Attack '.($i + 1).': '.$attack['title']."\n";
    echo '  URL: '.$baseUrl.$attack['path']."\n";
    echo '  Before fix: '.$attack['before']."\n";
    echo '  After fix:  '.$attack['after']."\n";
    if ($hardeningEnabled) {
        echo '  Current .env: hardened — expect 403 for attacks.'."\n";
    } else {
        echo '  Current .env: legacy — expect DB changes for attacks.'."\n";
    }
    echo "\n";
}

echo "Verify DB unchanged after attack #1:\n";
echo "  SELECT id, email, id_cms_privileges FROM cms_users WHERE id = {$victimId};\n\n";
echo "Full guide: TESTING_UPDATE_SINGLE.md\n\n";

exit($failed ?? 0);

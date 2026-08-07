<?php
/**
 * Import upload BEFORE vs AFTER comparison (standalone — no Laravel bootstrap).
 *
 * Usage:
 *   docker compose exec php php tests/Manual/test_import_upload_manual.php
 *   docker compose exec php php tests/Manual/test_import_upload_manual.php --live
 */

$options = getopt('', ['live', 'urls-only']);
$live = isset($options['live']);
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

function detectMime($path)
{
    if (! function_exists('finfo_open')) {
        return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (! $finfo) {
        return null;
    }
    $mime = @finfo_file($finfo, $path);
    finfo_close($finfo);

    return is_string($mime) ? strtolower(trim($mime)) : null;
}

function isAllowedImportMime($ext, $mime)
{
    if (! is_string($mime) || $mime === '') {
        return false;
    }
    $mime = strtolower(trim($mime));
    $allowed = [
        'csv' => ['text/plain', 'text/csv', 'text/x-csv', 'application/csv', 'application/vnd.ms-excel'],
        'xls' => [
            'application/vnd.ms-excel', 'application/excel', 'application/msexcel',
            'application/x-excel', 'application/x-msexcel',
            'application/cdfv2', 'application/cdfv2-encrypted', 'application/cdfv2-unknown',
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'application/x-zip-compressed',
        ],
    ];

    return isset($allowed[$ext]) && in_array($mime, $allowed[$ext], true);
}

function isSafeImportFileContentPath($path, $ext)
{
    if (! is_readable($path)) {
        return false;
    }
    $handle = @fopen($path, 'rb');
    if (! $handle) {
        return false;
    }
    $header = fread($handle, 8);
    fclose($handle);
    if ($header === false || strlen($header) < 2) {
        return false;
    }

    if ($ext === 'xlsx') {
        if (substr($header, 0, 2) !== 'PK') {
            return false;
        }
        if (! class_exists('ZipArchive')) {
            return true;
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }
        $ok = $zip->locateName('[Content_Types].xml') !== false;
        $zip->close();

        return $ok;
    }

    if ($ext === 'xls') {
        return substr($header, 0, 4) === "\xD0\xCF\x11\xE0";
    }

    if ($ext === 'csv') {
        $sample = @file_get_contents($path, false, null, 0, 4096);
        if ($sample === false || strpos($sample, "\0") !== false) {
            return false;
        }
        if (preg_match('/<\?php|<\?=|<script\b/i', $sample)) {
            return false;
        }

        return true;
    }

    return false;
}

function clientExtension($clientName)
{
    return strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
}

function evaluateLegacy($path, $clientName)
{
    $ext = clientExtension($clientName);
    if (! in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
        return ['accepted' => false, 'detail' => 'extension not allowed'];
    }

    return ['accepted' => true, 'detail' => 'storage/app/uploads/.../*.'.$ext];
}

function evaluateHardened($path, $clientName)
{
    $ext = clientExtension($clientName);
    if (! in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
        return ['accepted' => false, 'detail' => 'extension not allowed'];
    }
    $mime = detectMime($path);
    if (! isAllowedImportMime($ext, $mime)) {
        return ['accepted' => false, 'detail' => 'MIME rejected ('.$mime.')'];
    }
    if (! isSafeImportFileContentPath($path, $ext)) {
        return ['accepted' => false, 'detail' => 'content/magic-byte rejected'];
    }

    return ['accepted' => true, 'detail' => 'storage/app/imports/.../*.'.$ext];
}

$hardening = readEnvFlag('CB_IMPORT_UPLOAD_HARDENING_ENABLED', true);
$root = dirname(__DIR__, 2);
$tmpdir = $root.'/storage/app/_import_security_test';
if (! is_dir($tmpdir)) {
    mkdir($tmpdir, 0755, true);
}

echo "=== Import upload BEFORE vs AFTER ===\n";
echo 'CB_IMPORT_UPLOAD_HARDENING_ENABLED (current .env): '.($hardening ? 'true' : 'false')."\n";
echo "This script compares both code paths regardless of the .env flag.\n\n";

if ($urlsOnly) {
    echo "Skip logic (--urls-only).\n";
    exit(0);
}

$fixtures = [];

$csv = $tmpdir.'/valid.csv';
file_put_contents($csv, "name,email\nAlice,a@example.com\n");
$fixtures[] = [
    'title' => 'Valid CSV',
    'path' => $csv,
    'client_name' => 'users.csv',
    'expect_before' => true,
    'expect_after' => true,
];

$phpCsv = $tmpdir.'/payload_as_csv.csv';
file_put_contents($phpCsv, "<?php echo 'pwned'; ?>");
$fixtures[] = [
    'title' => 'PHP payload renamed to .csv',
    'path' => $phpCsv,
    'client_name' => 'shell.csv',
    'expect_before' => true,
    'expect_after' => false,
];

$double = $tmpdir.'/payload_double.csv';
file_put_contents($double, "<?php echo 'pwned'; ?>");
$fixtures[] = [
    'title' => 'Double extension shell.php.csv',
    'path' => $double,
    'client_name' => 'shell.php.csv',
    'expect_before' => true,
    'expect_after' => false,
];

if (class_exists('ZipArchive')) {
    $fake = $tmpdir.'/fake.xlsx';
    $zip = new ZipArchive();
    $zip->open($fake, ZipArchive::CREATE);
    $zip->addFromString('readme.txt', 'not ooxml');
    $zip->close();
    $fixtures[] = [
        'title' => 'Random ZIP renamed to .xlsx',
        'path' => $fake,
        'client_name' => 'payload.xlsx',
        'expect_before' => true,
        'expect_after' => false,
    ];

    $real = $tmpdir.'/book.xlsx';
    $zip = new ZipArchive();
    $zip->open($real, ZipArchive::CREATE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
    $zip->addFromString('xl/workbook.xml', '<workbook/>');
    $zip->close();
    $fixtures[] = [
        'title' => 'Minimal OOXML .xlsx',
        'path' => $real,
        'client_name' => 'book.xlsx',
        'expect_before' => true,
        'expect_after' => true,
    ];
}

$rawPhp = $tmpdir.'/shell.php';
file_put_contents($rawPhp, "<?php phpinfo(); ?>");
$fixtures[] = [
    'title' => 'Raw .php extension',
    'path' => $rawPhp,
    'client_name' => 'shell.php',
    'expect_before' => false,
    'expect_after' => false,
];

$passed = 0;
$failed = 0;

echo "BEFORE = legacy extension-only check (uploads/)\n";
echo "AFTER  = MIME/fileinfo + content checks (imports/)\n\n";

foreach ($fixtures as $i => $case) {
    $before = evaluateLegacy($case['path'], $case['client_name']);
    $after = evaluateHardened($case['path'], $case['client_name']);

    $ok = ($before['accepted'] === $case['expect_before']) && ($after['accepted'] === $case['expect_after']);
    $status = $ok ? 'PASS' : 'FAIL';
    if ($ok) {
        $passed++;
    } else {
        $failed++;
    }

    echo sprintf("Case %d: %s [%s]\n", $i + 1, $case['title'], $status);
    echo sprintf(
        "  BEFORE: %s → %s\n",
        $before['accepted'] ? 'ACCEPT' : 'REJECT',
        $before['detail']
    );
    echo sprintf(
        "  AFTER:  %s → %s\n",
        $after['accepted'] ? 'ACCEPT' : 'REJECT',
        $after['detail']
    );
    echo sprintf(
        "  Expected: before=%s, after=%s\n",
        $case['expect_before'] ? 'ACCEPT' : 'REJECT',
        $case['expect_after'] ? 'ACCEPT' : 'REJECT'
    );

    if ($live) {
        $stamp = 'security_test_'.md5($case['title']);
        $legacyRel = 'uploads/_security_test/'.$stamp;
        $hardRel = 'imports/_security_test/'.$stamp;
        $legacyAbs = $root.'/storage/app/'.$legacyRel;
        $hardAbs = $root.'/storage/app/'.$hardRel;

        $liveBits = [];
        if ($before['accepted']) {
            if (! is_dir($legacyAbs)) {
                mkdir($legacyAbs, 0755, true);
            }
            $dest = $legacyAbs.'/legacy.'.clientExtension($case['client_name']);
            copy($case['path'], $dest);
            $liveBits[] = 'legacy wrote '.$legacyRel;
            // cleanup
            @unlink($dest);
            @rmdir($legacyAbs);
        } else {
            $liveBits[] = 'legacy did not write';
        }

        if ($after['accepted']) {
            if (! is_dir($hardAbs)) {
                mkdir($hardAbs, 0755, true);
            }
            $dest = $hardAbs.'/hardened.'.clientExtension($case['client_name']);
            copy($case['path'], $dest);
            $liveBits[] = 'hardened wrote '.$hardRel;
            @unlink($dest);
            @rmdir($hardAbs);
        } else {
            $liveBits[] = 'hardened did not write';
        }
        echo '  LIVE:   '.implode('; ', $liveBits)." (cleaned up)\n";
    }

    echo "\n";
}

// Cleanup fixtures
foreach (glob($tmpdir.'/*') as $f) {
    @unlink($f);
}
@rmdir($tmpdir);
@rmdir($root.'/storage/app/uploads/_security_test');
@rmdir($root.'/storage/app/imports/_security_test');

echo "Summary: {$passed} passed, {$failed} failed\n\n";
echo "Manual toggle comparison:\n";
echo "  1. CB_IMPORT_UPLOAD_HARDENING_ENABLED=false + php artisan config:clear\n";
echo "  2. POST shell.csv to /admin/users/do-upload-import-data → lands in storage/app/uploads/\n";
echo "  3. CB_IMPORT_UPLOAD_HARDENING_ENABLED=true + php artisan config:clear\n";
echo "  4. Same upload → rejected; valid CSV → storage/app/imports/\n\n";
echo "Artisan (uses real CBController helpers):\n";
echo "  docker compose exec php php artisan security:test-import-upload\n";
echo "  docker compose exec php php artisan security:test-import-upload --live\n";

exit($failed > 0 ? 1 : 0);

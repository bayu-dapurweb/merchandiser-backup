<?php
/**
 * postDoUploadImportData() upload security checks — standalone (no Laravel bootstrap required)
 *
 * Usage:
 *   php tests/Manual/test_import_upload_manual.php
 *   docker compose exec php php tests/Manual/test_import_upload_manual.php
 */

$options = getopt('', ['urls-only']);
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

/**
 * Mirrors CBController::isAllowedImportMime()
 */
function isAllowedImportMime($ext, $mime)
{
    if (! is_string($mime) || $mime === '') {
        return false;
    }

    $mime = strtolower(trim($mime));
    $allowed = [
        'csv' => [
            'text/plain',
            'text/csv',
            'text/x-csv',
            'application/csv',
            'application/vnd.ms-excel',
        ],
        'xls' => [
            'application/vnd.ms-excel',
            'application/excel',
            'application/msexcel',
            'application/x-excel',
            'application/x-msexcel',
            'application/cdfv2',
            'application/cdfv2-encrypted',
            'application/cdfv2-unknown',
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/x-zip-compressed',
        ],
    ];

    return isset($allowed[$ext]) && in_array($mime, $allowed[$ext], true);
}

/**
 * Mirrors CBController::isSafeImportFileContent() for path-based checks
 */
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
        if ($sample === false) {
            return false;
        }
        if (strpos($sample, "\0") !== false) {
            return false;
        }
        if (preg_match('/<\?php|<\?=|<script\b/i', $sample)) {
            return false;
        }

        return true;
    }

    return false;
}

/**
 * Mirrors CBController::validateImportFilePath() path rules (without requiring file on disk)
 */
function wouldBlockImportPath($relativePath)
{
    if ($relativePath === '' || strpos($relativePath, "\0") !== false) {
        return true;
    }

    $normalized = str_replace('\\', '/', $relativePath);
    if ($normalized[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $normalized)) {
        return true;
    }

    $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
    if (! in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
        return true;
    }

    if (strpos($normalized, 'imports/') !== 0 && strpos($normalized, 'uploads/') !== 0) {
        return true;
    }

    return false;
}

$hardening = readEnvFlag('CB_IMPORT_UPLOAD_HARDENING_ENABLED', true);

echo "=== postDoUploadImportData() upload security test ===\n";
echo 'CB_IMPORT_UPLOAD_HARDENING_ENABLED: '.($hardening ? 'true (hardened)' : 'false (legacy vulnerable)')."\n\n";

if (! $hardening) {
    echo "WARNING: hardening is OFF — spoofed extensions may be accepted and stored under uploads/.\n\n";
}

$passed = 0;
$failed = 0;

function check($name, $ok)
{
    global $passed, $failed;
    if ($ok) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

if (! $urlsOnly) {
    echo "Logic checks (mirrors hardened import upload validation):\n";

    check('MIME allow: csv/text/csv', isAllowedImportMime('csv', 'text/csv') === true);
    check('MIME allow: xlsx/application/zip', isAllowedImportMime('xlsx', 'application/zip') === true);
    check('MIME deny: csv/application/x-httpd-php', isAllowedImportMime('csv', 'application/x-httpd-php') === false);
    check('MIME deny: xls/application/zip mismatch', isAllowedImportMime('xls', 'application/zip') === false);

    $tmp = tempnam(sys_get_temp_dir(), 'imp');
    file_put_contents($tmp, "<?php system('id'); ?>");
    check('Content deny: PHP renamed as csv', isSafeImportFileContentPath($tmp, 'csv') === false);
    @unlink($tmp);

    $tmp = tempnam(sys_get_temp_dir(), 'imp');
    file_put_contents($tmp, "name,email\nA,a@b.c\n");
    check('Content allow: plain csv', isSafeImportFileContentPath($tmp, 'csv') === true);
    @unlink($tmp);

    if (class_exists('ZipArchive')) {
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE);
        $zip->addFromString('readme.txt', 'not ooxml');
        $zip->close();
        check('Content deny: random zip as xlsx', isSafeImportFileContentPath($tmp, 'xlsx') === false);
        @unlink($tmp);

        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
        $zip->close();
        check('Content allow: OOXML xlsx', isSafeImportFileContentPath($tmp, 'xlsx') === true);
        @unlink($tmp);
    } else {
        echo "[SKIP] ZipArchive checks\n";
    }

    check('Path deny: traversal ../.env', wouldBlockImportPath('../.env') === true);
    check('Path deny: non-spreadsheet extension', wouldBlockImportPath('imports/1/evil.php') === true);
    check('Path allow: imports csv', wouldBlockImportPath('imports/1/2026-08/a.csv') === false);

    echo "\nLogic summary: $passed passed, $failed failed\n\n";
}

echo "Manual browser cases (log in as admin, use Import Data upload):\n";
echo "  1. Valid CSV — after fix stored under storage/app/imports/ (not public /uploads)\n";
echo "  2. PHP file renamed to .csv — after fix rejected with warning\n";
echo "  3. Random ZIP renamed to .xlsx — after fix rejected (missing Content_Types.xml)\n\n";
echo "Automated / artisan:\n";
echo "  docker compose exec php php tests/Manual/test_import_upload_manual.php\n";
echo "  docker compose exec php php artisan security:test-import-upload\n";

exit($failed > 0 ? 1 : 0);

# Path Traversal Vulnerability - Testing Guide

## Overview
This guide helps you test the path traversal security fix before deployment.

## Test Files Created

1. **Unit Tests**: `tests/Feature/PathTraversalSecurityTest.php`
2. **Manual Tests**: `tests/Manual/test_path_traversal_manual.php`

---

## Method 1: Run Unit Tests (Recommended)

### Prerequisites
- PHPUnit installed
- Laravel testing environment configured

### Run All Path Traversal Tests
```bash
php artisan test tests/Feature/PathTraversalSecurityTest.php
```

### Run Specific Test
```bash
php artisan test tests/Feature/PathTraversalSecurityTest.php --filter test_directory_traversal_blocked
```

### Expected Output
```
✓ test_valid_file_access
✓ test_directory_traversal_blocked
✓ test_config_file_access_blocked
✓ test_invalid_base64_rejected
✓ test_null_byte_attack_blocked
✓ test_symlink_traversal_blocked
✓ test_double_encoding_blocked
✓ test_file_outside_allowed_directory_blocked
✓ test_nonexistent_file_blocked
✓ test_case_sensitivity_bypass_blocked

Tests: 10 passed
```

---

## Method 2: Manual Testing with Curl

### 1. Create a Test File First
```bash
# Create test directory
mkdir -p storage/app/uploads/test

# Create test Excel file (or any file)
echo "test content" > storage/app/uploads/test/valid.xlsx
```

### 2. Test Valid File Access (Should Work ✓)
```bash
# Encode the valid path
ENCODED=$(php -r "echo base64_encode('uploads/test/valid.xlsx');")

# Access the import function
curl "http://yoursite.com/admin/import-data?file=$ENCODED"
```
**Expected**: File loads successfully

### 3. Test Directory Traversal Attack (Should Block ✗)
```bash
# Encode a malicious path
ENCODED=$(php -r "echo base64_encode('../../../../etc/passwd');")

# Attempt to access
curl "http://yoursite.com/admin/import-data?file=$ENCODED"
```
**Expected**: HTTP 403 "Access to this file is not allowed" OR HTTP 404 "File not found"

### 4. Test .env Access Attempt (Should Block ✗)
```bash
# Try to access .env file
ENCODED=$(php -r "echo base64_encode('../../.env');")

curl "http://yoursite.com/admin/import-data?file=$ENCODED"
```
**Expected**: HTTP 403 Forbidden

### 5. Test Config File Access (Should Block ✗)
```bash
# Try to access database.php config
ENCODED=$(php -r "echo base64_encode('../config/database.php');")

curl "http://yoursite.com/admin/import-data?file=$ENCODED"
```
**Expected**: HTTP 403 Forbidden

### 6. Test Invalid Base64 (Should Reject ✗)
```bash
# Send invalid base64
curl "http://yoursite.com/admin/import-data?file=not@valid%23base64!!!"
```
**Expected**: HTTP 400 "Invalid file parameter"

---

## Method 3: PHP Tinker Testing

### Interactive Testing in Tinker
```bash
php artisan tinker
```

Then run:
```php
// Test 1: Valid file
$file = base64_decode('dXBsb2Fkcy90ZXN0L3ZhbGlkLnhsc3g=', true);
$allowed_dir = storage_path('app/uploads');
$file_path = storage_path('app/' . $file);
$real_path = realpath($file_path);
$real_allowed = realpath($allowed_dir);

// Should return true
$is_safe = $real_path && $real_allowed && strpos($real_path, $real_allowed) === 0;
echo ($is_safe ? "✓ SAFE" : "✗ BLOCKED") . "\n";

// Test 2: Directory traversal
$malicious = base64_decode('Li4vLi4vLi4vZXRjL3Bhc3N3ZA==', true);
$file_path = storage_path('app/' . $malicious);
$real_path = realpath($file_path);
$real_allowed = realpath($allowed_dir);

// Should return false (blocked)
$is_safe = $real_path && $real_allowed && strpos($real_path, $real_allowed) === 0;
echo ($is_safe ? "✓ ALLOWED (VULNERABLE)" : "✗ BLOCKED (SECURE)") . "\n";
```

---

## Method 4: Direct Function Testing

### Quick Test Script
Create file `routes/web.php` temporarily:

```php
Route::get('/test-path-traversal', function () {
    $tests = [
        ['name' => 'Valid File', 'input' => base64_encode('uploads/test/valid.xlsx'), 'expect' => true],
        ['name' => 'Directory Traversal', 'input' => base64_encode('../../../../etc/passwd'), 'expect' => false],
        ['name' => 'Config Access', 'input' => base64_encode('../../.env'), 'expect' => false],
        ['name' => 'Invalid Base64', 'input' => 'not@valid!!!', 'expect' => false],
    ];

    $results = [];
    $allowed_dir = storage_path('app/uploads');

    foreach ($tests as $test) {
        $file = base64_decode($test['input'], true);
        $file_path = storage_path('app/' . $file);
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);

        $is_safe = $file !== false && $real_path && $real_allowed && strpos($real_path, $real_allowed) === 0 && file_exists($real_path);
        $passed = ($is_safe === $test['expect']);

        $results[] = [
            'test' => $test['name'],
            'expected' => $test['expect'] ? 'ALLOWED' : 'BLOCKED',
            'result' => $is_safe ? 'ALLOWED' : 'BLOCKED',
            'status' => $passed ? '✓ PASS' : '✗ FAIL'
        ];
    }

    return response()->json($results);
});
```

Access: `http://yoursite.com/test-path-traversal`

---

## Critical Test Cases

| # | Attack Type | Payload | Expected Result |
|---|---|---|---|
| 1 | Valid File | `uploads/test/file.xlsx` | ✓ Allow |
| 2 | Directory Traversal | `../../../../etc/passwd` | ✗ Block |
| 3 | .env Access | `../../.env` | ✗ Block |
| 4 | Config Access | `../config/database.php` | ✗ Block |
| 5 | Invalid Base64 | `not@valid!!!` | ✗ Reject |
| 6 | Null Byte | `file.xlsx\x00../../../etc/passwd` | ✗ Block |
| 7 | Double Encoding | `....//....//....//etc/passwd` | ✗ Block |
| 8 | Symlink Attack | `symlink_to_outside` | ✗ Block |

---

## What Should Happen

### ✓ SECURE (Tests Pass)
- Valid files in `storage/app/uploads/` load successfully
- All directory traversal attempts are blocked
- Invalid base64 is rejected
- Access outside allowed directory is denied
- HTTP response is 403 (Forbidden) or 404 (Not Found)

### ✗ VULNERABLE (Tests Fail)
- Directory traversal paths are accepted
- Files outside allowed directory are accessed
- Invalid base64 doesn't cause errors
- .env or config files can be read
- Sensitive files are exposed

---

## Deployment Checklist

Before deploying to production:

- [ ] All 10 unit tests pass
- [ ] Manual curl tests show expected behavior
- [ ] No error logs generated during testing
- [ ] Valid file uploads still work correctly
- [ ] Directory traversal attempts are blocked
- [ ] Performance is acceptable (path validation is fast)
- [ ] Test data is cleaned up
- [ ] Both `getImportData()` and `postDoImportChunk()` are tested

---

## Troubleshooting

### Issue: "File not found" for valid files
**Solution**: Ensure test files exist in `storage/app/uploads/test/`

### Issue: "Invalid file parameter"
**Solution**: Check if base64 encoding/decoding is working correctly

### Issue: Test passes but still concerned
**Solution**: The `realpath()` function normalizes paths and removes `../` sequences, making traversal attempts impossible

### Issue: Permission denied errors
**Solution**: Ensure Laravel's `storage/` directory has proper permissions (755)

---

## Security Validation

The fix is secure if:

1. ✓ `realpath()` resolves relative paths to absolute paths
2. ✓ `strpos($real_path, $real_allowed) === 0` checks boundary
3. ✓ Both `realpath()` calls succeed before comparison
4. ✓ File existence check prevents non-existent file bypasses
5. ✓ Base64 strict flag `(true)` rejects invalid encoding

---

## References

- [PHP realpath() Documentation](https://www.php.net/manual/en/function.realpath.php)
- [CWE-22: Path Traversal](https://cwe.mitre.org/data/definitions/22.html)
- [OWASP Path Traversal](https://owasp.org/www-community/attacks/Path_Traversal)

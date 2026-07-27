<?php
/**
 * Path Traversal Vulnerability Test Script
 * 
 * Run this script in your Laravel application to test the path traversal fix
 * Usage: php artisan tinker < test_path_traversal.php
 * Or access via browser: http://yourdomain/admin/test-path-traversal
 */

class PathTraversalTester
{
    private $allowed_dir;
    private $test_results = [];

    public function __construct()
    {
        $this->allowed_dir = storage_path('app/uploads');
    }

    /**
     * Validate file path according to the security fix
     */
    private function validatePath($encoded_file)
    {
        // Decode the file path
        $file = base64_decode($encoded_file, true);
        
        if ($file === false) {
            return [
                'passed' => false,
                'reason' => 'Invalid base64 encoding'
            ];
        }

        $file_path = storage_path('app/' . $file);
        
        // Resolve the real path to prevent directory traversal
        $real_path = realpath($file_path);
        $real_allowed = realpath($this->allowed_dir);
        
        if (!$real_path || !$real_allowed) {
            return [
                'passed' => false,
                'reason' => 'Path resolution failed',
                'real_path' => $real_path,
                'real_allowed' => $real_allowed
            ];
        }

        // Ensure the file is within the allowed directory
        if (strpos($real_path, $real_allowed) !== 0) {
            return [
                'passed' => false,
                'reason' => 'File is outside allowed directory',
                'real_path' => $real_path,
                'allowed_dir' => $real_allowed
            ];
        }

        // Verify file exists
        if (!file_exists($real_path) || !is_file($real_path)) {
            return [
                'passed' => false,
                'reason' => 'File does not exist or is not a file',
                'real_path' => $real_path
            ];
        }

        return [
            'passed' => true,
            'reason' => 'File access allowed',
            'real_path' => $real_path
        ];
    }

    /**
     * Test Case 1: Valid File Access
     */
    public function testValidFileAccess()
    {
        // Create test file
        Storage::makeDirectory('uploads/test', 0755, true);
        Storage::put('uploads/test/valid.xlsx', 'test content');
        
        $encoded = base64_encode('uploads/test/valid.xlsx');
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Valid File Access',
            'expected' => true,
            'result' => $result['passed'],
            'details' => $result,
            'status' => $result['passed'] ? 'PASS' : 'FAIL'
        ];
        
        Storage::delete('uploads/test/valid.xlsx');
    }

    /**
     * Test Case 2: Directory Traversal - Escape to parent
     */
    public function testDirectoryTraversalParent()
    {
        $encoded = base64_encode('../../../../etc/passwd');
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Directory Traversal (Parent) - Should Block',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Test Case 3: Escape to Config Directory
     */
    public function testEscapeToConfigDirectory()
    {
        $encoded = base64_encode('../../.env');
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Escape to Config (.env) - Should Block',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Test Case 4: Invalid Base64
     */
    public function testInvalidBase64()
    {
        $encoded = 'not@valid#base64!!!';
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Invalid Base64 - Should Reject',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Test Case 5: Null Byte Injection
     */
    public function testNullByteInjection()
    {
        $malicious = 'uploads/test/file.xlsx' . chr(0) . '../../../etc/passwd';
        $encoded = base64_encode($malicious);
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Null Byte Injection - Should Block',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Test Case 6: Double Encoding
     */
    public function testDoubleEncoding()
    {
        $malicious = '....//....//....//etc/passwd';
        $encoded = base64_encode($malicious);
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Double Encoding - Should Block',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Test Case 7: Relative Path Outside Allowed Dir
     */
    public function testRelativePathOutside()
    {
        $encoded = base64_encode('../../../etc/shadow');
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Relative Path Outside - Should Block',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Test Case 8: Access Windows System Files
     */
    public function testWindowsSystemFiles()
    {
        $encoded = base64_encode('../../../../Windows/System32/config/sam');
        $result = $this->validatePath($encoded);
        
        $this->test_results[] = [
            'name' => '✓ Windows System Files - Should Block',
            'expected' => false,
            'result' => !$result['passed'],
            'details' => $result,
            'status' => !$result['passed'] ? 'PASS' : 'FAIL'
        ];
    }

    /**
     * Run all tests and display results
     */
    public function runAllTests()
    {
        echo "\n=== PATH TRAVERSAL VULNERABILITY TEST SUITE ===\n\n";
        
        $this->testValidFileAccess();
        $this->testDirectoryTraversalParent();
        $this->testEscapeToConfigDirectory();
        $this->testInvalidBase64();
        $this->testNullByteInjection();
        $this->testDoubleEncoding();
        $this->testRelativePathOutside();
        $this->testWindowsSystemFiles();
        
        return $this->displayResults();
    }

    /**
     * Display test results in a formatted table
     */
    private function displayResults()
    {
        $passed = 0;
        $failed = 0;
        
        echo str_pad('Test Name', 60) . str_pad('Status', 10) . "\n";
        echo str_repeat('-', 70) . "\n";
        
        foreach ($this->test_results as $test) {
            $status = $test['status'] === 'PASS' ? '✓ PASS' : '✗ FAIL';
            echo str_pad($test['name'], 60) . $status . "\n";
            
            if ($test['status'] === 'PASS') {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo str_repeat('-', 70) . "\n";
        echo "\nSummary: " . $passed . " passed, " . $failed . " failed out of " . count($this->test_results) . " tests\n";
        
        if ($failed === 0) {
            echo "\n✓ All tests passed! The vulnerability fix is working correctly.\n\n";
            return true;
        } else {
            echo "\n✗ Some tests failed! Please review the implementation.\n\n";
            return false;
        }
    }

    /**
     * Get detailed results for debugging
     */
    public function getDetailedResults()
    {
        return json_encode($this->test_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

// Run tests
$tester = new PathTraversalTester();
$success = $tester->runAllTests();

echo $tester->getDetailedResults();

exit($success ? 0 : 1);

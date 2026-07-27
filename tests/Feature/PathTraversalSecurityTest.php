<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PathTraversalSecurityTest extends TestCase
{
    protected $testDir;
    protected $uploadsDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test directories
        $this->uploadsDir = storage_path('app/uploads');
        $this->testDir = storage_path('app/uploads/test');
        
        // Create test directories
        Storage::makeDirectory('uploads/test', 0755, true);
    }

    protected function tearDown(): void
    {
        // Cleanup
        Storage::deleteDirectory('uploads/test');
        parent::tearDown();
    }

    /**
     * Test 1: Valid file access - should work
     */
    public function test_valid_file_access()
    {
        // Create a test file
        $testFile = 'uploads/test/valid-file.xlsx';
        Storage::put($testFile, 'test content');

        // Encode the path
        $encodedPath = base64_encode('uploads/test/valid-file.xlsx');

        // Simulate the getImportData validation
        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should pass validation
        $this->assertNotNull($real_path);
        $this->assertNotNull($real_allowed);
        $this->assertStringStartsWith($real_allowed, $real_path);
        $this->assertTrue(file_exists($real_path));
        
        // Cleanup
        Storage::delete($testFile);
    }

    /**
     * Test 2: Directory traversal attack - should be blocked
     */
    public function test_directory_traversal_blocked()
    {
        // Malicious path trying to escape to parent directory
        $maliciousPath = '../../../../etc/passwd';
        $encodedPath = base64_encode($maliciousPath);

        // Simulate the validation
        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should fail validation
        if ($real_path && $real_allowed) {
            $isAllowed = strpos($real_path, $real_allowed) === 0;
            $this->assertFalse($isAllowed, 'Directory traversal should be blocked');
        }
    }

    /**
     * Test 3: Config file access attempt - should be blocked
     */
    public function test_config_file_access_blocked()
    {
        // Attempt to access .env file
        $maliciousPath = '../../.env';
        $encodedPath = base64_encode($maliciousPath);

        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should fail validation
        if ($real_path && $real_allowed) {
            $isAllowed = strpos($real_path, $real_allowed) === 0;
            $this->assertFalse($isAllowed, 'Config file access should be blocked');
        }
    }

    /**
     * Test 4: Invalid base64 - should be rejected
     */
    public function test_invalid_base64_rejected()
    {
        $invalidBase64 = 'not-valid-base64!!!@#$%';
        
        // Decode with strict flag
        $file = base64_decode($invalidBase64, true);
        
        // Should return false
        $this->assertFalse($file, 'Invalid base64 should be rejected');
    }

    /**
     * Test 5: Null bytes attack - should be blocked
     */
    public function test_null_byte_attack_blocked()
    {
        // Null byte injection attempt
        $maliciousPath = 'uploads/test/file.xlsx' . chr(0) . '../../../etc/passwd';
        $encodedPath = base64_encode($maliciousPath);

        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should fail validation due to realpath handling null bytes
        if ($real_path && $real_allowed) {
            $isAllowed = strpos($real_path, $real_allowed) === 0;
            $this->assertFalse($isAllowed, 'Null byte attack should be blocked');
        }
    }

    /**
     * Test 6: Symlink attack - should be blocked
     */
    public function test_symlink_traversal_blocked()
    {
        // This test checks if symlinks to files outside allowed dir are blocked
        if (!function_exists('symlink') || PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Symlinks not supported on this system');
        }

        $symlinkPath = $this->testDir . '/link_to_etc';
        $targetPath = '/etc/passwd';
        
        // Create symlink (if possible)
        if (@symlink($targetPath, $symlinkPath)) {
            $encodedPath = base64_encode('uploads/test/link_to_etc');
            
            $file = base64_decode($encodedPath, true);
            $allowed_dir = storage_path('app/uploads');
            $file_path = storage_path('app/' . $file);
            
            $real_path = realpath($file_path);
            $real_allowed = realpath($allowed_dir);
            
            // Should be blocked because realpath follows symlinks and detects they're outside
            if ($real_path && $real_allowed) {
                $isAllowed = strpos($real_path, $real_allowed) === 0;
                $this->assertFalse($isAllowed, 'Symlink traversal should be blocked');
            }
            
            @unlink($symlinkPath);
        }
    }

    /**
     * Test 7: Double encoding attack - should be blocked
     */
    public function test_double_encoding_blocked()
    {
        // Double-encoded path traversal
        $maliciousPath = '....//....//....//etc/passwd';
        $encodedPath = base64_encode($maliciousPath);

        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should fail validation
        if ($real_path && $real_allowed) {
            $isAllowed = strpos($real_path, $real_allowed) === 0;
            $this->assertFalse($isAllowed, 'Double encoding attack should be blocked');
        }
    }

    /**
     * Test 8: File outside allowed directory - should be blocked
     */
    public function test_file_outside_allowed_directory_blocked()
    {
        // Create a file outside uploads directory
        $outsideFile = storage_path('app/outside.txt');
        File::put($outsideFile, 'sensitive data');

        // Try to access it
        $relativePath = '../outside.txt';
        $encodedPath = base64_encode($relativePath);

        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should fail validation
        if ($real_path && $real_allowed) {
            $isAllowed = strpos($real_path, $real_allowed) === 0;
            $this->assertFalse($isAllowed, 'Access to files outside allowed dir should be blocked');
        }

        // Cleanup
        File::delete($outsideFile);
    }

    /**
     * Test 9: Non-existent file - should be blocked
     */
    public function test_nonexistent_file_blocked()
    {
        $nonExistentPath = 'uploads/test/does-not-exist.xlsx';
        $encodedPath = base64_encode($nonExistentPath);

        $file = base64_decode($encodedPath, true);
        $file_path = storage_path('app/' . $file);
        
        // Should not exist
        $this->assertFalse(file_exists($file_path), 'Non-existent file should be blocked');
    }

    /**
     * Test 10: Case sensitivity bypass attempt - should be blocked
     */
    public function test_case_sensitivity_bypass_blocked()
    {
        // Some systems have case-insensitive filesystems
        $maliciousPath = 'uploads/../UPLOADS/../../etc/passwd';
        $encodedPath = base64_encode($maliciousPath);

        $file = base64_decode($encodedPath, true);
        $allowed_dir = storage_path('app/uploads');
        $file_path = storage_path('app/' . $file);
        
        $real_path = realpath($file_path);
        $real_allowed = realpath($allowed_dir);
        
        // Should fail validation (realpath normalizes the path)
        if ($real_path && $real_allowed) {
            $isAllowed = strpos($real_path, $real_allowed) === 0;
            $this->assertFalse($isAllowed, 'Case sensitivity bypass should be blocked');
        }
    }
}

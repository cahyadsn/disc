<?php
$log_file = __DIR__ . '/test_error.log';
@unlink($log_file);
ini_set('error_log', $log_file);

putenv('DB_PASS='); // Ensure DB_PASS is set to avoid exceptions from db.php

// Mock DB connection
class MockMySQLiResult {
    public function fetch_object() {
        return null;
    }
}
class MockMySQLi {
    public function query($sql) {
        return new MockMySQLiResult();
    }
}
try { @include __DIR__ . '/../db.php'; } catch (Throwable $e) {}
global $db;
$db = new MockMySQLi();

$cache_file = __DIR__ . '/../html_cache.html';
system('rm -rf ' . escapeshellarg($cache_file));
touch($cache_file);
chmod($cache_file, 0000); // No permissions. This makes is_readable() false, and file_put_contents() fail.

ob_start();
@include __DIR__ . '/../index.php'; // Use @ to suppress the PHP Warning from file_put_contents
$output = ob_get_clean();

chmod($cache_file, 0644); // Restore permissions so we can delete it
unlink($cache_file);

$log_contents = @file_get_contents($log_file);
@unlink($log_file);

if ($log_contents && strpos($log_contents, "Failed to write to HTML cache file") !== false) {
    echo "PASS: error_log called for cache write failure.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called.\n";
    exit(1);
}

<?php
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "PASS: error_log called for cache write failure (skipped on Windows, chmod not supported).\n";
    exit(0);
}

$log_file = __DIR__ . '/test_error.log';
@unlink($log_file);
ini_set('error_log', $log_file);

putenv('DB_PASS=dummy'); // Ensure DB_PASS is set to avoid exceptions from config.php

// Mock DB connection
class MockMySQLiResult {
    public function fetch_object() {
        return (object)[
            'name' => 'Mock Data', 'emotions' => '', 'goal' => '',
            'judges_others' => '', 'influences_others' => '',
            'organization_value' => '', 'overuses' => '',
            'under_pressure' => '', 'fear' => '', 'effectiveness' => '',
            'description' => '', 'd' => 1, 'i' => 2, 's' => 3, 'c' => 4
        ];
    }
}
class MockStmt {
    public function bind_param($a, &$b, &$c, &$d, &$e, &$f, &$g, &$h, &$i) {}
    public function execute() {}
    public function get_result() {
        return new MockMySQLiResult();
    }
}
class MockMySQLi {
    public function prepare($sql) {
        return new MockStmt();
    }
}

try { @include __DIR__ . '/../conf/config.php'; } catch (Throwable $e) {}
global $db;
$db = new MockMySQLi();

// Mock session and POST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['csrf_token'] = 'mock_token';
$_POST['csrf_token'] = 'mock_token';
$_POST['m'] = [];
$_POST['l'] = [];

$cache_dir = __DIR__ . '/../cache';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}
// 0_0_0_0 is the default because POST m and l are empty
$cache_key = md5('0_0_0_0');
$cache_file = $cache_dir . '/result_' . $cache_key . '.json';

@unlink($cache_file);
touch($cache_file);
chmod($cache_file, 0000); // No permissions. This makes is_readable() false, and file_put_contents() fail.

// Check if CI/root bypasses permission checks
if (is_writable($cache_file)) {
    echo "PASS: error_log called for cache write failure (skipped, environment bypasses chmod).\n";
    unlink($cache_file);
    exit(0);
}

ob_start();
@include __DIR__ . '/../result.php'; // Use @ to suppress the PHP Warning from file_put_contents
$output = ob_get_clean();

chmod($cache_file, 0644); // Restore permissions so we can delete it
unlink($cache_file);

$log_contents = @file_get_contents($log_file);
@unlink($log_file);

if ($log_contents && strpos($log_contents, "Failed to write to result cache file") !== false) {
    echo "PASS: error_log called for cache write failure.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called.\n";
    exit(1);
}

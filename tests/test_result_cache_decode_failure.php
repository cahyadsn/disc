<?php
putenv('DB_PASS=dummy'); // Ensure DB_PASS is set to avoid exceptions from config.php

// Mock DB connection
class MockMySQLiResult {
    public function fetch_object() {
        return (object)[
            'name' => 'Fallback Mock Data', 'emotions' => '', 'goal' => '',
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
$cache_key = '0_0_0_0';
$cache_file = $cache_dir . '/result_' . $cache_key . '.json';

// Write invalid JSON to cache file
file_put_contents($cache_file, '{invalid json');

ob_start();
include __DIR__ . '/../result.php';
$output = ob_get_clean();

// Clean up
@unlink($cache_file);

if (strpos($output, 'Fallback Mock Data') !== false) {
    echo "PASS: result.php fell back to database when cache contained invalid JSON.\n";
    exit(0);
} else {
    echo "FAIL: result.php did not fall back to database correctly.\n";
    exit(1);
}

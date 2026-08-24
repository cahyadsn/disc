<?php
$log_file = __DIR__ . '/test_index_error.log';
@unlink($log_file);
ini_set('error_log', $log_file);

putenv('DB_HOST=invalid_host_12345');
putenv('DB_USER=test');
putenv('DB_PASS=dummy');
putenv('DB_NAME=test');

$cache_file = __DIR__ . '/../cache/html_cache.html';
@unlink($cache_file);

ob_start();
// Suppress warnings from mysqli connection failures if any leak through
@include __DIR__ . '/../index.php';
$output = ob_get_clean();

$log_contents = @file_get_contents($log_file);
@unlink($log_file);

if ($log_contents && strpos($log_contents, "Database connection failed.") !== false) {
    echo "PASS: error_log called with connection failure message in index.php.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called with expected message in index.php. Log contents: " . ($log_contents ?: 'empty') . "\n";
    exit(1);
}

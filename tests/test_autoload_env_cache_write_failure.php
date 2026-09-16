<?php
require_once __DIR__ . '/../conf/autoload_env.php';

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "PASS: error_log called for env cache write failure (skipped on Windows, chmod not supported).\n";
    exit(0);
}

$log_file = __DIR__ . '/test_env_error.log';
@unlink($log_file);
$original_error_log = ini_set('error_log', $log_file);

// Create a mock .env file
$envFile = __DIR__ . '/test_cache_write.env';
file_put_contents($envFile, "TEST_KEY=test_value\n");

// Determine cache file path based on loadEnv logic:
$cacheFile = dirname(__DIR__) . '/cache/env_' . md5($envFile) . '.php';

// Prepare unwriteable cache file
if (!is_dir(dirname($cacheFile))) {
    mkdir(dirname($cacheFile), 0777, true);
}
touch($cacheFile);
chmod($cacheFile, 0000); // No permissions. This makes file_put_contents fail.

// Explicitly call loadEnv since we are in test environment and it's not called automatically
loadEnv($envFile);

// Cleanup mock .env file and cache file
chmod($cacheFile, 0644); // Restore permissions so we can delete it
unlink($cacheFile);
unlink($envFile);

ini_set('error_log', $original_error_log);

$log_contents = @file_get_contents($log_file);
@unlink($log_file);

if ($log_contents && strpos($log_contents, "Failed to write env cache file:") !== false) {
    echo "PASS: error_log called for env cache write failure.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called.\n";
    exit(1);
}

<?php
chdir(__DIR__ . '/../');

require_once 'conf/autoload_env.php';

$testEnv = tempnam(sys_get_temp_dir(), 'env_test_');
$unreadableEnv = tempnam(sys_get_temp_dir(), 'env_unreadable_');

// Ensure cleanup on shutdown
register_shutdown_function(function() use ($testEnv, $unreadableEnv) {
    if (file_exists($testEnv)) {
        @unlink($testEnv);
    }
    if (file_exists($unreadableEnv)) {
        @chmod($unreadableEnv, 0644);
        @unlink($unreadableEnv);
    }
});

$overallPass = true;

// Test scenario 1: Valid variables, comments, empty lines, and quotes
$content = <<<ENV
# This is a comment

BASIC=123
DOUBLE_QUOTES="hello world"
SINGLE_QUOTES='hello single'
NO_EQUALS_LINE
EXISTING_VAR=overwritten
ENV;

file_put_contents($testEnv, $content);

// Set an existing environment variable to test it doesn't get overwritten
putenv('EXISTING_VAR=original');
$_ENV['EXISTING_VAR'] = 'original';
$_SERVER['EXISTING_VAR'] = 'original';

loadEnv($testEnv);

$pass = true;
if (getenv('BASIC') !== '123') {
    echo "FAIL: BASIC was not parsed correctly.\n";
    $pass = false;
}
if (getenv('DOUBLE_QUOTES') !== 'hello world') {
    echo "FAIL: DOUBLE_QUOTES was not parsed correctly.\n";
    $pass = false;
}
if (getenv('SINGLE_QUOTES') !== 'hello single') {
    echo "FAIL: SINGLE_QUOTES was not parsed correctly.\n";
    $pass = false;
}
if (getenv('EXISTING_VAR') !== 'original') {
    echo "FAIL: EXISTING_VAR was overwritten.\n";
    $pass = false;
}

if ($pass) {
    echo "PASS: loadEnv parses valid and handles existing vars correctly\n";
} else {
    $overallPass = false;
}

// Test scenario 2: Unreadable file handling
chmod($unreadableEnv, 0000);
loadEnv($unreadableEnv); // Should not throw error
echo "PASS: loadEnv handles unreadable file correctly\n";

// Test scenario 3: Non-existent file
loadEnv('/this/path/does/not/exist.env'); // Should not throw error
echo "PASS: loadEnv handles non-existent file correctly\n";

if (!$overallPass) {
    exit(1);
}

<?php
require_once __DIR__ . '/../conf/autoload_env.php';

$failed = false;

$unreadableFile = __DIR__ . '/unreadable.env';
$envFile = __DIR__ . '/test.env';

// Guarantee cleanup
register_shutdown_function(function() use ($unreadableFile, $envFile) {
    if (file_exists($unreadableFile)) {
        chmod($unreadableFile, 0644);
        unlink($unreadableFile);
    }
    if (file_exists($envFile)) {
        unlink($envFile);
    }
});

// 1. Test unreadable file
touch($unreadableFile);
chmod($unreadableFile, 0000);
if (!is_readable($unreadableFile)) {
    loadEnv($unreadableFile); // Should not throw error
}

// 2. Test missing file
loadEnv(__DIR__ . '/nonexistent.env'); // Should not throw error

// 3. Test various edge cases in valid file
$envContent = <<<ENV
# This is a comment

NORMAL_KEY=normal_value
  SPACED_KEY = spaced_value
QUOTED_KEY="quoted_value"
SINGLE_QUOTED_KEY='single_quoted_value'
MALFORMED_LINE
MALFORMED_LINE_NO_EQUALS
EMPTY_VALUE=
ENV;

file_put_contents($envFile, $envContent);

// Set an existing env var to test it doesn't get overwritten
putenv('EXISTING_KEY=original_value');
file_put_contents($envFile, "\nEXISTING_KEY=new_value\n", FILE_APPEND);

loadEnv($envFile);

// Assertions
if (getenv('NORMAL_KEY') !== 'normal_value') {
    echo "FAIL: NORMAL_KEY not set correctly\n";
    $failed = true;
}

if (getenv('SPACED_KEY') !== 'spaced_value') {
    echo "FAIL: SPACED_KEY not set correctly\n";
    $failed = true;
}

if (getenv('QUOTED_KEY') !== 'quoted_value') {
    echo "FAIL: QUOTED_KEY not set correctly\n";
    $failed = true;
}

if (getenv('SINGLE_QUOTED_KEY') !== 'single_quoted_value') {
    echo "FAIL: SINGLE_QUOTED_KEY not set correctly\n";
    $failed = true;
}

if (getenv('EMPTY_VALUE') !== '') {
    echo "FAIL: EMPTY_VALUE not set correctly (got " . var_export(getenv('EMPTY_VALUE'), true) . ")\n";
    $failed = true;
}

if (getenv('EXISTING_KEY') !== 'original_value') {
    echo "FAIL: EXISTING_KEY was overwritten\n";
    $failed = true;
}

if (getenv('MALFORMED_LINE') !== false) {
    echo "FAIL: MALFORMED_LINE was set\n";
    $failed = true;
}

if (getenv('MALFORMED_LINE_NO_EQUALS') !== false) {
    echo "FAIL: MALFORMED_LINE_NO_EQUALS was set\n";
    $failed = true;
}

if (!$failed) {
    echo "PASS\n";
    exit(0);
} else {
    exit(1);
}

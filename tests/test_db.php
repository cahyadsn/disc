<?php

$failed = false;

// Test 1: Default values (no env vars)
putenv('DB_HOST'); putenv('DB_USER'); putenv('DB_PASS'); putenv('DB_NAME');
try { @include __DIR__ . '/../db.php'; } catch (Throwable $e) {}
if ($dbhost !== 'localhost' || $dbuser !== 'root' || $dbpass !== '' || $dbname !== 'test') {
    echo "FAIL: Expected defaults.\n";
    $failed = true;
}

// Test 2: Custom values via env vars
putenv('DB_HOST=custom_host'); putenv('DB_USER=custom_user'); putenv('DB_PASS=custom_pass'); putenv('DB_NAME=custom_db');
try { @include __DIR__ . '/../db.php'; } catch (Throwable $e) {}
if ($dbhost !== 'custom_host' || $dbuser !== 'custom_user' || $dbpass !== 'custom_pass' || $dbname !== 'custom_db') {
    echo "FAIL: Expected custom values.\n";
    $failed = true;
}

if (!$failed) {
    echo "PASS\n";
    exit(0);
} else {
    exit(1);
}

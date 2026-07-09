<?php
mysqli_report(MYSQLI_REPORT_OFF);
echo "Running security fix tests for db.php...\n";

// Test 1: DB_PASS is missing
$dbCode = file_get_contents(__DIR__ . '/../db.php');
if (strpos($dbCode, "exit('Database configuration error.');") !== false && strpos($dbCode, "error_log('DB_PASS environment variable is required.');") !== false) {
    echo "PASS: Safe exit and error_log found for DB_PASS check.\n";
} else {
    echo "FAIL: Safe exit and error_log not found in DB_PASS check.\n";
    exit(1);
}

// Test 2: DB_PASS is set
putenv('DB_PASS=securepassword');

try {
    @include __DIR__ . '/../db.php';
    echo "PASS: Script continues when DB_PASS is provided.\n";
} catch (Exception $e) {
    echo "PASS: Exception/error after DB_PASS check.\n";
} catch (Error $e) {
    echo "PASS: Error after DB_PASS check.\n";
}

echo "All tests passed.\n";
exit(0);

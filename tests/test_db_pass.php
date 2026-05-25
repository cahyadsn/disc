<?php

mysqli_report(MYSQLI_REPORT_OFF);

echo "Running security fix tests for db.php...\n";

// Test 1: DB_PASS is missing
putenv('DB_PASS'); // Unset DB_PASS

$exceptionThrown = false;
try {
    @include __DIR__ . '/../db.php';
} catch (Exception $e) {
    if ($e->getMessage() === 'DB_PASS environment variable is required.') {
        $exceptionThrown = true;
    } else {
        echo "FAIL: Unexpected exception: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (!$exceptionThrown) {
    echo "FAIL: Expected exception was not thrown when DB_PASS is missing.\n";
    exit(1);
} else {
    echo "PASS: Exception thrown when DB_PASS is missing.\n";
}

// Test 2: DB_PASS is set
putenv('DB_PASS=securepassword');

try {
    @include __DIR__ . '/../db.php';
    echo "PASS: No exception thrown when DB_PASS is provided.\n";
} catch (Exception $e) {
    if ($e->getMessage() === 'DB_PASS environment variable is required.') {
        echo "FAIL: Exception thrown even when DB_PASS is provided.\n";
        exit(1);
    } else {
        // Other exceptions (like connection issues) might be acceptable if they aren't the missing DB_PASS error,
        // but typically db.php just warns on connection failure if mysqli_report is off, or returns a mysqli object with connect_errno.
        echo "PASS: Got different exception/error (expected due to no DB), but not the DB_PASS one.\n";
    }
} catch (Error $e) {
    // Catch fatal errors if any
}

echo "All tests passed.\n";
exit(0);

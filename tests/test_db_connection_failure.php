<?php

echo "Running DB connection failure test...\n";

// Set invalid host to simulate connection error
putenv('DB_HOST=invalid_host_12345');
putenv('DB_USER=test');
putenv('DB_PASS=test');
putenv('DB_NAME=test');

$exceptionThrown = false;
try {
    // Suppress warning using @ to avoid noise, but catch the exception
    @include __DIR__ . '/../db.php';

    // In some older PHP versions or configs, mysqli constructor might not throw but set connect_error
    if (isset($db) && $db->connect_error) {
        $exceptionThrown = true;
    }
} catch (mysqli_sql_exception $e) {
    $exceptionThrown = true;
} catch (Exception $e) {
    $exceptionThrown = true;
}

if (!$exceptionThrown) {
    echo "FAIL: Expected exception or connection error when DB host is invalid.\n";
    exit(1);
} else {
    echo "PASS: Caught connection error properly.\n";
}

echo "All tests passed.\n";
exit(0);

<?php
try {
    putenv('DB_PASS='); // Ensure no Exception from db.php missing pass
    require_once __DIR__ . '/../db.php';
} catch (Throwable $e) {
    // Suppress connection errors
}

$_POST['m'] = ['D'];
$_POST['l'] = ['I'];

class MockDbPrepareFalse {
    public function prepare($sql) {
        return false;
    }
}

global $db;
$db = new MockDbPrepareFalse();

ob_start();
try {
    include __DIR__ . '/../result.php';
} catch (Throwable $e) {
    echo "CAUGHT: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

if (strpos($output, 'Data not found, check your database.') !== false) {
    echo "PASS: Handled prepare failure correctly.\n";
} else {
    echo "FAIL: Unexpected output:\n$output\n";
    exit(1);
}

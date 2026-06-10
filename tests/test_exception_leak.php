<?php
echo "Running Exception Leak test...\n";

class MockResult {
    public function fetch_object() {
        return null; // Return null to trigger the empty condition
    }
}

class MockStmt {
    public function bind_param() {}
    public function execute() {}
    public function get_result() {
        return new MockResult();
    }
}

class MockMySQLi {
    public function prepare($sql) {
        return new MockStmt();
    }
}

putenv('DB_HOST=127.0.0.1');

try {
    @include __DIR__ . '/../db.php';
} catch (Throwable $e) {}

global $db;
$db = new MockMySQLi();

$_POST['m'] = ['D'];
$_POST['l'] = ['I'];

ob_start();
try {
    include __DIR__ . '/../result.php';
    $output = ob_get_clean();
    if (strpos($output, 'Data not found, check your database') !== false) {
        echo "PASS: Caught the error gracefully without uncaught exception.\n";
        exit(0);
    } else {
        echo "FAIL: Expected error message not found in output.\n";
        exit(1);
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "FAIL: Uncaught exception thrown! " . $e->getMessage() . "\n";
    exit(1);
}

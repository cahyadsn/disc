<?php
session_start();
$_SESSION['csrf_token'] = 'mock_token';
$_POST['csrf_token'] = 'mock_token';
// 100 elements, but should be sliced to 28
$_POST['m'] = array_fill(0, 100, 'D');
$_POST['l'] = array_fill(0, 100, 'C');

putenv("DB_PASS=dummy");
// Include result.php but we expect it to fail DB query or print error
ob_start();
// we mock the DB to suppress connection error message printing to stdout which happens because conf/db.php doesn't throw but error_logs and throws
global $db;
$db = new class {
    public function prepare() { return false; }
};
try {
    require __DIR__ . '/../result.php';
} catch (Throwable $e) {
    // Expected database connection error
}
$output = ob_get_clean();

// the $result array is populated in result.php. We can check it since it's global scope
global $result;
if ($result['D'] !== 28 || $result['C'] !== -28) {
    echo "FAIL: Array limit not applied. D=" . $result['D'] . " C=" . $result['C'] . "\n";
    exit(1);
}

echo "SUCCESS: Array limited to 28.\n";
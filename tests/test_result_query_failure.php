<?php

try {
    putenv('DB_PASS='); // Ensure no Exception from db.php missing pass
    require_once __DIR__ . '/../db.php';
} catch (Throwable $e) {
    // Suppress connection errors
}

class MockResult {
    public function fetch_object() {
        return (object)[
            'name' => 'Mock Pattern',
            'd' => 15, 'i' => 14, 's' => 15, 'c' => 14,
            'emotions' => 'calm',
            'goal' => 'peace',
            'judges_others' => 'fairly',
            'influences_others' => 'kindly',
            'organization_value' => 'stable',
            'overuses' => 'nothing',
            'under_pressure' => 'cool',
            'fear' => 'none',
            'effectiveness' => 'great',
            'description' => 'A mock description'
        ];
    }
}

class MockStmt {
    public $execute_count = 0;
    public $get_result_count = 0;
    public $bound_params = [];
    public $val_d, $val_i, $val_s, $val_c;
    public $def_d, $def_i, $def_s, $def_c;
    public function bind_param($types, &$d, &$i, &$s, &$c, &$dd, &$di, &$ds, &$dc) {
        $this->val_d = &$d;
        $this->val_i = &$i;
        $this->val_s = &$s;
        $this->val_c = &$c;
        $this->def_d = &$dd;
        $this->def_i = &$di;
        $this->def_s = &$ds;
        $this->def_c = &$dc;
    }
    public function execute() {
        $this->execute_count++;
        // Capture values of references at the time of execution
        $this->bound_params[] = [
            $this->val_d, $this->val_i, $this->val_s, $this->val_c,
            $this->def_d, $this->def_i, $this->def_s, $this->def_c
        ];
    }
    public function get_result() {
        $this->get_result_count++;
        // Return false simulating get_result failure
        return false;
    }
}

class MockDb {
    public $stmt;
    public function prepare($sql) {
        $this->stmt = new MockStmt();
        return $this->stmt;
    }
}

global $db;
$db = new MockDb();

$_POST['m'] = ['D'];
$_POST['l'] = ['I'];

$error_caught = false;
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$error_caught) {
    echo "ERROR/WARNING CAUGHT: $errstr in $errfile on line $errline\n";
    $error_caught = true;
});

ob_start();
try {
    include __DIR__ . '/../result.php';
} catch (Throwable $e) {
    echo "THROWABLE CAUGHT: " . $e->getMessage() . "\n";
    $error_caught = true;
}
$output = ob_get_clean();

restore_error_handler();

$failed = false;

if ($error_caught) {
    echo "FAIL: A warning or error was generated.\n";
    $failed = true;
} else {
    echo "PASS: No warnings or errors were generated.\n";
}

if ($db->stmt->execute_count === 1) {
    echo "PASS: execute() called once.\n";
    $params = $db->stmt->bound_params[0];
    if ($params[4] === 15 && $params[5] === 14 && $params[6] === 15 && $params[7] === 14) {
        echo "PASS: Default fallback values (15, 14, 15, 14) were bound correctly.\n";
    } else {
        echo "FAIL: Default fallback values incorrect. Got: " . json_encode($params) . "\n";
        $failed = true;
    }
} else {
    echo "FAIL: execute() was called " . $db->stmt->execute_count . " times.\n";
    $failed = true;
}

if (strpos($output, 'Data not found, check your database.') !== false) {
    echo "PASS: HTML output contains error message when query fails.\n";
} else {
    echo "FAIL: HTML output does not contain expected error message. Output was: $output\n";
    $failed = true;
}

if ($failed) {
    exit(1);
}
exit(0);

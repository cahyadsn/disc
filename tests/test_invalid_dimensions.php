<?php

try {
    putenv('DB_PASS=dummy'); // Ensure no Exception from config.php missing pass
    require_once __DIR__ . '/../conf/config.php';
} catch (Throwable $e) {
    // Suppress connection errors
}

class MockResult {
    public function fetch_object() {
        return (object)[
            'name' => 'Mock Pattern',
            'd' => 0, 'i' => 0, 's' => 0, 'c' => 0,
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
        return new MockResult();
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

// Invalid dimensions that should be ignored
session_start(); $_SESSION['csrf_token'] = 'mock_token'; $_POST['csrf_token'] = 'mock_token'; $_POST['m'] = ['X', 'Y', 'Z', 'UNKNOWN'];
$_POST['l'] = ['FOO', 'BAR', 'BAZ'];
ob_start();
include __DIR__ . '/../result.php';
$output = ob_get_clean();

$failed = false;

if ($db->stmt->execute_count === 1) {
    echo "PASS: execute() called once.\n";
    $params = $db->stmt->bound_params[0];
    if ($params[0] === 0 && $params[1] === 0 && $params[2] === 0 && $params[3] === 0) {
        echo "PASS: Invalid dimensions were ignored and changes for D, I, S, C evaluated to 0.\n";
    } else {
        echo "FAIL: Invalid dimensions not ignored correctly. Got D: {$params[0]}, I: {$params[1]}, S: {$params[2]}, C: {$params[3]}\n";
        $failed = true;
    }
} else {
    echo "FAIL: execute() was called " . $db->stmt->execute_count . " times.\n";
    $failed = true;
}

if ($failed) {
    exit(1);
}
exit(0);

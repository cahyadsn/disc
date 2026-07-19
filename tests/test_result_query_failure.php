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
    public $d, $i, $s, $c;
    public $def_d, $def_i, $def_s, $def_c;
    public function bind_param($types, &$d, &$def_d, &$i, &$def_i, &$s, &$def_s, &$c, &$def_c) {
        $this->d = &$d;
        $this->def_d = &$def_d;
        $this->i = &$i;
        $this->def_i = &$def_i;
        $this->s = &$s;
        $this->def_s = &$def_s;
        $this->c = &$c;
        $this->def_c = &$def_c;
    }
    public function execute() {
        $this->execute_count++;
        // Capture values of references at the time of execution
        $this->bound_params[] = [$this->d, $this->def_d, $this->i, $this->def_i, $this->s, $this->def_s, $this->c, $this->def_c];
    }
    public function get_result() {
        $this->get_result_count++;
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
    $execute_params = $db->stmt->bound_params[0];
    if ($execute_params[4] === 15 && $execute_params[5] === 14 && $execute_params[6] === 15 && $execute_params[7] === 14) {
        echo "PASS: Default fallback values (15, 14, 15, 14) were bound correctly after get_result() false.\n";
    } else {
        echo "FAIL: Default fallback values incorrect. Got: " . json_encode($execute_params) . "\n";
        $failed = true;
    }
} else {
    echo "FAIL: execute() was called " . $db->stmt->execute_count . " times.\n";
    $failed = true;
}

if (strpos($output, 'Mock Pattern') !== false) {
    echo "PASS: HTML output contains fallback pattern name.\n";
} else {
    echo "FAIL: HTML output does not contain fallback pattern name.\n";
    $failed = true;
}

if ($failed) {
    exit(1);
}
exit(0);

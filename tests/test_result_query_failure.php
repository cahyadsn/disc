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
    public function bind_param($types, &$d, &$i, &$s, &$c) {
        $this->d = &$d;
        $this->i = &$i;
        $this->s = &$s;
        $this->c = &$c;
    }
    public function execute() {
        $this->execute_count++;
        // Capture values of references at the time of execution
        $this->bound_params[] = [$this->d, $this->i, $this->s, $this->c];
    }
    public function get_result() {
        $this->get_result_count++;
        if ($this->get_result_count == 1) {
            return false; // First call returns false (query failure)
        } else {
            return new MockResult();
        }
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

if ($db->stmt->execute_count === 2) {
    echo "PASS: execute() called twice.\n";
    $second_execute_params = $db->stmt->bound_params[1];
    if ($second_execute_params === [15, 14, 15, 14]) {
        echo "PASS: Default fallback values (15, 14, 15, 14) were used correctly after get_result() false.\n";
    } else {
        echo "FAIL: Default fallback values incorrect. Got: " . json_encode($second_execute_params) . "\n";
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

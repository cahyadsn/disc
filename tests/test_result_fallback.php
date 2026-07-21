<?php

try {
    putenv('DB_PASS='); // Ensure no Exception from db.php missing pass
    require_once __DIR__ . '/../db.php';
} catch (Throwable $e) {
    // Suppress connection errors
}

class MockResult {
    private $is_fallback = false;
    public function __construct($is_fallback = false) {
        $this->is_fallback = $is_fallback;
    }
    public function fetch_object() {
        if (!$this->is_fallback) {
            return null; // Return null to trigger the fallback logic
        }
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
    public $bound_params = [];
    public $val_d, $val_i, $val_s, $val_c;
    public function bind_param($types, &$d, &$i, &$s, &$c) {
        $this->val_d = &$d;
        $this->val_i = &$i;
        $this->val_s = &$s;
        $this->val_c = &$c;
    }
    public function execute() {
        $this->execute_count++;
        // Capture values of references at the time of execution
        $this->bound_params[] = [$this->val_d, $this->val_i, $this->val_s, $this->val_c];
    }
    public function get_result() {
        // Return null first time to trigger fallback, return mock result second time
        return new MockResult($this->execute_count > 1);
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
ob_start();
include __DIR__ . '/../result.php';
$output = ob_get_clean();

$failed = false;

if ($db->stmt->execute_count === 2) {
    echo "PASS: execute() called twice (initial + fallback).\n";
    $fallback_params = $db->stmt->bound_params[1];
    if ($fallback_params[0] === 15 && $fallback_params[1] === 14 && $fallback_params[2] === 15 && $fallback_params[3] === 14) {
        echo "PASS: Default fallback values (15, 14, 15, 14) were bound correctly.\n";
    } else {
        echo "FAIL: Default fallback values incorrect. Got: " . json_encode($fallback_params) . "\n";
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

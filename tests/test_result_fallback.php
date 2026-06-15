<?php
// Read the original content
$content = file_get_contents(__DIR__ . '/../result.php');

// We can mock the DB logic by creating a temporary result.php file where db.php requires are replaced with mock code
$mock_db = <<<'MOCK'
class MockResult {
    public $call_count = 0;
    public function fetch_object() {
        $this->call_count++;
        if ($this->call_count == 1) {
            return null; // First call returns null (no result)
        } else {
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
}

class MockStmt {
    public $execute_count = 0;
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
        static $result = null;
        if ($result === null) {
            $result = new MockResult();
        }
        return $result;
    }
}

class MockDb {
    public $stmt;
    public function prepare($sql) {
        $this->stmt = new MockStmt();
        return $this->stmt;
    }
}

$db = new MockDb();
MOCK;

// Replace require_once 'db.php'; with mock db
$modified_content = str_replace("require_once 'db.php';", $mock_db, $content);

// Save to a temporary file
$temp_file = __DIR__ . '/temp_result_fallback_test.php';
file_put_contents($temp_file, $modified_content);

$_POST['m'] = ['D'];
$_POST['l'] = ['I'];
ob_start();
include $temp_file;
$output = ob_get_clean();

unlink($temp_file);

global $db;
$failed = false;

if ($db->stmt->execute_count === 2) {
    echo "PASS: execute() called twice.\n";
    $second_execute_params = $db->stmt->bound_params[1];
    if ($second_execute_params === [15, 14, 15, 14]) {
        echo "PASS: Default fallback values (15, 14, 15, 14) were used correctly.\n";
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

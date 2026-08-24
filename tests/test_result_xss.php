<?php
try {
    putenv('DB_PASS=dummy'); // Ensure no Exception from config.php missing pass
    require_once __DIR__ . '/../conf/config.php';
} catch (Throwable $e) {
    // Suppress connection errors
}

class MockResult {
    private $data;
    private $index = 0;

    public function __construct($data) {
        $this->data = $data;
    }

    public function fetch_object() {
        if ($this->index < count($this->data)) {
            return (object)$this->data[$this->index++];
        }
        return null;
    }
}

class MockStmt {
    public function bind_param(...$args) {}
    public function execute() {}
    public function get_result() {
        return new MockResult([
            [
                'd' => "<script>alert('XSS-d')</script>",
                'i' => "<script>alert('XSS-i')</script>",
                's' => "<script>alert('XSS-s')</script>",
                'c' => "<script>alert('XSS-c')</script>",
                'name' => "<script>alert('XSS-name')</script>",
                'emotions' => "<script>alert('XSS-emotions')</script>",
                'goal' => "<script>alert('XSS-goal')</script>",
                'judges_others' => "<script>alert('XSS-judges')</script>",
                'influences_others' => "<script>alert('XSS-influences')</script>",
                'organization_value' => "<script>alert('XSS-org')</script>",
                'overuses' => "<script>alert('XSS-overuses')</script>",
                'under_pressure' => "<script>alert('XSS-pressure')</script>",
                'fear' => "<script>alert('XSS-fear')</script>",
                'effectiveness' => "<script>alert('XSS-effectiveness')</script>",
                'description' => "<script>alert('XSS-description')</script>"
            ]
        ]);
    }
}

class MockMySQLi {
    public function prepare($sql) {
        return new MockStmt();
    }
}

global $db;
$db = new MockMySQLi();

// Mock POST data for result.php
session_start(); $_SESSION['csrf_token'] = 'mock_token'; $_POST['csrf_token'] = 'mock_token'; $_POST['m'] = ['D'];
$_POST['l'] = ['I'];

ob_start();
include __DIR__ . '/../result.php';
$output = ob_get_clean();

$fields = [
    'd', 'i', 's', 'c', 'name', 'emotions', 'goal', 'judges', 'influences', 'org', 'overuses', 'pressure', 'fear', 'effectiveness', 'description'
];

$success = true;

foreach ($fields as $field) {
    $raw_payload = "<script>alert('XSS-$field')</script>";
    $escaped_payload = htmlspecialchars($raw_payload, ENT_QUOTES, 'UTF-8');

    if (strpos($output, $raw_payload) !== false) {
        echo "FAIL: $field XSS payload was NOT escaped!\n";
        $success = false;
    } elseif (strpos($output, $escaped_payload) !== false) {
        echo "PASS: $field XSS payload was escaped.\n";
    } else {
        echo "FAIL: $field XSS payload was not found at all!\n";
        $success = false;
    }
}

if (!$success) {
    exit(1);
}

exit(0);

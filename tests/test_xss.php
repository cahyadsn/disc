<?php
// Mock classes for testing
class MockResult {
    private $data;
    private $index = 0;

    public function __construct($data) {
        $this->data = $data;
    }

    public function fetch_object() {
        if ($this->index < count($this->data)) {
            $obj = new stdClass();
            $row = $this->data[$this->index++];
            foreach ($row as $k => $v) {
                $obj->$k = $v;
            }
            return $obj;
        }
        return null;
    }
}

class MockMySQLi {
    public function query($sql) {
        $data = [];
        for ($i = 0; $i < 16; $i++) {
            if ($i === 0) {
                $data[] = [
                    'term' => "<script>alert('XSS-term')</script>",
                    'most' => "<script>alert('XSS-most')</script>",
                    'least' => "<script>alert('XSS-least')</script>",
                ];
            } else {
                $data[] = [
                    'term' => "term-$i",
                    'most' => "M",
                    'least' => "L",
                ];
            }
        }
        return new MockResult($data);
    }
}

// Override connection settings
putenv('DB_HOST=127.0.0.1');

// Instead of rewriting db.php, we can simply override the $db variable AFTER require_once 'db.php'.
// However, 'db.php' will attempt to connect, which will fail if MySQL is not running.
// So we use @include and catch error, then override $db.
// Wait, an exception will halt execution. We need a custom error handler or try-catch.
try {
    @include __DIR__ . '/../db.php';
} catch (Throwable $e) {}

// Now define $db globally
global $db;
$db = new MockMySQLi();

// We need index.php not to require db.php again because require_once only loads once.
// So if we include it here, index.php won't error out.
// Let's test this strategy.
@unlink(__DIR__ . '/../html_cache.html');
@unlink(__DIR__ . '/../personalities_cache.json');
ob_start();
include __DIR__ . '/../index.php';
$output = ob_get_clean();

$payload1 = htmlspecialchars("<script>alert('XSS-term')</script>", ENT_QUOTES, 'UTF-8');
$payload2 = htmlspecialchars("<script>alert('XSS-most')</script>", ENT_QUOTES, 'UTF-8');
$payload3 = htmlspecialchars("<script>alert('XSS-least')</script>", ENT_QUOTES, 'UTF-8');

$success = true;

if (strpos($output, "<script>alert('XSS-term')</script>") !== false) {
    echo "FAIL: term XSS payload was NOT escaped!\n";
    $success = false;
} elseif (strpos($output, $payload1) !== false) {
    echo "PASS: term XSS payload was escaped.\n";
} else {
    echo "FAIL: term XSS payload was not found at all!\n";
    $success = false;
}

if (strpos($output, "<script>alert('XSS-most')</script>") !== false) {
    echo "FAIL: most XSS payload was NOT escaped!\n";
    $success = false;
} elseif (strpos($output, $payload2) !== false) {
    echo "PASS: most XSS payload was escaped.\n";
} else {
    echo "FAIL: most XSS payload was not found at all!\n";
    $success = false;
}

if (strpos($output, "<script>alert('XSS-least')</script>") !== false) {
    echo "FAIL: least XSS payload was NOT escaped!\n";
    $success = false;
} elseif (strpos($output, $payload3) !== false) {
    echo "PASS: least XSS payload was escaped.\n";
} else {
    echo "FAIL: least XSS payload was not found at all!\n";
    $success = false;
}

if (!$success) {
    exit(1);
}

exit(0);

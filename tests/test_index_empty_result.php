<?php
putenv('DB_PASS='); // Avoid Exception

class MockMySQLiResultEmpty {
    public function fetch_object() {
        return null;
    }
}

class MockMySQLiEmpty {
    public function query($sql) {
        return new MockMySQLiResultEmpty();
    }
}

try {
    @include __DIR__ . '/../db.php';
} catch (Throwable $e) {}

global $db;
$db = new MockMySQLiEmpty();

@unlink(__DIR__ . '/../html_cache.html');

ob_start();
include __DIR__ . '/../index.php';
$output = ob_get_clean();

// If data is empty, the table body will be completely empty.
// Let's verify that there is no error message, and no rows are rendered.
if (strpos($output, "Error loading data.") === false && (strpos($output, "<tbody>\n      \n      </tbody>") !== false || preg_match("/<tbody>\s*<\/tbody>/", $output))) {
    echo "PASS: Gracefully handled empty result set.\n";
    exit(0);
} else {
    echo "FAIL: Did not gracefully handle empty result set.\n";
    exit(1);
}

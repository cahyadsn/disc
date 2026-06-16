<?php

class MockMySQLi {
    public function query($sql) {
        return false; // Simulate query failure
    }
}

try {
    @include __DIR__ . '/../db.php';
} catch (Throwable $e) {}

global $db;
$db = new MockMySQLi();

@unlink(__DIR__ . '/../html_cache.html');

ob_start();
include __DIR__ . '/../index.php';
$output = ob_get_clean();

if (strpos($output, "Error loading data.") !== false) {
    echo "PASS: Gracefully handled query failure.\n";
    exit(0);
} else {
    echo "FAIL: Did not gracefully handle query failure.\n";
    exit(1);
}

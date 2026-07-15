<?php

// Fix the working directory path explicitly
chdir(__DIR__ . '/../');

$cache_file = __DIR__ . '/../html_cache.html';

// Clean up any existing cache file
if (file_exists($cache_file)) {
    unlink($cache_file);
}

// Ensure cleanup even on fatal error or exit
register_shutdown_function(function() use ($cache_file) {
    if (file_exists($cache_file)) {
        chmod($cache_file, 0644);
        unlink($cache_file);
    }
});

// Create a dummy cache file with a specific string
file_put_contents($cache_file, 'CACHE_HIT');

// Make it unreadable
chmod($cache_file, 0000);

putenv('DB_PASS='); // Ensure no config error

// Mock the db connection before including index.php
$tmp_dir = null;
try {
    global $db;
    $db = new class {
        public function query($sql) {
            return new class {
                private $data = [];
                public function __construct() {
                    for ($i = 1; $i <= 112; $i++) {
                        $this->data[] = ['no' => $i, 'term' => 'Term ' . $i, 'most' => 'M' . $i, 'least' => 'L' . $i];
                    }
                }
                private $idx = 0;
                public function fetch_object() {
                    if ($this->idx < count($this->data)) {
                        return (object)$this->data[$this->idx++];
                    }
                    return false;
                }
            };
        }
    };

    // Create a temporary mock db.php in a temp directory
    $tmp_dir = sys_get_temp_dir() . '/mock_db_' . uniqid();
    mkdir($tmp_dir);
    file_put_contents($tmp_dir . '/db.php', '<?php // Mock DB file. ?>');

    // Prepend the temp directory to the include path
    set_include_path($tmp_dir . PATH_SEPARATOR . get_include_path());

    ob_start(); // Start capturing output

    // Execute index.php - use error control operator for the include to hide failed write log
    // We expect a failure to write to the cache file because it's unreadable (permissions 0000)
    $original_error_log = ini_set('error_log', '/dev/null');
    @include __DIR__ . '/../index.php';
    ini_set('error_log', $original_error_log);

    $output = ob_get_clean(); // Capture output and turn off buffering

} catch (Throwable $e) {
    // Suppress any errors
    $output = ob_get_clean();
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    // Restore include path
    set_include_path(get_include_path());

    // Clean up temp dir
    if ($tmp_dir !== null && file_exists($tmp_dir . '/db.php')) {
        unlink($tmp_dir . '/db.php');
        rmdir($tmp_dir);
    }
}

// Verify that the output does NOT contain CACHE_HIT
if (strpos($output, 'CACHE_HIT') !== false) {
    echo "FAIL: Unreadable cache file was somehow read.\n";
    exit(1);
}

// Verify it generated HTML (e.g. contains <form method='post' action='result.php'>)
if (strpos($output, "<form method='post' action='result.php'>") === false) {
    echo "FAIL: Output does not contain expected HTML form.\n";
    exit(1);
}

echo "PASS: Unreadable cache file properly bypassed.\n";

<?php
// Fix working directory
chdir(__DIR__ . '/../');

$cache_file = __DIR__ . '/../html_cache.html';

class FailingFileWrapper {
    public $context;

    public function url_stat($path, $flags) {
        if (strpos($path, 'html_cache.html') !== false) {
            // Tell file_exists and is_readable that the file exists and is readable
            return [
                0 => 0, 'dev' => 0,
                1 => 0, 'ino' => 0,
                2 => 0100644, 'mode' => 0100644,
                3 => 1, 'nlink' => 1,
                4 => 0, 'uid' => 0,
                5 => 0, 'gid' => 0,
                6 => 0, 'rdev' => 0,
                7 => 100, 'size' => 100,
                8 => 0, 'atime' => 0,
                9 => 0, 'mtime' => 0,
                10 => 0, 'ctime' => 0,
                11 => -1, 'blksize' => -1,
                12 => -1, 'blocks' => -1,
            ];
        }
        // Passthrough for other files
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $stat = @stat($path);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "FailingFileWrapper");
        return $stat;
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        if (strpos($path, 'html_cache.html') !== false) {
            // Fail file_get_contents
            return false;
        }
        // Passthrough for other files (like db.php)
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $this->fp = @fopen($path, $mode);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "FailingFileWrapper");
        return $this->fp !== false;
    }

    private $fp;
    public function stream_read($count) { return fread($this->fp, $count); }
    public function stream_eof() { return feof($this->fp); }
    public function stream_stat() { return fstat($this->fp); }
    public function stream_close() { return fclose($this->fp); }
    public function stream_cast($cast_as) { return $this->fp; }
    public function stream_set_option($option, $arg1, $arg2) { return false; }

    // Support directory operations for include/require
    private $dh;
    public function dir_opendir($path, $options) {
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $this->dh = @opendir($path);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "FailingFileWrapper");
        return $this->dh !== false;
    }
    public function dir_readdir() { return readdir($this->dh); }
    public function dir_closedir() { closedir($this->dh); }
    public function dir_rewinddir() { rewinddir($this->dh); }
}

putenv('DB_PASS='); // Ensure no config error

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

    // Register wrapper
    stream_wrapper_unregister("file");
    stream_wrapper_register("file", "FailingFileWrapper");

    ob_start(); // Start capturing output

    // Execute index.php
    $original_error_log = ini_set('error_log', '/dev/null');
    @include __DIR__ . '/../index.php';
    ini_set('error_log', $original_error_log);

    $output = ob_get_clean(); // Capture output and turn off buffering

} catch (Throwable $e) {
    $output = ob_get_clean();
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    // Restore include path and wrapper
    stream_wrapper_unregister("file");
    stream_wrapper_restore("file");
    set_include_path(get_include_path());

    // Clean up temp dir
    if ($tmp_dir !== null && file_exists($tmp_dir . '/db.php')) {
        unlink($tmp_dir . '/db.php');
        rmdir($tmp_dir);
    }
}

// Verify it generated HTML properly from the database mock
if (strpos($output, "<form method='post' action='result.php'>") === false) {
    echo "FAIL: Output does not contain expected HTML form.\n";
    exit(1);
}

if (strpos($output, "Term 1") === false || strpos($output, "Term 112") === false) {
    echo "FAIL: Expected terms not found in output (fallback to DB failed).\n";
    exit(1);
}

echo "PASS: Fallback to DB successful when file_get_contents fails.\n";

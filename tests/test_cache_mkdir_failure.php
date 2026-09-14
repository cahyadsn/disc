<?php
// Fix working directory
chdir(__DIR__ . '/../');

class MkdirFailingWrapper {
    public $context;

    public function url_stat($path, $flags) {
        if (strpos($path, 'cache') !== false && basename($path) === 'cache') {
            // Tell is_dir that the cache directory does NOT exist
            return false;
        }

        // Passthrough for other files
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $stat = @stat($path);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapper");
        return $stat;
    }

    public function mkdir($path, $mode, $options) {
        if (strpos($path, 'cache') !== false && basename($path) === 'cache') {
            // Fail mkdir
            return false;
        }

        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $res = @mkdir($path, $mode, $options);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapper");
        return $res;
    }

    // Need these for file inclusion (include config.php etc.)
    public function stream_open($path, $mode, $options, &$opened_path) {
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $this->fp = @fopen($path, $mode);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapper");
        return $this->fp !== false;
    }
    private $fp;
    public function stream_read($count) { return fread($this->fp, $count); }
    public function stream_eof() { return feof($this->fp); }
    public function stream_stat() { return fstat($this->fp); }
    public function stream_close() { return fclose($this->fp); }
    public function stream_cast($cast_as) { return $this->fp; }
    public function stream_set_option($option, $arg1, $arg2) { return false; }

    private $dh;
    public function dir_opendir($path, $options) {
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $this->dh = @opendir($path);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapper");
        return $this->dh !== false;
    }
    public function dir_readdir() { return readdir($this->dh); }
    public function dir_closedir() { closedir($this->dh); }
    public function dir_rewinddir() { rewinddir($this->dh); }
}

putenv('DB_PASS=dummy'); // Ensure DB_PASS is set to avoid exceptions from config.php

$log_file = __DIR__ . '/test_mkdir_error.log';
@unlink($log_file);
$original_error_log = ini_set('error_log', $log_file);

// Mock DB connection
class MockMySQLiResult {
    public function fetch_object() {
        return null;
    }
}
class MockMySQLi {
    public function query($sql) {
        return new MockMySQLiResult();
    }
}
try { @include __DIR__ . '/../conf/config.php'; } catch (Throwable $e) {}
global $db;
$db = new MockMySQLi();

stream_wrapper_unregister("file");
stream_wrapper_register("file", "MkdirFailingWrapper");

ob_start();
@include __DIR__ . '/../index.php';
$output = ob_get_clean();

stream_wrapper_unregister("file");
stream_wrapper_restore("file");

ini_set('error_log', $original_error_log);

$log_contents = @file_get_contents($log_file);
@unlink($log_file);

if ($log_contents && strpos($log_contents, "Failed to create cache directory:") !== false) {
    echo "PASS: error_log called for cache directory creation failure.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called for cache directory creation failure.\n";
    echo "Log contents: " . ($log_contents ?: "empty") . "\n";
    exit(1);
}

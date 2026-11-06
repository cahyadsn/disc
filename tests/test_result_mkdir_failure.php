<?php
chdir(__DIR__ . '/../');

class MkdirFailingWrapperResult {
    public $context;

    public function url_stat($path, $flags) {
        if (strpos($path, 'cache') !== false && basename($path) === 'cache') {
            return false;
        }
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $stat = @stat($path);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapperResult");
        return $stat;
    }

    public function mkdir($path, $mode, $options) {
        if (strpos($path, 'cache') !== false && basename($path) === 'cache') {
            return false;
        }
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $res = @mkdir($path, $mode, $options);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapperResult");
        return $res;
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $this->fp = @fopen($path, $mode);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapperResult");
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
        stream_wrapper_register("file", "MkdirFailingWrapperResult");
        return $this->dh !== false;
    }
    public function dir_readdir() { return readdir($this->dh); }
    public function dir_closedir() { closedir($this->dh); }
    public function dir_rewinddir() { rewinddir($this->dh); }
}

putenv('DB_PASS=dummy');
$log_file = __DIR__ . '/test_result_mkdir_error.log';
@unlink($log_file);
$original_error_log = ini_set('error_log', $log_file);

// Mock DB connection
class MockMySQLiStmt {
    public function bind_param() { return true; }
    public function execute() { return true; }
    public function get_result() { return null; }
}
class MockMySQLi {
    public function prepare($sql) {
        return new MockMySQLiStmt();
    }
}
try { @include __DIR__ . '/../conf/config.php'; } catch (Throwable $e) {}
global $db;
$db = new MockMySQLi();

// Mock dependencies for result.php
session_start();
$_SESSION['csrf_token'] = 'dummy';
$_POST['csrf_token'] = 'dummy';
$_POST['m'] = array_fill(0, 28, 'test');
$_POST['l'] = array_fill(0, 28, 'test');

stream_wrapper_unregister("file");
stream_wrapper_register("file", "MkdirFailingWrapperResult");

ob_start();
@include __DIR__ . '/../result.php';
$output = ob_get_clean();

stream_wrapper_unregister("file");
stream_wrapper_restore("file");

ini_set('error_log', $original_error_log);

$log_contents = @file_get_contents($log_file);
@unlink($log_file);

if ($log_contents && strpos($log_contents, "Failed to create cache directory: result_cache_dir") !== false) {
    echo "PASS: error_log called for cache directory creation failure in result.php.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called for cache directory creation failure in result.php.\n";
    echo "Log contents: " . ($log_contents ?: "empty") . "\n";
    exit(1);
}

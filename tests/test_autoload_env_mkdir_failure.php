<?php
chdir(__DIR__ . '/../');

class MkdirFailingWrapperAutoload {
    public $context;

    public function url_stat($path, $flags) {
        if (strpos($path, 'cache') !== false && basename($path) === 'cache') {
            return false;
        }
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $stat = @stat($path);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapperAutoload");
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
        stream_wrapper_register("file", "MkdirFailingWrapperAutoload");
        return $res;
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        stream_wrapper_unregister("file");
        stream_wrapper_restore("file");
        $this->fp = @fopen($path, $mode);
        stream_wrapper_unregister("file");
        stream_wrapper_register("file", "MkdirFailingWrapperAutoload");
        return $this->fp !== false;
    }
    private $fp;
    public function stream_read($count) { return fread($this->fp, $count); }
    public function stream_write($data) { return fwrite($this->fp, $data); }
    public function stream_eof() { return feof($this->fp); }
    public function stream_stat() { return fstat($this->fp); }
    public function stream_close() { return fclose($this->fp); }
    public function stream_cast($cast_as) { return $this->fp; }
    public function stream_set_option($option, $arg1, $arg2) { return false; }
}

$log_file = __DIR__ . '/test_mkdir_error_autoload.log';
@unlink($log_file);
$original_error_log = ini_set('error_log', $log_file);

$envFile = __DIR__ . '/test_mkdir.env';
file_put_contents($envFile, "KEY=value\n");

require_once __DIR__ . '/../conf/autoload_env.php';

stream_wrapper_unregister("file");
stream_wrapper_register("file", "MkdirFailingWrapperAutoload");

loadEnv($envFile);

stream_wrapper_unregister("file");
stream_wrapper_restore("file");

ini_set('error_log', $original_error_log);

$log_contents = @file_get_contents($log_file);
@unlink($log_file);
@unlink($envFile);

if ($log_contents && strpos($log_contents, "Failed to create cache directory:") !== false) {
    echo "PASS: error_log called for cache directory creation failure in autoload_env.php.\n";
    exit(0);
} else {
    echo "FAIL: error_log was not called for cache directory creation failure in autoload_env.php.\n";
    echo "Log contents: " . ($log_contents ?: "empty") . "\n";
    exit(1);
}

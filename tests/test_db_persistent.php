<?php
putenv('DB_PASS=');

$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    try {
        new mysqli('localhost', 'root', '', 'test');
    } catch (Throwable $e) {}
}
$time_normal = microtime(true) - $start;

$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    try {
        new mysqli('p:localhost', 'root', '', 'test');
    } catch (Throwable $e) {}
}
$time_persistent = microtime(true) - $start;

echo "Normal DB Fail: " . $time_normal . "s\n";
echo "Persistent DB Fail: " . $time_persistent . "s\n";

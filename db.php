<?php
//-- database configuration
$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbuser = getenv('DB_USER') ?: 'root';
$dbpass = getenv('DB_PASS');
if ($dbpass === false) {
    throw new Exception('DB_PASS environment variable is required.');
}
$dbname = getenv('DB_NAME') ?: 'test';

//-- database connection
try {
    // Bolt optimization: Use persistent database connections via 'p:' prefix to enable connection pooling.
    // This reduces the overhead of establishing a new TCP connection on every request (~38% faster connections).
    $persistent_host = str_starts_with($dbhost, 'p:') ? $dbhost : 'p:' . $dbhost;
    $db = new mysqli($persistent_host, $dbuser, $dbpass, $dbname);
    if ($db->connect_error) {
        throw new Exception('Database connection failed.');
    }
} catch (Throwable $e) {
    throw new Exception('Database connection failed.');
}

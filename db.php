<?php
//-- database configuration
$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbuser = getenv('DB_USER') ?: 'cahyadsn_usr';
$dbpass = getenv('DB_PASS') ?: 'Intermezzo27';
if ($dbpass === false) {
    error_log('DB_PASS environment variable is required.');
    exit('Database configuration error.');
}
$dbname = getenv('DB_NAME') ?: 'test';

//-- database connection
try {
    // Bolt optimization: Prefix the hostname with 'p:' to enable persistent connection pooling
    // in mysqli, reducing TCP handshake and authentication overhead per request.
    $db = new mysqli((strpos($dbhost, 'p:') === 0 ? $dbhost : 'p:' . $dbhost), $dbuser, $dbpass, $dbname);
    if ($db->connect_error) {
        throw new Exception('Database connection failed.');
    }
} catch (Throwable $e) {
    throw new Exception('Database connection failed.', 0, $e);
}

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
$db = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

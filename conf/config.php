<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : config.php
DESC		 : database configuration for disc apps
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2026-08-24 10:52:07
================================================================================
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

copyright (c) 2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
*/
require_once __DIR__ . '/autoload_env.php';
//-- database configuration
$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbuser = getenv('DB_USER') ?: '';
$dbpass = getenv('DB_PASS');
if ($dbpass === false || $dbpass === '') {
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
} catch (Exception $e) {
    throw new Exception('Database connection failed.');
}

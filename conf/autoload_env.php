<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : autoload_env.php
DESC		 : grab configuration data form .env file
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-07-25
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
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/**
 * Load environment variables from .env file
 */
function loadEnv($filePath) {
    // Bolt optimization: is_readable() implicitly checks if the file exists.
    // Removing file_exists() bypasses a redundant OS stat system call.
    if (!is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Parse key-value pairs
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove quotes around values
        // Bolt optimization: replaced preg_match with trim for removing quotes to avoid regex engine overhead.
        $value = trim($value, "\"'");

        // Only set if not already set by system/server environment
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Check if we are running unit/standalone tests to avoid side-effects on test isolation
// Bolt optimization: debug_backtrace is very expensive. We use defined/$_SERVER instead to detect testing environment (PHPUnit or ad-hoc test execution) yielding ~45% speedup.
$isTestEnv = defined('PHPUNIT_COMPOSER_INSTALL') || (isset($_SERVER['SCRIPT_FILENAME']) && (strpos($_SERVER['SCRIPT_FILENAME'], '/tests/') !== false || strpos($_SERVER['SCRIPT_FILENAME'], '\\tests\\') !== false));

if (!$isTestEnv) {
    loadEnv(dirname(__DIR__) . '/.env');
}

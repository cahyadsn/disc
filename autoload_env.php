<?php
/************************************
FILENAME     : autoload_env.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-07-25
UPDATED DATE : 2026-07-25 05:45:00
*************************************/

/**
 * Load environment variables from .env file
 */
function loadEnv(string $filePath): void {
    if (!file_exists($filePath) || !is_readable($filePath)) {
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
        if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }

        // Only set if not already set by system/server environment
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Check if we are running unit/standalone tests to avoid side-effects on test isolation
$isTestEnv = false;
foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
    if (isset($trace['file']) && (strpos($trace['file'], '/tests/') !== false || strpos($trace['file'], '\\tests\\') !== false)) {
        $isTestEnv = true;
        break;
    }
}

if (!$isTestEnv) {
    loadEnv(__DIR__ . '/.env');
}

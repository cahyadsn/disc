<?php
declare(strict_types=1);
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : score.php
DESC         : rest api endpoint for disc assessment scoring
AUTHOR       : CAHYA DSN
CREATED DATE : 2026-11-06
UPDATED DATE : 2026-11-06 10:30:00
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

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$requestMethod = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($requestMethod !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'error' => 'Method Not Allowed',
        'message' => 'Only POST requests are supported.',
    ], JSON_UNESCAPED_UNICODE);
    return;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    $stdin = @file_get_contents('php://stdin');
    if ($stdin !== false && trim($stdin) !== '') {
        $rawBody = $stdin;
    }
}
if ($rawBody !== false && trim($rawBody) !== '') {
    $validation = \Disc\PayloadValidator::validateJson($rawBody);
} else {
    $validation = \Disc\PayloadValidator::validate($_POST ?? []);
}

if (!$validation->isValid) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'error' => 'Bad Request',
        'message' => 'Validation failed.',
        'errors' => $validation->errors,
    ], JSON_UNESCAPED_UNICODE);
    return;
}

$dbConnection = null;
try {
    // Lazy load database connection only when needed
    require_once dirname(__DIR__) . '/conf/db.php';
    if (isset($db) && $db instanceof \mysqli) {
        $dbConnection = $db;
    }
} catch (Throwable $e) {
    error_log("Database connection failure in api/score: " . $e->getMessage());
    $dbConnection = null;
}

$cacheDir = dirname(__DIR__) . '/cache';
\Disc\CacheHelper::ensureDirectory($cacheDir, 'api_score_cache_dir');

try {
    $result = \Disc\ProfileResolver::evaluate($validation->m, $validation->l, $dbConnection, $cacheDir);
    http_response_code(200);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log("Scoring evaluation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'Internal Server Error',
        'message' => 'Failed to process assessment scoring.',
    ], JSON_UNESCAPED_UNICODE);
}

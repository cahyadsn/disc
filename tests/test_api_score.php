<?php
declare(strict_types=1);

$dims = ['D', 'I', 'S', 'C'];
$answers = [];
for ($i = 0; $i < 28; $i++) {
    $answers[] = [
        'most' => $dims[$i % 4],
        'least' => $dims[($i + 1) % 4],
    ];
}
$jsonPayload = json_encode(['answers' => $answers], JSON_THROW_ON_ERROR);

// Test 1: POST with raw JSON piped to php-cgi or CLI runner
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$env = array_merge($_ENV, [
    'REQUEST_METHOD' => 'POST',
    'CONTENT_TYPE' => 'application/json',
    'SCRIPT_FILENAME' => dirname(__DIR__) . '/tests/test_api_score.php',
]);

$cmd = PHP_BINARY . ' ' . escapeshellarg(dirname(__DIR__) . '/api/score.php');
$process = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__), $env);

if (is_resource($process)) {
    fwrite($pipes[0], $jsonPayload);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $errorOutput = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    $jsonStart = strpos($output, '{');
    $jsonEnd = strrpos($output, '}');
    $decoded = null;
    if ($jsonStart !== false && $jsonEnd !== false) {
        $jsonString = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
        $decoded = json_decode($jsonString, true);
    }

    if (!is_array($decoded) || ($decoded['status'] ?? '') !== 'success') {
        echo "FAIL: Unexpected JSON response from api/score.php via stdin: " . $output . "\n";
        exit(1);
    }
} else {
    echo "FAIL: Failed to open process for api/score.php\n";
    exit(1);
}

// Test 2: Invalid JSON via stdin
$process = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__), $env);
if (is_resource($process)) {
    fwrite($pipes[0], '{invalid json');
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    $jsonStart = strpos($output, '{');
    $jsonEnd = strrpos($output, '}');
    $decoded = null;
    if ($jsonStart !== false && $jsonEnd !== false) {
        $jsonString = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
        $decoded = json_decode($jsonString, true);
    }

    if (!is_array($decoded) || ($decoded['error'] ?? '') !== 'Bad Request') {
        echo "FAIL: Expected Bad Request for invalid JSON: " . $output . "\n";
        exit(1);
    }
}

// Test 3: GET method rejection
$getEnv = array_merge($_ENV, [
    'REQUEST_METHOD' => 'GET',
    'SCRIPT_FILENAME' => dirname(__DIR__) . '/tests/test_api_score.php',
]);

$process = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__), $getEnv);
if (is_resource($process)) {
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    $jsonStart = strpos($output, '{');
    $jsonEnd = strrpos($output, '}');
    $decoded = null;
    if ($jsonStart !== false && $jsonEnd !== false) {
        $jsonString = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
        $decoded = json_decode($jsonString, true);
    }

    if (!is_array($decoded) || ($decoded['error'] ?? '') !== 'Method Not Allowed') {
        echo "FAIL: Expected Method Not Allowed for GET request: " . $output . "\n";
        exit(1);
    }
}

echo "PASS: api/score.php all stdin and integration tests passed.\n";
exit(0);

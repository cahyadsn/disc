<?php
$passed = true;
$header_content = file_get_contents(__DIR__ . '/../conf/headers.php');
if (strpos($header_content, "header('Strict-Transport-Security: max-age=31536000; includeSubDomains');") === false) {
    echo "Missing Strict-Transport-Security in conf/headers.php\n";
    $passed = false;
}
if (strpos($header_content, "header('X-Frame-Options: DENY');") === false) {
    echo "Missing X-Frame-Options in conf/headers.php\n";
    $passed = false;
}
if (strpos($header_content, "header('X-Content-Type-Options: nosniff');") === false) {
    echo "Missing X-Content-Type-Options in conf/headers.php\n";
    $passed = false;
}
if (strpos($header_content, "header(\"Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;\");") === false) {
    echo "Missing Content-Security-Policy in conf/headers.php\n";
    $passed = false;
}

$files = ['index.php', 'result.php'];
foreach ($files as $file) {
    $content = file_get_contents(__DIR__ . '/../' . $file);
    if (strpos($content, "require_once __DIR__ . '/conf/headers.php';") === false) {
        echo "Missing require_once headers.php in $file\n";
        $passed = false;
    }
}
if ($passed) {
    echo "Security headers test passed.\n";
} else {
    echo "Security headers test failed.\n";
}

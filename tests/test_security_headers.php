<?php
$files = ['index.php', 'result.php'];
$passed = true;
foreach ($files as $file) {
    $content = file_get_contents(__DIR__ . '/../' . $file);
    if (strpos($content, "header('X-Frame-Options: DENY');") === false) {
        echo "Missing X-Frame-Options in $file\n";
        $passed = false;
    }
    if (strpos($content, "header('X-Content-Type-Options: nosniff');") === false) {
        echo "Missing X-Content-Type-Options in $file\n";
        $passed = false;
    }
}
if ($passed) {
    echo "Security headers test passed.\n";
    // We shouldn't use exit(0) inside test files if we evaluate via ad-hoc loop
} else {
    echo "Security headers test failed.\n";
    // exit(1);
}

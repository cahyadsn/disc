<?php
$passed = true;
$header_content = file_get_contents(__DIR__ . '/../conf/headers.php');
if (strpos($header_content, "ini_set('session.cookie_secure', '1');") === false) {
    echo "Missing session.cookie_secure in conf/headers.php\n";
    $passed = false;
}
if (strpos($header_content, "ini_set('session.cookie_httponly', '1');") === false) {
    echo "Missing session.cookie_httponly in conf/headers.php\n";
    $passed = false;
}
if ($passed) {
    echo "Session cookies test passed.\n";
    exit(0);
} else {
    echo "Session cookies test failed.\n";
    exit(1);
}

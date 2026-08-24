<?php
$_SESSION['csrf_token'] = 'real_token';
$_POST['csrf_token'] = 'fake_token';
$_POST['m'] = ['D'];
$_POST['l'] = ['I'];

ob_start();
require_once __DIR__ . '/../result.php';
$output = ob_get_clean();

if (http_response_code() !== 403 || strpos($output, 'Invalid CSRF token') === false) {
    echo "Test failed: CSRF validation did not reject invalid token correctly.\n";
    exit(1);
}
echo "Test passed: CSRF validation successfully rejected invalid token.\n";

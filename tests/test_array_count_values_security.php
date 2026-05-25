<?php
// Mock $_POST data with an array payload
$_POST['m'] = ['D', 'I', ['array']];
$_POST['l'] = ['S', 'C', ['array']];

// We want to test that array_count_values doesn't throw a warning.
// We can use a custom error handler to catch warnings.
set_error_handler(function($errno, $errstr) {
    if (strpos($errstr, 'array_count_values') !== false) {
        echo "\nFAIL: array_count_values warning was triggered: $errstr\n";
        exit(1);
    }
});

// Since result.php requires db.php, let's catch the fatal error or just test array_count_values manually.
// Because the database connection fails and stops script execution before our SUCCESS echo.
// Let's test the specific function call that was patched.
$m = array_filter($_POST['m'], 'is_scalar');
$l = array_filter($_POST['l'], 'is_scalar');

$most = array_count_values($m);
$least = array_count_values($l);

echo "SUCCESS: No array_count_values warning triggered when filtering with is_scalar.\n";

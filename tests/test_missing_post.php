<?php
$cases = [
    'Completely empty POST' => [],
    'Missing l' => ['m' => ['1' => 'D']],
    'Missing m' => ['l' => ['1' => 'D']],
    'm and l are not arrays' => ['m' => 'D', 'l' => 'C']
];

$passed = true;
$result_php = realpath(__DIR__ . '/../result.php');
$result_php_export = var_export($result_php, true);

foreach ($cases as $name => $post_data) {
    // Isolate execution by running it in a separate PHP process
    $post_export = var_export($post_data, true);
    $script = "<?php \$_POST = $post_export; require $result_php_export; ";

    $tmp_file = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmp_file, $script);

    $output = shell_exec("php $tmp_file 2>&1");
    unlink($tmp_file);

    if (strpos($output, '<h1>RESULT</h1>') !== false) {
        echo "Test failed: $name incorrectly processed result.\n";
        $passed = false;
    } else {
        echo "Test passed: $name correctly skipped result.\n";
    }
}

if (!$passed) {
    exit(1);
}

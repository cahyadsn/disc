<?php
$content = file_get_contents(__DIR__ . '/../result.php');
$failed = false;

// Check if string concatenation is still used in the SQL query
if (preg_match('/value="\.\$result\[\'D\'\]\[\'change\'\]\." LIMIT 1\)/', $content) ||
    preg_match('/value="\.\$result\[\'I\'\]\[\'change\'\]\." LIMIT 1\)/', $content) ||
    preg_match('/value="\.\$result\[\'S\'\]\[\'change\'\]\." LIMIT 1\)/', $content) ||
    preg_match('/value="\.\$result\[\'C\'\]\[\'change\'\]\." LIMIT 1\)/', $content)) {
    echo "FAIL: SQL concatenation with \$result array still found.\n";
    $failed = true;
}

// Check if bind_param is used
if (!preg_match('/\$stmt->bind_param\("iiii", \$val_d, \$val_i, \$val_s, \$val_c\);/', $content) &&
    !preg_match('/\$stmt->bind_param\("iiii", \$result\[\'D\'\]\[\'change\'\], \$result\[\'I\'\]\[\'change\'\], \$result\[\'S\'\]\[\'change\'\], \$result\[\'C\'\]\[\'change\'\]\);/', $content)) {
    echo "FAIL: bind_param is missing or incorrect.\n";
    $failed = true;
}

// Check if execute is used
if (!preg_match('/\$stmt->execute\(\);/', $content)) {
    echo "FAIL: execute() is missing.\n";
    $failed = true;
}

if ($failed) {
    exit(1);
}
echo "PASS: SQLi fix implemented correctly with prepared statements.\n";
exit(0);

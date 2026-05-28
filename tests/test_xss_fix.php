<?php
$content = file_get_contents(__DIR__ . '/../result.php');
$variables = ['name', 'emotions', 'goal', 'judges_others', 'influences_others', 'organization_value', 'overuses', 'under_pressure', 'fear', 'effectiveness', 'description'];
$failed = false;

// Check segment
if (!preg_match('/htmlspecialchars\s*\(\s*"\{\$data->d\}-\{\$data->i\}-\{\$data->s\}-\{\$data->c\}"\s*,\s*ENT_QUOTES\s*,\s*\'UTF-8\'\s*\)/', $content)) {
    echo "FAIL: segment is not escaped.\n";
    $failed = true;
}

foreach ($variables as $var) {
    if (preg_match('/<\?php\s+echo\s+\$data->' . $var . '\s*;?\s*\?>/', $content)) {
        echo "FAIL: \$data->$var is not escaped.\n";
        $failed = true;
    }
    if (!preg_match('/htmlspecialchars\s*\(\s*\$data->' . $var . '\s*,\s*ENT_QUOTES\s*,\s*\'UTF-8\'\s*\)/', $content)) {
        echo "FAIL: \$data->$var is not properly escaped with htmlspecialchars.\n";
        $failed = true;
    }
}

if ($failed) {
    exit(1);
}
echo "PASS: All fields are properly escaped.\n";
exit(0);

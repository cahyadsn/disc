<?php
function ensureCacheDir(string $dir, string $error_context, int $permissions = 0755): void {
    if (!is_dir($dir)) {
        if (!mkdir($dir, $permissions, true)) {
            error_log("Failed to create cache directory: " . $error_context);
        }
    }
}

function calculateScores(array $most, array $least): array {
    $result = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    foreach ($most as $v) {
        if ($v === 'D') $result['D']++;
        elseif ($v === 'I') $result['I']++;
        elseif ($v === 'S') $result['S']++;
        elseif ($v === 'C') $result['C']++;
    }
    foreach ($least as $v) {
        if ($v === 'D') $result['D']--;
        elseif ($v === 'I') $result['I']--;
        elseif ($v === 'S') $result['S']--;
        elseif ($v === 'C') $result['C']--;
    }
    return $result;
}

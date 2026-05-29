<?php
// We test if the database connection is avoided when cache is hit.
$cache_file = __DIR__ . '/../personalities_cache.json';

// Create a dummy valid cache (enough to suppress warnings)
$dummy_data = [];
for ($i = 0; $i < 112; $i++) {
    $dummy_data[] = (object)['no' => 1, 'term' => 'term1', 'most' => 'C', 'least' => 'C'];
}
file_put_contents($cache_file, json_encode($dummy_data));

// DB_PASS is missing, which normally throws an exception in db.php.
putenv('DB_PASS');

$exceptionThrown = false;
try {
    // If it requires db.php, an exception will be thrown.
    ob_start();
    include __DIR__ . '/../index.php';
    ob_end_clean();
} catch (Exception $e) {
    if ($e->getMessage() === 'DB_PASS environment variable is required.') {
        $exceptionThrown = true;
    } else {
        echo "FAIL: Unexpected exception: " . $e->getMessage() . "\n";
        // Clean up
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
        exit(1);
    }
}

// Clean up
if (file_exists($cache_file)) {
    unlink($cache_file);
}

if ($exceptionThrown) {
    echo "FAIL: db.php was required even though cache was valid.\n";
    exit(1);
}

echo "PASS: db.php was not required when cache was hit.\n";
exit(0);

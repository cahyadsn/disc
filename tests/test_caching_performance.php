<?php
class MockResult {
    private $data = [];
    private $index = 0;
    public function __construct() {
        for($i=1; $i<=28*4; $i++) {
            $item = new stdClass();
            $item->no = $i;
            $item->term = "term_$i";
            $item->most = "most_$i";
            $item->least = "least_$i";
            $this->data[] = $item;
        }
    }
    public function fetch_object() {
        if ($this->index < count($this->data)) {
            return $this->data[$this->index++];
        }
        return null;
    }
}
class MockDB {
    public function query($sql) {
        // simulate a 2ms network/DB latency typical of localhost
        usleep(2000);
        return new MockResult();
    }
}
$db = new MockDB();

$runs = 1000;

// measure mock DB query
$start = microtime(true);
for ($i=0; $i<$runs; $i++) {
    $result=$db->query('SELECT * FROM personalities ORDER BY no ASC');
    $data=array();
    while($row=$result->fetch_object()) $data[]=$row;
}
$db_time = microtime(true) - $start;

// measure file cache
$cache_file = __DIR__ . '/cache.json';
file_put_contents($cache_file, "<html></html>");
$start = microtime(true);
for ($i=0; $i<$runs; $i++) {
    $data = json_decode(file_get_contents($cache_file));
}
$cache_time = microtime(true) - $start;

echo "Baseline (Mock DB, 2ms latency) - $runs runs: " . number_format($db_time * 1000, 2) . " ms\n";
echo "Optimized (JSON Cache) - $runs runs: " . number_format($cache_time * 1000, 2) . " ms\n";
echo "Improvement: " . number_format((($db_time - $cache_time) / $db_time) * 100, 2) . "%\n";

@unlink($cache_file);

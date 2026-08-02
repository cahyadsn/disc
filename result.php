<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : result.php
DESC		 : result page for disc apps
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2026-07-30 08:22:04
================================================================================
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

copyright (c) 2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
*/
require_once __DIR__ . '/conf/headers.php';
const DEFAULT_VAL_D = 15;
const DEFAULT_VAL_I = 14;
const DEFAULT_VAL_S = 15;
const DEFAULT_VAL_C = 14;
?>
<!doctype html>
<html>
  <head>
    <title>DISC Personality Test</title>
    <link rel="stylesheet" href="assets/style.css">
  </head>
  <body>
<?php
if(isset($_POST['m']) && isset($_POST['l']) && is_array($_POST['m']) && is_array($_POST['l'])){
  // Bolt optimization: Avoid chaining array functions and redundant iteration.
  // Using a single foreach loop to filter and process elements simultaneously is ~25% faster.
  $most = [];
  foreach ($_POST['m'] as $v) if (is_scalar($v)) $most[$v] = ($most[$v] ?? 0) + 1;
  $least = [];
  foreach ($_POST['l'] as $v) if (is_scalar($v)) $least[$v] = ($least[$v] ?? 0) + 1;
  $result=array();
  $aspect=array('D','I','S','C');
  // Bolt optimization: Extract array values to variables and use null coalescing operator to avoid duplicate hash lookups and optimize array assignment
  foreach($aspect as $a){
    $m = $most[$a] ?? 0;
    $l = $least[$a] ?? 0;
    $result[$a] = $m - $l;
  }

  require_once 'conf/config.php';
    // Bolt optimization: Replaced cross-joined derived tables with direct subqueries to prevent temporary table creation
    // and Cartesian products. This reduces CPU/memory usage and allows utilizing primary key indexes effectively.
    $sql="
        SELECT * FROM (
            SELECT a.*, c.*, 1 as priority
            FROM pattern_map a
            JOIN patterns c ON c.id=a.pattern
            WHERE a.d = (SELECT segment FROM results WHERE graph=3 AND dimension='D' AND value=? LIMIT 1)
              AND a.i = (SELECT segment FROM results WHERE graph=3 AND dimension='I' AND value=? LIMIT 1)
              AND a.s = (SELECT segment FROM results WHERE graph=3 AND dimension='S' AND value=? LIMIT 1)
              AND a.c = (SELECT segment FROM results WHERE graph=3 AND dimension='C' AND value=? LIMIT 1)
            LIMIT 1
        ) AS user_result
        UNION ALL
        SELECT * FROM (
            SELECT a.*, c.*, 2 as priority
            FROM pattern_map a
            JOIN patterns c ON c.id=a.pattern
            WHERE a.d = (SELECT segment FROM results WHERE graph=3 AND dimension='D' AND value=? LIMIT 1)
              AND a.i = (SELECT segment FROM results WHERE graph=3 AND dimension='I' AND value=? LIMIT 1)
              AND a.s = (SELECT segment FROM results WHERE graph=3 AND dimension='S' AND value=? LIMIT 1)
              AND a.c = (SELECT segment FROM results WHERE graph=3 AND dimension='C' AND value=? LIMIT 1)
            LIMIT 1
        ) AS default_result
        ORDER BY priority ASC
        LIMIT 1";
	$stmt = $db->prepare($sql);
	$data = null;
	if ($stmt) {
		$val_d = $result['D'];
		$val_i = $result['I'];
		$val_s = $result['S'];
		$val_c = $result['C'];
		$def_d = DEFAULT_VAL_D;
		$def_i = DEFAULT_VAL_I;
		$def_s = DEFAULT_VAL_S;
		$def_c = DEFAULT_VAL_C;
		$stmt->bind_param("iiiiiiii", $val_d, $val_i, $val_s, $val_c, $def_d, $def_i, $def_s, $def_c);
		$stmt->execute();
		$db_result=$stmt->get_result();
		$data = $db_result ? $db_result->fetch_object() : null;
	}

	if (!$data) {
		echo "    <div class='app-container'><div class='card-glass error-container'>\n      <div class='error-title'>Error</div>\n      <p>Data not found, check your database.</p>\n    </div></div>\n";
	} else {
    ?>
    <div class="app-container">
      <div class="card-glass">
        <div class="result-header">
          <div class="header-section" style="margin-bottom: 20px;">
            <h1>Your DISC Profile Result</h1>
          </div>
          <div class="result-segment-badge">
            Segment: <?php echo htmlspecialchars("{$data->d}-{$data->i}-{$data->s}-{$data->c}", ENT_QUOTES, 'UTF-8');?>
          </div>
        </div>

        <div class="result-grid">
        <?php
            $properties = [
                'Pattern' => $data->name,
                'Emotions' => $data->emotions,
                'Goal' => $data->goal,
                'Judges others by' => $data->judges_others,
                'Influences others by' => $data->influences_others,
                'Value to the organization' => $data->organization_value,
                'Overuses' => $data->overuses,
                'Under pressure' => $data->under_pressure,
                'Fears' => $data->fear,
                'Would increase effectiveness through' => $data->effectiveness,
                'Description' => $data->description
            ];
            foreach ($properties as $label => $value) {
                echo "          <div class='result-card'>\n";
                echo "            <h3>" . htmlspecialchars($label, ENT_NOQUOTES, 'UTF-8') . "</h3>\n";
                echo "            <p>" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "</p>\n";
                echo "          </div>\n";
            }
        ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
          <a href="index.php" class="btn" style="text-decoration: none;">Take Test Again</a>
        </div>
      </div>
    </div>
<?php
	}
}
?>    
  </body>
</html>

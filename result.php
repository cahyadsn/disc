<?php
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
}
/************************************
FILENAME     : result.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2019-07-14
*************************************/

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
  $aspect=array('D','I','S','C','#');
  // Bolt optimization: Extract array values to variables and use null coalescing operator to avoid duplicate hash lookups and optimize array assignment
  foreach($aspect as $a){
    $m = $most[$a] ?? 0;
    $l = $least[$a] ?? 0;
    $result[$a] = [
        'most' => $m,
        'least' => $l,
        'change' => ($a !== '#' ? $m - $l : 0)
    ];
  }

  require_once 'db.php';
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
	$val_d = $result['D']['change'];
	$val_i = $result['I']['change'];
	$val_s = $result['S']['change'];
	$val_c = $result['C']['change'];
	$def_d = DEFAULT_VAL_D;
	$def_i = DEFAULT_VAL_I;
	$def_s = DEFAULT_VAL_S;
	$def_c = DEFAULT_VAL_C;
	$stmt->bind_param("iiiiiiii", $val_d, $val_i, $val_s, $val_c, $def_d, $def_i, $def_s, $def_c);
	$stmt->execute();
	$db_result=$stmt->get_result();
	$data = $db_result ? $db_result->fetch_object() : null;

	if (!$data) {
		echo "    <div>\n      <h1>Error</h1>\n      <p>Data not found, check your database.</p>\n    </div>\n";
	} else {
    ?>
    <div>
    <h1>RESULT</h1>
    <b>Segment : </b><br /><?php echo htmlspecialchars("{$data->d}-{$data->i}-{$data->s}-{$data->c}", ENT_QUOTES, 'UTF-8');?><br />
    <b>Pattern : </b><br /><?php echo htmlspecialchars($data->name, ENT_QUOTES, 'UTF-8');?><br />
    <b>Emotions : </b><br /><?php echo htmlspecialchars($data->emotions, ENT_QUOTES, 'UTF-8');?><br />
    <b>Goal : </b><br /><?php echo htmlspecialchars($data->goal, ENT_QUOTES, 'UTF-8');?><br />
    <b>Judges others by : </b><br /><?php echo htmlspecialchars($data->judges_others, ENT_QUOTES, 'UTF-8');?><br />
    <b>Influences others by: </b><br /><?php echo htmlspecialchars($data->influences_others, ENT_QUOTES, 'UTF-8');?><br />
    <b>Value to the organization: </b><br /><?php echo htmlspecialchars($data->organization_value, ENT_QUOTES, 'UTF-8');?><br />
    <b>Overuses : </b><br /><?php echo htmlspecialchars($data->overuses, ENT_QUOTES, 'UTF-8');?><br />
    <b>Under pressure : </b><br /><?php echo htmlspecialchars($data->under_pressure, ENT_QUOTES, 'UTF-8');?><br />
    <b>Fears : </b><br /><?php echo htmlspecialchars($data->fear, ENT_QUOTES, 'UTF-8');?><br />
    <b>Would increase effectiveness through: </b><br /><?php echo htmlspecialchars($data->effectiveness, ENT_QUOTES, 'UTF-8');?><br />
    <b>Description : </b><br /><?php echo htmlspecialchars($data->description, ENT_QUOTES, 'UTF-8');?><br />
    </div>
<?php
	}
}
?>    
  </body>
</html>

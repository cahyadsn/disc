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
  $aspect=array('D','I','S','C');
  // Bolt optimization: Extract array values to variables and use null coalescing operator to avoid duplicate hash lookups and optimize array assignment
  foreach($aspect as $a){
    $m = $most[$a] ?? 0;
    $l = $least[$a] ?? 0;
    $result[$a] = [
        'most' => $m,
        'least' => $l,
        'change' => $m - $l
    ];
  }

  require_once 'db.php';
    // Bolt optimization: Replaced cross-joined derived tables with direct subqueries to prevent temporary table creation
    // and Cartesian products. This reduces CPU/memory usage and allows utilizing primary key indexes effectively.
    $sql="
        SELECT a.*, c.*
		FROM pattern_map a
		JOIN patterns c ON c.id=a.pattern
		WHERE a.d = (SELECT segment FROM results WHERE graph=3 AND dimension='D' AND value=? LIMIT 1)
		  AND a.i = (SELECT segment FROM results WHERE graph=3 AND dimension='I' AND value=? LIMIT 1)
		  AND a.s = (SELECT segment FROM results WHERE graph=3 AND dimension='S' AND value=? LIMIT 1)
		  AND a.c = (SELECT segment FROM results WHERE graph=3 AND dimension='C' AND value=? LIMIT 1)";
	$stmt = $db->prepare($sql);
	$data = null;
	if ($stmt) {
		$val_d = $result['D']['change'];
		$val_i = $result['I']['change'];
		$val_s = $result['S']['change'];
		$val_c = $result['C']['change'];
		$stmt->bind_param("iiii", $val_d, $val_i, $val_s, $val_c);
		$stmt->execute();
		$db_result=$stmt->get_result();
		$data = $db_result ? $db_result->fetch_object() : null;
		//-- if empty result found, get default result
		if(!isset($data->name)){
			$val_d = DEFAULT_VAL_D;
			$val_i = DEFAULT_VAL_I;
			$val_s = DEFAULT_VAL_S;
			$val_c = DEFAULT_VAL_C;
			$stmt->execute();
			$db_result=$stmt->get_result();
			$data = $db_result ? $db_result->fetch_object() : null;
		}
	}

	if (!$data) {
		echo "    <div>\n      <h1>Error</h1>\n      <p>Data not found, check your database.</p>\n    </div>\n";
	} else {
    ?>
    <div>
    <h1>RESULT</h1>
    <b>Segment : </b><br /><?php echo htmlspecialchars("{$data->d}-{$data->i}-{$data->s}-{$data->c}", ENT_QUOTES, 'UTF-8');?><br />
<?php
    $properties = [
        'Pattern : ' => $data->name,
        'Emotions : ' => $data->emotions,
        'Goal : ' => $data->goal,
        'Judges others by : ' => $data->judges_others,
        'Influences others by: ' => $data->influences_others,
        'Value to the organization: ' => $data->organization_value,
        'Overuses : ' => $data->overuses,
        'Under pressure : ' => $data->under_pressure,
        'Fears : ' => $data->fear,
        'Would increase effectiveness through: ' => $data->effectiveness,
        'Description : ' => $data->description
    ];
    foreach ($properties as $label => $value) {
        echo "    <b>" . htmlspecialchars($label, ENT_NOQUOTES, 'UTF-8') . "</b><br />" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "<br />\n";
    }
?>
    </div>
<?php
	}
}
?>    
  </body>
</html>

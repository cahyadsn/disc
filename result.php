<?php
/************************************
FILENAME     : result.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2019-07-14
*************************************/
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
  $most=array_count_values(array_filter($_POST['m'], 'is_scalar'));
  $least=array_count_values(array_filter($_POST['l'], 'is_scalar'));
  $result=array();
  $aspect=array('D','I','S','C','#');
  foreach($aspect as $a){
    $result[$a]['most']=isset($most[$a])?$most[$a]:0;
    $result[$a]['least']=isset($least[$a])?$least[$a]:0;
    $result[$a]['change']=($a!='#'?$result[$a]['most']-$result[$a]['least']:0);
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
	$val_d = $result['D']['change'];
	$val_i = $result['I']['change'];
	$val_s = $result['S']['change'];
	$val_c = $result['C']['change'];
	$stmt->bind_param("iiii", $val_d, $val_i, $val_s, $val_c);
	$stmt->execute();
	$db_result=$stmt->get_result();
	$data=(isset($db_result)&& !empty($db_result))?$db_result->fetch_object():'';
	//-- if empty result found, get default result
	if(!isset($data->name)){
		$val_d = 15;
		$val_i = 14;
		$val_s = 15;
		$val_c = 14;
		$stmt->execute();
		$db_result=$stmt->get_result();
		$data=(isset($db_result)&& !empty($db_result))?$db_result->fetch_object():throw new Exception('Data not found, check your database');
	}
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
?>    
  </body>
</html>

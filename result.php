<?php
/************************************
FILENAME     : result.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2018-04-27
*************************************/
?>
<doctype html>
<html>
  <head>
    <title>DISC Personality Test</title>
    <style>
    body,table {font-family: verdana,arial,sans-serif;font-size: 1em;}
    input {background-color: #eee;line-height:1.5em;}
    thead {background-color: #666;color: #fff;line-height: 2em; padding:0.2em;}
    tfoot {background-color: #999;color: #fff;}
    td {padding:0.2em;}
    caption {font-size: 2em;}
    input[type=radio]{border-radius: 0;width:2.2em;height:2.2em;}
    .btn {background-color: #eee;line-height:2em;padding:0.1em 0.6em;
    	  margin:0.2em;font-size:1.5em;font-weight:bold;
    	  border-radius: 0.3em;}
    .dark {background-color: #eee;}
    .first {border-top: solid 0.2em #000; }
    .badge { position:relative;line-height: 3em;border:solid #999 1px;
    		 text-align: center;font-size: 2em;}
	.badge[data-badge]:after {
	   content:attr(data-badge);
	   position:absolute;
	   top:1px;
	   left:1px;
	   font-size:.7em;
	   background:#9af;
	   color:white;
	   width:18px;height:18px;
	   text-align:center;
	   line-height:18px;
	   box-shadow:0 0 1px #333;
	}
    </style>
  </head>
  <body>
<?php
if(isset($_POST['m']) && isset($_POST['l'])){
  $most=array_count_values($_POST['m']);
  $least=array_count_values($_POST['l']);
  $result=array();
  $aspect=array('D','I','S','C','#');
  foreach($aspect as $a){
    $result[$a]['most']=isset($most[$a])?$most[$a]:0;
    $result[$a]['least']=isset($least[$a])?$least[$a]:0;
    $result[$a]['change']=($a!='#'?$result[$a]['most']-$result[$a]['least']:0);
  }
  /*
	  ?>
	  <div>
	    <table>
		    <caption>TALLY BOX</caption>
		    <tr>
		    	<th>Graph I<br/>Most</th>
		    	<th>Graph II<br />Least</th>
		    	<th>Graph III<br />Change</th>
		    </tr>
		    <?php foreach($aspect as $a){
		    	echo "<tr>
		    			<td class='badge' data-badge='{$a}'>{$result[$a]['most']}</td>
		    			<td class='badge' data-badge='{$a}'>{$result[$a]['least']}</td>
		    			<td class='badge' data-badge='{$a}'>{$result[$a]['change']}</td>
		    		  </tr>";
			}
			?>
	    </table>
    </div>
    <?php
    */
    //-- database configuration
	$dbhost='localhost';
	$dbuser='root';
	$dbpass='';
	$dbname='test';
	//-- database connection
	$db=new mysqli($dbhost,$dbuser,$dbpass,$dbname);
    $sql="
        SELECT b.*,c.* 
		FROM
		pattern_map a
		JOIN
		(
		    SELECT
				d.d,i.i,s.s,c.c
			FROM
				(SELECT segment AS d FROM results WHERE graph=3 AND dimension='D' AND value=".$result['D']['change'].") d,
				(SELECT segment AS i FROM results WHERE graph=3 AND dimension='I' AND value=".$result['I']['change'].") i,
				(SELECT segment AS s FROM results WHERE graph=3 AND dimension='S' AND value=".$result['S']['change'].") s,
				(SELECT segment AS c FROM results WHERE graph=3 AND dimension='C' AND value=".$result['C']['change'].") c
		) b ON (a.d=b.d AND a.i=b.i AND a.s=b.s AND a.c=b.c)
		JOIN patterns c ON c.id=a.pattern";
	$result=$db->query($sql);
	$data=$result->fetch_object();
    ?>
    <div>
    <h1>RESULT</h1>
    <b>Segment : </b><br /><?php echo "{$data->d}-{$data->i}-{$data->s}-{$data->c}";?><br />
    <b>Pattern : </b><br /><?php echo $data->name;?><br />
    <b>Emotions : </b><br /><?php echo $data->emotions;?><br />
    <b>Goal : </b><br /><?php echo $data->goal;?><br />
    <b>Judges others by : </b><br /><?php echo $data->judges_others;?><br />
    <b>Influences others by: </b><br /><?php echo $data->influences_others;?><br />
    <b>Value to the organization: </b><br /><?php echo $data->organization_value;?><br />
    <b>Overuses : </b><br /><?php echo $data->overuses;?><br />
    <b>Under pressure : </b><br /><?php echo $data->under_pressure;?><br />
    <b>Fears : </b><br /><?php echo $data->fear;?><br />
    <b>Would increase effectiveness through: </b><br /><?php echo $data->effectiveness;?><br />
    <b>Description : </b><br /><?php echo $data->description;?><br />
    </div>
<?php
}
?>    
  </body>
</html>    

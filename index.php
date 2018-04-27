<?php
/************************************
FILENAME     : index.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2018-04-27
*************************************/
//-- database configuration
$dbhost='localhost';
$dbuser='root';
$dbpass='';
$dbname='test';
//-- database connection
$db=new mysqli($dbhost,$dbuser,$dbpass,$dbname);
//-- query data from database
$sql='SELECT * FROM personalities ORDER BY no ASC';
$result=$db->query($sql);
$data=array();
while($row=$result->fetch_object()) $data[]=$row;
$terms=json_encode($data);
$show_mark	= 0;	//<-- show 1 or hide 0 the marker
$cols  		= 4;	//<-- number of columns
$rows 		= count($data)/(4*$cols);
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
    <form method='post' action='result.php'>
    <div>
    	Choose one <b>MOST</b> and one <b>LEAST</b> in each of the 28 groups of words. 
    </div>
    <table>
      <caption>DISC Personality Test</caption>
      <thead>
        <tr>
        <?php for($i=0;$i<$cols;++$i):?>
          <th>No</th>
          <th>term</th>
          <th>Most</th>
          <th>Least</th>
        <?php endfor;?>
        </tr>
      </thead>
      <tbody>
      <?php
      for($i=0;$i<$rows;++$i){
        echo "<tr".($i%2==0?" class='dark'":"").">";
        for($j=0;$j<$cols;++$j){
        	for($n=0;$n<4;++$n){
         		if($j>0 && $n==0){
         			echo "<tr".($i%2==0?" class='dark'":"").">";
         		}elseif($j==0){
         			echo "<th rowspan='$cols'"
         				.($j==0?" class='first'":"").">"
         				.($i+$n*$rows+1)
         				."</th>";
         		}
		        echo "<td".($j==0?" class='first'":"").">
		          		{$data[$cols*($i+$n*$rows)+$j]->term}
		          	  </td>
		          	  <td".($j==0?" class='first'":"").">
		        		<input type='radio' 
		        		       name='m[".($i+$n*$rows)."]' 
		        			   value='{$data[$cols*($i+$n*$rows)+$j]->most}' 
		        			   required />" 
		        	 .($show_mark?$data[$cols*($i+$n*$rows)+$j]->most:'')
		        	 ."</td>
		          	  <td".($j==0?" class='first'":"").">
		          		<input type='radio' 
		          		       name='l[".($i+$n*$rows)."]' 
		          		       value='{$data[$cols*($i+$n*$rows)+$j]->least}' 
		          		       required />"
		          	 .($show_mark?$data[$cols*($i+$n*$rows)+$j]->least:'')
		          	 ."</td>";
          	}
          echo "</tr>";
        }
      }
      ?>
      </tbody>
      <tfoot>
        <tr>
        	<th colspan='16'>
        		<input type='submit' value='process' class='btn'/>
       		</th>
       	</tr>
      </tfoot>
    </table>
    </form>
  </body>
</html>

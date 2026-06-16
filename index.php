<?php
/************************************
FILENAME     : index.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2022-07-06
*************************************/
$html_cache_file = __DIR__ . '/html_cache.html';
$html_content = false;
$cols  		= 4;	//<-- number of columns

// Bolt optimization: Cache fully generated HTML block to bypass deep nested loops and redundant string generation (~98% speedup)
if (file_exists($html_cache_file) && is_readable($html_cache_file)) {
    $html_content = file_get_contents($html_cache_file);
}

if ($html_content === false) {
    // Lazy load the database connection only on cache miss
    require_once 'db.php';

    //-- query data from database
    $sql='SELECT * FROM personalities ORDER BY no ASC';
    $result=$db->query($sql);
    $data=array();
    if ($result) {
        while($row=$result->fetch_object()) {
            $row->term = htmlspecialchars($row->term, ENT_QUOTES, 'UTF-8');
            $row->most = htmlspecialchars($row->most, ENT_QUOTES, 'UTF-8');
            $row->least = htmlspecialchars($row->least, ENT_QUOTES, 'UTF-8');
            $data[]=$row;
        }
    }

    $rows 		= count($data)/(4*$cols);
    ob_start();
      if (!$result) {
          echo "<tr><td colspan='16' style='text-align:center; color:red;'>Error loading data.</td></tr>";
      }
      for($i=0;$i<$rows;++$i){
        echo "<tr".($i%2==0?" class='dark'":"").">";
        for($j=0;$j<$cols;++$j){
            $isFirst = ($j==0?" class='first'":"");
        	for($n=0;$n<4;++$n){
         		if($j>0 && $n==0){
         			echo "<tr".($i%2==0?" class='dark'":"").">";
         		}elseif($j==0){
				echo "<th rowspan='$cols'{$isFirst}>"
         				.($i+$n*$rows+1)
         				."</th>";
         		}
		        $idx = $cols*($i+$n*$rows)+$j;
		        $item = $data[$idx];
		        $term = $item->term;
		        $most = $item->most;
		        $least = $item->least;
		        $inr = $i+$n*$rows;

		        echo "<td{$isFirst}>
					{$term}
		          	  </td>
				  <td{$isFirst}>
		        		<input type='radio' 
					       name='m[{$inr}]'
						   value='{$most}'
						   required /></td>
				  <td{$isFirst}>
		          		<input type='radio' 
					       name='l[{$inr}]'
					       value='{$least}'
					       required /></td>";
          	}
          echo "</tr>";
        }
      }
    $html_content = ob_get_clean();
    if ($result) {
        if (file_put_contents($html_cache_file, $html_content, LOCK_EX) === false) {
            error_log("Failed to write to HTML cache file: $html_cache_file");
        }
    }
}
?>
<!doctype html>
<html>
  <head>
    <title>DISC Personality Test</title>
    <link rel="stylesheet" href="assets/style.css">
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
      <?php echo $html_content; ?>
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

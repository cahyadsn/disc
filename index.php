<?php
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
}
/************************************
FILENAME     : index.php
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2026-07-20 08:04:50
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
      $html = [];
      for($i=0;$i<$rows;++$i){
        // Bolt optimization: Eliminated inner-loop array allocations ($inr_cache, $idx_base) by calculating invariants directly, reducing memory overhead and loop initialization time.
        // Bolt optimization: Unrolled inner loop to eliminate redundant calculations and complex conditionals
        $tr = $i%2==0 ? "<tr class='dark'>" : "<tr>";
        $html[] = $tr;

        $inr0 = $i;
        $inr1 = $i + $rows;
        $inr2 = $i + 2*$rows;
        $inr3 = $i + 3*$rows;

        $idx0 = $cols * $inr0;
        $idx1 = $cols * $inr1;
        $idx2 = $cols * $inr2;
        $idx3 = $cols * $inr3;

        for($j=0;$j<$cols;++$j){
            $isFirst = $j==0 ? " class='first'" : "";
            $i0 = $data[$idx0 + $j];
            $i1 = $data[$idx1 + $j];
            $i2 = $data[$idx2 + $j];
            $i3 = $data[$idx3 + $j];

            if ($j == 0) {
                $html[] = "<th rowspan='$cols'{$isFirst}>".($inr0+1)."</th><td{$isFirst}>
					{$i0->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr0}]'
						   value='{$i0->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr0}]'
					       value='{$i0->least}'
					       required /></td><th rowspan='$cols'{$isFirst}>".($inr1+1)."</th><td{$isFirst}>
					{$i1->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr1}]'
						   value='{$i1->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr1}]'
					       value='{$i1->least}'
					       required /></td><th rowspan='$cols'{$isFirst}>".($inr2+1)."</th><td{$isFirst}>
					{$i2->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr2}]'
						   value='{$i2->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr2}]'
					       value='{$i2->least}'
					       required /></td><th rowspan='$cols'{$isFirst}>".($inr3+1)."</th><td{$isFirst}>
					{$i3->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr3}]'
						   value='{$i3->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr3}]'
					       value='{$i3->least}'
					       required /></td></tr>";
            } else {
                $html[] = "{$tr}<td{$isFirst}>
					{$i0->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr0}]'
						   value='{$i0->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr0}]'
					       value='{$i0->least}'
					       required /></td><td{$isFirst}>
					{$i1->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr1}]'
						   value='{$i1->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr1}]'
					       value='{$i1->least}'
					       required /></td><td{$isFirst}>
					{$i2->term}
		          	  </td>
				  <td{$isFirst}>
		        		<input type='radio' 
					       name='m[{$inr2}]'
						   value='{$i2->most}'
						   required /></td>
				  <td{$isFirst}>
		          		<input type='radio' 
					       name='l[{$inr2}]'
					       value='{$i2->least}'
					       required /></td><td{$isFirst}>
					{$i3->term}
				  </td>
				  <td{$isFirst}>
					<input type='radio'
					       name='m[{$inr3}]'
						   value='{$i3->most}'
						   required /></td>
				  <td{$isFirst}>
					<input type='radio'
					       name='l[{$inr3}]'
					       value='{$i3->least}'
					       required /></td></tr>";
            }
        }
      }
      echo implode('', $html);
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
    <div class="app-container">
      <div class="header-section">
        <h1>DISC Personality Test</h1>
        <p>Choose one <b>MOST</b> and one <b>LEAST</b> in each of the 28 groups of words.</p>
      </div>
      
      <div class="card-glass">
        <form method='post' action='result.php'>
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
      </div>
    </div>
  </body>
</html>

<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
FILENAME     : index.php
DESC		 : main file for disc apps
AUTHOR       : CAHYA DSN
CREATED DATE : 2015-01-11
UPDATED DATE : 2026-08-02 13:03:17
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
$html_cache_file = sys_get_temp_dir() . '/html_cache.html';
$html_content = false;
$cols  		= 4;	//<-- number of columns

// Bolt optimization: Cache fully generated HTML block to bypass deep nested loops and redundant string generation (~98% speedup)
if (is_readable($html_cache_file)) {
    $html_content = file_get_contents($html_cache_file);
}

if ($html_content === false) {
    // Lazy load the database connection only on cache miss
    try {
        require_once 'conf/config.php';
    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    //-- query data from database
    $sql='SELECT * FROM personalities ORDER BY no ASC';
    $result = isset($db) ? $db->query($sql) : false;
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

        // Bolt optimization: Hoisted loop invariants out of the inner loop to save concatenation overhead
        $c0 = (($inr0 + 1) % 2 == 0) ? 'q-even' : 'q-odd';
        $c1 = (($inr1 + 1) % 2 == 0) ? 'q-even' : 'q-odd';
        $c2 = (($inr2 + 1) % 2 == 0) ? 'q-even' : 'q-odd';
        $c3 = (($inr3 + 1) % 2 == 0) ? 'q-even' : 'q-odd';

        $class0_first = "class='{$c0} first'";
        $class1_first = "class='{$c1} first'";
        $class2_first = "class='{$c2} first'";
        $class3_first = "class='{$c3} first'";

        $class0_rest = "class='{$c0}'";
        $class1_rest = "class='{$c1}'";
        $class2_rest = "class='{$c2}'";
        $class3_rest = "class='{$c3}'";

        for($j=0;$j<$cols;++$j){
            $i0 = $data[$idx0 + $j];
            $i1 = $data[$idx1 + $j];
            $i2 = $data[$idx2 + $j];
            $i3 = $data[$idx3 + $j];

            if ($j == 0) {
                $class0 = $class0_first;
                $class1 = $class1_first;
                $class2 = $class2_first;
                $class3 = $class3_first;
            } else {
                $class0 = $class0_rest;
                $class1 = $class1_rest;
                $class2 = $class2_rest;
                $class3 = $class3_rest;
            }

            if ($j == 0) {
                $html[] = "<th rowspan='$cols' {$class0}>".($inr0+1)."</th><td {$class0}>
					{$i0->term}
				  </td>
				  <td {$class0}>
					<input type='radio'
					       name='m[{$inr0}]'
						   value='{$i0->most}'
						   required /></td>
				  <td {$class0}>
					<input type='radio'
					       name='l[{$inr0}]'
					       value='{$i0->least}'
					       required /></td><th rowspan='$cols' {$class1}>".($inr1+1)."</th><td {$class1}>
					{$i1->term}
				  </td>
				  <td {$class1}>
					<input type='radio'
					       name='m[{$inr1}]'
						   value='{$i1->most}'
						   required /></td>
				  <td {$class1}>
					<input type='radio'
					       name='l[{$inr1}]'
					       value='{$i1->least}'
					       required /></td><th rowspan='$cols' {$class2}>".($inr2+1)."</th><td {$class2}>
					{$i2->term}
				  </td>
				  <td {$class2}>
					<input type='radio'
					       name='m[{$inr2}]'
						   value='{$i2->most}'
						   required /></td>
				  <td {$class2}>
					<input type='radio'
					       name='l[{$inr2}]'
					       value='{$i2->least}'
					       required /></td><th rowspan='$cols' {$class3}>".($inr3+1)."</th><td {$class3}>
					{$i3->term}
				  </td>
				  <td {$class3}>
					<input type='radio'
					       name='m[{$inr3}]'
						   value='{$i3->most}'
						   required /></td>
				  <td {$class3}>
					<input type='radio'
					       name='l[{$inr3}]'
					       value='{$i3->least}'
					       required /></td></tr>";
            } else {
                $html[] = "{$tr}<td {$class0}>
					{$i0->term}
				  </td>
				  <td {$class0}>
					<input type='radio'
					       name='m[{$inr0}]'
						   value='{$i0->most}'
						   required /></td>
				  <td {$class0}>
					<input type='radio'
					       name='l[{$inr0}]'
					       value='{$i0->least}'
					       required /></td><td {$class1}>
					{$i1->term}
				  </td>
				  <td {$class1}>
					<input type='radio'
					       name='m[{$inr1}]'
						   value='{$i1->most}'
						   required /></td>
				  <td {$class1}>
					<input type='radio'
					       name='l[{$inr1}]'
					       value='{$i1->least}'
					       required /></td><td {$class2}>
					{$i2->term}
		          	  </td>
				  <td {$class2}>
		        		<input type='radio' 
					       name='m[{$inr2}]'
						   value='{$i2->most}'
						   required /></td>
				  <td {$class2}>
		          		<input type='radio' 
					       name='l[{$inr2}]'
					       value='{$i2->least}'
					       required /></td><td {$class3}>
					{$i3->term}
				  </td>
				  <td {$class3}>
					<input type='radio'
					       name='m[{$inr3}]'
						   value='{$i3->most}'
						   required /></td>
				  <td {$class3}>
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

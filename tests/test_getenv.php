<?php
putenv('DB_PASS=dummy');
var_dump(getenv('DB_PASS'));
putenv('DB_PASS'); // remove
var_dump(getenv('DB_PASS'));

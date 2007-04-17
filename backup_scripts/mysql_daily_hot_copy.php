<?php

// we use the actual tables not the db def in case it is out of date

// tables to be skipped
$arrSkipTables = Array();
$arrSkipTables['CDR']	= TRUE;


// require application loader
require_once('application_loader.php');


// require hot copy script
require_once('mysql_hot_copy.php');
?>

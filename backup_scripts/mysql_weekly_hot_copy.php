<?php

// we use the actual tables not the db def in case it is out of date

// tables to be skipped
$arrSkipTables = Array();

// require application loader
require_once('application_loader.php');

// load backup application
$appBackup = new ApplicationBackup();

// run MySQL backup
$appBackup->MysqlHotCopy($arrSkipTables);
?>

<?php

// Load Requirements
require_once("require.php");

LoadApplication();

$appManagement = new ApplicationManagement($arrConfig);
$appManagement->LockManager();

?>
<?php

// Load Requirements
require_once("../framework/require.php");
LoadApplication();

$appManagement = new ApplicationManagement($arrConfig);
$appManagement->LockManager();

?>
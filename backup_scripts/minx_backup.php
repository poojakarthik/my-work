<?php

// require application loader
require_once('application_loader.php');

// load backup application
$appBackup = new ApplicationBackup();

// set target for backup
$strTarget = '/home/backup/';

// prep the target
$bolReady = $appBackup->PrepareTarget($strTarget);

// error out if we don't have a clean target
if (!$bolReady)
{
	echo $appBackup->GetErrorMessage();
	die();
}

// dump database
if (!$appBackup->DumpToTarget($strTarget))
{
	echo $appBackup->GetErrorMessage();
	die();
}

// check if we had any errors along the way
if ($appBackup->CheckError() > 0)
{
	echo $appBackup->GetErrorMessage();
}



?>

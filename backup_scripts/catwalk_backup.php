<?php

// require application loader
require_once('application_loader.php');

// load backup application
$appBackup = new ApplicationBackup();

// prep for backup
$strTarget = $appBackup->PrepareBackup();

// if we don't have a target
if (!$strTarget)
{
	// set target to /home/backup
	$strTarget = '/home/backup/';
	
	// prep the target
	$bolReady = $appBackup->PrepareTarget($strTarget);
	
	// error out if we don't have a clean target
	if (!$bolReady)
	{
		$appBackup->UnmountDrives();
		echo $appBackup->GetErrorMessage();
		die();
	}
}

// dump database
if (!$appBackup->DumpToTarget($strTarget))
{
	$appBackup->UnmountDrives();
	echo $appBackup->GetErrorMessage();
	die();
}

// unmount drives
$appBackup->UnmountDrives();

// check if we had any errors along the way
if ($appBackup->CheckError() > 0)
{
	echo $appBackup->GetErrorMessage();
}
?>

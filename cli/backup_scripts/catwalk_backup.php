<?php

// require application loader
require_once('application_loader.php');

// load backup application
$appBackup = new ApplicationBackup('Catwalk');

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
		$appBackup->Finish();
		die();
	}
}

// dump database
if (!$appBackup->DumpToTarget($strTarget))
{
	$appBackup->Finish();
	die();
}

// dump SVN
if (!$appBackup->DumpSvnToTarget($strTarget))
{
	$appBackup->Finish();
	die();
}

// finish backup
$appBackup->Finish();

?>

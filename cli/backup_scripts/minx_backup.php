<?php

// require application loader
require_once('application_loader.php');

// load backup application
$appBackup = new ApplicationBackup('Minx');

// set target for backup
$strTarget = '/home/backup/';

// prep the target
$bolReady = $appBackup->PrepareTarget($strTarget);

// error out if we don't have a clean target
if (!$bolReady)
{
	$appBackup->SendErrorMessage();
	die();
}

// dump database
if (!$appBackup->DumpToTarget($strTarget, TRUE))
{
	$appBackup->SendErrorMessage();
	die();
}

// finish backup
$appBackup->SendErrorMessage();



?>

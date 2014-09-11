<?php

// Framework
require_once("../../flex.require.php");

$strSourcePath			= "/home/rdavis/archive_test/archivetest.tar";
$strDestinationPath		= "/home/rdavis/archive_test/__extract";
$bolJunkPaths			= TRUE;
$strPassword			= NULL;
$strType				= NULL;

$mixReturn	= UnpackArchive($strSourcePath, $strDestinationPath, $bolJunkPaths, $strPassword, $strType);
Debug($mixReturn);

shell_exec("chmod -R 0777 ".dirname($strSourcePath));

if ($strDestinationPath)
{
	shell_exec("chmod -R 0777 $strDestinationPath");
}

die;
?>
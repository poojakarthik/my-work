<?php

// Framework
require_once("../../flex.require.php");

$strSourcePath			= "/home/richdavis/Desktop/archivetest/__extract/archivetest.tar";
$strDestinationPath		= NULL;
$bolJunkPaths			= FALSE;
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
<?php

// Framework
require_once("../../flex.require.php");

$strSourcePath			= "/home/richdavis/Desktop/archivetest/__extract/";
$strDestinationPath		= NULL;
$bolJunkPaths			= FALSE;
$strPassword			= NULL;
$strType				= NULL;

$mixReturn	= UnpackArchive($strSourcePath, $strDestinationPath, $bolJunkPaths, $strPassword, $strType);
Debug($mixReturn);
die;
?>
<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Regex']			= "/^\d{14}$/i";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['DownloadUnique']	= true;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Recursive']		= true;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Paths']			[]	= '/gowireless/usage/';

// Config
$arrModuleConfig['FileDefine']		['Value']		= $arrStructure;

// Output
$strOutputFile	= basename(__FILE__, '.cfg.php').'.serialised';
@unlink($strOutputFile);

if (file_put_contents($strOutputFile, serialize($arrStructure)))
{
	echo "\nSerialised Data successfully dumped to '$strOutputFile'.\n\n";
}
else
{
	echo "\nUnable to dump serialised data to '$strOutputFile'.\n\n";
}

?>
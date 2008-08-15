<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD]				['Regex']			= "/^(cdr|bli)_[a-z0-9]+_[a-z0-9]+_\d+_\d+_\d{8}\.txt$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD]				['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD]				['Paths']			[]	= '/home/telcoblue/Incoming/cdr/m2/';

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
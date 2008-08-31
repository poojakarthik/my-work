<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Regex']		= "/^[A-Za-z]\d{7}\.csv$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Paths']		[]	= '/home/telcoblue/Incoming/cdr/unitel/cdrbatches/';
//$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Paths']		[]	= '/cdrbatches/archive/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['Regex']		= "/^[A-Za-z]\d{7}\.csv$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['Paths']		[]	= '/home/telcoblue/Incoming/cdr/unitel/mobilecdrbatches/';

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
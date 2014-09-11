<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['Regex']			= "/^W\d{9}\.[A-La-l][0-3]\d$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['Paths']		[]	= '/home/telcoblue/Incoming/cdr/aapt/';

// PROVISIONING
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]		['Regex']			= "/^..\d{6}\.\d{2}$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]		['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]		['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]		['Paths']		[]	= '/home/telcoblue/Incoming/cdr/aapt/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['Regex']			= "/^\d{8}\.(LSD|lsd)$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['Paths']		[]	= '/home/telcoblue/Incoming/cdr/aapt/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]			['Regex']			= "/^R\d{9}\.\d{5}$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]			['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]			['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]			['Paths']		[]	= '/home/telcoblue/Incoming/cdr/aapt/';

/*$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]		['Regex']			= "";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]		['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]			['DownloadUnique']	= TRUE;
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]		['Paths']		[]	= '/';*/

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
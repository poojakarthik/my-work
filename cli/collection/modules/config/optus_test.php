<?php

require_once("../../../../flex.require.php");

// Archived Files
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]	['Regex']			= "/^tap_[A-Za-z]{3}\d_\d{14}_\d{4}[01]\d[0-3]\d_\d{6}_a_[sl]\.dat$/misU";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]	['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]	['Paths']			[]	= '';

// General Files
$arrStructure['ARCHIVE']							['PathDefine']		= $arrArchiveFiles;
$arrStructure['ARCHIVE']							['Regex']			= "/^tap_[A-Za-z]{3}\d_\d{14}_\d{4}[01]\d[0-3]\d_\d{6}_a_[sl]\.dat.zip$/misU";
$arrStructure['ARCHIVE']							['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure['ARCHIVE']							['Paths']			[]	= '';
$arrStructure['ARCHIVE']							['DownloadOnly']	= TRUE;
$arrStructure['ARCHIVE']							['ArchiveType']		= 'zip';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]		['Regex']			= "/^tap_[A-Za-z]{3}\d_\d{14}_\d{4}[01]\d[0-3]\d_\d{6}_a_[sl]\.dat$/misU";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]		['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]		['Paths']			[]	= '';


// Config
$arrModuleConfig['Host']			['Value']		= '10.50.50.132';
$arrModuleConfig['Username']		['Value']		= 'telcoblue';
$arrModuleConfig['Password']		['Value']		= 'V01ceT4!k';
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
<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Regex']		= "/^[A-Za-z]\d{7}\.csv$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Paths']		[]	= '/cdrbatches/';
//$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD]				['Paths']		[]	= '/cdrbatches/archive/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E]				['Regex']		= "/^[A-Za-z]{3}(On|Off)netBatch_SE_[A-Z]\d{5}_\d{8}.csv$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E]				['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E]				['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E]				['Paths']		[]	= '/cdrbatchesoffnet/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['Regex']		= "/^[A-Za-z]\d{7}\.csv$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE]				['Paths']		[]	= '/mobilecdrbatches/';

// PROVISIONING
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER]	['Regex']		= "/^rsl\d{3}w\d{4}\d{4}[01]\d[0-3]\d.txt$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER]	['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER]	['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER]	['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER]	['Paths']		[]	= '/ebill_dailyorderfiles/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS]	['Regex']		= "/^rsl\d{3}d\d{4}[01]\d[0-3]\d\_[0-2]\d[0-5]\d[0-5]\d.txt$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS]	['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS]	['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS]	['Paths']		[]	= '/ebill_dailyorderfiles/dsc_reports/';
//$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS]	['Paths']		[]	= '/ebill_dailyorderfiles/dsc_reports/archive/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS]		['Regex']		= "/^rsl\d{3}a\d{4}\d{4}[01]\d[0-3]\d.txt$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS]		['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS]		['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS]		['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS]		['Paths']		[]	= '/reports/ebill_agreedservicebaskets/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION]	['Regex']		= "/^rssaw\d{4}\d{4}[01]\d[0-3]\d$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION]	['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION]	['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION]	['Paths']		[]	= '/dailychurn/';
//$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION]	['Paths']		[]	= '/dailychurn/sentreports/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS]	['Regex']		= "/^LineStatus.csv$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS]	['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS]	['FTPMode']		= FTP_ASCII;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS]	['Paths']		[]	= '/reports/linestatus/';

// Config
$arrModuleConfig['Host']			['Value']		= 'ftp.rslcom.com.au';
$arrModuleConfig['Username']		['Value']		= 'sp321';
$arrModuleConfig['Password']		['Value']		= 'KfYRSBOgm4Ci';
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
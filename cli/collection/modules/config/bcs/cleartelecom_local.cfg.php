<?php

require_once("../../../../../flex.require.php");

// File Types
$arrFileTypes	= array();

// -- Long Distance
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]							= array();
//$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['strRegex']				= "/^\d{14}$/i";
//$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['strImportUniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['Regex']					= "/^W\d{9}\.[A-La-l][0-3]\d$/i";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['DownloadUnique']		= true;

// -- Full Service
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE]							= array();
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE]['Regex']					= "/^EBILLDAY\d{2}\d{2}\d{2}\_APe$/i";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE]['DownloadUnique']		= true;

// -- Monthly Invoice (S&E)
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE]							= array();
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE]['Regex']					= "/^EBILLDAY\d{2}\d{2}\d{2}\_APe$/i";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE]['DownloadUnique']		= true;


// Directory Structure
$arrDirectories	= array();

// (/YYYY/__FILES__)
$arrDirectories['home']['arrSubdirectories']['rdavis']['arrSubdirectories']['bcs']['arrSubdirectories']['cdr']['arrSubdirectories']['clear-telecoms']['arrSubdirectories']['regex:/^\d{4}$/']['arrFileTypes'][RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				= &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD];
$arrDirectories['home']['arrSubdirectories']['rdavis']['arrSubdirectories']['bcs']['arrSubdirectories']['cdr']['arrSubdirectories']['clear-telecoms']['arrSubdirectories']['regex:/^\d{4}$/']['arrFileTypes'][RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE]		= &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE];
$arrDirectories['home']['arrSubdirectories']['rdavis']['arrSubdirectories']['bcs']['arrSubdirectories']['cdr']['arrSubdirectories']['clear-telecoms']['arrSubdirectories']['regex:/^\d{4}$/']['arrFileTypes'][RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE]	= &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE];

// Config
$arrModuleConfig['FileDefine']		['Value']		= $arrDirectories;

// Output
$strOutputFile	= basename(__FILE__, '.cfg.php').'.serialised';
@unlink($strOutputFile);

if (file_put_contents($strOutputFile, serialize($arrDirectories)))
{
	echo "\nSerialised Data successfully dumped to '$strOutputFile'.\n\n";
}
else
{
	echo "\nUnable to dump serialised data to '$strOutputFile'.\n\n";
}
?>
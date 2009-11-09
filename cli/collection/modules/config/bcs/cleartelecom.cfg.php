<?php

require_once("../../../../../flex.require.php");

// File Types
$arrFileTypes	= array();

// -- Data Usage 
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]							= array();
//$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['strRegex']				= "/^\d{14}$/i";
//$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['strImportUniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['Regex']					= "/^W\d{9}\.[A-La-l][0-3]\d$/i";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]['DownloadUnique']		= true;

// Directory Structure
$arrDirectories	= array();

// (/YYYY/__FILES__)
$arrDirectories['regex:/^\d{4}$/']['arrFileTypes'][RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]	= &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD];

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
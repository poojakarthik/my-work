<?php

require_once("../../../../../flex.require.php");
/*
// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Regex']			= "/^\d{14}$/i";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['DownloadUnique']	= true;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Recursive']		= true;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]				['Paths']			[]	= '/\/home\/TB\/gowireless\/usage\/\d{4}\/\d{2}\//';

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
*/


// NEW FILE DEFINE FORMAT

// File Types
$arrFileTypes	= array();

// -- Data Usage 
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]							= array();
//$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]['strRegex']				= "/^\d{14}$/i";
//$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]['strImportUniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]['Regex']				= "/^\d{14}$/i";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]['DownloadUnique']		= true;

// Directory Structure
$arrDirectories	= array();
$arrDirectories['home']['arrSubdirectories']['TB']['arrSubdirectories']['gowireless']['arrSubdirectories']['usage']['arrSubdirectories']['regex:\d{4}']['arrSubdirectories']['regex:\d{2}']['arrFileTypes'][RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA]	= &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA];

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
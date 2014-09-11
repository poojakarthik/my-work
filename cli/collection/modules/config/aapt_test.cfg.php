<?php

require_once("../../../../flex.require.php");
 
// Archived Files
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]		['Regex']			= "/^W\d{9}\.[A-La-l][0-3]\d$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]		['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]		['Paths']			[]	= '';

// General Files
$arrStructure['XML_ARCHIVE']						['PathDefine']		= $arrArchiveFiles;
$arrStructure['XML_ARCHIVE']						['Regex']			= "/^AAPT_NewFiles_\d{4}-\d{2}-\d{2}_\d{6}\.zip$/";
$arrStructure['XML_ARCHIVE']						['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure['XML_ARCHIVE']						['Paths']			[]	= '';
$arrStructure['XML_ARCHIVE']						['DownloadOnly']	= TRUE;
$arrStructure['XML_ARCHIVE']						['ArchiveType']		= 'zip';
$arrStructure['XML_ARCHIVE']						['ArchivePassword']	= 'zbj6v04ls';

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
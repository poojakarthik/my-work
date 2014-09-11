<?php

require_once("../../../../../flex.require.php");

// CDR
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['Regex']		= "/^W\d{9}\.[A-La-l][0-3]\d$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD]				['Paths']		[]	= '/';

// PROVISIONING
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]	['Regex']		= "/^..\d{6}\.\d{2}$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]	['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN]	['Paths']		[]	= '/';

$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['Regex']		= "/^\d{8}\.(LSD|lsd)$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD]			['Paths']		[]	= '/';

$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]		['Regex']		= "/^R\d{9}\.\d{5}$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]		['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT]		['Paths']		[]	= '/';

/*$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]		['Regex']		= "";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]		['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS]		['Paths']		[]	= '/';*/

// Encapsulating Archive(s)
$arrStructure['XML_ARCHIVE']								['PathDefine']		= $arrArchiveFiles;
$arrStructure['XML_ARCHIVE']								['Regex']			= "/^(AAPT_|)(NewFiles)_\d{4}-\d{2}-\d{2}_\d{6}\.zip$/";
$arrStructure['XML_ARCHIVE']								['DownloadOnly']	= TRUE;
$arrStructure['XML_ARCHIVE']								['ArchiveType']		= 'zip';
$arrStructure['XML_ARCHIVE']								['ArchivePassword']	= 'zbj6v04ls';

// Config
$arrModuleConfig['Host']			['Value']		= 'https://wholesalebbs.aapt.com.au/';
$arrModuleConfig['Username']		['Value']		= 'telcoblue';
$arrModuleConfig['Password']		['Value']		= 'zbj6v04ls';
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
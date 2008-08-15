<?php

require_once("../../../../flex.require.php");

// Archived Files
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]			['Regex']		= "/^test1_(\d+).txt$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]			['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]			['Paths']		[]	= '/';

$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]			['Regex']		= "/^test2_(\d+).txt$/";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]			['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]			['Paths']		[]	= '/test2/';
$arrArchiveFiles[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]			['Paths']		[]	= '/';

// Archive
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['PathDefine']		= $arrArchiveFiles;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['Regex']			= "/^test_archive_(\d+).zip$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['FTPMode']			= FTP_BINARY;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['Paths']			[]	= '/home/telcoblue/Incoming/collection_test/';
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['DownloadOnly']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_ARCHIVE]		['ArchiveType']		= 'zip';

// Test files
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]				['Regex']		= "/^test1_(\d+).txt$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]				['FTPMode']		= FTP_BINARY;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]				['Paths']		[]	= '/home/telcoblue/Incoming/collection_test/test1/';
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_1]				['Paths']		[]	= '/home/telcoblue/Incoming/collection_test/test1/archive/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]				['Regex']		= "/^test2_(\d+).txt$/";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]				['FTPMode']		= FTP_BINARY;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]				['Paths']		[]	= '/home/telcoblue/Incoming/collection_test/test1/';
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_FLEX_TEST_2]				['Paths']		[]	= '/home/telcoblue/Incoming/collection_test/test2/';

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
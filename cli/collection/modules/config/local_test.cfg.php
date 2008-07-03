<?php

require_once("../../../../flex.require.php");

// Archived Files
$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_1]			['Regex']		= "/^test1_(\d+).txt$/";
$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_1]			['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_1]			['Paths']		[]	= '/';

$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_2]			['Regex']		= "/^test2_(\d+).txt$/";
$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_2]			['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_2]			['Paths']		[]	= '/test2/';
$arrArchiveFiles[FILE_IMPORT_FLEX_TEST_2]			['Paths']		[]	= '/';

// Archive
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['PathDefine']		= $arrArchiveFiles;
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['Regex']			= "/^test_archive_(\d+).zip$/";
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['FTPMode']			= FTP_BINARY;
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['Paths']			[]	= '/home/rdavis/collection_test/';
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['DownloadOnly']	= TRUE;
$arrStructure[FILE_IMPORT_FLEX_TEST_ARCHIVE]		['ArchiveType']		= 'zip';

// Test files
$arrStructure[FILE_IMPORT_FLEX_TEST_1]				['Regex']		= "/^test1_(\d+).txt$/";
$arrStructure[FILE_IMPORT_FLEX_TEST_1]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_FLEX_TEST_1]				['FTPMode']		= FTP_BINARY;
$arrStructure[FILE_IMPORT_FLEX_TEST_1]				['Paths']		[]	= '/home/rdavis/collection_test/test1/';
$arrStructure[FILE_IMPORT_FLEX_TEST_1]				['Paths']		[]	= '/home/rdavis/collection_test/test1/archive/';

$arrStructure[FILE_IMPORT_FLEX_TEST_2]				['Regex']		= "/^test2_(\d+).txt$/";
$arrStructure[FILE_IMPORT_FLEX_TEST_2]				['Uniqueness']	= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_FLEX_TEST_2]				['FTPMode']		= FTP_BINARY;
$arrStructure[FILE_IMPORT_FLEX_TEST_2]				['Paths']		[]	= '/home/rdavis/collection_test/test1/';
$arrStructure[FILE_IMPORT_FLEX_TEST_2]				['Paths']		[]	= '/home/rdavis/collection_test/test2/';

// Config
$arrModuleConfig['FileDefine']		['Value']		= $arrStructure;

// Output
$strOutputFile	= basename(__FILE__, '.cfg.php').'.serialised';
@unlink($strOutputFile);

if (file_put_contents($strOutputFile, serialize($arrModuleConfig['FileDefine'])))
{
	echo "\nSerialised Data successfully dumped to '$strOutputFile'.\n\n";
}
else
{
	echo "\nUnable to dump serialised data to '$strOutputFile'.\n\n";
}

?>
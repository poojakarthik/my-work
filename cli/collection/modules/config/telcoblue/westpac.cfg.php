<?php

require_once("../../../../../flex.require.php");

// Payments
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC]					['Regex']			= "/^[a-z]{3}\_\d{1,2}-\d{1,2}-\d{4}\_\d{1,10}\.CSV$/i";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC]					['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC]					['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC]					['Paths']			[]	= '/home/telcoblue/Incoming/bpay/';

$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	['Regex']			= "/^AUDES\d+\_\d+\_D\_\d+\.csv$/i";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	['Paths']			[]	= '/home/telcoblue/Incoming/westpac/ausde_export_reports/telcoblue';
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	['Paths']			[]	= '/home/telcoblue/Incoming/westpac/ausde_export_reports/voicetalk';
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT]	['Paths']			[]	= '/home/telcoblue/Incoming/westpac/ausde_export_reports/protalk';

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
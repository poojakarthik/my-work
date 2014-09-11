<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['Regex']			= "/^\w+ \d{6}\.txt$/i";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['DownloadUnique']	= TRUE;
$arrStructure[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['Paths']			[]	= '/home/telcoblue/Incoming/bill_express/';

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
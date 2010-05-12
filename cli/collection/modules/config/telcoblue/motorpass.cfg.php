<?php

require_once("../../../../../flex.require.php");

// File Types
$arrFileTypes	= array();

// -- Data Usage
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT]						= array();
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT]['Regex']				= "/^([a-z]+)\_BILLING_\d{8}\_\d{6}\.txt$/i";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT]['DownloadUnique']		= true;

// Directory Structure
$arrDirectories	= array();

$arrDirectories['home']['arrSubdirectories']['telcoblue']['arrSubdirectories']['Incoming']['arrSubdirectories']['motorpass']['arrFileTypes'][RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT]	= &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT];

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
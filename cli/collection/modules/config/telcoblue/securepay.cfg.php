<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD]				['Regex']			= "/^[a-z]{3}\d{2}_\d{4}-\d{2}-\d{2}_\d{2}_\d{1,4}\.txt$/i";
$arrStructure[FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD]				['DownloadUnique']	= TRUE;
$arrStructure[FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD]				['Paths']			[]	= '/home/telcoblue/Incoming/secure_pay/';

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
<?php

// CDR
$arrStructure[FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['Regex']			= "/^\w+ \d{6}\.txt$/i";
$arrStructure[FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['DownloadUnique']	= TRUE;
$arrStructure[FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD]				['Paths']			[]	= '/home/telcoblue/Incoming/bill_express/';

// Config
$arrModuleConfig['FileDefine']		['Value']		= $arrStructure;

?>
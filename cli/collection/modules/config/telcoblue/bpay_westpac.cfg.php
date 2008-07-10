<?php

// CDR
$arrStructure[FILE_IMPORT_PAYMENT_BPAY_WESTPAC]				['Regex']			= "/^[a-z]{3}\_\d{1,2}-\d{1,2}-\d{4}\_\d{1,10}\.CSV$/i";
$arrStructure[FILE_IMPORT_PAYMENT_BPAY_WESTPAC]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_PAYMENT_BPAY_WESTPAC]				['DownloadUnique']	= TRUE;
$arrStructure[FILE_IMPORT_PAYMENT_BPAY_WESTPAC]				['Paths']			[]	= '/home/telcoblue/Incoming/bpay/';

// Config
$arrModuleConfig['FileDefine']		['Value']		= $arrStructure;

?>
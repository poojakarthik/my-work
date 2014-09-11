<?php

// load framework
require_once("../../flex.require.php");

ob_start();
echo "\n\n[ INVOICE OUTPUT BACKUP ]\n\n";

// Backup
echo " * Backing up InvoiceOutput...\t\t\t";
ob_flush();
$qrySelectInto = new QuerySelectInto();
if (!$qrySelectInto->Execute("InvoiceOutputArchive", "InvoiceOutput"))
{
	// Err0r
	echo "[ FAILED ]\n\t-Reason: ".$qrySelectInto->Error()."\n\n";
	die;
}
echo "[   OK   ]\n";

// Truncate
echo " * Truncating InvoiceOutput...\t\t\t";
ob_flush();
$qryTruncate = new QueryTruncate();
if (!$qryTruncate->Execute("InvoiceOutput"))
{
	// Err0r
	echo "[ FAILED ]\n\t-Reason: ".$qryTruncate->Error()."\n\n";
	die;
}
echo "[   OK   ]\n\n";
die;

?>
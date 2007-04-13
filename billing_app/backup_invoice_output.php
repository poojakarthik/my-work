<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

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
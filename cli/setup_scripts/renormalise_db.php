#!/usr/bin/php
<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// RENORMALISE_DB
//----------------------------------------------------------------------------//
/**
 * renormalise_db
 *
 * Cleans the database for re-normalisation
 *
 * Truncates the Invoice, InvoiceOutput, InvoicePayment,
 * InvoiceTemp, Payment, ProvisioningExport, ProvisioningLog, Request, ServiceTotal,
 * ServiceTypeTotal.  Zeros out Service.CappedCharge and Service.UncappedCharge
 *
 * @file		clean_db.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste' Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// Tables to Truncate
//$arrTables[]	= "Invoice";
//$arrTables[]	= "InvoiceOutput";
//$arrTables[]	= "InvoicePayment";
//$arrTables[]	= "InvoiceTemp";
//$arrTables[]	= "Payment";
$arrTables[]	= "ProvisioningExport";
$arrTables[]	= "ProvisioningLog";
$arrTables[]	= "Request";
$arrTables[]	= "ServiceTotal";
$arrTables[]	= "ServiceTypeTotal";

// Truncate tables
$qryTruncate = new QueryTruncate();
foreach ($arrTables as $strTable)
{
	echo TruncateName(" + Truncating '$strTable'...", 60);
	if(!$qryTruncate->Execute($strTable))
	{
		// Error
		echo "[ FAILED ]\n\tReason: ".$qryTruncate->Error()."\n";
	}
	else
	{
		// OK
		echo "[   OK   ]\n";
	}
}

echo "\n";

// Zero-out fields
echo TruncateName(" + Zeroing Out Fields...", 60);
$qryUpdate = new Query();
if (($mixResults = $qryUpdate->Execute("UPDATE Service SET CappedCharge = 0, UncappedCharge = 0")) === FALSE)
{
	// ERROR
	echo "[ FAILED ]\n\tReason: ".$updZeroOut->Error()."\n";
}
else
{
	// OK
	echo "[   OK   ]\n";
}
?>

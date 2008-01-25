<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Revert a comitted billing run
//----------------------------------------------------------------------------//

// get invoice run
$strInvoiceRun = trim($argv[1]);

// die if no invoice run is set
if (!$strInvoiceRun)
{
	echo("\n\n-- FAIL : No Invoice Run --\n");
	Die();
}

// load application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// revert bill
$bolResponse = $appBilling->RevertInvoiceRun($strInvoiceRun);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
die();

?>

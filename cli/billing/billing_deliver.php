<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Commit a billing run
//----------------------------------------------------------------------------//

// Load Framework
require_once("../../flex.require.php");

// Parse Command Line Parameters
if (!($strInvoiceRun	= $argv[1]))
{
	CliEcho("\nPlease specify an InvoiceRun!\n");
	die;
}

// Load Application
$arrConfig					= LoadApplication();
$arrConfig['PrintingMode']	= 'FINAL';
$appBilling					= new ApplicationBilling($arrConfig);

// Build and Deliver the Output for the specified InvoiceRun
foreach ($appBilling->_arrBillOutput as $intModule=>&$bilOutputModule)
{
	// Build Output
	CliEcho("[ BUILDING OUTPUT FOR $intModule ]");
	if ($bilOutputModule->BuildOutput($strInvoiceRun))
	{
		// Deliver Output (disabled for the time being)
		CliEcho("[ DELIVERING OUTPUT FOR $intModule ]");
		$mixResult	= $bilOutputModule->SendOutput();
		
		if (is_array($mixResult))
		{
			CliEcho("Sending Output Failed!");
			// Which ones failed?
			// TODO
		}
	}
	else
	{
		// Error Generating Output
		CliEcho("Building Output Failed!");
	}
}

// finished
CliEcho("\n-- End of Billing::Deliver --");
?>
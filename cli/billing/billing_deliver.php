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


// Load Application
$arrConfig					= LoadApplication();
$arrConfig['PrintingMode']	= 'FINAL';
$appBilling					= new ApplicationBilling($arrConfig);

// Build and Deliver the Output for the specified InvoiceRun
foreach ($appBilling->_arrBillOutput as $intModule=>&$bilOutputModule)
{
	// Build Output
	if ($bilOutputModule->BuildOutput($strInvoiceRun))
	{
		// Deliver Output (disabled for the time being)
		//$mixResult	= $bilOutputModule->SendOutput();
		
		if (is_array($mixResult))
		{
			// Which ones failed?
			// TODO
		}
	}
	else
	{
		// Error Generating Output
		// TODO
	}
}

// finished
CliEcho("\n-- End of Billing::Deliver --");
?>
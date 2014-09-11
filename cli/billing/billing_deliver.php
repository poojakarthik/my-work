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
$arrModes			= Array();
$bolGeneratePDFs	= TRUE;
foreach ($argv as $strArg)
{
	switch (trim($strArg))
	{
		case '-b':
			$bolBuildOutput		= TRUE;
			break;
			
		case '-s':
			$bolSendOutput		= TRUE;
			break;
			
		case '-d':
			$bolGeneratePDFs	= FALSE;
			break;
			
		case '-e':
			$arrModes[]			= 'EMAIL';
			break;
		
		case '-p':
			$arrModes[]			= 'PRINT';
			break;
			
		default:
			$strInvoiceRun		= $strArg;
			break;
	}
}
if (!($strInvoiceRun))
{
	CliEcho("\nPlease specify an InvoiceRun!\n");
	die;
}
if ($argc === 2)
{
	$bolSendOutput	= TRUE;
	$bolBuildOutput	= TRUE;
}
if (!count($arrModes))
{
	$arrModes		= NULL;
}

// Load Application
$arrConfig					= LoadApplication();
$arrConfig['PrintingMode']	= 'FINAL';
$appBilling					= new ApplicationBilling($arrConfig);

// Build and Deliver the Output for the specified InvoiceRun
foreach ($appBilling->_arrBillOutput as $intModule=>&$bilOutputModule)
{
	// Build Output
	$bolBuildResult	= TRUE;
	if ($bolBuildOutput)
	{
		CliEcho("[ BUILDING OUTPUT FOR $intModule ]");
		if (!($bolBuildResult = $bilOutputModule->BuildOutput($strInvoiceRun)))
		{
			// Error Generating Output
			CliEcho("Building Output Failed!");
		}
	}
	
	// Deliver Output
	if ($bolSendOutput && $bolBuildResult)
	{
		CliEcho("[ DELIVERING OUTPUT FOR $intModule ]");
		$mixResult	= $bilOutputModule->SendOutput($strInvoiceRun, $arrModes, $bolDeliverOnly);
		
		if (is_array($mixResult))
		{
			CliEcho("Sending Output Failed!");
			// Which ones failed?
			// TODO
		}
	}
}

// finished
CliEcho("\n-- End of Billing::Deliver --");
?>
<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Run Billing and all supporting scripts
//----------------------------------------------------------------------------//

// Load the Framework
require_once("../../flex.require.php");

// Grab the lastest payment_terms details
$selPaymentTerms	= new StatementSelect("payment_terms", "*", "created <= NOW()", "id DESC", "1");
$mixResult			= $selPaymentTerms->Execute();
if ($mixResult === FALSE)
{
	CliEcho("ERROR: \$selPaymentTerms failed: ".$selPaymentTerms->Error());
	exit(1);
}
elseif (!$mixResult)
{
	CliEcho("ERROR: There are no Payment Terms defined (see payment_terms table)");
	exit(1);
}
$arrPaymentTerms	= $selPaymentTerms->Fetch();

// Calculate the next Invoice Date
$intInvoiceOffset	= $arrPaymentTerms['invoice_day'] - 1;
$strDate			= date("Y-m-01");
$intInvoiceDate		= strtotime("+{$intInvoiceOffset} days", strtotime($strDate));
if ($intInvoiceDate < strtotime(date("Y-m-d", time())))
{
	// Calculated date is in the past, add 1 month
	$intInvoiceDate			= strtotime("+{$intInvoiceOffset} days", strtotime("+1 months", strtotime($strDate)));
}
$intBronzeDate			= strtotime("+{$arrPaymentTerms['samples_bronze_days']} days", $intInvoiceDate);
$intSilverDate			= strtotime("+{$arrPaymentTerms['samples_silver_days']} days", $intInvoiceDate);
$intInternalInitialDate	= strtotime("+{$arrPaymentTerms['samples_internal_initial_days']} days", $intInvoiceDate);
$intInternalFinalDate	= strtotime("+{$arrPaymentTerms['samples_internal_final_days']} days", $intInvoiceDate);

$strInvoiceDate			= date("Y-m-d", $intInvoiceDate);
$strBronzeDate			= date("Y-m-d", $intBronzeDate);
$strSilverDate			= date("Y-m-d", $intSilverDate);
$strInternalInitialDate	= date("Y-m-d", $intInternalInitialDate);
$strInternalFinalDate	= date("Y-m-d", $intInternalFinalDate);
$strTodaysDate			= date("Y-m-d");

// What are we supposed to run today?
$strScript	= NULL;
if (substr($GLOBALS['**arrDatabase']['flex']['Database'], -8) !== '_working')
{
	CliEcho("Operating on the LIVE Server");
	// This is the Live Server
	if ($strInvoiceDate === $strTodaysDate)
	{
		// Today is the Invoice Run Day, so perform a Gold Run
		CliEcho("Gold Samples/Full Billing are Scheduled for today...");
		$strScript		= "billing.cfg.php";
		$strBillingMode	= 'gold';
	}
	else
	{
		CliEcho("Today ($strTodaysDate) is not Billing Day ($strInvoiceDate).");
	}
}
else
{
	CliEcho("This is the _working Server");
	// This is the Working/Samples Server
	$strBillingMode	= NULL;
	switch ($strTodaysDate)
	{
		case $strBronzeDate:
			CliEcho("Bronze Samples are Scheduled for today...");
			$strBillingMode	= 'bronze';
			break;
			
		case $strSilverDate:
			CliEcho("Silver Samples are Scheduled for today...");
			$strBillingMode	= 'silver';
			break;
			
		case $strInternalInitialDate:
			CliEcho("Initial Internal Samples are Scheduled for today...");
			$strBillingMode	= 'internalinitial';
			break;
			
		case $strInternalFinalDate:
			CliEcho("Final Internal Samples are Scheduled for today...");
			$strBillingMode	= 'internalfinal';
			break;
		
		default:
			// Nothing happens today
			CliEcho("No Billing is scheduled for today ($strTodaysDate)");
	}
	
	$strScript	= "billing_samples.cfg.php";
}

// Run the Billing/Samples Multipart Script
if ($strBillingMode)
{
	$strScript	.= " --BillingMode={$strBillingMode}";
	$strCommand	= "php multipart.php ".$strScript;
	
	// DEBUG
	//Debug($strCommand);
	//die;
	//DEBUG
	
	$strWorkingDirectory	= getcwd();
	chdir(BACKEND_BASE_PATH.'process/');
	$ptrProcess				= popen($strCommand, 'r');
	$arrBlank				= Array();
	stream_set_blocking($ptrProcess, 0);
	while (!feof($ptrProcess))
	{
		$arrProcess	= Array($ptrProcess);
		if (stream_select($arrProcess, $arrBlank, $arrBlank, 0, 500000))
		{
			// Check for output every 0.5s
			CliEcho(stream_get_contents($ptrProcess), FALSE);
		}
	}
	$intReturnCode = pclose($ptrProcess);
	
	chdir($strWorkingDirectory);
	
	exit($intReturnCode);
}
else
{
	// No Multipart Script to run
	CliEcho("No Multipart Script to run");
	Debug($arrPaymentTerms);
	exit(1);
}

?>

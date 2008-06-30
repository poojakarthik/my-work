<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Execute a billing run
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();
// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Retrieve Command Line Parameters
$strParam	= trim($argv[1]);
if (!$strParam)
{
	CliEcho("Please specify a Billing Execute mode! (gold, silver, bronze, internalinitial or internalfinal)");	
}
else
{
	// execute bill
	$strDateTime = date("Y-m-d H:i:s");
	SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing Started @ $strDateTime", "viXen Billing Started @ $strDateTime");
	$bolResponse = $appBilling->Execute($strParam);
	$strDateTime = date("Y-m-d H:i:s");
	SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing Ended @ $strDateTime", "viXen Billing Started @ $strDateTime");
}

// finished
$appBilling->FinaliseReport();
echo("\n\n-- End of Billing --\n");
echo "</pre>";

if ($bolResponse === FALSE)
{
	// An error occurred
	exit(1);
}
die;
?>
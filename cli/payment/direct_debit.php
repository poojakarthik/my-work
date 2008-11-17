<?php

// Framework & Application
require_once("../../flex.require.php");
$arrConfig		= LoadApplication();
$appPayments	= new ApplicationPayment($arrConfig);

define("PAYMENTS_DEBUG_MODE"	, FALSE);

$bolReportOnly	= FALSE;

// Parse CLI Parameters
foreach ($argv as $strArgument)
{
	switch ($strArgument)
	{
		// Report Only Mode
		case '-r':
			$bolReportOnly	= TRUE;
			break;
	}
}

// Run Direct Debits
$arrResponses	= $appPayments->RunDirectDebits($bolReportOnly);

$bolPassed	= TRUE;
foreach ($arrResponses as $arrResponse)
{
	$bolPassed	= ($arrResponse['Success'] === TRUE || $arrResponse === TRUE) ? $bolPassed : FALSE;
}

if ($bolPassed)
{
	// Direct Debits run successfully
	CliEcho("Direct Debits successfully run!");
	Debug($arrResponses);
	exit(0);
}
else
{
	// Error
	CliEcho("Direct Debits failed!");
	Debug($arrResponse);
	exit(1);
}
?>
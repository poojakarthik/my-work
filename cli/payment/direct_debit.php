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
$arrResponse	= $appPayments->RunDirectDebits($bolReportOnly);

if ($arrResponse['Success'] === TRUE || $arrResponse === TRUE)
{
	// Direct Debits run successfully
	CliEcho("Direct Debits successfully run!");
	Debug($arrResponse);
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
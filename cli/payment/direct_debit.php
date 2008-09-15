<?php

// Framework & Application
require_once("../../flex.require.php");
$arrConfig		= LoadApplication();
$appPayments	= new ApplicationPayment($arrConfig);

define("PAYMENTS_DEBUG_MODE"	, TRUE);

// Run Direct Debits
$arrResponse	= $appPayments->RunDirectDebits();

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
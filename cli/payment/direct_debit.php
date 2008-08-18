<?php

// Framework & Application
require_once("../../flex.require.php");
$arrConfig		= LoadApplication();
$appPayments	= new ApplicationPayment($arrConfig);

// Run Direct Debits
$arrResponse	= $appPayments->RunDirectDebits();

if ($arrResponse['Success'] === TRUE || $arrResponse === TRUE)
{
	// Direct Debits run successfully
	exit(0);
}
else
{
	// Error
	Debug($arrResponse['Description']);
}
?>
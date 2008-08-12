<?php

// Framework & Application
require_once("../../flex.require.php");
$arrConfig		= LoadApplication();
$appPayments	= new ApplicationPayment($arrConfig);

// Run Direct Debits
$appPayments->RunDirectDebits();
exit(0);
?>
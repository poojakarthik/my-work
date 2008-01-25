<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import and process payments
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appPayment = new ApplicationPayment($arrConfig);

$intPaymentId = (int)trim($argv[1]);

// Execute the application
if ($intPaymentId)
{
	echo ($appPayment->ReversePayment($intPaymentId)) ? "Reversed Payment #$intPaymentId\n" : "FAILED!\n" ;
}
else
{
	echo "FAILED: '".trim($argv[1])."' is not a valid Payment Id\n";
}

// finished
echo("\n-- End of Payments --\n");
echo "</pre>";
die();

?>

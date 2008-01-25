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
require_once("../framework/require.php");
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing Started @ $strDateTime", "viXen Billing Started @ $strDateTime");
$bolResponse = $appBilling->Execute();
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing Ended @ $strDateTime", "viXen Billing Started @ $strDateTime");

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";

?>
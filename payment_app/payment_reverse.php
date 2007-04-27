<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import and process payments
//----------------------------------------------------------------------------//
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appPayment = new ApplicationPayment($arrConfig);

// Execute the application
$appPayment->ReversePayment(33994);
//$appPayment->ReversePayment(34942, 22);

// finished
echo("\n-- End of Payments --\n");
echo "</pre>";
die();

?>

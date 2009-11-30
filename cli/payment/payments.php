<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import and process payments
//----------------------------------------------------------------------------//
require_once('../../lib/classes/Flex.php');
Flex::load();

// Ensure that Payments isn't already running, then identify that it is now running
Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_PROCESSING)->lock();

//require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appPayment = new ApplicationPayment($arrConfig);

// Execute the application
$appPayment->Execute();

// finished
echo("\n-- End of Payments --\n");
echo "</pre>";
die();

?>

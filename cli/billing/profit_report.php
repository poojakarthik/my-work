<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Generate a Profit Report
//----------------------------------------------------------------------------//

// load application
LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

$appBilling->GenerateProfitReport("/home/richdavis/Desktop/ProfitApr07.xls", '46362bac43428');

?>
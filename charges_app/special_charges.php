<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add special charges
//----------------------------------------------------------------------------//
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appCharge = new ApplicationCharge($arrConfig);

// Remove/Mark Inbound S&E CDRs
$appCharge->MarkInboundSAndECDR();

// Add Landline S&E Credits
$appCharge->AddLLSAndECredits();

// Add Inbound Service Fee
$appCharge->AddActiveInboundFees();

// Add Pinnacle Mobile Fees
$appCharge->AddPinnacleMobileFees();

// finished
echo("\n-- End of Charges::Special --\n");


















?>
<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add special charges
//----------------------------------------------------------------------------//
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appCharge = new ApplicationCharge($arrConfig);

// Remove/Mark Inbound S&E CDRs
$appCharge->MarkInboundSAndECDR();

// finished
echo("\n-- End of Charges::Special --\n");


















?>
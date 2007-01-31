<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once('application_loader.php');

echo "<pre>\n";

// Turn on reporting
$arrConfig['Reporting'] = TRUE;

// Application entry point - create an instance of the application object
$appRating = new ApplicationRating($arrConfig);

// Change status of all CDRs with missing rate 
$appRating->ReRate(CDR_RATE_NOT_FOUND);

// run the Rate method until there is nothing left to rate
while ($appRating->Rate())
{

}

// Empty the Donkey Account
Debug("Donkey Account = $".$appRating->_DonkeyAccount);

//TODO!!!! - send the report

// finished
echo("\n-- End of Rating --\n");
echo "</pre>\n";
die();




?>

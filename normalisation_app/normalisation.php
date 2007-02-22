<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// normalisation application
//----------------------------------------------------------------------------//
require_once('application_loader.php');

echo "<pre>\n";

// set addresses for report
$mixEmailAddress = 'flame@telcoblue.com.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// Change status of all CDRs with missing destination 
$appNormalise->ReNormalise(CDR_BAD_DESTINATION);

// Change status of all CDRs with missing owner 
$appNormalise->ReFindOwner(CDR_BAD_OWNER);

// Import lines from CDR files into the database
$appNormalise->Import();

// run the Normalise method until there is nothing left to normalise
while ($appNormalise->Normalise())
{
	//break;
}

// finished
echo("\n-- End of Normalisation --\n");
echo "</pre>\n";
die();

?>

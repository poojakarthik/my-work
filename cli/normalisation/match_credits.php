<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// normalisation application
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// set addresses for report
$mixEmailAddress = 'flame@telcoblue.com.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// run the Normalise method until there is nothing left to normalise
while ($appNormalise->MatchCredits() !== FALSE)
{
	//break;
}

// finished
echo("\n-- End of Normalisation --\n");
die();

?>

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

// Check for command line args
$intLimit = ((int)trim($argv[1])) ? (int)trim($argv[1]) : NULL;

// set addresses for report
$mixEmailAddress = 'flame@telcoblue.com.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// Import lines from CDR files into the database
$appNormalise->Import($intLimit);

// finished
echo("\n-- End of Normalise::ImportSingle --\n");
die();

?>

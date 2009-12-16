<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// normalisation application
//----------------------------------------------------------------------------//
require_once('../../lib/classes/Flex.php');
Flex::load();

// Ensure that Rating isn't already running, then identify that it is now running
Flex_Process::factory(Flex_Process::PROCESS_CDR_IMPORT)->lock();

//require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// Check for command line args
$intCDRLimit = ((int)trim($argv[1])) ? (int)trim($argv[1]) : NULL;

// set addresses for report
//$mixEmailAddress = 'flame@telcoblue.com.au';
$mixEmailAddress = 'rdavis@ybs.net.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// Import lines from CDR files into the database
$appNormalise->Import($intCDRLimit);

// finished
echo("\n-- End of Normalise::Import --\n");
die();

?>

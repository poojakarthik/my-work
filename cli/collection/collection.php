<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once('../../lib/classes/Flex.php');
Flex::load();

//require_once("../../flex.require.php");
$arrConfig = LoadApplication();

define('COLLECTION_DEBUG_MODE',		FALSE);

// Application entry point - create an instance of the application object
$appCollection = new ApplicationCollection($arrConfig);

// CLI Parameters
$iCarrierModuleId	= null;
if ($argc > 1)
{
	if (is_numeric($argv[1]))
	{
		$iCarrierModuleId	= (int)$argv[1];
	}
	else
	{
		throw new Exception("Invalid Carrier Module Id provided: '{$argv[1]}'");
	}
}

// run the thing
$intOldTrackErrors	= ini_set('track_errors', 1);
$appCollection->Collect($iCarrierModuleId);
ini_set('track_errors', $intOldTrackErrors);

// finished
CliEcho("\n-- End of Collection --\n");
exit(0);

?>

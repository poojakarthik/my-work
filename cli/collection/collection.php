<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

require_once("Archive/Tar.php");

define('COLLECTION_DEBUG_MODE',		FALSE);

// Application entry point - create an instance of the application object
$appCollection = new ApplicationCollection($arrConfig);

// CLI Parameters
$iCarrierId	= null;
if ($argc > 1)
{
	if (is_numeric($argv[1]))
	{
		$iCarrierId	= (int)$argv[1];
	}
	else
	{
		throw new Exception("Invalid Carrier Id provided: '{$argv[1]}'");
	}
}

// run the thing
$intOldTrackErrors	= ini_set('track_errors', 1);
$appCollection->Collect($iCarrierId);
ini_set('track_errors', $intOldTrackErrors);

// finished
CliEcho("\n-- End of Collection --\n");
exit(0);

?>

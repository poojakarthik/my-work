<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// normalisation application
//----------------------------------------------------------------------------//
// Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

// Ensure that Rating isn't already running, then identify that it is now running
Flex_Process::factory(Flex_Process::PROCESS_CDR_NORMALISATION)->lock();

//require_once('../../flex.require.php');
$arrConfig = LoadApplication();

echo "<pre>\n";

// Parse Command Line Arguments
$bolOnlyNew	= FALSE;
$bolImport	= FALSE;
foreach ($argv as $strArg)
{
	switch (trim($strArg))
	{
		case '-n':
			// Only Normalise new CDRs
			$bolOnlyNew = TRUE;
			break;
		
		case '-i':
			// Import before normalise
			$bolImport = TRUE;
			break;
	}
	
	// Parse LIMIT
	$intRemaining = ((int)$strArg) ? ((int)$strArg) : NULL;
}

// set addresses for report
$mixEmailAddress = 'flame@telcoblue.com.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// Import if its a full run
if ($bolImport)
{
	Flex_Process::factory(Flex_Process::PROCESS_CDR_IMPORT)->lock();
	$appNormalise->Import();
	Flex_Process::factory(Flex_Process::PROCESS_CDR_IMPORT)->unlock();
}

// run the Normalise method until there is nothing left to normalise
$intNormalisedTotal = 0;
while (($intRemaining > 0 || $intRemaining === NULL) && $intNormalised = $appNormalise->Normalise($intRemaining, $bolOnlyNew))
{
	// Subtract from remaining (if a limit was specified)
	if ($intRemaining)
	{
		$intRemaining -= $intNormalised;
	}
	// break;
}

// finished
echo("\n-- End of Normalisation --\n");
echo "</pre>\n";
die();

?>

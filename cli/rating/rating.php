<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

define('FLEX_RATING_BATCH_SIZE'	, 1000);

echo "<pre>\n";

// Turn on reporting
$arrConfig['Reporting'] = TRUE;

// Parse Command Line Arguments
$bolOnlyNew	= FALSE;
$intLimit	= NULL;
foreach ($argv as $intIndex=>$strArg)
{
	if ($intIndex > 0)
	{
		switch (trim($strArg))
		{
			case '-n':
				// Only Rate new CDRs
				$bolOnlyNew = TRUE;
				break;
			
			default:
				if ((int)$strArg)
				{
					// Limit
					$intLimit	= (int)$strArg;
				}
		}
	}
}

// Application entry point - create an instance of the application object
$appRating = new ApplicationRating($arrConfig);

// Change status of all CDRs with missing rate 
$appRating->ReRate(CDR_RATE_NOT_FOUND);

if ($intLimit)
{
	CliEcho("\n *** Rating Run Limit set to $intLimit");
}
else
{
	CliEcho("\n *** No Rating Run Limit set; will rate until no CDRs left");
}

// run the Rate method until there is nothing left to rate
$mixRemaining	= ($intLimit) ? $intLimit : TRUE;
$intBatch		= FLEX_RATING_BATCH_SIZE;
while ($mixRemaining)
{
	if (is_int($mixRemaining))
	{
		CliEcho("\n *** $mixRemaining CDRs remaining...");
		
		if ($intBatch > $mixRemaining)
		{
			$intBatch		= $mixRemaining;
			$mixRemaining	= 0;
		}
		else
		{
			$mixRemaining	= ($mixRemaining - $intBatch);
		}
	}
	
	// Rate this batch
	CliEcho(" *** Rating Batch of $intBatch");
	if ($appRating->Rate($bolOnlyNew, $intBatch) === FALSE)
	{
		// If there is nothing left to rate, then exit
		break;
	}
}

// Check our profit margin
Debug("Profit Margin: ".$appRating->GetMargin(49, 1000, 'flame@voiptelsystems.com.au, rich@voiptelsystems.com.au')."%");

// Empty the Donkey Account
Debug("Donkey Account = $".$appRating->_DonkeyAccount);

//TODO!!!! - send the report

// finished
echo("\n-- End of Rating --\n");
echo "</pre>\n";
die();




?>

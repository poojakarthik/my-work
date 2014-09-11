<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating a single CDR
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// load rating class
$appRating = new ApplicationRating($arrConfig);

// Select All RATE_NOT_FOUND Cdrs
$selRateNotFoundCDRs = new StatementSelect("CDR", "Id, Service", "Status = 151 AND StartDatetime > '2007-04-01'");
$intTotal = $selRateNotFoundCDRs->Execute();
echo "<pre>";

// Rate the CDR
$intRated = 0;
$arrServices = Array();
while ($arrCDR = $selRateNotFoundCDRs->Fetch())
{
	if ($fltCharge = $appRating->RateCDR($arrCDR['Id']))
	{
		//echo "\nCDR {$arrCDR['Id']} Rated at : $".money_format('%i',$fltCharge);
		$intRated++;
	}
	else
	{
		//echo "\nCouldnt Rate CDR {$arrCDR['Id']}!";
		
		if (!in_array($arrCDR['Service'], $arrServices))
		{
			$arrServices[] = $arrCDR['Service'];
			echo "{$arrCDR['Service']}\n";
		}
	}
	ob_flush();
}

$intServiceCount = count($arrServices);
echo "\n\nRated $intRated of $intTotal CDRs.  $intServiceCount Unique Services that couldnt be rated";

die();
?>

<?php

$strLastBillDate	= '2008-02-01 00:00:00';

// Load Framework
require_once("../../flex.require.php");

$arrCols = Array();
$arrCols['LastChargedOn']	= new MySQLFunction("NOW()");
$ubiServiceRatePlan	= new StatementUpdateById("ServiceRatePlan", $arrCols);
$selServiceRatePlan	= new StatementSelect("ServiceRatePlan", "*", "Active = 1 AND StartDatetime < '$strLastBillDate'");
$selService			= new StatementSelect("Service", "Id", "Id = <Service> AND EarliestCDR IS NOT NULL AND EarliestCDR < '$strLastBillDate'");

// Get all Active ServiceRatePlan Records before this month
$intUpdated	= 0;
$intSkipped	= 0;
$strOutput	= "";
$intTotal	= $selServiceRatePlan->Execute();
while ($arrServiceRatePlan = $selServiceRatePlan->Fetch())
{
	// Has this Service received a CDR before the last bill?
	if ($selService->Execute($arrServiceRatePlan))
	{
		// Update the ServiceRatePlan entry
		$arrServiceRatePlan['LastChargedOn']	 = new MySQLFunction("NOW()");
		$ubiServiceRatePlan->Execute($arrServiceRatePlan);
		$intUpdated++;
	}
	else
	{
		$intSkipped++;
	}
	
	$intRemaining	= $intTotal - $intUpdated - $intSkipped;
	$intLength		= strlen($strOutput);
	CliEcho("\033[{$intLength}D", FALSE);
	$strOutput		= "Updated: $intUpdated; Skipped: $intSkipped; Remaining: $intRemaining";
	CliEcho($strOutput, FALSE);
}
?>
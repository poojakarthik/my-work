<?php

$strLastBillDate	= '2008-02-01 00:00:00';

// Load Framework
require_once("../../flex.require.php");

$selServiceRatePlan	= new StatementSelect("ServiceRatePlan", "*", "Active = 1 AND StartDatetime < '$strLastBillDate'");
$selService			= new StatementSelect("Service", "*", "EarliestCDR IS NOT NULL AND EarliestCDR < '$strLastBillDate'");

// Get all Active ServiceRatePlan Records before this month
$intUpdated	= 0;
$intTotal	= $selServiceRatePlan->Execute();
while ($arrServiceRatePlan = $selServiceRatePlan->Fetch())
{
	// Has this Service received a CDR before the last bill?
	if ($selService->Execute($arrServiceRatePlan))
	{
		$intUpdated++;
	}
}

?>
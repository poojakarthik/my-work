<?php

require_once("../../flex.require.php");
LoadApplication('cli/rating');
$appRating	= new ApplicationRating();

// Statements
$arrCols				= Array();
$arrCols['Charge']		= NULL;
$ubiCDR					= new StatementUpdateById('CDR', $arrCols);

$selOldSandECDRs		= new StatementSelect("CDR", "*", "StartDatetime < <StartDatetime> AND RecordType = 21 AND Service = <Service> AND Status = 150");

$selMinMonthlyServices	= new StatementSelect(	"(Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service) JOIN RatePlan ON RatePlan.Id = ServiceRatePlan.RatePlan",
												"Account, Service, FNN, StartDatetime",
												"WHERE Service.ServiceType = 102 AND MinMonthly > 0.0 AND ServiceRatePlan.Id = (SELECT Id FROM ServiceRatePlan WHERE Service = Service.Id AND Active = 1 AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC LIMIT 1)");




// Get Services who are currently on a MinMonthly Plan
$intTotal	= $selMinMonthlyServices->Execute();
while ($arrService = $selMinMonthlyServices->Fetch())
{	
	// Do we have any guilty CDRs?
	if (!$selOldSandECDRs->Execute($arrService))
	{
		// Service has no guilty CDRs
		//CliEcho("\t\t\t[  SKIP  ]");
		
		if ($selOldSandECDRs->Error())
		{
			Debug($selOldSandECDRs->Error());
		}
	}
	else
	{
		CliEcho("\n\n * Account: {$arrService['Account']}; Service: {$arrService['Service']}", FALSE);
	}
	
	// Fix each CDR
	while ($arrCDR = $selOldSandECDRs->Fetch())
	{
		CliEcho("\n\t + {$arrCDR['Description']} ({$arrCDR['StartDatetime']}) ...");
		
		// Fake StartDatetime
		$arrCDR['StartDatetime']	= date("Y-m-d H:i:s");
		$fltCharge = $appRating->RateCDR($arrCDR);
		CliEcho("\t\tOld Charge: \${$arrCDR['Charge']}; New Charge: \${$fltCharge}");
		
		// Update CDR
		// TODO
	}
}

CliEcho("Total: $intTotal");

?>
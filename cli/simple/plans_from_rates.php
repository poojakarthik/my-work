<?php

require_once("../../flex.require.php");
											
$selRates			= new StatementSelect(	"Rate",
											"*, Id AS Rate",
											"RecordType NOT IN (27, 28)");

$selPlanFromRate	= new StatementSelect(	"(RateGroupRate RGR JOIN RatePlanRateGroup RPRG ON RGR.RateGroup = RPRG.RateGroup) JOIN RatePlan ON RatePlan.Id = RPRG.RatePlan",
											"RatePlan.*",
											"RGR.Rate = <Rate>");


// Get Rate Information
CliEcho("[ GETTING RATE INFO ]\n");
$selRates->Execute();
while ($arrRate = $selRates->Fetch())
{
	$strCapped	= ($arrRate['Uncapped']) ? 'Ex Cap' : 'In Cap';
	CliEcho("\n\t + {$arrRate['Name']} ($strCapped)...");
	
	// See if any other Plans use this Rate
	if ($selPlanFromRate->Execute($arrRate))
	{
		// List the linked Plans
		while ($arrLinkedPlans = $selPlanFromRate->Fetch())
		{
			CliEcho("\t\t + {$arrLinkedPlans['Name']}");
		}
	}
	elseif ($selPlanFromRate->Error())
	{
		Debug($selPlanFromRate->Error());
		die;
	}
}

CliEcho("\nDone!\n");

?>
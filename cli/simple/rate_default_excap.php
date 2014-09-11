<?php

require_once("../../flex.require.php");

$selPlans			= new StatementSelect(	"RatePlan", "*, Id AS RatePlan", "1");

$selRateGroups		= new StatementSelect(	"RateGroup JOIN RatePlanRateGroup ON RateGroup.Id = RatePlanRateGroup.RateGroup",
											"RateGroup.*, RateGroup",
											"RatePlan = <RatePlan> AND RecordType NOT IN (27, 28)");
											
$selRates			= new StatementSelect(	"RateGroupRate RGR JOIN Rate R ON R.Id = RGR.Rate",
											"R.*, Rate",
											"RateGroup = <RateGroup> AND R.RecordType NOT IN (27, 28)");

$selPlanFromRate	= new StatementSelect(	"(RateGroupRate RGR JOIN RatePlanRateGroup RPRG ON RGR.RateGroup = RPRG.RateGroup) JOIN RatePlan ON RatePlan.Id = RPRG.RatePlan",
											"RatePlan.*",
											"RGR.Rate = <Rate> AND RatePlan.Id != <RatePlan>");

// Retrieve Info for all Plans
CliEcho("\n * Getting Plans...");
$selPlans->Execute();
while ($arrPlan = $selPlans->Fetch())
{
	CliEcho("\n\t + {$arrPlan['Name']}...");
	
	// Get RateGroup Information
	$selRateGroups->Execute($arrPlan);
	while ($arrRateGroup = $selRateGroups->Fetch())
	{
		CliEcho("\n\t\t + {$arrRateGroup['Name']}...");
		
		// Get Rate Information
		$selRates->Execute($arrRateGroup);
		while ($arrRate = $selRates->Fetch())
		{
			$strCapped	= ($arrRate['Uncapped']) ? 'Ex Cap' : 'In Cap';
			CliEcho("\n\t\t\t + {$arrRate['Name']} ($strCapped)...");
			
			// See if any other Plans use this Rate
			if ($selPlanFromRate->Execute(Array('Rate' => $arrRate['Rate'], 'RatePlan' => $arrPlan['RatePlan'])))
			{
				// List the linked Plans
				while ($arrLinkedPlans = $selPlanFromRate->Fetch())
				{
					CliEcho("\t\t\t\t + {$arrLinkedPlans['Name']}\t\t\t\t\t[ MATCH ]");
				}
			}
			elseif ($selPlanFromRate->Error())
			{
				Debug($selPlanFromRate->Error());
				die;
			}
		}
	}
}

CliEcho("\nDone!\n");

?>
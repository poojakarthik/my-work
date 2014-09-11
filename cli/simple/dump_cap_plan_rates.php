<?php

require_once("../../flex.require.php");

// Statements
$selPlans			= new StatementSelect(	"RatePlan", "*, Id AS RatePlan", "MinMonthly > 0.0");

$selRateGroups		= new StatementSelect(	"(RateGroup JOIN RatePlanRateGroup ON RateGroup.Id = RatePlanRateGroup.RateGroup) JOIN RecordType ON RecordType.Id = RateGroup.RecordType",
											"RateGroup.*, RateGroup, RecordType.Description AS RecordType",
											"RatePlan = <RatePlan> AND RecordType NOT IN (27, 28)");
											
$selRates			= new StatementSelect(	"RateGroupRate RGR JOIN Rate R ON R.Id = RGR.Rate",
											"R.*, Rate",
											"RateGroup = <RateGroup> AND R.RecordType NOT IN (27, 28)");

CliEcho("\nOpening File...\n");

// Init File
$strDumpPath	= "/home/richdavis/Desktop/cap_plan_rates.csv";
$ptrFile		= fopen($strDumpPath, 'w');

// Add Header
$arrLine	= Array("Rate Plan", "Call Type", "Rate Name", "Current In/Ex Cap?", "Correct In/Ex Cap?");
WriteCSV($ptrFile, $arrLine);

CliEcho("Fetching Plan Info...");

// Get Plans
$selPlans->Execute();
while ($arrPlan = $selPlans->Fetch())
{
	WriteCSV($ptrFile, Array());
	
	CliEcho("\t + {$arrPlan['Name']}");
	
	$intPlanLine	= 0;
	$selRateGroups->Execute($arrPlan);
	while ($arrRateGroup = $selRateGroups->Fetch())
	{
		$intGroupLine	= 0;
		$selRates->Execute($arrRateGroup);
		while ($arrRate = $selRates->Fetch())
		{
			$arrLine = Array();
			$arrLine[]	= ($intPlanLine) ? '' : $arrPlan['Name'];
			$arrLine[]	= ($intGroupLine) ? '' : $arrRateGroup['RecordType'];
			$arrLine[]	= $arrRate['Name'];
			$arrLine[]	= ($arrRate['Uncapped'] ? 'EX' : 'IN');
			$arrLine[]	= '';
			
			WriteCSV($ptrFile, $arrLine);
			
			$intPlanLine++;
			$intGroupLine++;
		}
		WriteCSV($ptrFile, Array());
	}
}

fclose($ptrFile);
die;



function WriteCSV($ptrFile, $arrData, $strDelimiter = ';', $strEnclosed = '"')
{
	// Add Enclosed Characters
	foreach ($arrData as $intId=>$mixField)
	{
		$arrData[$intId]	= $strEnclosed.$mixField.$strEnclosed;
	}
	
	// Write to File
	$strLine	= implode($strDelimiter, $arrData);
	return fwrite($ptrFile, $strLine."\n");
}

?>
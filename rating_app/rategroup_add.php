<?php

$arrRates = Array();
$arrRates[]	= 91;		// Business Hours
$arrRates[]	= 92;		// Morning
$arrRates[]	= 93;		// Afternoon
$arrRates[]	= 94;		// Weekend
$arrRates[]	= 13974;	// Fleet

// Key = RatePlan; Value = Old RateGroup
$arrPlans = Array();
$arrPlans[100000001]	= 6;

require_once('../framework/require.php');

$arrRateGroup	= $GLOBALS['dbaDatabase']->FetchClean("RateGroup");

// Create RateGroup
$arrRateGroup['Name']			= "VirtualVoipFleet_LL-LL";
$arrRateGroup['Description']	= "VirtualVoipFleet_LL-LL";
$arrRateGroup['RecordType']		= 19;
$arrRateGroup['ServiceType']	= SERVICE_TYPE_LAND_LINE;
$arrRateGroup['Fleet']			= 1;
$arrRateGroup['Archived']		= 0;
$insRateGroup	= new StatementInsert("RateGroup");
$intRateGroup	= $insRateGroup->Execute($arrRateGroup);

Debug("RateGroup: ".$intRateGroup);


// Create RateGroupRates
$insRateGroupRate	= new StatementInsert("RateGroupRate");
foreach ($arrRates as $intRate)
{
	$arrRateGroupRate['RateGroup']	= $intRateGroup;
	$arrRateGroupRate['Rate']		= $intRate;
	$intRateGroupRate				= $insRateGroupRate->Execute($arrRateGroupRate);
}

// Update RatePlanRateGroup Records
$arrRateGroupRate = Array();
$arrRateGroupRate['RateGroup']	= $intRateGroup;
$updRatePlanRateGroup	= new StatementUpdate("RatePlanRateGroup", "RatePlan = <RatePlan> AND RateGroup = <RateGroup>", $arrRateGroupRate);
foreach ($arrPlans as $intPlan=>$intOldRateGroup)
{
	$arrWhere = Array();
	$arrWhere['RatePlan']	= $intPlan;
	$arrWhere['RateGroup']	= $intOldRateGroup;
	$updRatePlanRateGroup->Execute(Array('RateGroup' => $intRateGroup), $arrWhere);
}

?>
<?php

$arrRates = Array();
$arrRates[]	= 144;		// Residential
$arrRates[]	= 13975;	// Business
$arrRates[]	= 13976;	// Fax
$arrRates[]	= 133;		// ISDN HOME
$arrRates[]	= 134;		// ISDN 2
$arrRates[]	= 136;		// ISDN 10
$arrRates[]	= 138;		// ISDN 20
$arrRates[]	= 140;		// ISDN 30
$arrRates[]	= 124;		// Rebilled @ Cost


// Key = RatePlan; Value = Old RateGroup
$arrPlans = Array();
$arrPlans[30]	= 167;

require_once('../../flex.require.php');

$arrRateGroup	= DataAccess::getDataAccess()->FetchClean("RateGroup");

// Create RateGroup
$arrRateGroup['Name']			= "S&E-R2979-B3567";
$arrRateGroup['Description']	= "S&E Residential \$29.79 Business \$35.67";
$arrRateGroup['RecordType']		= 21;
$arrRateGroup['ServiceType']	= SERVICE_TYPE_LAND_LINE;
$arrRateGroup['Fleet']			= 0;
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
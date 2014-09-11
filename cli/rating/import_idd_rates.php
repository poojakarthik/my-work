<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import_idd_rates
//----------------------------------------------------------------------------//
/**
 * import_idd_rates
 *
 * Creates RateGroups for all IDD Rates, and adds them to the appropriate RatePlans
 *
 * Creates RateGroups for all IDD Rates, and adds them to the appropriate RatePlans
 *
 * @file		import_idd_rates.php
 * @language	PHP
 * @package		rating_application
 * @author		Rich 'Waste' Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

$insRateGroup = new StatementInsert("RateGroup");
$insRateGroupRate = new StatementInsert("RateGroupRate");
$insRatePlanRateGroup = new StatementInsert("RatePlanRateGroup");

$selRates = new StatementSelect("Rate", "Id, Name, RecordType, ServiceType", "RecordType = 27 OR RecordType = 28");
$selRates->Execute();
$arrRates = $selRates->FetchAll();

$arrOutput = Array();
$arrRateGroups = Array();

foreach($arrRates as $arrRate)
{
	// Generate RateGroup name
	$arrName = explode(" : ", $arrRate['Name']);
	$strRateGroupName = "IDD : " . $arrName[0];
	
	// Add to RateGroup array
	$arrRateGroups[$strRateGroupName][] = $arrRate;
	
	// Count how many records parsed for this RateGroup
	$arrOutput[$strRateGroupName]++;
}

foreach($arrRateGroups as $strName=>$arrRate)
{	
	
	$arrName = explode(" : ", $strName);

	// Which RatePlan to link up to
	switch ($arrName[1])
	{
		case "39c Cap Intl":
			$intRatePlanId = 21;
			break;
		case "Blue 15c CTM":
			$intRatePlanId = 22;
			break;
		case "Blue Virtual VOIP":
			$intRatePlanId = 23;
			break;
		case "Mobile Zero Plan":
			$intRatePlanId = 9;
			break;
		case "National 16":
			$intRatePlanId = 27;
			break;
		case "Residential":
			$intRatePlanId = 29;
			break;
		case "Tier 3 corporate capped":
			$intRatePlanId = 25;
			break;
		case "Tier 3 corporate Long Distance":
			echo "\t\t[ IGNORE ] Tier 3 corporate Long Distance\n";
			break;
		case "Tier 3 corporate Mobile Saver":
			$intRatePlanId = 31;
			break;
		case "True Blue Fleet":
			$intRatePlanId = 26;
			break;
		case "VoiceTalk":
			$intRatePlanId = 28;
			break;
		default:
			echo "\t\t\t\t[ FAILED ] Unable to match '$strName'\n";
			continue 2;
	}
	
	// Create RateGroup
	$arrRateGroupData['Name']			= $strName;
	$arrRateGroupData['Description']	= $strName;
	$arrRateGroupData['RecordType']		= $arrRate[0]['RecordType'];
	$arrRateGroupData['ServiceType']	= $arrRate[0]['ServiceType'];
	$arrRateGroupData['Fleet']			= 0;
	$arrRateGroupData['Archived']		= 0;
	$intInsertId = $insRateGroup->Execute($arrRateGroupData);
	/*
	if ($intInsertId === FALSE)
	{
		echo "BAD => ".$insRateGroup->Error();
		continue;
	}
	echo "OK";
	continue;*/
		
	foreach($arrRate as $arrRateDetails)
	{
		// Link Rate to RateGroup
		$arrRateGroupRateData['RateGroup']	= $intInsertId;
		$arrRateGroupRateData['Rate']		= $arrRateDetails['Id'];
		$insRateGroupRate->Execute($arrRateGroupRateData);
	}
	
	// Link RateGroup to RatePlan
	$arrRatePlanRateGroupData['RateGroup']	= $intInsertId;
	$arrRatePlanRateGroupData['RatePlan']	= $intRatePlanId;
	$insRatePlanRateGroup->Execute($arrRatePlanRateGroupData);

	echo "[  OK  ]\n";
}

//print_r($arrOutput);

?>

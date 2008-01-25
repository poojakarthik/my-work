<?php
die;	// No longer used
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// replace_servicerategroup
//----------------------------------------------------------------------------//
/**
 * replace_servicerategroup
 *
 * Global Definitions
 *
 * This file exclusively declares global constants
 *
 * @file		replace_servicerategroup.php
 * @language	PHP
 * @package		rating
 * @author		Rich Davis
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
/*// Blue15CTM ,VV, VVF, Voicetalk Capped, T3CC, T3LS S&E
$arrPlans = Array();
$arrPlans[]	= 100000001;
$arrPlans[]	= 22;
$arrPlans[]	= 23;
$arrPlans[]	= 25;
$arrPlans[]	= 28;
$arrPlans[]	= 30;

$intNewRateGroup	= 167;
$intRecordType		= 21;
$intFleet			= 0;

$arrOldRateGroups = Array();
$arrOldRateGroups[]	= 20;
$arrOldRateGroups[]	= 8;
$arrOldRateGroups[]	= 15;
$arrOldRateGroups[]	= 19;
$arrOldRateGroups[]	= 16;*/


/*// Virtual VOIP Fleet LL -> LL
$arrPlans = Array();
$arrPlans[]	= 100000001;

$intNewRateGroup	= 168;
$intRecordType		= 19;
$intFleet			= 1;

$arrOldRateGroups = Array();
$arrOldRateGroups[]	= 6;*/


/*// Virtual VOIP LL -> Mobile
$arrPlans = Array();
$arrPlans[]	= 23;

$intNewRateGroup	= 166;
$intRecordType		= 20;
$intFleet			= 0;

$arrOldRateGroups = Array();
$arrOldRateGroups[]	= 126;
$arrOldRateGroups[]	= 127;
$arrOldRateGroups[]	= 166;*/

require_once('../../flex.require.php');

$strPlans = implode(', ', $arrPlans);

$arrServiceRateGroup = Array();
$arrServiceRateGroup['Service']			= NULL;
$arrServiceRateGroup['RateGroup']		= $intNewRateGroup;
$arrServiceRateGroup['CreatedBy']		= 22;
$arrServiceRateGroup['CreatedOn']		= new MySQLFunction("NOW()");
$arrServiceRateGroup['StartDatetime']	= NULL;
$arrServiceRateGroup['EndDatetime']		= NULL;
$insServiceRateGroup	= new StatementInsert("ServiceRateGroup", $arrServiceRateGroup);
$selServices			= new StatementSelect("Service", "Id AS Service, FNN", "(SELECT RatePlan FROM ServiceRatePlan WHERE Service = Service.Id AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC LIMIT 1) IN ($strPlans)");
$selServiceRateGroup	= new StatementSelect("ServiceRateGroup JOIN RateGroup ON RateGroup.Id = ServiceRateGroup.RateGroup", "ServiceRateGroup.StartDatetime AS StartDatetime, ServiceRateGroup.EndDatetime, RateGroup.*", "Fleet = <Fleet> AND RecordType = <RecordType> AND Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", 1);

Debug("[ REPLACE SERVICERATEGROUP ]\n");

// Grab all Services on the RatePlan we want
$ptrFile = fopen("replaced_servicerategroups_".date("Ymd_His").".csv", 'w');
$intServiceCount = $selServices->Execute(Array('RatePlan' => $intPlan));
$arrRateGroupCount = Array();
while ($arrService = $selServices->Fetch())
{
	//CliEcho(" + Service {$arrService['FNN']} ({$arrService['Service']}) is on Plan $intPlan");
	
	$arrService['RecordType']	= $intRecordType;
	$arrService['Fleet']		= $intFleet;
	$selServiceRateGroup->Execute($arrService);
	$arrServiceRateGroupOld = $selServiceRateGroup->Fetch();
	
	$bolMatch = FALSE;
	foreach ($arrOldRateGroups as $intRateGroup)
	{
		if ($arrServiceRateGroupOld['Id'] == $intRateGroup)
		{
			$bolMatch = TRUE;
			break;
		}
	}
	
	$arrRateGroupCount[$arrServiceRateGroupOld['Name']]++;
	
	if (in_array($arrServiceRateGroupOld['Id'], $arrOldRateGroups))
	{
		$arrServiceRateGroup = Array();
		$arrServiceRateGroup['StartDatetime']	= $arrServiceRateGroupOld['StartDatetime'];
		$arrServiceRateGroup['EndDatetime']		= $arrServiceRateGroupOld['EndDatetime'];
		$arrServiceRateGroup['CreatedOn']		= new MySQLFunction("NOW()");
		$arrServiceRateGroup['CreatedBy']		= 22;
		$arrServiceRateGroup['RateGroup']		= $intNewRateGroup;
		$arrServiceRateGroup['Service']			= $arrService['Service'];
		$intInsertId = $insServiceRateGroup->Execute($arrServiceRateGroup);
	
		fwrite($ptrFile, "{$arrService['Service']},$intInsertId\n");
	}
}
fclose($ptrFile);

Debug($arrRateGroupCount);

Debug(" * $intServiceCount Services on Plan $intPlan");
?>
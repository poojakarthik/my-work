<?php
require_once('../../flex.require.php');

// Services to revert RateGroups to default on
$arrServices = Array(23443, 23444, 23445, 23446, 23447, 23448, 23449, 23450, 23451);

// Optional Start/EndDatetime overrides
$strStartDatetime	= "2006-01-01 00:00:00";
$strEndDatetime		= "2030-11-30 11:57:45";
$intCreatedBy		= 22;

$selDefaults			= new StatementSelect(	"RatePlanRateGroup JOIN RateGroup ON RatePlanRateGroup.RateGroup = RateGroup.Id",
												"RateGroup.*",
												"RatePlan = (SELECT RatePlan FROM ServiceRatePlan WHERE Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC LIMIT 1)");

$selServiceRateGroup	= new StatementSelect("ServiceRateGroup JOIN RateGroup ON ServiceRateGroup.RateGroup = RateGroup.Id", "ServiceRateGroup.*", "Service = <Service> AND RecordType = <RecordType> AND NOW() BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", 1);

$arrServiceRateGroup	= DataAccess::getDataAccess()->FetchClean("ServiceRateGroup");
unset($arrServiceRateGroup['Id']);
$arrServiceRateGroup['CreatedOn']	= new MySQLFunction("NOW()");
$insServiceRateGroup	= new StatementInsert("ServiceRateGroup", $arrServiceRateGroup);

foreach ($arrServices as $intService)
{
	CliEcho("\n + Updating RateGroups for Service #$intService...");
	
	$arrData = Array();
	$arrData['Service']	= $intService;
	
	// Get Default RateGroups for this Service's RatePlan
	$selDefaults->Execute($arrData);
	while ($arrDefault = $selDefaults->Fetch())
	{
		CliEcho("\t + Updating RecordType {$arrDefault['RecordType']} to RateGroup {$arrDefault['Id']}...\t", FALSE);
		
		// Get old ServiceRateGroup entry
		$arrData['RecordType']	= $arrDefault['RecordType'];
		$selServiceRateGroup->Execute($arrData);
		$arrServiceRateGroup	= $selServiceRateGroup->Fetch();
		
		$arrServiceRateGroup['StartDatetime']	= ($strStartDatetime)	? $strStartDatetime	: $arrServiceRateGroup['StartDatetime'];
		$arrServiceRateGroup['EndDatetime']		= ($strEndDatetime)		? $strEndDatetime	: $arrServiceRateGroup['EndDatetime'];
		
		// Add a new ServiceRateGroup entry
		unset($arrServiceRateGroup['Id']);
		$arrServiceRateGroup['Service']		= $intService;
		$arrServiceRateGroup['RateGroup']	= $arrDefault['Id'];
		$arrServiceRateGroup['CreatedOn']	= new MySQLFunction("NOW()");
		$arrServiceRateGroup['CreatedBy']	= $intCreatedBy;
		//Debug($arrServiceRateGroup);
		$intInsertId = $insServiceRateGroup->Execute($arrServiceRateGroup);
		if ($intInsertId === FALSE)
		{
			Debug($insServiceRateGroup->Error());
		}
		
		CliEcho("(Id = $intInsertId)");
	}
}
?>
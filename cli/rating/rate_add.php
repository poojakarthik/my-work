<?php


require_once('../../flex.require.php');


$arrRate			= DataAccess::getDataAccess()->FetchClean("Rate");

Debug($arrRate);

$arrRate['Name']			= "BusinessLine-3667";
$arrRate['Description']		= "\$36.67 Business Line Rental";
$arrRate['RecordType']		= 21;
$arrRate['ServiceType']		= SERVICE_TYPE_LAND_LINE;
$arrRate['PassThrough']		= 0;
$arrRate['StdUnits']		= 1;
$arrRate['StdRatePerUnit']	= 36.67;
$arrRate['StdFlagfall']		= 0;
$arrRate['StdPercentage']	= 0;
$arrRate['StdMarkup']		= 0;
$arrRate['StdMinCharge']	= 0;
$arrRate['ExsUnits']		= 1;
$arrRate['ExsRatePerUnit']	= 36.67;
$arrRate['ExsFlagfall']		= 0;
$arrRate['ExsPercentage']	= 0;
$arrRate['ExsMarkup']		= 0;
$arrRate['StartTime']		= "00:00:00";
$arrRate['EndTime']			= "23:59:59";
$arrRate['Monday']			= 1;
$arrRate['Tuesday']			= 1;
$arrRate['Wednesday']		= 1;
$arrRate['Thursday']		= 1;
$arrRate['Friday']			= 1;
$arrRate['Saturday']		= 1;
$arrRate['Sunday']			= 1;
$arrRate['Destination']		= 80004;
$arrRate['CapUnits']		= 0;
$arrRate['CapCost']			= 0;
$arrRate['CapUsage']		= 0;
$arrRate['CapLimit']		= 0;
$arrRate['Prorate']			= 1;
$arrRate['Fleet']			= 0;
$arrRate['Uncapped']		= 0;
$arrRate['Archived']		= 0;

$insRate	= new StatementInsert("Rate");
$intId		= $insRate->Execute($arrRate);

Debug($intId);
?>
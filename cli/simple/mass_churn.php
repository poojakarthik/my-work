<?php

require_once("../../flex.require.php");

// Output MySQL Timestamp
$selTimeStamp	= new StatementSelect("Config", "NOW() AS Timestamp", "1");
$selTimeStamp->Execute();
$arrTimeStamp	= $selTimeStamp->Fetch();
CliEcho("StartTime: {$arrTimeStamp['Timestamp']}");

// Statements
$arrCols = Array();
$arrCols['Carrier']	= NULL;
$ubiRequest			= new StatementUpdateById("Request", $arrCols);

$arrRequest = Array();
$arrRequest['Service']			= NULL;
$arrRequest['Employee']			= NULL;
$arrRequest['RequestDatetime']	= new MySQLFunction("NOW()");
$arrRequest['Status']			= NULL;
$arrRequest['Carrier']			= NULL;
$arrRequest['RequestType']		= NULL;
$insRequest			= new StatementInsert("Request", $arrRequest);

$selVTPlans			= new StatementSelect("RatePlan", "*", "CarrierFullService = 5 OR CarrierPreselection = 5");
$selActiveRequests	= new StatementSelect("Request", "Id, Carrier, RequestType", "Service = <Service> AND RequestType = <RequestType> AND Status = 300");
//$selBadRequests		= new StatementSelect("Request", "Id, Carrier, RequestType", "Service = <Service> AND RequestType IN (900, 901) AND Status = 301");
$selFNN				= new StatementSelect("Service", "FNN", "Id = <Service>");

$arrRequestTypes	= Array('FullService' => 900, 'Preselection' => 901);

CliEcho("\nGetting Plans...");

// Get all UnitelVT Plans
$arrPlanIds	= Array();
$arrPlans	= Array();
$selVTPlans->Execute();
while ($arrVTPlan = $selVTPlans->Fetch())
{
	$arrPlans[$arrVTPlan['Id']]	= $arrVTPlan;
	$arrPlanIds[]				= $arrVTPlan['Id'];
	CliEcho("\t + {$arrVTPlan['Name']} - FullService: {$arrVTPlan['CarrierFullService']}; Preselection: {$arrVTPlan['CarrierPreselection']}");
}

//Debug($arrPlans);

CliEcho("\nGetting Ignore List...");
$arrIgnoreList	= Array();
$ptrFile		= fopen('/home/richdavis/Desktop/UnitelVTIgnore.csv', 'r');
while (!feof($ptrFile))
{
	if ($intFNN	= (int)fgets($ptrFile))
	{
		$arrIgnoreList[]	= "0".$intFNN;
		CliEcho("\t + 0".$intFNN);
	}
}
CliEcho("\n\t * Ignoring ".count($arrIgnoreList)." Services");

CliEcho("\nGetting Services...");

// Find all people who have only ever been on a Voicetalk plan
$intBadRequest	= 0;
$intUpdated		= 0;
$intIgnored		= 0;
$i				= 0;
$strRatePlans	= implode(', ', $arrPlanIds);
$selServices	= new StatementSelect("ServiceRatePlan", "DISTINCT Service, RatePlan", "RatePlan IN ($strRatePlans) AND 1 = (SELECT COUNT(Id) FROM ServiceRatePlan SRP2 WHERE Service = ServiceRatePlan.Service)");
$intTotal		= $selServices->Execute();
while ($arrService = $selServices->Fetch())
{
	$i++;
	CliEcho("\t + ($i of $intTotal)\t", FALSE);	
	CliEcho("Service #{$arrService['Service']}... ", FALSE);
	
	// Is this on our ignore list?
	$selFNN->Execute($arrService);
	$arrFNN	= $selFNN->Fetch();
	if (in_array($arrFNN['FNN'], $arrIgnoreList))
	{
		$intIgnored++;
		CliEcho("IGNORED!");
		continue;
	}
	
	// Does this Service still have unsent Full Service and Preselection Requests?
	foreach ($arrRequestTypes as $strType=>$intType)
	{
		if ($selActiveRequests->Execute(Array('Service' => $arrService['Service'], 'RequestType' => $intType)))
		{
			$intUpdated++;
			
			// Update these requests
			while ($arrRequest = $selActiveRequests->Fetch())
			{
				$arrRequest['Carrier']	= $arrPlans[$arrService['RatePlan']]['CarrierPreselection'];
				CliEcho("$strType: {$arrRequest['Carrier']} (UPDATED); ", FALSE);
				
				// Update Request
				$ubiRequest->Execute($arrRequest);
			}
		}
		else
		{
			// Add the request
			$arrRequest = Array();
			$arrRequest['Service']			= $arrService['Service'];
			$arrRequest['Employee']			= 22;
			$arrRequest['RequestDatetime']	= new MySQLFunction("NOW()");
			$arrRequest['Status']			= 300;
			$arrRequest['Carrier']			= $arrPlans[$arrService['RatePlan']]['CarrierFullService'];
			$arrRequest['RequestType']		= $intType;
			$insRequest->Execute($arrRequest);
			CliEcho("$strType: {$arrRequest['Carrier']} (CREATED); ", FALSE);
			
		}
	}
	CliEcho('');
	/*else
	{
		//Debug($arrService);
		$intBadRequest++;		
		CliEcho("Creating Requests for Service #{$arrService['Service']}... ", FALSE);
		
		// All Bad Requests have been sent already, so create new ones to reverse the issue
		$arrRequest = Array();
		$arrRequest['Service']			= $arrService['Service'];
		$arrRequest['Employee']			= 22;
		$arrRequest['RequestDatetime']	= new MySQLFunction("NOW()");
		$arrRequest['Status']			= 300;
		
		// Full Service
		$arrRequest['Carrier']			= $arrPlans[$arrService['RatePlan']]['CarrierFullService'];
		$arrRequest['RequestType']		= 900;
		//$insRequest->Execute($arrRequest);
		CliEcho("FullService: {$arrRequest['Carrier']}; ", FALSE);
		
		// Preselection
		$arrRequest['Carrier']			= $arrPlans[$arrService['RatePlan']]['CarrierPreselection'];
		$arrRequest['RequestType']		= 901;
		//$insRequest->Execute($arrRequest);
		CliEcho("Preselection: {$arrRequest['Carrier']};");
	}*/
}

CliEcho("\n + Services Ignored: $intIgnored\n");

// Output MySQL Timestamp
$selTimeStamp->Execute();
$arrTimeStamp	= $selTimeStamp->Fetch();
CliEcho("EndTime: {$arrTimeStamp['Timestamp']}");


?>
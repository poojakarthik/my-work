<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selLostServices	= new StatementSelect(	"ProvisioningResponse", "DISTINCT Account, Service, FNN", "EffectiveDate BETWEEN <Start> AND <End> AND Type IN (916, 910) AND Status IN (401, 402)", "Account, FNN");

$selPeriodResponses	= new StatementSelect(	"ProvisioningResponse",
											"MAX(CASE WHEN Type IN (910, 916) THEN EffectiveDate ELSE NULL END) AS LostDate, MAX(CASE WHEN Type IN (900) THEN EffectiveDate ELSE NULL END) AS GainDate",
											"FNN = <FNN> AND EffectiveDate BETWEEN <Start> AND <End> AND Status IN (401, 402)");

$selCDRDetails		= new StatementSelect(	"Service", "MAX(LatestCDR) AS LatestCDR", "FNN = <FNN>");

$arrOutputTemplate	= Array(
								'Month'			=> NULL,
								'Account'		=> NULL,
								'FNN'			=> NULL,
								'Lost Date'		=> NULL,
								'Wonback Date'	=> NULL,
								'Latest CDR'	=> NULL,
								'Latest Status'	=> NULL
							);

// Init Periods
$strReportDate		= "2008-10-01";
$intReportDate		= strtotime($strReportDate);
$intMonth			= 3;

$strPeriodStart		= date("Y-m-d H:i:s", strtotime("-{$intHistoryMonths} months", $intReportDate));
$strPeriodEnd		= date("Y-m-d H:i:s", strtotime("-1 second", $intReportDate));
CliEcho("Report Period: {$strPeriodStart} to {$strPeriodEnd}");

$arrPeriods			= Array();
while ($intMonth > 0)
{
	// Calculate Periods
	$strPeriodName	= date("F y");
	$arrPeriods[$strPeriodName]['Start']	= date("Y-m-d H:i:s", strtotime("-{$intMonth} months", $intReportDate));
	$arrPeriods[$strPeriodName]['End']		= date("Y-m-d H:i:s", strtotime("-1 second", strtotime("-".($intMonth-1)." months", $intReportDate)));
	
	$intMonth--;
}
Debug($arrPeriods);

// Output file
$resOutputFile	= fopen("/home/rdavis/telcoblue_lost_customers_report.csv");

// Get the List of Services which were Lost at some stage in our period
if (($intTotal = $selLostServices->Execute(Array('Start'=>$strPeriodStart, 'End'=>$strPeriodEnd))) === FALSE)
{
	throw new Exception($selLostServices->Error());
}
$intCount	= 0;
while ($arrService = $selLostServices->Fetch())
{
	$intCount++;
	CliEcho(" + ({$intCount}/{$intTotal}) {$arrService['Account']}::{$arrService['FNN']}...", FALSE);
	
	$arrOutput	= $arrOutputTemplate;
	$arrOutput['Account']	= $arrService['Account'];
	$arrOutput['FNN']		= $arrService['FNN'];
	
	// Process each period
	foreach ($arrPeriods as $strPeriodName=>$arrDates)
	{
		CliEcho("{$strPeriodName}...");
		
		$arrOutput['Month']	= $strPeriodName;
		
		// Get the Latest Loss/Winback details for the period
		if ($selPeriodResponses->Execute(Array('FNN'=>$arrService['FNN'], 'Start'=>$arrDates['Start'], 'End'=>$arrDates['End'])) === FALSE)
		{
			throw new Exception($selPeriodResponses->Error());
		}
		$arrPeriodResponses			= $selPeriodResponses->Fetch();
		$arrOutput['Lost Date']		= $arrPeriodResponses['LostDate'];
		$arrOutput['Wonback Date']	= $arrPeriodResponses['GainDate'];
		
		// Calculate Latest Status
		if (!$arrPeriodResponses['LostDate'] && !$arrPeriodResponses['GainDate'])
		{
			$arrOutput['Latest Status']	= NULL;
		}
		elseif ($arrPeriodResponses['LostDate'] > $arrPeriodResponses['GainDate'])
		{
			$arrOutput['Latest Status']	= "Lost";
		}
		else
		{
			$arrOutput['Latest Status']	= "Wonback";
		}
		
		// Get CDR Details
		if ($selCDRDetails->Execute($arrService) === FALSE)
		{
			throw new Exception($selCDRDetails->Error());
		}
		$arrCDRDetails				= $selCDRDetails->Fetch();
		$arrOutput['Latest CDR']	= $arrCDRDetails['LatestCDR'];
		
		// Dump this line
		fwrite($resOutputFile, '"'.implode('","', $arrOutput).'"'."\n");
	}
}
CliEcho();
exit(0);
?>
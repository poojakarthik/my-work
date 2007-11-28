<?php

//----------------------------------------------------------------------------//
// Finds the earliest and latest CDR for a given Service
//----------------------------------------------------------------------------//

require_once("../framework/require.php");

$arrCols = Array();
$arrCols['EarliestCDR']	= NULL;
$arrCols['LatestCDR']	= NULL;
$ubiService	= new StatementUpdateById("Service", $arrCols);
$selService	= new StatementSelect("Service", "*", "1");
$selCDRs	= new StatementSelect("CDR USE INDEX (Service)", "Service AS Id, MIN(StartDatetime) AS EarliestCDR, MAX(StartDatetime) AS LatestCDR", "Service = <Id> AND Status IN (150, 198, 199)", NULL, NULL, "Service");

// Get Earliest and Latest CDR Dates
$intCount	= $selService->Execute();
$intI		= 0;
while ($arrService = $selService->Fetch())
{
	// Quick Run: Disable for full run
	if ($arrCDRs['EarliestCDR'])
	{
		continue;
	}
	
	$intI++;
	$strI		= str_pad($intI, strlen($intCount), '0', STR_PAD_LEFT);
	$strService	= str_pad($arrService['Id'], strlen($intCount), '0', STR_PAD_LEFT);
	CliEcho(" + ($strI/$intCount) Updating Service $strService: ", FALSE);
	
	// Get CDR Info
	$selCDRs->Execute($arrService);
	$arrCDRs	= $selCDRs->Fetch();
	$strEarliest	= ($arrCDRs['EarliestCDR'])	? $arrCDRs['EarliestCDR']	: '               NULL';
	$strLatest		= ($arrCDRs['LatestCDR'])	? $arrCDRs['LatestCDR']		: 'NULL               ';
	CliEcho("$strEarliest/$strLatest...", FALSE);
	
	// Update Service Table
	$ubiService->Execute($arrCDRs);
	CliEcho("\t\t\t[  DONE  ]");
}
?>
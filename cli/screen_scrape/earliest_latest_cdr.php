<?php

//----------------------------------------------------------------------------//
// Finds the earliest and latest CDR for a given Service
//----------------------------------------------------------------------------//

require_once("../../flex.require.php");

$arrCols = Array();
$arrCols['EarliestCDR']	= NULL;
$arrCols['LatestCDR']	= NULL;
$ubiService		= new StatementUpdateById("Service", $arrCols);
//$selService		= new StatementSelect("Service", "*", "1");
$selService		= new StatementSelect("Service", "*", "EarliestCDR IS NULL");
$selCDRs		= new StatementSelect("CDR USE INDEX (Service)", "Service AS Id, MIN(StartDatetime) AS EarliestCDR, MAX(StartDatetime) AS LatestCDR", "Service = <Id> AND Status IN (150, 198, 199) AND StartDatetime IS NOT NULL", NULL, NULL, "Service");
$selCDRInvoiced	= new StatementSelect("CDRInvoiced USE INDEX (Service)", "Service AS Id, MIN(StartDatetime) AS EarliestCDR, MAX(StartDatetime) AS LatestCDR", "Service = <Id> AND Status IN (150, 198, 199) AND StartDatetime IS NOT NULL", NULL, NULL, "Service");

// Get Earliest and Latest CDR Dates
$intCount	= $selService->Execute();
$intI		= 0;
while ($arrService = $selService->Fetch())
{
	$intI++;
	
	// Quick Run: Disable for full run
	if (true)
	//if (!$arrService['EarliestCDR'])
	//if (strtotime($arrService['EarliestCDR']) > strtotime("2007-11-02") || !$arrService['EarliestCDR'])
	{
		$strI		= str_pad($intI, strlen($intCount), '0', STR_PAD_LEFT);
		$strService	= str_pad($arrService['Id'], strlen($intCount), '0', STR_PAD_LEFT);
		CliEcho(" + ($strI/$intCount) Updating Service $strService: ", FALSE);
		
		// Get CDR Info
		$selCDRs->Execute($arrService);
		$selCDRInvoiced->Execute($arrService);
		$arrCDR			= $selCDRs->Fetch();
		$arrCDRInvoiced	= $selCDRInvoiced->Fetch();
		
		// Earliest CDR
		$arrCDRs = Array();
		if (!$arrCDR['EarliestCDR'])
		{
			$strEarliestType	= "CDR table is NULL";
			$arrCDRs['EarliestCDR']	= $arrCDRInvoiced['EarliestCDR'];
		}
		elseif (!$arrCDRInvoiced['EarliestCDR'])
		{
			$strEarliestType	= "CDRInvoiced table is NULL";
			$arrCDRs['EarliestCDR']	= $arrCDR['EarliestCDR'];
		}
		else
		{
			$strEarliestType	= "Choose lesser";
			$arrCDRs['EarliestCDR']	= date("Y-m-d H:i:s", min(strtotime($arrCDR['EarliestCDR']), strtotime($arrCDRInvoiced['EarliestCDR'])));
		}
		
		// Latest CDR
		if (!$arrCDR['LatestCDR'])
		{
			$strLatestType	= "CDR table is NULL";
			$arrCDRs['LatestCDR']	= $arrCDRInvoiced['LatestCDR'];
		}
		elseif (!$arrCDRInvoiced['EarliestCDR'])
		{
			$strLatestType	= "CDRInvoiced table is NULL";
			$arrCDRs['LatestCDR']	= $arrCDR['LatestCDR'];
		}
		else
		{
			$strLatestType	= "Choose greater";
			$arrCDRs['LatestCDR']	= date("Y-m-d H:i:s", max(strtotime($arrCDR['LatestCDR']), strtotime($arrCDRInvoiced['LatestCDR'])));
		}
		
		/*if ($arrCDRs['EarliestCDR'] || $arrCDRs['LatestCDR'])
		{
			$strEarliest	= ($arrCDRs['EarliestCDR'])	? $arrCDRs['EarliestCDR']	: '               NULL';
			$strLatest		= ($arrCDRs['LatestCDR'])	? $arrCDRs['LatestCDR']		: 'NULL               ';
			CliEcho("$strEarliest/$strLatest...", FALSE);
			Debug($arrCDRInvoiced);
			Debug($arrCDR);
			Debug("$strEarliestType:$strLatestType");
			die;
		}*/
		
		$strEarliest	= ($arrCDRs['EarliestCDR'])	? $arrCDRs['EarliestCDR']	: '               NULL';
		$strLatest		= ($arrCDRs['LatestCDR'])	? $arrCDRs['LatestCDR']		: 'NULL               ';
		CliEcho("$strEarliest/$strLatest...", FALSE);
		
		// Update Service Table
		$ubiService->Execute($arrCDRs);
		CliEcho("\t\t\t[  DONE  ]");
	}
}
?>
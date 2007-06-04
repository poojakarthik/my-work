<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

// call application
require_once("../framework/require.php");

// Get STT Entries
$selServiceTypeTotal	= new StatementSelect("ServiceTypeTotal", "*", "Id > <MaxId>", NULL, 1000);
$selSTTCost				= new StatementSelect("CDR", "SUM(Cost) AS Cost", "RecordType = <RecordType> AND FNN = <FNN> AND InvoiceRun = <InvoiceRun>");
$selRateGroup			= new StatementSelect(	"((ServiceRateGroup JOIN RateGroup ON RateGroup.Id = ServiceRateGroup.RateGroup) JOIN Service ON Service.Id = ServiceRateGroup.Service) JOIN Invoice USING(Account)",
												"RateGroup.Id AS RateGroup",
												"ServiceRateGroup.Service = <Service> AND RateGroup.RecordType = <RecordType> AND Invoice.InvoiceRun = <InvoiceRun>",
												"(Invoice.CreatedOn BETWEEN ServiceRateGroup.StartDatetime AND ServiceRateGroup.EndDatetime) DESC, " .
												"(ServiceRateGroup.StartDatetime < Invoice.CreatedOn) DESC, ServiceRateGroup.StartDatetime DESC");

$arrCols = Array();
$arrCols['Cost'] 		= NULL;
$arrCols['RateGroup']	= NULL;
$ubiServiceTypeTotal	= new StatementUpdateById("ServiceTypeTotal", $arrCols);

echo "\n[ GENERATING OLD ServiceTypeTotal.Cost ENTRIES ]\n\n";

// Get entries in groups of 1000
$arrWhere			= Array();
$arrWhere['MaxId']	= 0;
$intTotal	= 0;
$intPassed	= 0;
while ($selServiceTypeTotal->Execute($arrWhere))
{
	// for each entry, update
	foreach ($selServiceTypeTotal->FetchAll() as $arrServiceTypeTotal)
	{
		$intTotal++;
		echo " + {$arrServiceTypeTotal['InvoiceRun']}\t{$arrServiceTypeTotal['FNN']}\t{$arrServiceTypeTotal['RecordType']}...\t\t\t";
		ob_flush();
		
		// Get Total CDR cost
		$selSTTCost->Execute($arrServiceTypeTotal);
		$arrSTTCost = $selSTTCost->Fetch();
		
		// Get RateGroup
		$selRateGroup->Execute($arrServiceTypeTotal);
		$arrRateGroup = $selRateGroup->Fetch();
		
		// Update
		$arrData = Array();
		$arrData['Id']			= $arrServiceTypeTotal['Id'];
		$arrData['Cost']		= $arrSTTCost['Cost'];
		$arrData['RateGroup']	= $arrRateGroup['RateGroup'];
		if ($ubiServiceTypeTotal->Execute($arrSTTCost) === FALSE)
		{
			echo "[ FAILED ]\n";
		}
		else
		{
			echo "[   OK   ]\n";
			$intPassed++;
		}
		
		$arrWhere['MaxId'] = $arrServiceTypeTotal['Id'];
	}
}

echo " * Updated $intPassed of $intTotal ServiceTypeTotals.\n\n";
ob_flush();


$selServiceTotal	= new StatementSelect(	"ServiceTotal", "*", "Id > <MaxId>", NULL, 1000);
$selCDRTotals		= new StatementSelect(	"CDR USE INDEX (Service_2) JOIN Rate ON (CDR.Rate = Rate.Id)",
											"Rate.Uncapped AS Uncapped, SUM(CDR.Cost) AS Cost",
											"CDR.Service = <Service> AND " .
											"CDR.Credit = 0".
											" AND CDR.InvoiceRun = <InvoiceRun>" ,
											NULL,
											NULL,
											"Rate.Uncapped");

$arrCols = Array();
$arrCols['UncappedCost']	= NULL;
$arrCols['CappedCost']		= NULL;
$arrCols['RatePlan']		= NULL;
$ubiServiceTotal		= new StatementUpdateById("ServiceTotal", $arrCols);

$selRatePlan			= new StatementSelect(	"(ServiceRatePlan JOIN Service ON Service.Id = ServiceRatePlan.Service) JOIN Invoice USING(Account)",
												"ServiceRatePlan.RatePlan AS RatePlan",
												"ServiceRatePlan.Service = <Service> AND Invoice.InvoiceRun = <InvoiceRun>",
												"(Invoice.CreatedOn BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime) DESC, " .
												"(ServiceRatePlan.StartDatetime < Invoice.CreatedOn) DESC, ServiceRatePlan.StartDatetime DESC");

echo "\n[ GENERATING OLD ServiceTotal.Cost ENTRIES ]\n\n";

// Get entries in groups of 1000
$arrWhere			= Array();
$arrWhere['MaxId']	= 0;
$intTotal	= 0;
$intPassed	= 0;
while ($selServiceTotal->Execute($arrWhere))
{
	// for each entry, update
	foreach ($selServiceTotal->FetchAll() as $arrServiceTotal)
	{
		$intTotal++;
		echo " + {$arrServiceTotal['InvoiceRun']}\t{$arrServiceTotal['FNN']}...\t\t\t";
		ob_flush();
		
		// Get RatePlan
		$selRatePlan->Execute($arrServiceTotal);
		$arrRatePlan = $selRatePlan->Fetch();
		
		// Get Total CDR cost
		$selCDRTotals->Execute($arrServiceTotal);
		$arrCDRTotals = $selCDRTotals->FetchAll();
		$arrData = Array();
		$arrData['UncappedCost']	= 0;
		$arrData['CappedCost']		= 0;
		$arrData['RatePlan']		= $arrRatePlan['RatePlan'];
		foreach ($arrCDRTotals as $arrTotal)
		{
			if ($arrTotal['Uncapped'])
			{
				$arrData['UncappedCost']	= ($arrTotal['Cost']) ? $arrTotal['Cost'] : 0;
			}
			else
			{
				$arrData['CappedCost']		= ($arrTotal['Cost']) ? $arrTotal['Cost'] : 0;
			}
		}
		
		// Update
		$arrData['Id']			= $arrServiceTotal['Id'];
		if ($ubiServiceTotal->Execute($arrCDRTotals) === FALSE)
		{
			echo "[ FAILED ]\n";
		}
		else
		{
			echo "[   OK   ]\n";
			$intPassed++;
		}
		
		$arrWhere['MaxId'] = $arrServiceTotal['Id'];
	}
}

echo " * Updated $intPassed of $intTotal ServiceTotals.\n\n";

?>
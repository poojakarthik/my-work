<?php

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

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

$arrColumns = Array();
$arrColumns['CDREtechId']		= "CDREtech.Id";
$arrColumns['CDRId']			= "CDR.Id";
$arrColumns['Charge']			= "CDREtech.Charge";
$arrColumns['CDRStatus']		= "CDR.Status";
$arrColumns['RecordType']		= "CDREtech.RecordType";
$arrColumns['BillingPeriod']	= "CDREtech.InvoiceRun";
$selMatchEtechCDRs = new StatementSelect(	"CDREtech LEFT JOIN CDR ON " .
											"(CDREtech.StartDatetime = CDR.StartDatetime AND " .
											"CDREtech.FNN LIKE CDR.FNN AND " .
											/*"CDR.Credit = 0 AND " .*/
											"CDR.Status != 184 AND " .
											"CDREtech.VixenCDR IS NULL AND " .
											"(CDR.Units = CDREtech.Units OR (CDR.Units = 1 AND CDREtech.Units = 0)))",
											$arrColumns,
											"CDREtech.Id > <MinId>",
											NULL,
											1000);

$arrColumns = Array();
$arrColumns['VixenCDR']	= NULL;
$ubiCDREtech	= new StatementUpdateById("CDREtech", $arrColumns);

$arrColumns = Array();
$arrColumns['Status']	= NULL;
$arrColumns['Charge']	= NULL;
$ubiCDR			= new StatementUpdateById("CDR", $arrColumns);

echo "\n\n[ MATCHING CDRETECH TO CDR ]\n\n";
ob_flush();

$intMinId		= 0;
$intTotal		= 0;
$intGrandTotal	= 0;
$intCredits		= 0;
$arrNoMatch		= Array();
$arrNoMatchTotals = Array();
$framework->StartWatch();
while ($selMatchEtechCDRs->Execute(Array('MinId' => $intMinId)))
{
	$arrResults = $selMatchEtechCDRs->FetchAll();
	
	// Separate matches from non-matches
	$intCount = 0;
	$arrMatchedCDRs	= Array();
	foreach ($arrResults as $arrCDR)
	{
		if ($arrCDR['CDRId'])
		{
			if (!in_array($arrCDR['CDRId'], $arrMatchedCDRs))
			{
				$intCount++;
				$arrMatchedCDRs[] = $arrCDR['CDRId'];
				
				// Update CDRs
				$arrData = Array();
				$arrData['Id']			= $arrCDR['CDREtechId'];
				$arrData['VixenCDR']	= $arrCDR['CDRId'];
				if ($ubiCDREtech->Execute($arrData) === FALSE)
				{
					Debug($ubiCDREtech->Error());
				}
				
				$arrData = Array();
				$arrData['Id']			= $arrCDR['CDRId'];
				$arrData['Status']		= CDR_ETECH_RATED;
				$arrData['Charge']		= $arrCDR['Charge'];
				if ($ubiCDR->Execute($arrData) === FALSE)
				{
					Debug($ubiCDR->Error());
				}
			}
		}
		else
		{
				
			// Allowed memory size of 268435456 bytes exhausted (tried to allocate 72 bytes)
			//$arrNoMatch[]	= $arrCDR;
			
			$arrNoMatchTotals['RecordTypes'][$arrCDR['RecordType']]++;
			$arrNoMatchTotals['BillingPeriods'][$arrCDR['BillingPeriod']]++;
		}
		
		if ($arrCDR['Charge'] < 0)
		{
			$intCredits++;
		}
	}
	
	$intTotal += $intCount;
	$intGrandTotal += count($arrResults);
	$intPeriod = $framework->LapWatch();
	$intTotalPeriod = $framework->SplitWatch();
	echo " + Updated $intCount CDRs in $intPeriod seconds!\t\t($intTotal so far, total time: $intTotalPeriod)\n";
	ob_flush();
	
	$arrLast = end($arrResults);
	$intMinId = max($intMinId, $arrLast['CDREtechId']);
	
	// Break out if we've reached 10 non-matches
	/*$intDiff = $intGrandTotal - $intTotal;
	if ($intDiff > 10)
	{
		echo " *** ";
		foreach ($arrNoMatch as $arrCDR)
		{
			echo $arrCDR['CDREtechId']."\t";
		}
		echo "\n";
		break;
	}*/
}

$intTotalPeriod = $framework->SplitWatch();
$intUpdated = count($arrMatchedCDRs);
echo "\n * Updated $intTotal of $intGrandTotal CDRs in $intTotalPeriod seconds!  $intCredits credits\n\n";

// No Match Report
echo "[ No Match Totals ]\n\n";
foreach ($arrNoMatchTotals as $strBreakdown=>$arrTotals)
{
	echo "$strBreakdown\t| Total\n--------------------------\n";
	foreach ($arrTotals as $intRecordType=>$intTotal)
	{
		echo "$intRecordType\t\t| $intTotal\n";
	}
	echo "\n";
}
echo "\n";
die;
?>
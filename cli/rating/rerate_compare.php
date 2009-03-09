<?php

require_once("application_loader.php");
require_once("Spreadsheet/Excel/Writer.php");

// Application Instance
$appRating = new ApplicationRating($arrConfig);

// Variables
$strInvoiceRun	= "465f4b2218916";
$strStartDate	= "2007-05-31";
$strPath		= "/home/richdavis/Desktop/Rating Adjustments Report.xls";

// Statements
$selInvoices	= new StatementSelect("Invoice", "*", "InvoiceRun = '$strInvoiceRun'");

$arrServiceColumns = Array();
$arrServiceColumns['Shared']			= "RatePlan.Shared";
$arrServiceColumns['MinMonthly']		= "RatePlan.MinMonthly";
$arrServiceColumns['ChargeCap']			= "RatePlan.ChargeCap";
$arrServiceColumns['UsageCap']			= "RatePlan.UsageCap";
$arrServiceColumns['FNN']				= "ServiceTotal.FNN";
$arrServiceColumns['CappedCharge']		= "ServiceTotal.CappedCharge";
$arrServiceColumns['UncappedCharge']	= "ServiceTotal.UncappedCharge";
$arrServiceColumns['Service']			= "ServiceTotal.Service";
$selServices	= new StatementSelect(	"ServiceTotal JOIN RatePlan ON RatePlan.Id = ServiceTotal.RatePlan",
										$arrServiceColumns,
										"ServiceTotal.Account = <Account> AND ServiceTotal.InvoiceRun = '$strInvoiceRun'");

$selCDRTotals		= new StatementSelect(	"CDR JOIN Rate ON (CDR.Rate = Rate.Id)",
											"Rate.Uncapped AS Uncapped, SUM(CDR.Charge) AS Charge",
											"CDR.Service = <Service> AND " .
											"CDR.Credit = 0".
											" AND CDR.InvoiceRun = '$strInvoiceRun'" .
											" AND CDR.RatedOn < '2007-05-31'" ,
											NULL,
											NULL,
											"Rate.Uncapped");
$selFuckedCDRs		= new StatementSelect(	"CDR JOIN Rate ON (CDR.Rate = Rate.Id)",
											"CDR.*, Rate.Uncapped AS Uncapped", 
											"CDR.Service = <Service> AND CDR.InvoiceRun = '$strInvoiceRun' AND CDR.RatedOn > '2007-05-31'");

$selAdjustments		= new StatementSelect(	"Charge",
										 	"Nature, SUM(Amount) AS Amount",
									 		"Service = <Service> AND InvoiceRun = '$strInvoiceRun'",
									  		NULL,
									  		"2",
									  		"Nature");
									  		
$selServiceTotal	= new StatementSelect("ServiceTotal", "*", "Service = <Service> AND InvoiceRun = '$strInvoiceRun'");

echo "\n[ RE-RATING FUCKED INVOICED CDRS ]\n\n";

// Open and Init Excel Spreadsheet
$wkbWorkbook = new Spreadsheet_Excel_Writer($strPath);
$wksAccounts =& $wkbWorkbook->addWorksheet("Per Account");
$wksServices =& $wkbWorkbook->addWorksheet("Per Service");

// Set up Formats
$fmtFNN			= $wkbWorkbook->addFormat();
$fmtFNN->setNumFormat('0000000000');
$arrFormat['FNN']				= $fmtFNN;

$fmtCurrency	= $wkbWorkbook->addFormat();
$fmtCurrency->setNumFormat('$#,##0.00;$#,##0.00 CR');
$arrFormat['Currency']		= $fmtCurrency;

$fmtTitle =& $wkbWorkbook->addFormat();
$fmtTitle->setBold();
$fmtTitle->setFgColor(22);
$fmtTitle->setBorder(1);
$arrFormat['Title']			= $fmtTitle;

$fmtInteger =& $wkbWorkbook->addFormat();
$fmtInteger->setNumFormat('00');
$arrFormat['Integer']		= $fmtInteger;

// Set up titles
$wksAccounts->writeString(0, 0, "Account No."	, $arrFormat['Title']);
$wksAccounts->writeString(0, 1, "Difference"	, $arrFormat['Title']);

$wksServices->writeString(0, 0, "Account No."	, $arrFormat['Title']);
$wksServices->writeString(0, 1, "Service FNN"	, $arrFormat['Title']);
$wksServices->writeString(0, 2, "Difference"	, $arrFormat['Title']);

// Foreach Invoice
$selInvoices->Execute();
$intAccountRow	= 1;
$intServiceRow	= 1;
while ($arrInvoice = $selInvoices->Fetch())
{
	echo " + Account #{$arrInvoice['Account']}\t\t\t";
	ob_flush();
	
	$fltAccountCappedDifference		= 0;
	$fltAccountUncappedDifference	= 0;
	
	if ($selServices->Execute($arrInvoice))
	{
		echo "\n";
		$arrServices = $selServices->FetchAll();
		
		// Get a list of shared plans for this account
		$arrSharedPlans = Array();
		foreach($arrServices as $arrService)
		{
			if ($arrService['Shared'])
			{
				$arrSharedPlans[$arrService['RatePlan']]['Count']++;
				$arrSharedPlans[$arrService['RatePlan']]['MinMonthly']	= $arrService['MinMonthly'];
				$arrSharedPlans[$arrService['RatePlan']]['UsageCap']	= $arrService['UsageCap'];
				$arrSharedPlans[$arrService['RatePlan']]['ChargeCap']	= $arrService['ChargeCap'];
			}
		}
			
		// Foreach Service
		foreach ($arrServices as $arrService)
		{
			$fltServiceCredits		= 0.0;
			$fltServiceDebits		= 0.0;
			$fltTotalCharge			= 0.0;
			$fltUncappedCDRCharge	= 0.0;
			$fltCappedCDRCharge		= 0.0;
			
			echo "\tService {$arrService['FNN']}...\t\t";
			ob_flush();
			
			// Calculate Total Charges for non-fucked CDRs
			$selCDRTotals->Execute($arrService);
			while ($arrCDRTotal = $selCDRTotals->Fetch())
			{
				if ($arrCDRTotal['Uncapped'])
				{
					$fltUncappedCDRCharge	= $arrCDRTotal['Charge'];
				}
				else
				{
					$fltCappedCDRCharge		= $arrCDRTotal['Charge'];
				}
			}
			
			// Calculate Total Charges for fucked CDRs
			$selFuckedCDRs->Execute($arrService);
			while ($arrCDR = $selFuckedCDRs->Fetch())
			{
				$fltCharge = $appRating->RateCDR($arrCDR);
				if ($arrCDR['Uncapped'])
				{
					$fltUncappedCDRCharge	+= $fltCharge;
				}
				else
				{
					$fltCappedCDRCharge		+= $fltCharge;
				}
			}
			
			// Apply sharing
			if ($arrService['Shared'] > 0)
			{
				// this is a shared plan, add to rateplan count
				$arrSharedPlans[$arrService['RatePlan']]['ServicesBilled']++;
				
				// is this the last Service for this RatePlan?
				if ($arrSharedPlans[$arrService['RatePlan']]['ServicesBilled'] == $arrSharedPlans[$arrService['RatePlan']]['Count'])
				{
					// this is the last service, add min monthly to this service
					$fltMinMonthly 	= max($arrSharedPlans[$arrService['RatePlan']]['MinMonthly'], 0);
				}
				else
				{
					$fltMinMonthly 	= 0;
				}
				$fltUsageCap 		= max($arrSharedPlans[$arrService['RatePlan']]['UsageCap'], 0);
				$fltChargeCap 		= max($arrSharedPlans[$arrService['RatePlan']]['ChargeCap'], 0);
			}
			else
			{
				// this is not a shared plan
				$fltMinMonthly 		= $arrService['MinMonthly'];
				$fltUsageCap 		= $arrService['UsageCap'];
				$fltChargeCap 		= $arrService['ChargeCap'];
			}
			
			// add capped charges
			if ($arrService['ChargeCap'] > 0.0)
			{
				// this is a capped plan
				if ($fltChargeCap > $fltCappedCDRCharge)
				{
					// under the Charge Cap : add the Full Charge
					$fltTotalCharge = $fltCappedCDRCharge;
				}
				elseif ($arrService['UsageCap'] > 0 && $fltUsageCap < $fltCappedCDRCharge)
				{
					// over the Usage Cap : add the Charge Cap + Charge - Usage Cap
					$fltTotalCharge = (float)$fltChargeCap + $fltCappedCDRCharge - (float)$fltUsageCap;
				}
				else
				{
					// over the Charge Cap, Under the Usage Cap : add Charge Cap
					$fltTotalCharge = (float)$fltChargeCap;
				}
			}
			else
			{
				// this is not a capped plan
				$fltTotalCharge = $fltCappedCDRCharge;
			}
			
			// add uncapped charges
			$fltTotalCharge += $fltUncappedCDRCharge;
		
			// If there is a minimum monthly charge, apply it
			if ($fltMinMonthly > 0)
			{
				$fltTotalCharge = max($fltMinMonthly, $fltTotalCharge);
			}
			
			// if this is a shared plan
			if ($arrService['Shared'] > 0)
			{
				// remove total charged from min monthly
				$arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] = $arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] - $fltTotalCharge;
				
				// reduce caps
				$arrSharedPlans[$arrService['RatePlan']]['ChargeCap'] -= $fltUncappedCDRCharge;
				$arrSharedPlans[$arrService['RatePlan']]['UsageCap'] -= $fltUncappedCDRCharge;
			}
			
			// Add Service Charges
			if ($selAdjustments->Execute())
			{
				$arrDebitsCredits = $selAdjustments->FetchAll();
				foreach($arrDebitsCredits as $arrCharge)
				{
					if ($arrCharge['Nature'] == "DR")
					{
						$fltServiceDebits	+= $arrCharge['Amount'];
					}
					else
					{
						$fltServiceCredits	+= $arrCharge['Amount'];
					}
				}
			}
			
			// Calc New ServiceTotal
			$fltServiceTotal	= $fltTotalCharge + $fltServiceDebits - $fltServiceCredits;
			
			// Get old ServiceTotal
			$selServiceTotal->Execute($arrService);
			$arrServiceTotal = $selServiceTotal->Fetch();
			
			// Find Difference between Fucked and UnFucked
			$fltCappedDifference	= $arrServiceTotal['CappedCharge']		- $fltCappedCDRCharge;
			$fltUncappedDifference	= $arrServiceTotal['UncappedCharge']	- $fltUncappedCDRCharge;
			$fltTotalDifference		= $fltUncappedDifference + $fltCappedDifference;
			
			echo "\${$fltTotalDifference}\n";
			$wksServices->writeNumber($intServiceRow, 0, $arrInvoice['Account']	, $arrFormat['Integer']);
			$wksServices->writeNumber($intServiceRow, 1, $arrService['FNN']		, $arrFormat['FNN']);
			$wksServices->writeNumber($intServiceRow, 2, $fltTotalDifference	, $arrFormat['Currency']);
			$intServiceRow++;
			
			// Add to Account's Total Difference
			$fltAccountCappedDifference		+= $fltCappedDifference;
			$fltAccountUncappedDifference	+= $fltUncappedDifference;
		}
		
		$fltAccountDifference = $fltAccountCappedDifference + $fltAccountUncappedDifference;
		echo "\n\t* Account Total Difference:\t\${$fltAccountDifference}\n\n";
		
		$wksAccounts->writeNumber($intAccountRow, 0, $arrInvoice['Account']	, $arrFormat['Integer']);
		$wksAccounts->writeNumber($intAccountRow, 1, $fltAccountDifference	, $arrFormat['Currency']);
		$intAccountRow++;
	}
	else
	{
		echo "[  SKIP  ]\n\n";
	}
	ob_flush();
}

$wkbWorkbook->close();
echo "DONE\n";

?>

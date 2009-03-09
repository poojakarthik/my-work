<?php

// INVOICE RUN
$strInvoiceRun	= '465f4b2218916';	// June

// Get Framework
require_once("../../flex.require.php");

// Statements
$selAccounts	= new StatementSelect("Invoice", "*", "InvoiceRun = <InvoiceRun>");

$arrServiceColumns = Array();
$arrServiceColumns['Shared']			= "RatePlan.Shared";
$arrServiceColumns['MinMonthly']		= "RatePlan.MinMonthly";
$arrServiceColumns['ChargeCap']			= "RatePlan.ChargeCap";
$arrServiceColumns['UsageCap']			= "RatePlan.UsageCap";
$arrServiceColumns['FNN']				= "Service.FNN";
$arrServiceColumns['CappedCharge']		= "Service.CappedCharge";
$arrServiceColumns['UncappedCharge']	= "Service.UncappedCharge";
$arrServiceColumns['Service']			= "Service.Id";
$arrServiceColumns['RatePlan']			= "RatePlan.Id";
$selServices					= new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, " .
															"RatePlan",
															$arrServiceColumns,
															"Service.Account = <Account> AND RatePlan.Id = ServiceRatePlan.RatePlan AND " .
															"Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.") AND (<CreatedOn> BETWEEN Service.CreatedOn AND Service.ClosedOn)" .
															" AND ServiceRatePlan.Id = ( SELECT Id FROM ServiceRatePlan WHERE Service = Service.Id AND <CreatedOn> BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC LIMIT 1)",
															"RatePlan.Id");

$selCDRTotals		= new StatementSelect(	"CDR JOIN Rate ON (CDR.Rate = Rate.Id)",
											"SUM(CASE WHEN Rate.Uncapped THEN CDR.Charge ELSE 0 END) AS UncappedCharge, " .
											"SUM(CASE WHEN Rate.Uncapped THEN CDR.Cost ELSE 0 END) AS UncappedCost, " .
											"SUM(CASE WHEN Rate.Uncapped THEN 0 ELSE CDR.Charge END) AS CappedCharge, " .
											"SUM(CASE WHEN Rate.Uncapped THEN 0 ELSE CDR.Cost END) AS CappedCost, " .
											"CDR.RecordType AS RecordType",
											"CDR.Service = <Service> AND " .
											"CDR.Credit = 0".
											" AND CDR.InvoiceRun = <InvoiceRun>",
											NULL,
											NULL,
											"CDR.RecordType, Rate.Uncapped");
											
$selDebitsCredits				= new StatementSelect(	"Charge",
													 	"Nature, SUM(Amount) AS Amount",
												 		"Service = <Service> AND InvoiceRun = <InvoiceRun>",
												  		NULL,
												  		"2",
												  		"Nature");



$selAccounts->Execute(Array('InvoiceRun' => $strInvoiceRun));
while ($arrAccount = $selAccounts->Fetch())
{	
	// Retrieve list of services for this account
	$selServices->Execute($arrAccount);
	if(!$arrServices = $selServices->FetchAll())
	{
		// Report and continue
		//$_rptBillingReport->AddMessageVariables(MSG_LINE_FAILED, Array('<Reason>' => "No Services for this Account"));
		continue;
	}
	//$_rptBillingReport->AddMessage(MSG_OK);
	
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
	$arrAccountReturn['SharedPlans']	= $arrSharedPlans;
	
	// for each service belonging to this account
	$arrUniqueServiceList = Array();
	foreach ($arrServices as $arrService)
	{
		$arrServiceReturn = Array();
		
		$fltServiceCredits		= 0.0;
		$fltServiceDebits		= 0.0;
		$fltTotalCharge			= 0.0;
		$fltUncappedCDRCharge	= 0.0;
		$fltCappedCDRCharge		= 0.0;
		$fltUncappedCDRCost		= 0.0;
		$fltCappedCDRCost		= 0.0;
		
		// get capped & uncapped charges
		$arrService['InvoiceRun']	= $strInvoiceRun;
		$selCDRTotals->Execute($arrService);
		$arrCDRTotals = $selCDRTotals->FetchAll();
		foreach($arrCDRTotals as $arrCDRTotal)
		{
			$fltCappedCDRCost		+= $arrCDRTotal['CappedCost'];
			$fltUncappedCDRCost		+= $arrCDRTotal['UncappedCost'];
			$fltUncappedCDRCharge	+= $arrCDRTotal['UncappedCharge'];
			$fltCappedCDRCharge		+= $arrCDRTotal['CappedCharge'];
		}
		
		// Determine Plan Charges
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
		
		// Calculate Service Debit and Credit Totals
		//$_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
		$mixResult = $selDebitsCredits->Execute(Array('Service' => $arrService['Service'], 'InvoiceRun' => $strInvoiceRun));
		if($mixResult > 2 || $mixResult === FALSE)
		{
			if ($mixResult === FALSE)
			{

			}
			
			/*// Incorrect number of rows returned or an error
			$_rptBillingReport->AddMessageVariables(MSG_SERVICE_TITLE, Array('<FNN>' => $arrService['FNN']));
			$_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
			$_rptBillingReport->AddMessage(MSG_FAILED);*/
			continue;
		}
		else
		{
			$arrDebitsCredits = $selDebitsCredits->FetchAll();
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
			//$_rptBillingReport->AddMessage(MSG_OK);
		}
		
		
		// service total
		$fltServiceTotal	= $fltTotalCharge + $fltServiceDebits - $fltServiceCredits;
		
		// insert into ServiceTotal
		//$_rptBillingReport->AddMessage(MSG_SERVICE_TOTAL, FALSE);
		$arrServiceTotal = Array();
		$arrServiceTotal['FNN']				= $arrService['FNN'];
		$arrServiceTotal['AccountGroup']	= $arrAccount['AccountGroup'];
		$arrServiceTotal['Account']			= $arrAccount['Id'];
		$arrServiceTotal['Service']			= $arrService['Service'];
		$arrServiceTotal['InvoiceRun']		= $strInvoiceRun;
		$arrServiceTotal['CappedCharge']	= $fltCappedCDRCharge;
		$arrServiceTotal['UncappedCharge']	= $fltUncappedCDRCharge;
		$arrServiceTotal['TotalCharge']		= $fltTotalCharge;
		$arrServiceTotal['Credit']			= $fltServiceCredits;
		$arrServiceTotal['Debit']			= $fltServiceDebits;
		$arrServiceTotal['RatePlan']		= $arrService['RatePlan'];
		$arrServiceTotal['CappedCost']		= $fltCappedCDRCost;
		$arrServiceTotal['UncappedCost']	= $fltUncappedCDRCost;
		
		/*if (!$insServiceTotal->Execute($arrServiceTotal) && !$bolReturnData)
		{
			Debug($insServiceTotal->Error());
			$_rptBillingReport->AddMessageVariables(MSG_SERVICE_TITLE, Array('<FNN>' => $arrService['FNN']));
			$_rptBillingReport->AddMessage(MSG_SERVICE_TOTAL, FALSE);
			$_rptBillingReport->AddMessage(MSG_FAILED);
			continue;
		}*/
		
		CliEcho(" + Serivce #{$arrService['FNN']}...\t\t\${$arrServiceTotal['TotalCharge']}");
		
		/*if ($arrServiceTotal['TotalCharge'])
		{
			Debug($arrServiceTotal);
		}*/
	}
}


?>

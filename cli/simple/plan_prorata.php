<?php

$intAccount	= (int)$argv[1];
$arrAccount	= Array();
$arrAccount['Id']			= $intAccount;


// Get Framework
require_once("../../flex.require.php");

// Init Select Statements
$arrServiceColumns = Array();
$arrServiceColumns['Shared']			= "RatePlan.Shared";
$arrServiceColumns['MinMonthly']		= "RatePlan.MinMonthly";
$arrServiceColumns['InAdvance']			= "RatePlan.InAdvance";
$arrServiceColumns['ChargeCap']			= "RatePlan.ChargeCap";
$arrServiceColumns['UsageCap']			= "RatePlan.UsageCap";
$arrServiceColumns['FNN']				= "Service.FNN";
$arrServiceColumns['CappedCharge']		= "Service.CappedCharge";
$arrServiceColumns['UncappedCharge']	= "Service.UncappedCharge";
$arrServiceColumns['Service']			= "Service.Id";
$arrServiceColumns['RatePlan']			= "RatePlan.Id";
$arrServiceColumns['CreatedOn']			= "Service.CreatedOn";
$arrServiceColumns['Indial100']			= "Service.Indial100";
$arrServiceColumns['LastBilledOn']		= "ServiceRatePlan.LastChargedOn";
$arrServiceColumns['ServiceRatePlan']	= "ServiceRatePlan.Id";
$selServices							= new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, " .
																"RatePlan",
																$arrServiceColumns,
																"Service.Account = <Account> AND RatePlan.Id = ServiceRatePlan.RatePlan AND " .
																"Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.") AND (NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime)" .
																" AND ServiceRatePlan.Id = ( SELECT Id FROM ServiceRatePlan WHERE Service = Service.Id AND NOW() BETWEEN StartDatetime AND EndDatetime AND Active = 1 ORDER BY CreatedOn DESC LIMIT 1)",
																"RatePlan.Id");

$selAccount	= new StatementSelect("Account", "*", "Id = <Id>");
if (!$selAccount->Execute($arrAccount))
{
	CliEcho("$intAccount is not a valid Account Number!\n");
	die;
}
$arrAccount	= $selAccount->Fetch();

// Retrieve list of services for this account
$selServices->Execute(Array('Account' => $arrAccount['Id']));
if(!$arrServices = $selServices->FetchAll())
{
	// Report and continue
	//$_rptBillingReport->AddMessageVariables(MSG_LINE_FAILED, Array('<Reason>' => "No Services for this Account"));
	continue;
}
//$_rptBillingReport->AddMessage(MSG_OK);

// Get a list of shared plans for this account
$selEarliestCDR		= new StatementSelect("Service", "EarliestCDR", "Id = <Service>");
$selPlanDate		= new StatementSelect("ServiceRatePlan", "StartDatetime", "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime AND Active = 1", "CreatedOn DESC", 1);
$selLastBillDate	= new StatementSelect("Invoice", "CreatedOn", "Account = <Id>", "CreatedOn DESC", 1);
$selLastTotal		= new StatementSelect("ServiceTotal", "Id", "Service = <Service>");
$selPlanLastBilled	= new StatementSelect("ServiceRatePlan", "Id", "Id = <ServiceRatePlan> AND LastChargedOn IS NOT NULL");
$intTime			= strtotime('2008-03-01 04:00:00');
if ($selLastBillDate->Execute($arrAccount))
{
	// Previous Invoice
	$arrLastBillDate	= $selLastBillDate->Fetch();
	$intLastBillDate	= strtotime($arrLastBillDate['CreatedOn']);
	CliEcho("Last Invoiced Date\t: ".date("Y-m-d", $intLastBillDate));
}
else
{
	// No Previous Invoice: Calculate what it should have been
	//$strBillingDate		= str_pad($arrAccount['BillingDate'], 2, '0', STR_PAD_LEFT);
	$strBillingDate		= '01';
	$intDate			= strtotime(date("Y-m-01", $intTime));
	$intLastBillDate	= strtotime("-{$arrAccount['BillingFreq']} month", strtotime(date("Y-m-$strBillingDate", $intDate)));
	CliEcho("Faked Invoiced Date\t: ".date("Y-m-d", $intLastBillDate));
}
//$intLastBilledDate	= strtotime('2008-02-01');
$arrSharedPlans	= Array();
foreach($arrServices as $mixIndex=>$arrService)
{
	CliEcho("\n[ {$arrService['FNN']} ({$arrService['Service']}) ]");
	
	if ($arrService['MinMonthly'] > 0)
	{
		CliEcho("Initial Min Monthly\t\t: {$arrService['MinMonthly']}");
		
		// Prorate Minimum Monthly
		$selEarliestCDR->Execute($arrService);
		$selPlanDate->Execute($arrService);
		$arrEarliestCDR	= $selEarliestCDR->Fetch();
		$arrPlanDate	= $selPlanDate->Fetch();
		
		$intCDRDate		= strtotime($arrEarliestCDR['EarliestCDR']);
		$intServiceDate	= strtotime($arrService['CreatedOn']);
		$intPlanDate	= strtotime($arrPlanDate['StartDatetime']);
		
		$strCDRDate		= ($intCDRDate) ? date("Y-m-d", $intCDRDate) : 'No CDRs';
		CliEcho("Earliest CDR\t\t\t: $strCDRDate");
		
		// If the first CDR is unbilled
		if (!$intCDRDate)
		{
			// No CDRs
			$arrService['MinMonthly']				= 0;
			$arrServices[$mixIndex]['MinMonthly']	= 0;
			CliEcho("No CDRs, no Min Monthly!");
		}
		elseif ($intCDRDate > $intLastBillDate)
		{
			$fltMinMonthly	= $arrService['MinMonthly'];
			
			// Prorate the Minimum Monthly
			$intProratePeriod						= $intTime - $intCDRDate;
			$intBillingPeriod						= $intTime - $intLastBillDate;
			$fltProratedMinMonthly					= ($arrService['MinMonthly'] / $intBillingPeriod) * $intProratePeriod;
			$arrService['MinMonthly']				= round($fltProratedMinMonthly, 2);
			$arrServices[$mixIndex]['MinMonthly']	= $arrService['MinMonthly'];
			
			$arrProRataPeriod	= SecondsToDays($intProratePeriod);
			$arrBillingPeriod	= SecondsToDays($intBillingPeriod);
			CliEcho("ProRata Period\t\t\t: {$arrProRataPeriod['d']} days");
			CliEcho("Billing Period\t\t\t: {$arrBillingPeriod['d']} days");
			CliEcho("Final Min Monthly\t\t: {$arrService['MinMonthly']}");
		}
		else
		{
			CliEcho("Keeping Min Monthly\t: {$arrService['MinMonthly']}");
		}
		
		// If this is the first invoice for this plan, add in "Charge in Advance" Adjustment
		if (!$arrService['LastBilledOn'] && $arrService['InAdvance'])
		{
			$arrAdvanceCharge = Array();
			$arrAdvanceCharge['AccountGroup']	= $arrAccount['AccountGroup'];
			$arrAdvanceCharge['Account']		= $arrAccount['Id'];
			$arrAdvanceCharge['Service']		= $arrService['Service'];
			$arrAdvanceCharge['ChargeType']		= 'PC'.round($fltMinMonthly, 2);
			$arrAdvanceCharge['Description']	= "Plan Charge in Advance from ".date("01/m/Y")." to ".date("d/m/Y", strtotime("+1 month", strtotime(date("Y-m-01"))));
			$arrAdvanceCharge['ChargedOn']		= date("Y-m-d");
			$arrAdvanceCharge['Nature']			= 'DR';
			$arrAdvanceCharge['Amount']			= $fltMinMonthly;
			//$Framework->AddCharge($arrAdvanceCharge);
			CliEcho("Adding Charge-in-Advance\t: {$arrAdvanceCharge['ChargeType']}");
		}
	}
	else
	{
		CliEcho("No Minimum Monthly!");
	}
	
	// Special Shared Plan Handling
	if ($arrService['Shared'])
	{
		$arrSharedPlans[$arrService['RatePlan']]['Count']++;
		$arrSharedPlans[$arrService['RatePlan']]['MinMonthly']	= max($arrService['MinMonthly'], $arrSharedPlans[$arrService['RatePlan']]['MinMonthly']);
		$arrSharedPlans[$arrService['RatePlan']]['UsageCap']	= $arrService['UsageCap'];
		$arrSharedPlans[$arrService['RatePlan']]['ChargeCap']	= $arrService['ChargeCap'];
	}
}

die;



function SecondsToDays($intSeconds)
{
	//CliEcho(date('Y-m-d H:i:s', $intSeconds));
	$intDays	= floor($intSeconds / (60*60*24));
	$intSeconds	= $intSeconds % (60*60*24);
	
	$intHours	= floor($intSeconds / (60*60));
	$intSeconds	= $intSeconds % (60*60);
	
	$intMinutes	= floor($intSeconds / (60));
	$intSeconds	= $intSeconds % (60);
	
	$arrDays['d']	= $intDays;
	$arrDays['h']	= $intHours;
	$arrDays['m']	= $intMinutes;
	$arrDays['s']	= $intSeconds;
	//Debug($arrDays);
	return $arrDays;
}

?>

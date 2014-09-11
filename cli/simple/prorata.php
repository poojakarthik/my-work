<?php

require_once("../../flex.require.php");

$strLastBillDate	= NULL;
$intBillDatetime	= strtotime('2008-04-01 10:00:00');
$strEarliestCDR		= '2008-03-12 00:00:00';
$bolHasInvoicedCDRs	= FALSE;
$arrServices		= Array();

$arrService			= Array();
$arrService['MinMonthly']		= 69.00;
$arrService['LastChargedOn']	= '';
$arrService['InAdvance']		= TRUE;
$arrServices[]					= $arrService;

$arrAccount	= Array();
$arrAccount['BillingFreq']		= 1;

if ($strLastBillDate)
{
	// Previous Invoice
	//$arrLastBillDate	= $selLastBillDate->Fetch();
	$intLastBillDate	= strtotime($strLastBillDate);
}
else
{
	// No Previous Invoice: Calculate what it should have been
	//$strBillingDate		= str_pad($arrAccount['BillingDate'], 2, '0', STR_PAD_LEFT);
	$strBillingDate		= '01';
	$intDate			= strtotime(date("Y-m-01", $intBillDatetime));
	$intLastBillDate	= strtotime("-{$arrAccount['BillingFreq']} month", strtotime(date("Y-m-$strBillingDate", $intDate)));
}

CliEcho();

$arrSharedPlans	= Array();
foreach($arrServices as $mixIndex=>$arrService)
{
	if ($arrService['MinMonthly'] > 0)
	{
		// Prorate Minimum Monthly
		/*$selEarliestCDR->Execute($arrService);
		$selPlanDate->Execute($arrService);
		$arrEarliestCDR	= $selEarliestCDR->Fetch();
		$arrPlanDate	= $selPlanDate->Fetch();*/
		
		$intCDRDate		= strtotime($strEarliestCDR);
		//$intServiceDate	= strtotime($arrService['CreatedOn']);
		//$intPlanDate	= strtotime($arrPlanDate['StartDatetime']);
		
		// If the Service is tolling (has an EarliestCDR)
		if ($intCDRDate)
		{
			//$bolHasInvoicedCDRs	= $selHasInvoicedCDRs->Execute($arrService);
			
			// If this is the first invoice for this plan, add in "Charge in Advance" Adjustment
			if ((!$arrService['LastChargedOn'] || !$bolHasInvoicedCDRs) && $arrService['InAdvance'])
			{
				$arrAdvanceCharge = Array();
				$arrAdvanceCharge['AccountGroup']	= $arrAccount['AccountGroup'];
				$arrAdvanceCharge['Account']		= $arrAccount['Id'];
				$arrAdvanceCharge['Service']		= $arrService['Service'];
				$arrAdvanceCharge['ChargeType']		= 'PCA'.round($arrService['MinMonthly'], 2);
				$arrAdvanceCharge['Description']	= "Plan Charge in Advance from ".date("01/m/Y", $intBillDatetime)." to ".date("d/m/Y", strtotime("-1 day", strtotime("+1 month", strtotime(date("Y-m-01", $intBillDatetime)))));
				$arrAdvanceCharge['ChargedOn']		= date("Y-m-d", $intBillDatetime);
				$arrAdvanceCharge['Nature']			= 'DR';
				$arrAdvanceCharge['Amount']			= $arrService['MinMonthly'];
				//$this->Framework->AddCharge($arrAdvanceCharge);
				
				CliEcho("Adding {$arrAdvanceCharge['ChargeType']} - {$arrAdvanceCharge['Description']}\t\${$arrAdvanceCharge['Amount']}");
			}
			
			// If the first CDR is unbilled, Pro Rata
			//if ($intCDRDate > $intLastBillDate)
			if (!$bolHasInvoicedCDRs)
			{
				$fltMinMonthly	= $arrService['MinMonthly'];
				
				// Prorate the Minimum Monthly
				$intProratePeriod						= TruncateTime($intBillDatetime, 'd', 'floor') - TruncateTime($intCDRDate, 'd', 'floor');
				$intBillingPeriod						= TruncateTime($intBillDatetime, 'd', 'floor') - TruncateTime($intLastBillDate, 'd', 'floor');
				$fltProratedMinMonthly					= ($arrService['MinMonthly'] / $intBillingPeriod) * $intProratePeriod;
				$arrService['MinMonthly']				= round($fltProratedMinMonthly, 2);
				$arrServices[$mixIndex]['MinMonthly']	= 0.0;
				
				// For now, add an adjustment instead of actually changing the min monthly
				$arrProrataCharge = Array();
				$arrProrataCharge['AccountGroup']	= $arrAccount['AccountGroup'];
				$arrProrataCharge['Account']		= $arrAccount['Id'];
				$arrProrataCharge['Service']		= $arrService['Service'];
				$arrProrataCharge['ChargeType']		= 'PCP'.round($arrService['MinMonthly'], 2);
				$arrProrataCharge['Description']	= "Plan Charge in Arrears from ".date("d/m/Y", $intCDRDate)." to ".date("d/m/Y", strtotime("-1 day", $intBillDatetime));
				$arrProrataCharge['ChargedOn']		= date("Y-m-d", $intBillDatetime);
				$arrProrataCharge['Nature']			= 'DR';
				$arrProrataCharge['Amount']			= $arrService['MinMonthly'];
				//$this->Framework->AddCharge($arrProrataCharge);
				
				CliEcho("Adding {$arrProrataCharge['ChargeType']} - {$arrProrataCharge['Description']}\t\${$arrProrataCharge['Amount']}");
			}
		}
		else
		{
			// No CDRs
			$arrService['MinMonthly']				= 0;
			$arrServices[$mixIndex]['MinMonthly']	= 0;
		}
	}
	
	CliEcho("Minimum Monthly set to \${$arrServices[$mixIndex]['MinMonthly']}");
}




?>
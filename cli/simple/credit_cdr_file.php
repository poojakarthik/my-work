<?php

require_once("../../flex.require.php");

// The file which needs to be credited back
$arrParams			= Array();
$arrParams['File']	= 83515;

$selCDR			= new StatementSelect("CDR", "Id", "File = <File> AND Credit = 0");
$selCDRInvoiced	= new StatementSelect("CDRInvoiced", "Account, AccountGroup, COUNT(Id) AS Records, ROUND(SUM(Charge), 2) AS GrandTotal", "File = <File> AND Credit = 0", "Account", NULL, "Account HAVING GrandTotal > 0.0");

// Check the CDR table for CDRs to invalidate
// TODO

// Check the CDRInvoiced table for CDRs to credit
if ($selCDRInvoiced->Execute($arrParams))
{
	// Credit each Account
	while ($arrAccount = $selCDRInvoiced->Fetch())
	{
		$fltAmount					= (float)$arrAccount['GrandTotal'];
		$fltAmountGST				= $fltAmount + round($fltAmount / 10, 2);
		CliEcho("Crediting {$arrAccount['Account']} \${$fltAmountGST} for {$arrAccount['Records']} CDRs");
		
		// Create Adjustment
		$arrCharge = Array();
		$arrCharge['Nature']		= 'CR';
		$arrCharge['Notes']			= "Unitel Double-Charge Credit";
		$arrCharge['Description']	= "Double-Charge Credit";
		$arrCharge['ChargeType']	= 'DCC';
		$arrCharge['Amount']		= $fltAmount;
		$arrCharge['Status']		= CHARGE_APPROVED;
		$arrCharge['Account'] 		= $arrAccount['Account'];
		$arrCharge['AccountGroup'] 	= $arrAccount['AccountGroup'];
		$arrCharge['ChargedOn']		= date("Y-m-d");
		$arrCharge['CreatedOn']		= date("Y-m-d");
		
		// Return FALSE or amount charged
		$GLOBALS['fwkFramework']->AddCharge($arrCharge);
		
		// Add System Note
	 	$strContent			= "Account {$arrAccount['Account']} was credited \$($fltAmountGST) (incl. GST) for Unitel Double-Charges from April 2008.";
	 	$intAccountGroup	= $arrAccount['AccountGroup'];
	 	$intAccount			= $arrAccount['Account'];
	 	$GLOBALS['fwkFramework']->AddNote($strContent, 7, NULL, $intAccountGroup, $intAccount);
	 	
	 	// DEBUG
	 	die;
	}
}

?>
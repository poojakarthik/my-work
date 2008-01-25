<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// check for uninvoiced Special/Billing-Time charges
//----------------------------------------------------------------------------//
require_once("../../flex.require.php");

$arrFees = Array();
// Late Payment Fee
$arrFees['LatePayment']	['Like']		= 'LP____';
$arrFees['LatePayment']	['BillTime']	= TRUE;

// Account Processing Fee
$arrFees['AccountProc']	['Like']		= 'AP___';
$arrFees['AccountProc']	['BillTime']	= TRUE;

// Pinnacle Mobile Fee
$arrFees['Pinnacle']	['Like']		= 'PM15';
$arrFees['Pinnacle']	['BillTime']	= FALSE;

// Active Inbound Fee
$arrFees['Inbound']		['Like']		= 'INB15';
$arrFees['Inbound']		['BillTime']	= FALSE;

// Statements
$selFindCharges	= new StatementSelect("(Charge JOIN Account ON Account.Id = Charge.Account) LEFT JOIN InvoiceRun USING (InvoiceRun)", "Charge.*, Account.Archived", "Nature = 'DR' AND ChargeType LIKE <Like> AND InvoiceRun.InvoiceRun IS NULL");

// Check for each charge
$strContent	= "";
foreach ($arrFees as $strType=>$arrFee)
{
	$strContent .= CliEcho("\n * Checking $strType...");
	
	$selFindCharges->Execute($arrFee);	
	$intTotal = 0;
	if ($intCount = $selFindCharges->Execute($arrFee))
	{
		while ($arrCharge	= $selFindCharges->Fetch())
		{
			$arrCharge['Service'] = ($arrCharge['Service']) ? "::".$arrCharge['Service'] : "\t\t";
			$strContent .= CliEcho("\t+ {$arrCharge['Account']}{$arrCharge['Service']} (".GetConstantDescription($arrCharge['Archived'], 'Account').")\t\${$arrCharge['Amount']}\t{$arrCharge['CreatedOn']}");
			$intTotal += $arrCharge['Amount'];
		}
		$strContent .= CliEcho("\t! Found $intCount Charges, totalling \$$intTotal !");
	}
}

// Email results
Debug("Emailing results...");
$strAddress	= 'rich@voiptelsystems.com.au, turdminator@hotmail.com';
$strSubject	= "Unbilled Special Charges Report for ".date('Y-m-d H:i:s');
SendEmail($strAddress, $strSubject, $strContent);
?>
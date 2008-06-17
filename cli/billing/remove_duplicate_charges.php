<?php

LoadApplication();

//DataAccess::getDataAccess()->TransactionStart();

// Statements
$arrFields = Array();
$arrFields['Debits']		= NULL;
$arrFields['Total']			= NULL;
$arrFields['Tax']			= NULL;
$arrFields['TotalOwing']	= NULL;
$arrFields['Balance']		= NULL;
$ubiTempInvoice = new StatementUpdateById("InvoiceTemp", $arrFields);
$selTempInvoice = new StatementSelect("InvoiceTemp", "*");
$selCharges		= new StatementSelect("Charge", "COUNT(Id) AS Instances, ChargeType, Amount", "InvoiceRun = <InvoiceRun> AND Account = <Account>", NULL, NULL, "ChargeType HAVING Instances > 1");




// Get Temp Invoices
$selTempInvoice->Execute();
while ($arrInvoice = $selTempInvoice->Fetch())
{
	$arrInitialInvoice = $arrInvoice;
	
	// Get number of Charge Instances
	if ($selCharges->Execute($arrInvoice))
	{
		CliEcho("\n* Account {$arrInvoice['Account']}...");
		while ($arrCharge = $selCharges->Fetch())
		{
			if ($arrCharge['ChargeType'] == 'AP250' || $arrCharge['ChargeType'] == 'LP'.date("my"))
			{
				if ($arrCharge['Instances'] > 1)
				{
					// Invalidate duplicate charges
					$arrFields = Array();
					$arrFields['InvoiceRun']	= NULL;
					
					$updCharges	= new StatementUpdate("Charge", "ChargeType = '{$arrCharge['ChargeType']}' AND Account = <Account> AND InvoiceRun = <InvoiceRun>", $arrFields, $arrCharge['Instances'] - 1);
					$updCharges->Execute($arrFields, $arrInvoice);
					CliEcho("\t+ Invalidating ".($arrCharge['Instances'] - 1)." {$arrCharge['ChargeType']} charges...");
					
					// Update Invoice totals
					$arrInvoice['Debits']		-= ($arrCharge['Instances'] - 1) * $arrCharge['Amount'];
					$arrInvoice['Total']			-= ($arrCharge['Instances'] - 1) * $arrCharge['Amount'];
					$arrInvoice['TotalOwing']	-= ($arrCharge['Instances'] - 1) * $arrCharge['Amount'];
					$arrInvoice['Balance']		-= ($arrCharge['Instances'] - 1) * $arrCharge['Amount'];
					$arrInvoice['Tax']			-= (($arrCharge['Instances'] - 1) * $arrCharge['Amount']) / TAX_RATE_GST;
					$arrInvoice['TotalOwing']	-= (($arrCharge['Instances'] - 1) * $arrCharge['Amount']) / TAX_RATE_GST;
					$arrInvoice['Balance']		-= (($arrCharge['Instances'] - 1) * $arrCharge['Amount']) / TAX_RATE_GST;
				}
			}
		}
	
		// Update the Temp Invoice
		$ubiTempInvoice->Execute($arrInvoice);
		$arrDifference['Debits']		= $arrInvoice['Debits']		- $arrInitialInvoice['Debits'];
		$arrDifference['Total']			= $arrInvoice['Total']		- $arrInitialInvoice['Total'];
		$arrDifference['TotalOwing']	= $arrInvoice['TotalOwing']	- $arrInitialInvoice['TotalOwing'];
		$arrDifference['Balance']		= $arrInvoice['Balance']	- $arrInitialInvoice['Balance'];
		$arrDifference['Tax']			= $arrInvoice['Tax']		- $arrInitialInvoice['Tax'];
		
		if ($arrDifference['TotalOwing'])
		{
			CliEcho("\t+ Differences: {$arrDifference['Debits']}\t{$arrDifference['Total']}\t{$arrDifference['TotalOwing']}\t\t{$arrDifference['Balance']}\t{$arrDifference['Tax']}");
		}
		else
		{
			CliEcho("\t+ Nothing to do!");
		}
	}
}


//DataAccess::getDataAccess()->TransactionRollback();




?>
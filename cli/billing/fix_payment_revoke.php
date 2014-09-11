<?php

LoadApplication();

//DataAccess::getDataAccess()->TransactionStart();

$selLastInvoices	= new StatementSelect("Invoice", "*", "InvoiceRun = '465f4b2218916'");
$selInvoicePayments	= new StatementSelect("InvoicePayment", "SUM(Amount) AS TotalAmount", "InvoiceRun = <InvoiceRun> AND Account = <Account>");
$arrCols = Array();
$arrCols['Balance']	= new MySQLFunction("Balance + <Difference>", Array());
$ubiInvoice			= new StatementUpdateById("Invoice", $arrCols);
$selUpdatedInvoice	= new StatementSelect("Invoice", "*", "Id = <Id>");

$selLastInvoices->Execute();
$intCount = 0;
$fltTotal = 0;
while ($arrInvoice = $selLastInvoices->Fetch())
{
	$selInvoicePayments->Execute($arrInvoice);
	$arrInvoicePayments = $selInvoicePayments->Fetch();
	$fltDifference = round((($arrInvoice['Total'] + $arrInvoice['Tax']) - $arrInvoice['Balance']) - (float)$arrInvoicePayments['TotalAmount'], 2);
	
	if ($fltDifference > 0)
	{
		CliEcho(" * Account {$arrInvoice['Account']} difference: $", FALSE);
		CliEcho($fltDifference."\t\tOld Balance: \${$arrInvoice['Balance']}", FALSE);
		$intCount++;
		$fltTotal += $fltDifference;
	
		// Update Invoice Balance
		$arrCols['Balance']	= new MySQLFunction("Balance + <Difference>", Array('Difference' => $fltDifference));
		$arrCols['Id']		= $arrInvoice['Id'];
		$ubiInvoice->Execute($arrCols);
		
		$selUpdatedInvoice->Execute($arrInvoice);
		$arrUpdatedInvoice = $selUpdatedInvoice->Fetch();
		$fltUpdatedDifference = round((($arrUpdatedInvoice['Total'] + $arrUpdatedInvoice['Tax']) - $arrUpdatedInvoice['Balance']) - (float)$arrInvoicePayments['TotalAmount'], 2);
		CliEcho("\t\tUpdated: \${$fltUpdatedDifference}\t\t New Balance: \${$arrUpdatedInvoice['Balance']}");
	}
}
CliEcho("\nTotal Fucked Invoices\t: {$intCount}");
CliEcho("Total Revoked\t\t: \${$fltTotal}");


//DataAccess::getDataAccess()->TransactionRollback();

?>
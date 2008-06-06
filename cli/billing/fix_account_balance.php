<?php


LoadApplication();

//DataAccess::getDataAccess()->TransactionStart();

$arrCols = Array();
$arrCols['AccountBalance']	= NULL;
$arrCols['TotalOwing']		= NULL;
$ubiTempInvoice		= new StatementUpdateById("InvoiceTemp", $arrCols);
$selTempInvoice		= new StatementSelect("InvoiceTemp", "*");
$selUpdatedInvoice	= new StatementSelect("InvoiceTemp", "*", "Id = <Id>");
$selTempInvoice->Execute();
while ($arrInvoice = $selTempInvoice->Fetch())
{	
	// Get Current AccountBalance
	$fltAccountBalance = $GLOBALS['fwkFramework']->GetAccountBalance($arrInvoice['Account']);
	
	// Update Invoice
	$arrCols['Id']				= $arrInvoice['Id'];
	$arrCols['AccountBalance']	= $fltAccountBalance;
	$arrCols['TotalOwing']		= $fltAccountBalance + $arrInvoice['Balance'];
	$ubiTempInvoice->Execute($arrCols);
	
	// Check value
	$selUpdatedInvoice->Execute($arrInvoice);
	$arrUpdatedInvoice = $selUpdatedInvoice->Fetch();
	
	if ($arrUpdatedInvoice != $arrInvoice)
	{
		CliEcho(" * Account {$arrInvoice['Account']}::Old\t= (\${$arrInvoice['AccountBalance']},\t\${$arrInvoice['TotalOwing']})\t\t", FALSE);
		CliEcho("New\t = (\${$arrUpdatedInvoice['AccountBalance']},\t\${$arrUpdatedInvoice['TotalOwing']})");
	}
}

//DataAccess::getDataAccess()->TransactionRollback();

?>
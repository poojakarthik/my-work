<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_INVOICE | MODULE_CHARGE | MODULE_CDR;
	
	// call application
	require ('config/application.php');
	
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Invoice');
	
	try
	{
		// Get the Invoice
		$invInvoice		= $Style->attachObject (new Invoice ($_GET ['Id']));
		// Get the Account the Invoice was Charged to
		$actAccount		= $Style->attachObject ($invInvoice->Account ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/invoice/notfound.xsl');
		exit;
	}
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		// Get the Charges the Invoice has
		$cgsCharges	= $Style->attachObject ($invInvoice->Charges ());
		$cgsCharges->Sample ();
	}
	
	// Get the CDRs the Invoice has
	$cdrCDRs = $Style->attachObject ($invInvoice->CDRs ());
	$cdrCDRs->Constrain ('InvoiceRun', '=', $invInvoice->Pull ('InvoiceRun')->getValue ());
	
	$cdrCDRs->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1,
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
	);
	
	// Output the Account View
	$Style->Output ('xsl/content/invoice/view.xsl');
	
?>

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
		$invInvoice		= $Style->attachObject (new Invoice ($_GET ['Invoice']));
		// Get the Account the Invoice was Charged to
		$actAccount		= $Style->attachObject ($invInvoice->Account ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/invoice/notfound.xsl');
		exit;
	}
	
	// If no service is set, then ask for the service
	if (!$_GET ['Service'])
	{
		$Style->Output ('xsl/content/invoice/view_service_select.xsl');
		exit;
	}
	
	// If the Service is set, then filter for it
	try
	{
		// Get the Service
		$srvService	= $Style->attachObject ($actAccount->Service ($_GET ['Service']));		
	}
	catch (Exception $e)
	{
		$svsServices = $Style->attachObjefct (new Services ());
		// If the Service is not found, display an error
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		// Get the Charges the Invoice has
		$cgsCharges	= $Style->attachObject ($invInvoice->Charges ());
		$cgsCharges->Constrain ('Service', '=', $srvService->Pull ('Id')->getValue ());
		$cgsCharges->Sample ();
	}
	
	// Get the CDRs the Invoice has
	$cdrCDRs = $Style->attachObject ($invInvoice->CDRs ());
	$cdrCDRs->Constrain ('InvoiceRun',	'=', $invInvoice->Pull ('InvoiceRun')->getValue ());
	$cdrCDRs->Constrain ('Service',		'=', $srvService->Pull ('Id')->getValue ());
	
	$cdrCDRs->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1,
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
	);
	
	// Output the Account View
	$Style->Output ('xsl/content/invoice/view.xsl');
	
?>

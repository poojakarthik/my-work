<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Invoice');
	
	// Get the Invoice
	$invInvoice		= $Style->attachObject (new Invoice ($_GET ['Id']));
	
	// Get the Account the Invoice was Charged to
	$actAccount		= $Style->attachObject ($invInvoice->Account ());
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		// Get the Charges the Invoice has
		$cgsCharges		= $Style->attachObject ($invInvoice->Charges ());
		$cgsCharges->Sample ();
	}
	
	// Get the CDRs the Invoice has
	$cdrCDRs		= $Style->attachObject ($invInvoice->CDRs ());
	$cdrCDRs->Constrain ('Invoice', '=', $invInvoice->Pull ('Id')->getValue ());
	
	$cdrCDRs->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1,
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
	);
	
	// Output the Account View
	$Style->Output ('xsl/content/invoice/view.xsl');
	
?>

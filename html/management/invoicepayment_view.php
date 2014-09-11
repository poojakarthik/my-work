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
	$arrPage['Modules']		= MODULE_BASE | MODULE_INVOICE | MODULE_PAYMENT | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	
	
	// Get Account
	try
	{
		// Try to pull the Account
		$ivpInvoicePayment = $Style->attachObject (new InvoicePayment ($_GET ['Id']));	
	}
	catch (Exception $e)
	{
		// Output the Account View
		$Style->Output ('xsl/content/invoicepayment/notfound.xsl');
		exit;
	}
	
	//$payPayment = $Style->attachObject ($ivpInvoicePayment->Payment ());
	
	// Pull documentation information
	$docDocumentation->Explain ('Invoice');
	$docDocumentation->Explain ('Payment');
	
	// Output the Account View
	$Style->Output ('xsl/content/invoicepayment/view.xsl');
	
?>

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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_INVOICE;
	
	// call application
	require ('config/application.php');
	
	
	// Get Account
	try
	{
		// Try to pull the Account
		$actAccount = $Style->attachObject (new Account ($_GET ['Id']));	
	}
	catch (Exception $e)
	{
		// Output the Account View
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Retrieve the  invoices list
	$ivlInvoices = $Style->attachObject ($actAccount->Invoices ());
	$ivlInvoices->Sample ();
	
	// Retrieve the PDF Listing
	$pdlInvoices = $Style->attachObject ($actAccount->PDFInvoices ());

	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	// Output the Account View
	$Style->Output ('xsl/content/account/ledger.xsl');
	
?>

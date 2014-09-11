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
	$arrPage['Modules']		= MODULE_BASE | MODULE_INVOICE | MODULE_CHARGE | MODULE_CDR | MODULE_SERVICE | MODULE_SERVICE_TOTAL;
	
	// call application
	require ('config/application.php');
	
	try
	{
		// Get the Invoice
		$invInvoice		= $Style->attachObject (new Invoice ($_GET ['Invoice']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/invoice/notfound.xsl');
		exit;
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Invoice');
	
	// Output the Account View
	$Style->Output (
		'xsl/content/invoice/dispute/resolved.xsl',
		Array (
			'Account'	=> $invInvoice->Pull ('Account')->getValue (),
			'Invoice'	=> $invInvoice->Pull ('Id')->getValue ()
		)
	);
	
?>

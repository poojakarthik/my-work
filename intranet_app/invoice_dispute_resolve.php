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
		$invInvoice		= $Style->attachObject (new Invoice (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/invoice/notfound.xsl');
		exit;
	}
	
	// Error
	$oblstrError			= $Style->attachObject	(new dataString		('Error'));
	
	// UI Values (Remember)
	$oblarrUIValues			= $Style->attachObject	(new dataArray		('ui-values'));
	$oblintResolveMethod	= $oblarrUIValues->Push (new dataInteger	('ResolveMethod',	$_POST ['ResolveMethod']));
	$oblintPaymentAmount	= $oblarrUIValues->Push (new dataString		('PaymentAmount',	$_POST ['PaymentAmount']));
	
	if (isset ($_POST ['ResolveMethod']))
	{
		$oblfltAmount = new dataFloat ('PaymentAmount');
		
		if (!$oblfltAmount->setValue ($_POST ['PaymentAmount']))
		{
			$oblstrError->setValue ('Invalid PaymentAmount');
		}
		else
		{
			$invInvoice->Resolve ($_POST ['ResolveMethod'], $_POST ['PaymentAmount']);
			header ("Location: invoice_dispute_resolved.php?Invoice=" . $invInvoice->Pull ('Id')->getValue ());
		}
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Invoice');
	
	// Output the Account View
	$Style->Output (
		'xsl/content/invoice/dispute/resolve.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue (),
			'Invoice'	=> $invInvoice->Pull ('Id')->getValue ()
		)
	);
	
?>

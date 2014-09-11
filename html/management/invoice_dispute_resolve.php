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
	$arrPage['Modules']		= MODULE_BASE | MODULE_INVOICE | MODULE_CHARGE | MODULE_CDR | MODULE_SERVICE | MODULE_SERVICE_TOTAL | MODULE_NOTE | MODULE_EMPLOYEE;
	
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
	
	if ($invInvoice->Pull ('Status')->getValue () != INVOICE_DISPUTED)
	{
		header ("Location: invoice_view.php?Invoice=" . $invInvoice->Pull ('Id')->getValue ());
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
		$oblfltAmount = new dataFloat ('ResolveAmount');
		
		if ($_POST ['ResolveMethod'] == DISPUTE_RESOLVE_PARTIAL_PAYMENT && !$oblfltAmount->setValue ($_POST ['ResolveAmount']))
		{
			$oblstrError->setValue ('Invalid PaymentAmount');
		}
		else
		{
			$invInvoice->Resolve (
				$athAuthentication->AuthenticatedEmployee (), 
				$_POST ['ResolveMethod'], 
				$_POST ['ResolveAmount']
			);
			
			header ("Location: invoice_dispute_resolved.php?Invoice=" . $invInvoice->Pull ('Id')->getValue ());
		}
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Invoice');
	
	// Output the Account View
	$Style->Output (
		'xsl/content/invoice/dispute/resolve.xsl',
		Array (
			'Account'	=> $invInvoice->Pull ('Account')->getValue (),
			'Invoice'	=> $invInvoice->Pull ('Id')->getValue ()
		)
	);
	
?>

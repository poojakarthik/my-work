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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_CHARGE | MODULE_CHARGE_TYPE | MODULE_INVOICE;
	
	// call application
	require ('config/application.php');
	
	// Get the Account
	try
	{
		$actAccount = $Style->attachObject (new Account ($_POST ['Account']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	//debug($_POST);die;
	if ($_POST ['Service'])
	{
		// Get the Service
		try
		{
			$srvService = $Style->attachObject (new Service ($_POST ['Service']));
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/service/notfound.xsl');
			exit;
		}
	}
	
	// Get the Charge
	try
	{
		$chgCharge = $Style->attachObject (new ChargeType ($_POST ['ChargeType']));
	}
	catch (Exception $e)
	{
		header ('Location: ../admin/flex.php/Service/View/?Service.Id=' . $srvService->Pull ('Id')->getValue ());
		exit;
	}
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrAmount	= $oblarrUIValues->Push (new dataString ('Amount', $chgCharge->Pull ('Amount')->getValue ()));
	
	// If we've submitting meaningful data
	if ($_POST ['Confirm'])
	{
		$oblstrAmount->setValue ($_POST ['Amount']);
		$fltAmount = new dataFloat ('Amount');
		
		// If an Invoice is assigned, check it exists
		if ($_POST ['Invoice'])
		{
			try
			{
				$invInvoice = new Invoice ($_POST ['Invoice']);
			}
			catch (Exception $e)
			{
			}
		}
		
		if ($chgCharge->Pull ('Fixed')->isFalse () && !$fltAmount->setValue ($_POST ['Amount']))
		{
			// Check the Amount is actually valid
			$oblstrError->setValue ('Invalid Amount');
		}
		else if ($_POST ['Invoice'] && (!$invInvoice || $actAccount->Pull ('Id')->getValue () <> $invInvoice->Pull ('Account')->getValue ()))
		{
			// Ensure that the Invoice belongs to the Account
			$oblstrError->setValue ('Invoice Misplaced');
		}
		else
		{
			// Add the Charge
			$actAccount->ChargeAdd (
				$athAuthentication->AuthenticatedEmployee (),
				isset ($srvService) ? $srvService : NULL,
				$chgCharge,
				$fltAmount->getValue (),
				(($_POST ['Invoice']) ? $_POST ['Invoice'] : NULL),
				$_POST ['Notes']
			);
			
			header ('Location: account_charges_unbilled.php?Account=' . $actAccount->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	// Invoice List
	$invInvoices = $Style->attachObject (new Invoices ($_POST['Id']));
	//$invInvoices->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
	//$invInvoices->Sample (1, 6);

	
	// Documentation
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Charge Type');
	
	// Output
	$Style->Output (
		'xsl/content/charges/charges/assign.xsl',
		Array (
			'Account'				=> $actAccount->Pull ('Id')->getValue (),
			'Service'				=> (($srvService) ? $srvService->Pull ('Id')->getValue () : NULL),
			'Adjustments-Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>

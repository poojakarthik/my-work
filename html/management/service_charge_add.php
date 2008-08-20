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
	
	
	// Get the Service
	try
	{
		$srvService = $Style->attachObject (new Service ($_POST ['Service']));
		$actAccount = $srvService->getAccount ();
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
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
		else if ($_POST ['Invoice'] && (!$invInvoice || $srvService->Pull ('Account')->getValue () <> $invInvoice->Pull ('Account')->getValue ()))
		{
			// Ensure that the Invoice belongs to the Account
			$oblstrError->setValue ('Invoice Misplaced');
		}
		else
		{
			// Add the Charge
			$actAccount->ChargeAdd (
				$athAuthentication->AuthenticatedEmployee (),
				$srvService,
				$chgCharge,
				$fltAmount->getValue (),
				(($_POST ['Invoice']) ? $_POST ['Invoice'] : NULL),
				$_POST ['Notes']
			);
			
			header ('Location: service_unbilled.php?Id=' . $srvService->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	// Invoice List
	$invInvoices = $Style->attachObject (new Invoices($srvService->Pull ('Account')->getValue ()));
	//$invInvoices->Constrain ('Account', '=', $srvService->Pull ('Account')->getValue ());
	//$invInvoices->Sample (1, 6);
	
	// Documentation
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Charge Type');
	
	// Output
	$Style->Output (
		'xsl/content/service/charges/charges/add.xsl',
		Array (
			'Account'		=> $srvService->Pull ('Account')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_CHARGE | MODULE_RECURRING_CHARGE | MODULE_INVOICE;
	
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
	// Get the Charge
	try
	{
		$chgCharge = $Style->attachObject (new RecurringChargeType ($_POST ['RecurringChargeType']));
	}
	catch (Exception $e)
	{
		header ('Location: view_account.php?Id=' . $srvService->Pull ('Id')->getValue ());
		exit;
	}
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrAmount	= $oblarrUIValues->Push (new dataString ('RecursionCharge', $chgCharge->Pull ('RecursionCharge')->getValue ()));
	
	// If we've submitting meaningful data
	if ($_POST ['Confirm'])
	{
		$oblstrAmount->setValue ($_POST ['Amount']);
		$fltAmount = new dataFloat ('Amount');
		

		
		if ($chgCharge->Pull ('Fixed')->isFalse () && !$fltAmount->setValue ($_POST ['Amount']))
		{
			// Check the Amount is actually valid
			$oblstrError->setValue ('Invalid Charge');
		}
		else
		{
			// Add the Charge
			$actAccount->RecurringChargeAdd (
				$athAuthentication->AuthenticatedEmployee (),
				$chgCharge,
				$fltAmount->getValue ()
			);
			
			header ('Location: recurring_charge_list.php?Account=' . $actAccount->Pull ('Id')->getValue ());
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
	$docDocumentation->Explain ('Recurring Charge Type');
	
	// Output
	$Style->Output (
		'xsl/content/recurringcharge/assign.xsl',
		Array (
			'Account'				=> $actAccount->Pull ('Id')->getValue (),
			'ChargeType'			=> $_POST ['ChargeType'],
			'Adjustments-Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>

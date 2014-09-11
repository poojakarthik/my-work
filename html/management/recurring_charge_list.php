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
	$arrPage['Modules']		= MODULE_BASE | MODULE_ACCOUNT | MODULE_SERVICE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	
		// ChargeType and RecurringChargeType
	$tctCharges = $Style->attachObject (new dataArray ('TemplateChargeTypes'));
	
	// Get the Recurring ChargeTypes which can be put against this Account
	$rclRecurringChargeTypes	= $tctCharges->Push (new RecurringChargeTypes);
	$rclRecurringChargeTypes->Constrain ('Archived', '=', FALSE);
	$rclRecurringChargeTypes->Sample ();
	
	// Start the Recurring Charges List
	$rclRecurringCharges = $Style->attachObject (new RecurringCharges ());

	
	if ($_GET ['Account'])
	{
		try
		{
			// Try and get the Account and Constrain Against it
			// If the Account does not exist, then throw an error
			$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
			$rclRecurringCharges->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/account/notfound.xsl');
			exit;
		}
	}
	else if ($_GET ['Service'])
	{
		try
		{
			// Try and get the Service and Constrain Against it
			// If the Service does not exist, then throw an error
			$srvService = $Style->attachObject (new Service ($_GET ['Service']));
			$rclRecurringCharges->Constrain ('Service', '=', $srvService->Pull ('Id')->getValue ());
			$actAccount = $Style->attachObject ($srvService->getAccount ());
			
		}
		catch (Exception $e)
		{
			$Style->Output ('xsl/content/service/notfound.xsl');
			exit;
		}
	}
	else
	{
		// If we don't have an Account or a Service, then we shouldn't be here
		header ('Location: console.php');
		exit;
	}

	if (isset ($_POST ['Archived']))
	{
		// This will allow us to choose whether or not we show Archived Recurring Charges
		$rclRecurringCharges->Constrain ('Archived', '=', $_POST ['Archived']);
	}
	else
	{
		// By default, we don't want to show Archived Recurring Charges
		$rclRecurringCharges->Constrain ('Archived', '=', 0);
	}
	// Pull the List
	$oblsamRecurringCharges = $rclRecurringCharges->Sample ();
	
	// Add Service Information
	foreach ($oblsamRecurringCharges as $rciRecurringCharge)
	{
		$rciRecurringCharge->Service ();
	}
	// Explain an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archived');
	$docDocumentation->Explain ('Service');
	
	$arrStyleOut = Array ( "Account"		=> $actAccount->Pull ('Id')->getValue ());
	if ($_GET ['Service']) {
		$arrStyleOut["Service"] = $srvService->Pull ('Id')->getValue ();
	}
	
	$Style->Output (
		"xsl/content/recurringcharge/list.xsl",
		$arrStyleOut
		);
	
	
?>

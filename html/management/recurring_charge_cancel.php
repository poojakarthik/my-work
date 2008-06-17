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
	
	
	// Try to obtain the Recurring Charge
	try
	{
		// Try and get the Recurring Charge
		// If the Recurring Charge does not exist, then throw an error
		$rciRecurringCharge = $Style->attachObject (new RecurringCharge (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/recurringcharge/notfound.xsl');
		exit;
	}
	
	// If it's already cancelled, do nothing
	if ($rciRecurringCharge->Pull ('Archived')->isTrue ())
	{
		$Style->Output ('xsl/content/recurringcharge/cancel_archived.xsl');
		exit;
	}
	
	if ($_POST ['Confirm'])
	{
		// Cancel the Recurring Charge
		$rciRecurringCharge->Cancel ($athAuthentication->AuthenticatedEmployee ());
		
		header ("Location: recurring_charge_cancelled.php?Id=" . $rciRecurringCharge->Pull ('Id')->getValue ());
		exit;
	}
	
	// Get the Cancellation Amount
	$rciRecurringCharge->CancellationAmount ();
	
	// Display
	$Style->Output (
		"xsl/content/recurringcharge/cancel_confirm.xsl",
		Array (
			"Account"		=> $rciRecurringCharge->Pull ('Account')->getValue (),
			"Service"		=> $rciRecurringCharge->Pull ('Service')->getValue ()
		)
	);
	
?>

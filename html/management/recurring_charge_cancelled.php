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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	// Try to obtain the Recurring Charge
	try
	{
		// Try and get the Recurring Charge
		// If the Recurring Charge does not exist, then throw an error
		$rciRecurringCharge = $Style->attachObject (new RecurringCharge ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/recurringcharge/notfound.xsl');
		exit;
	}
	
	$Style->Output (
		"xsl/content/recurringcharge/cancel_confirmed.xsl",
		Array (
			"Account"		=> $rciRecurringCharge->Pull ('Account')->getValue (),
			"Service"		=> $rciRecurringCharge->Pull ('Service')->getValue ()
		)
	);
	
?>

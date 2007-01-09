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
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_RECURRING_CHARGE_TYPE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	
	
	// Get the RecurringCharge
	try
	{
		$rctRecurringChargeType		= $Style->attachObject (new RecurringChargeType ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/windowclose.xsl');
		exit;
	}
	
	// Output the Account View
	$Style->Output ('xsl/content/charges/recurringcharges/view.xsl');
	
?>

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
	
	// Start a new Account Search
	$rclRecurringChargeTypes = $Style->attachObject (new RecurringChargeTypes ());
	$rclRecurringChargeTypes->Order ('ChargeType', TRUE);
	$rclRecurringChargeTypes->Sample ();
	
	$Style->Output ('xsl/content/charges/recurringcharges/list.xsl');
	
?>

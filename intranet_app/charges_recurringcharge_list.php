<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	
	// Start a new Account Search
	$rclRecurringChargeTypes = $Style->attachObject (new RecurringChargeTypes ());
	$rclRecurringChargeTypes->Order ('ChargeType', TRUE);
	$rclRecurringChargeTypes->Sample ();
	
	$Style->Output ('xsl/content/charges/recurringcharges/list.xsl');
	
?>

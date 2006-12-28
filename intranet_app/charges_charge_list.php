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
	
	
	// Explain the Fundamentals
	$docDocumentation->Explain ('Charge Type');
	
	// Start a new Account Search
	$rclChargeTypes = $Style->attachObject (new ChargeTypes ());
	$rclChargeTypes->Order ('ChargeType', TRUE);
	$rclChargeTypes->Sample ();
	
	$Style->Output ('xsl/content/charges/charges/list.xsl');
	
?>

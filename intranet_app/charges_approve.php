<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$uchUnapprovedCharges = $Style->attachObject (new Charges_Unapproved ());
	$uchUnapprovedCharges->Sample ();
	
	$Style->Output ('xsl/content/charges/approve.xsl');
	
?>

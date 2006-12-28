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
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Archive');
	
	
	
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

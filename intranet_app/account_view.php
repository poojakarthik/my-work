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
	
	
	// Get the Account
	$actAccount = $Style->attachObject (new Account ($_GET ['Id']));
	
	// Record a request to view an Account in the Audit
	$athAuthentication->AuthenticatedEmployee ()->Audit ()->RecordAccount ($actAccount);
	
	// Output the Account View
	$Style->Output ('xsl/content/account/view.xsl');
	
?>

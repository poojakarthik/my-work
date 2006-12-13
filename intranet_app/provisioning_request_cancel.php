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
	
	if (!$_GET ['Id'])
	{
		header ('Location: console.php'); exit;
	}
	
	// Get the Provisioning Request
	try
	{
		$prqProvisioningRequest = $Style->attachObject (new ProvisioningRequest ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
	}
	
	// Make sure the Provisioning Request is Unprocessed
	if ($prqProvisioningRequest->Pull ('Status')->getValue () != REQUEST_STATUS_WAITING)
	{
		$Style->Output ("xsl/content/service/provisioning_cancel_failed_not_unprocessed.xsl");
		exit;
	}
	
	$prqProvisioningRequest->Cancel ();
	
	$Style->Output ("xsl/content/service/provisioning_cancel_confirm.xsl");
	
?>

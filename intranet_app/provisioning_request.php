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
	
	if (!$_POST ['Carrier'] || !$_POST ['Service'] || !$_POST ['RequestType'])
	{
		header ('Location: console.php'); exit;
	}
	
	// Check the requested Carrier Exists
	$carCarrier = new Carriers ();
	if (!$carCarrier->setValue ($_POST ['Carrier']))
	{
		header ('Location: console.php'); exit;
	}
	
	// Check the requested Provisioning Request Type exists
	$prtRequestType = new ProvisioningRequestTypes ();
	if (!$prtRequestType->setValue ($_POST ['RequestType']))
	{
		header ('Location: console.php'); exit;
	}
	
	// Get the Service
	try
	{
		$srvService = new Service ($_POST ['Service']);
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
	}
	
	// Do the Provisioning Request
	$srvService->CreateNewProvisioningRequest ($athAuthentication->AuthenticatedEmployee (), $_POST ['Carrier'], $_POST ['RequestType']);
	
	header ('Location: provisioning_request_created.php?Service=' . $_POST ['Service']);
	exit;
	
?>

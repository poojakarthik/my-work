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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_PROVISIONING | MODULE_CARRIER;
	
	// call application
	require ('config/application.php');
	
	
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

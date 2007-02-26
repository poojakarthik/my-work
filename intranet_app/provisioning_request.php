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
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_PROVISIONING | MODULE_CARRIER;
	
	// call application
	require ('config/application.php');
	
	
	// Get the Service
	try
	{
		$srvService = new Service ($_POST ['Service']);
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	if (!$_POST ['Carrier'] || !$_POST ['RequestType'])
	{
		header ('Location: service_address.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// Check the requested Carrier Exists
	$carCarrier = new Carriers ();
	if (!$carCarrier->setValue ($_POST ['Carrier']))
	{
		header ('Location: service_address.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// Check the requested Provisioning Request Type exists
	$prtRequestType = new ProvisioningRequestTypes ();
	if (!$prtRequestType->setValue ($_POST ['RequestType']))
	{
		header ('Location: service_address.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// Do the Provisioning Request
	$srvService->CreateNewProvisioningRequest ($athAuthentication->AuthenticatedEmployee (), $_POST ['Carrier'], $_POST ['RequestType']);
	
	header ('Location: provisioning_request_created.php?Service=' . $_POST ['Service']);
	exit;
	
?>

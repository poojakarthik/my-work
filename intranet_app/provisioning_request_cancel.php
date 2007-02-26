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
	
	
	if (!$_GET ['Id'])
	{
		header ('Location: console.php'); exit;
	}
	
	// Get the Provisioning Request
	try
	{
		$prqProvisioningRequest = $Style->attachObject (new ProvisioningRequest ($_GET ['Id']));
		$srvService				= $prqProvisioningRequest->Service ();
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
	}
	
	// Make sure the Provisioning Request is Unprocessed
	if ($prqProvisioningRequest->Pull ('Status')->getValue () != REQUEST_STATUS_WAITING)
	{
		$Style->Output (
			"xsl/content/service/provisioning/cancel_failed_not_unprocessed.xsl",
			Array (
				"Account"		=> $srvService->Pull ("Account")->getValue (),
				"Service"		=> $srvService->Pull ("Id")->getValue ()
			)
		);
		exit;
	}
	
	$prqProvisioningRequest->Cancel ();
	
	$Style->Output (
		"xsl/content/service/provisioning/cancel_confirm.xsl",
		Array (
			"Account"		=> $srvService->Pull ("Account")->getValue (),
			"Service"		=> $srvService->Pull ("Id")->getValue ()
		)
	);
	
?>

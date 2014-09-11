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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	// Get the Service we just added the recurring charge to
	try
	{
		$srvService = $Style->attachObject (new Service ($_GET ['Service']));
	}
	catch (Exception $e)
	{
		// If the service is not found, error
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Display the happy message
	
	$Style->Output (
		"xsl/content/service/charges/recurringcharges/added.xsl",
		Array (
			'Account'		=> $srvService->Pull ('Account')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

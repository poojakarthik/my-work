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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		$srvService = $Style->attachObject (new Service ($_GET ['Service']));
	}
	catch (Exception $e)
	{
		header ("Location: index.php"); exit;
	}
	
	$Style->Output (
		"xsl/content/service/provisioning/confirm.xsl",
		Array (
			"Account"		=> $srvService->Pull ("Account")->getValue (),
			"Service"		=> $srvService->Pull ("Id")->getValue ()
		)
	);
	
?>

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
	
	try
	{
		$srvService = $Style->attachObject (new Service ($_GET ['Service']));
	}
	catch (Exception $e)
	{
		header ("Location: console.php"); exit;
	}
	
	$Style->Output ("xsl/content/service/charges_recurringcharge_added.xsl");
	
?>

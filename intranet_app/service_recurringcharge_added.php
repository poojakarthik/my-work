<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
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

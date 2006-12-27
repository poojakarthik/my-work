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
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Service');
	
	try
	{
		// Get the Service
		$srvService		= $Style->attachObject (new Service ($_GET ['Id']));
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
	}
	
	$cdrUnbilled = $srvService->UnbilledCharges ();
	$Style->attachObject (
		$cdrUnbilled->Sample (
			isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
		)
	);
	
	// Output the Service Unbilled Charges
	$Style->Output ('xsl/content/service/unbilled.xsl');
	
?>

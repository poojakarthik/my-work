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
	
	
	// Get the Service
	try
	{
		$srvService = $Style->attachObject (new Service ($_POST ['Service']));
	}
	catch (Exception $e)
	{
		header ('Location: console.php'); exit;
	}
	
	// Get the Charge
	try
	{
		$chgCharge = $Style->attachObject (new ChargeType ($_POST ['ChargeType']));
	}
	catch (Exception $e)
	{
		header ('Location: service_view.php?Id=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	if ($_POST ['Confirm'])
	{
		$srvService->ChargeAdd (
			$athAuthentication->AuthenticatedEmployee (),
			$chgCharge,
			$_POST ['Amount']
		);
		
		header ('Location: service_charge_added.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Charge Type');
	
	$Style->Output ('xsl/content/service/charges_charge_add.xsl');
	
?>

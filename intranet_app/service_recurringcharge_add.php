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
		$rcgCharge = $Style->attachObject (new RecurringChargeType ($_POST ['RecurringChargeType']));
	}
	catch (Exception $e)
	{
		header ('Location: service_view.php?Id=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// If Confirm is set, then we want to apply the value
	if ($_POST ['Confirm'])
	{
		$srvService->RecurringChargeAdd (
			$athAuthentication->AuthenticatedEmployee (),
			$rcgCharge,
			$_POST ['Amount']
		);
		
		header ('Location: service_recurringcharge_added.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Frequency');
	$docDocumentation->Explain ('Recurring Charge Type');
	
	$Style->Output ('xsl/content/service/charges_recurringcharge_add.xsl');
	
?>

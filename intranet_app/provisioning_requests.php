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
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Carrier');
	$docDocumentation->Explain ('Provisioning');
	
	
	// Get the Service
	$srvService		= $Style->attachObject (new Service ($_GET ['Service']));
	
	// Get the Account
	$actAccount		= $Style->attachObject ($srvService->getAccount ());
	
	// Get Associated Provisioning Requests
	$prlProvision	= $Style->attachObject (new ProvisioningRequests ());
	$prlProvision->Constrain ('Service', 'EQUALS', $srvService->Pull ('Id')->getValue ());
	$prlProvision->Sample ();
	
	// Load the List of Carrier Objects
	$calCarriers	= $Style->attachObject (new Carriers ());
	
	// Load the List of Provisioning Request Type Objects
	$prtProvisioningRequestType	= $Style->attachObject (new ProvisioningRequestTypes ());
	
	// Output the Account View
	$Style->Output ('xsl/content/service/provisioning_requests.xsl');
	
?>

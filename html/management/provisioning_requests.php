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
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Archive');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Carrier');
	$docDocumentation->Explain ('Provisioning');
	
	try
	{
		// Get the Service
		$srvService		= $Style->attachObject (new Service ($_GET ['Service']));
		$actAccount		= $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Get Associated Provisioning Requests
	$prlProvision	= $Style->attachObject (new ProvisioningRequests ());
	$prlProvision->Constrain ('Service', 'EQUALS', $srvService->Pull ('Id')->getValue ());
	$prlProvision->Sample ();
	
	// Load the List of Carrier Objects
	$calCarriers	= $Style->attachObject (new Carriers ());
	
	// Load the List of Provisioning Request Type Objects
	$prtProvisioningRequestType	= $Style->attachObject (new ProvisioningRequestTypes ());
	
	// Output the Account View
	$Style->Output ('xsl/content/service/provisioning/requests.xsl');
	
?>

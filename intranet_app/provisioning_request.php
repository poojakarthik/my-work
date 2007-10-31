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
	
	
	// Get the Service
	try
	{
		$srvService = new Service ($_POST ['Service']);
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	if (!$_POST ['Carrier'] || !$_POST ['RequestType'])
	{
		header ('Location: service_address.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// Check the requested Carrier Exists
	$carCarrier = new Carriers ();
	if (!$carCarrier->setValue ($_POST ['Carrier']))
	{
		header ('Location: service_address.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// Check the requested Provisioning Request Type exists
	$prtRequestType = new ProvisioningRequestTypes ();
	if (!$prtRequestType->setValue ($_POST ['RequestType']))
	{
		header ('Location: service_address.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
	}

	// note here to check for hard and soft bars
	switch ($_POST['RequestType'])
	{
		case REQUEST_BAR_SOFT:
			// a soft bar
			$strBarAction = "Provisioning Request: Soft Bar\n";
			break;
		case REQUEST_UNBAR_SOFT:
			// a soft bar reversal
			$strBarAction = "Provisioning Request: Soft Bar Reversal\n";
			break;
		case REQUEST_BAR_HARD:
			// a hard bar
			$strBarAction = "Provisioning Request: Hard Bar\n";			
			break;
		case REQUEST_UNBAR_HARD:
			// a hard bar reversal
			$strBarAction = "Provisioning Request: Hard Bar Reversal\n";
			break;
		default:
			// default do nothing
			break;
	}
	
	if ($strBarAction)
	{
		// System note is generated when address details are changed
		$strEmployeeFirstName = $athAuthentication->AuthenticatedEmployee()->Pull('FirstName')->getValue();
		$strEmployeeLastName = $athAuthentication->AuthenticatedEmployee()->Pull('LastName')->getValue() ;
		$strEmployeeId = $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
		$strEmployeeFullName =  "$strEmployeeFirstName $strEmployeeLastName";
	
		$intAccountId = $srvService->Pull ('Account')->getValue ();
		$intAccountGroup = $srvService->Pull ('AccountGroup')->getValue ();
		$intServiceId = $_POST ['Service'];
	
		$strNote = "Service Provision was changed by $strEmployeeFullName on " . date('m/d/y') . "\n";
		$strNote .= "The following changes were made:\n";
		$strNote .= "Service: $intServiceId\n";
		$strNote .= "Carrier: " . GetConstantDescription($_POST ['Carrier'], 'Carrier') . "\n";
		$strNote .= $strBarAction;

		$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, $strEmployeeId, $intAccountGroup, $intAccountId, $intServiceId, NULL);
	}

	// Do the Provisioning Request
	$srvService->CreateNewProvisioningRequest ($athAuthentication->AuthenticatedEmployee(), $_POST ['Carrier'], $_POST ['RequestType']);
	
	//}
	
	header ('Location: provisioning_request_created.php?Service=' . $_POST ['Service']);
	exit;
	
?>

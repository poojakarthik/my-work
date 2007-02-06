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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_CHARGE | MODULE_CHARGE_TYPE;
	
	// call application
	require ('config/application.php');
	
	
	// Get the Service
	try
	{
		$srvService = $Style->attachObject (new Service ($_POST ['Service']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Get the Charge
	try
	{
		$chgCharge = $Style->attachObject (new ChargeType ($_POST ['ChargeType']));
	}
	catch (Exception $e)
	{
		header ('Location: service_view.php?Id=' . $srvService->Pull ('Id')->getValue ());
		exit;
	}
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrAmount	= $oblarrUIValues->Push (new dataString ('Amount', $chgCharge->Pull ('Amount')->getValue ()));
	
	if ($_POST ['Confirm'])
	{
		$oblstrAmount->setValue ($_POST ['Amount']);
		
		$fltAmount = new dataFloat ('Amount');
		
		if (!$fltAmount->setValue ($_POST ['Amount']))
		{
			$oblstrError->setValue ('Invalid Amount');
		}
		else
		{
			$srvService->ChargeAdd (
				$athAuthentication->AuthenticatedEmployee (),
				$chgCharge,
				$fltAmount->getValue ()
			);
			
			header ('Location: service_charge_added.php?Service=' . $srvService->Pull ('Id')->getValue ()); exit;
		}
	}
	
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Charge Type');
	
	$Style->Output (
		'xsl/content/service/charges/charges/add.xsl',
		Array (
			'Account'		=> $srvService->Pull ('Account')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

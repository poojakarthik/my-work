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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
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
	
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Frequency');
	$docDocumentation->Explain ('Recurring Charge Type');
	
	$Style->Output (
		'xsl/content/service/charges_recurringcharge_add.xsl',
		Array (
			'Account'		=> $srvService->Pull ('Account')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

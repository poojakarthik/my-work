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
	
	//debug($_POST);die;
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
		header ('Location: ../admin/flex.php/Service/View/?Service.Id=' . $srvService->Pull ('Id')->getValue ()); exit;
	}
	
	// Error Remembering
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// UI Values
	$oblarrUIValues = $Style->attachObject (new dataArray ('ui-values'));
	$oblstrRecurringCharge = $oblarrUIValues->Push (new dataString ('RecursionCharge', $rcgCharge->Pull ('RecursionCharge')->getValue ()));
	
	// If Confirm is set, then we want to apply the value
	if ($_POST ['Confirm'])
	{
		$oblstrRecurringCharge->setValue ($_POST ['Amount']);
		$fltAmount = new dataFloat ('Amount');
		
		if ($rcgCharge->Pull ('Fixed')->isFalse () && !$fltAmount->setValue ($_POST ['Amount']))
		{
			$oblstrError->setValue ('Invalid Amount');
		}
		else
		{
			$srvService->RecurringChargeAdd (
				$athAuthentication->AuthenticatedEmployee (),
				$rcgCharge,
				$fltAmount->getValue ()
			);
			
			header ('Location: recurring_charge_list.php?Service=' . $srvService->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Frequency');
	$docDocumentation->Explain ('Recurring Charge Type');
	
	$Style->Output (
		'xsl/content/service/charges/recurringcharges/add.xsl',
		Array (
			'Account'		=> $srvService->Pull ('Account')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

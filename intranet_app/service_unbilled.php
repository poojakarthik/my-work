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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_CDR | MODULE_CHARGE | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	try
	{
		// Get the Service
		$srvService = $Style->attachObject (new Service ($_GET ['Id']));
		$actAccount = $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	$cdrUnbilled = $srvService->UnbilledCDRs ();
	$Style->attachObject (
		$cdrUnbilled->Sample (
			isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
			isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
		)
	);
	
	if (!isset ($_GET ['rangePage']) || $_GET ['rangePage'] == 1)
	{
		$cubUnbilledCharges = $Style->attachObject ($srvService->UnbilledCharges ());
		$oblsamCharges = $cubUnbilledCharges->Sample ();
		
		$oblarrEmployees = $Style->attachObject (new dataArray ('Employees', 'Employee'));
		$arrEmployees = Array ();
		
		foreach ($oblsamCharges as $crgCharge)
		{
			if (!isset ($arrEmployees [$crgCharge->Pull ('CreatedBy')->getValue ()]))
			{
				$arrEmployees [$crgCharge->Pull ('CreatedBy')->getValue ()] = $oblarrEmployees->Push (
					new Employee ($crgCharge->Pull ('CreatedBy')->getValue ())
				);
			}
		}
	}
	
	// Pull documentation information for a Service and an Account
	$docDocumentation->Explain ('Service');
	
	// Output the Service Unbilled Charges
	$Style->Output (
		'xsl/content/service/charges/unbilled.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

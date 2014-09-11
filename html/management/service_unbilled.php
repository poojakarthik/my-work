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
	$arrPage['Permission']	= PERMISSION_OPERATOR_VIEW;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_CDR | MODULE_CHARGE | MODULE_EMPLOYEE | MODULE_RECORD_TYPE | MODULE_SERVICE_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Attempt to get the Service that's being Requested
	try
	{
		$srvService = $Style->attachObject (new Service ($_GET ['Id']));
		$actAccount = $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Pull unbilled Services
	$cdrUnbilled = $Style->attachObject ($srvService->UnbilledCDRs ());
	
	// If there is a specific Record Type wishing to be viewed, constrain against it
	if ($_GET ['RecordType'])
	{
		$cdrUnbilled->Constrain ('RecordType', '=', $_GET ['RecordType']);
	}
	
	// Pull the sample
	$cdrUnbilled->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30
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
	
	// Pull documentation information for a Service and CDR Records
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('CDR');
	
	// Get a list of Record Types that can be searched for
	$rtsRecordTypes = $Style->attachObject (new RecordTypes);
	$rtsRecordTypes->Constrain ('ServiceType', '=', $srvService->Pull ('ServiceType')->getValue ());
	$rtsRecordTypes->Sample ();
	
	// Output the Service Unbilled Charges
	$Style->Output (
		'xsl/content/service/charges/unbilled.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

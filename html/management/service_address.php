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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_SERVICE_ADDRESS | MODULE_CARRIER | MODULE_PROVISIONING | MODULE_STATE | MODULE_TITLE;
	
	// call application
	require ('config/application.php');
	
	
	// Check the Service Exists
	try
	{
		$srvService = $Style->attachObject (new Service (isset ($_POST ['Service']) ? $_POST ['Service'] : $_GET ['Service']));
		$actAccount = $Style->attachObject ($srvService->getAccount ());
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	if ($srvService->Pull ('ServiceType')->getValue () <> SERVICE_TYPE_LAND_LINE)
	{
		$Style->Output ('xsl/content/service/notfound.xsl');
		exit;
	}
	
	// Try getting the Original Service Address Information
	try
	{
		$sadServiceAddress = $Style->attachObject ($srvService->ServiceAddress ());
	}
	catch (Exception $e)
	{
		// It's ok - Service Address Information can be Blank
	}
	
	// Provisioning Selection Options
	$Style->attachObject (new ServiceAddressTypes		(($sadServiceAddress) ? $sadServiceAddress->Pull ('ServiceAddressType')->getValue ()		: null));
	$Style->attachObject (new ServiceStreetTypes		(($sadServiceAddress) ? $sadServiceAddress->Pull ('ServiceStreetType')->getValue ()			: null));
	$Style->attachObject (new ServiceStreetSuffixTypes	(($sadServiceAddress) ? $sadServiceAddress->Pull ('ServiceStreetTypeSuffix')->getValue ()	: null));
	$Style->attachObject (new TitleTypes				(($sadServiceAddress) ? $sadServiceAddress->Pull ('EndUserTitle')->getValue ()				: null));
	$Style->attachObject (new ServiceStateTypes			(($sadServiceAddress) ? $sadServiceAddress->Pull ('ServiceState')->getValue ()				: null));
	
	if ($_POST ['Service'])
	{
		// Build the system generated note
		if ($sadServiceAddress)
		{
			// There are already Service Address details in the database
			// Work out what has been changed
			if ($sadServiceAddress->Pull('ServiceAddressType')->getValue() != $_POST ['ServiceAddressType'])
			{
				$strChangesNote .= "Service Address Type: {$_POST ['ServiceAddressType']}\n";
			}
			if ($sadServiceAddress->Pull('ServiceAddressTypeNumber')->getValue() != $_POST ['ServiceAddressTypeNumber'])
			{
				$strChangesNote .= "Service Address Type Number: {$_POST ['ServiceAddressTypeNumber']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceAddressTypeSuffix')->getValue() != $_POST ['ServiceAddressTypeSuffix'])
			{
				$strChangesNote .= "Service Address Type Suffix: {$_POST ['ServiceAddressTypeSuffix']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceStreetNumberStart')->getValue() != $_POST ['ServiceStreetNumberStart'])
			{
				$strChangesNote .= "Service Street Number Start: {$_POST ['ServiceStreetNumberStart']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceStreetNumberEnd')->getValue() != $_POST ['ServiceStreetNumberEnd'])
			{
				$strChangesNote .= "Service Street Number End: {$_POST ['ServiceStreetNumberEnd']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceStreetNumberSuffix')->getValue() != $_POST ['ServiceStreetNumberSuffix'])
			{
				$strChangesNote .= "Service Street Number Suffix: {$_POST ['ServiceStreetNumberSuffix']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceStreetName')->getValue() != $_POST ['ServiceStreetName'])
			{
				$strChangesNote .= "Service Street Name: {$_POST ['ServiceStreetName']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceStreetType')->getValue() != $_POST ['ServiceStreetType'])
			{
				$strChangesNote .= "Service Street Type: {$_POST ['ServiceStreetType']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceStreetTypeSuffix')->getValue() != $_POST ['ServiceStreetTypeSuffix'])
			{
				$strChangesNote .= "Service Street Type Suffix: {$_POST ['ServiceStreetTypeSuffix']}\n";			
			}
			if ($sadServiceAddress->Pull('ServicePropertyName')->getValue() != $_POST ['ServicePropertyName'])
			{
				$strChangesNote .= "Service Property Name: {$_POST ['ServicePropertyName']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceLocality')->getValue() != $_POST ['ServiceLocality'])
			{
				$strChangesNote .= "Service Locality: {$_POST ['ServiceLocality']}\n";			
			}
			if ($sadServiceAddress->Pull('ServiceState')->getValue() != $_POST ['ServiceState'])
			{
				$strChangesNote .= "Service State: {$_POST ['ServiceState']}\n";			
			}
			if ($sadServiceAddress->Pull('ServicePostcode')->getValue() != $_POST ['ServicePostcode'])
			{
				$strChangesNote .= "Service Postcode: {$_POST ['ServicePostcode']}\n";			
			}
			
			if ($strChangesNote)
			{	
				$strChangesNote = "The following modifications have been made to the service's address:\n\n" . $strChangesNote;
			}
		}
		else
		{
			// There was not a record in the ServiceAddress table relating to this service.  The address details are being added for the first time
			$strChangesNote = "The service's address details have been defined";
		}
		
		// Save Information
		$bolServiceAddressUpdated = $srvService->ServiceAddressUpdate(
										Array (
											'Residential'					=> $_POST ['Residential'],
											'BillName'						=> $_POST ['BillName'],
											'BillAddress1'					=> $_POST ['BillAddress1'],
											'BillAddress2'					=> $_POST ['BillAddress2'],
											'BillLocality'					=> $_POST ['BillLocality'],
											'BillPostcode'					=> $_POST ['BillPostcode'],
											'EndUserTitle'					=> $_POST ['EndUserTitle'],
											'EndUserGivenName'				=> $_POST ['EndUserGivenName'],
											'EndUserFamilyName'				=> $_POST ['EndUserFamilyName'],
											'EndUserCompanyName'			=> $_POST ['EndUserCompanyName'],
											'DateOfBirth:day'				=> $_POST ['DateOfBirth']['day'],
											'DateOfBirth:month'				=> $_POST ['DateOfBirth']['month'],
											'DateOfBirth:year'				=> $_POST ['DateOfBirth']['year'],
											'Employer'						=> $_POST ['Employer'],
											'Occupation'					=> $_POST ['Occupation'],
											'ABN'							=> $_POST ['ABN'],
											'TradingName'					=> $_POST ['TradingName'],
											'ServiceAddressType'			=> $_POST ['ServiceAddressType'],
											'ServiceAddressTypeNumber'		=> $_POST ['ServiceAddressTypeNumber'],
											'ServiceAddressTypeSuffix'		=> $_POST ['ServiceAddressTypeSuffix'],
											'ServiceStreetNumberStart'		=> $_POST ['ServiceStreetNumberStart'],
											'ServiceStreetNumberEnd'		=> $_POST ['ServiceStreetNumberEnd'],
											'ServiceStreetNumberSuffix'		=> $_POST ['ServiceStreetNumberSuffix'],
											'ServiceStreetName'				=> $_POST ['ServiceStreetName'],
											'ServiceStreetType'				=> $_POST ['ServiceStreetType'],
											'ServiceStreetTypeSuffix'		=> $_POST ['ServiceStreetTypeSuffix'],
											'ServicePropertyName'			=> $_POST ['ServicePropertyName'],
											'ServiceLocality'				=> $_POST ['ServiceLocality'],
											'ServiceState'					=> $_POST ['ServiceState'],
											'ServicePostcode'				=> $_POST ['ServicePostcode']
										)
									);
		
		if ($bolServiceAddressUpdated && $strChangesNote)
		{
			// The address details were successfully updated
			// Save the system note
			$intEmployeeId = $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
			$intAccountId = $actAccount->Pull('Id')->getValue();
			$intAccountGroup = $actAccount->Pull('AccountGroup')->getValue();

			$GLOBALS['fwkFramework']->AddNote($strChangesNote, SYSTEM_NOTE_TYPE, $intEmployeeId, $intAccountGroup, $intAccountId, $_POST['Service']);
		}
		
		$Style->Output (
			'xsl/content/service/provisioning/serviceaddress_updated.xsl',
			Array (
				'Account'		=> $actAccount->Pull ('Id')->getValue (),
				'Service'		=> $srvService->Pull ('Id')->getValue ()
			)
		);
		exit;
	}
	
	// Get information about Note Types
	$ntsNoteTypes = $Style->attachObject (new NoteTypes);
	
	// Get Associated Notes
	$nosNotes = $Style->attachObject (new Notes);
	$nosNotes->Constrain ('Service', '=', $_GET ['Service']);
	$nosNotes->Sample (1, 5);
	
	// Provisioning Request Information
	$calCarriers	= $Style->attachObject (new Carriers);
	$prtPRQTypes	= $Style->attachObject (new ProvisioningRequestTypes);
	
	$docDocumentation->Explain ('Service Address');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Carrier');
	$docDocumentation->Explain ('Provisioning');
	
	// Output the Request Page
	$Style->Output (
		'xsl/content/service/provisioning/serviceaddress_update.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_NOTE | MODULE_EMPLOYEE | MODULE_SERVICE_ADDRESS | MODULE_CARRIER | MODULE_PROVISIONING;
	
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
	$Style->attachObject (new ServiceEndUserTitleTypes	(($sadServiceAddress) ? $sadServiceAddress->Pull ('EndUserTitle')->getValue ()				: null));
	$Style->attachObject (new ServiceStateTypes			(($sadServiceAddress) ? $sadServiceAddress->Pull ('ServiceState')->getValue ()				: null));
	
	if ($_POST ['Service'])
	{
		// Save Information
		
		$srvService->ServiceAddressUpdate (
			Array (
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
		
		$Style->Output (
			'xsl/content/service/address/updated.xsl',
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
		'xsl/content/service/address/update.xsl',
		Array (
			'Account'		=> $actAccount->Pull ('Id')->getValue (),
			'Service'		=> $srvService->Pull ('Id')->getValue ()
		)
	);
	
?>

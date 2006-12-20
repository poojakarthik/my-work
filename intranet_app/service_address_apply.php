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
	
	
	// Check that the Service is Set
	if (!$_POST ['Service'])
	{
		header ("Location: console.php"); exit;
	}
	
	// Check the Service Exists
	try
	{
		$srvService = $Style->attachObject (new Service ($_POST ['Service']));
	}
	catch (Exception $e)
	{
		header ("Location: console.php"); exit;
	}
	
	$sadServiceAddress = $srvService->ServiceAddress ();
	
	$sadServiceAddress->Update (
		Array (
			"BillName"						=> $_POST ['BillName'],
			"BillAddress1"					=> $_POST ['BillAddress1'],
			"BillAddress2"					=> $_POST ['BillAddress2'],
			"BillLocality"					=> $_POST ['BillLocality'],
			"BillPostcode"					=> $_POST ['BillPostcode'],
			"EndUserTitle"					=> $_POST ['EndUserTitle'],
			"EndUserGivenName"				=> $_POST ['EndUserGivenName'],
			"EndUserFamilyName"			=> $_POST ['EndUserFamilyName'],
			"EndUserCompanyName"			=> $_POST ['EndUserCompanyName'],
			"DateOfBirth:day"				=> $_POST ['DateOfBirth']['day'],
			"DateOfBirth:month"			=> $_POST ['DateOfBirth']['month'],
			"DateOfBirth:year"				=> $_POST ['DateOfBirth']['year'],
			"Employer"						=> $_POST ['Employer'],
			"Occupation"					=> $_POST ['Occupation'],
			"ABN"							=> $_POST ['ABN'],
			"TradingName"					=> $_POST ['TradingName'],
			"ServiceAddressType"			=> $_POST ['ServiceAddressType'],
			"ServiceAddressTypeNumber"		=> $_POST ['ServiceAddressTypeNumber'],
			"ServiceAddressTypeSuffix"		=> $_POST ['ServiceAddressTypeSuffix'],
			"ServiceStreetNumberStart"		=> $_POST ['ServiceStreetNumberStart'],
			"ServiceStreetNumberEnd"		=> $_POST ['ServiceStreetNumberEnd'],
			"ServiceStreetNumberSuffix"	=> $_POST ['ServiceStreetNumberSuffix'],
			"ServiceStreetName"			=> $_POST ['ServiceStreetName'],
			"ServiceStreetType"				=> $_POST ['ServiceStreetType'],
			"ServiceStreetTypeSuffix"		=> $_POST ['ServiceStreetTypeSuffix'],
			"ServicePropertyName"			=> $_POST ['ServicePropertyName'],
			"ServiceLocality"				=> $_POST ['ServiceLocality'],
			"ServiceState"					=> $_POST ['ServiceState'],
			"ServicePostcode"				=> $_POST ['ServicePostcode']
		)
	);
	
	$Style->Output ("xsl/content/service/service_address_updated.xsl");
	
?>

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
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_SERVICE_ADDRESS;
	
	// call application
	require ('config/application.php');
	
	// Try and get the associated account
	
	try
	{
		if ($_GET ['Account'])
		{
			$actAccount = $Style->attachObject (new Account ($_GET ['Account']));
		}
		else if ($_POST ['Account'])
		{
			$actAccount = $Style->attachObject (new Account ($_POST ['Account']));
		}
		else
		{
			header ('Location: /console.php');
			exit;
		}
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
	
	// Build the remembering base ...
	$oblstrError = $Style->attachObject (new dataString ('Error', ''));
	
	$oblarrService = $Style->attachObject (new dataArray ('Service'));
	
	$srvServiceTypes			= $oblarrService->Push (new ServiceTypes);
	$oblstrFNN					= $oblarrService->Push (new dataString ('FNN'));
	
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	
	if ($_POST ['FNN'])
	{
		$oblstrFNN->setValue ($_POST ['FNN']);
		
		$selService = new StatementSelect ('Service', 'count(*) AS Length', 'FNN = <FNN> AND ClosedOn IS NULL');
		$selService->Execute (Array ('FNN' => $_POST ['FNN']));
		$arrLength = $selService->Fetch ();
		
		if (!$srvServiceTypes->setValue ($_POST ['ServiceType']))
		{
			$oblstrError->setValue ('ServiceType');
		}
		else if (!$_POST ['FNN'])
		{
			$oblstrError->setValue ('FNN Empty');
		}
		else if ($arrLength ['Length'] <> 0)
		{
			$oblstrError->setValue ('FNN Exists');
		}
		else
		{
			// If we're up to here, add the information to the database
			if ($_POST ['ServiceType'] == SERVICE_TYPE_LAND_LINE)
			{
				$oblarrServiceAddress = $oblarrService->Push (new dataArray ('ServiceAddress'));
				
				$oblstrBillName						= $oblarrServiceAddress->Push (new dataString ('BillName'));
				$oblstrBillAddress1					= $oblarrServiceAddress->Push (new dataString ('BillAddress1'));
				$oblstrBillAddress2					= $oblarrServiceAddress->Push (new dataString ('BillAddress2'));
				$oblstrBillLocality					= $oblarrServiceAddress->Push (new dataString ('BillLocality'));
				$oblstrBillPostcode					= $oblarrServiceAddress->Push (new dataString ('BillPostcode'));
				$eutEndUserTitle					= $oblarrServiceAddress->Push (new ServiceEndUserTitleTypes ());
				$oblstrEndUserGivenName				= $oblarrServiceAddress->Push (new dataString ('EndUserGivenName'));
				$oblstrEndUserFamilyName			= $oblarrServiceAddress->Push (new dataString ('EndUserFamilyName'));
				$oblstrEndUserCompanyName			= $oblarrServiceAddress->Push (new dataString ('EndUserCompanyName'));
				$oblstrDateOfBirth_day				= $oblarrServiceAddress->Push (new dataString ('DateOfBirth-day'));
				$oblstrDateOfBirth_month			= $oblarrServiceAddress->Push (new dataString ('DateOfBirth-month'));
				$oblstrDateOfBirth_year				= $oblarrServiceAddress->Push (new dataString ('DateOfBirth-year'));
				$oblstrEmployer						= $oblarrServiceAddress->Push (new dataString ('Employer'));
				$oblstrOccupation					= $oblarrServiceAddress->Push (new dataString ('Occupation'));
				$oblstrABN							= $oblarrServiceAddress->Push (new dataString ('ABN'));
				$oblstrTradingName					= $oblarrServiceAddress->Push (new dataString ('TradingName'));
				$satServiceAddressType				= $oblarrServiceAddress->Push (new ServiceAddressTypes ());
				$oblstrServiceAddressTypeNumber		= $oblarrServiceAddress->Push (new dataString ('ServiceAddressTypeNumber'));
				$oblstrServiceAddressTypeSuffix		= $oblarrServiceAddress->Push (new dataString ('ServiceAddressTypeSuffix'));
				$oblstrServiceStreetNumberStart		= $oblarrServiceAddress->Push (new dataString ('ServiceStreetNumberStart'));
				$oblstrServiceStreetNumberEnd		= $oblarrServiceAddress->Push (new dataString ('ServiceStreetNumberEnd'));
				$oblstrServiceStreetNumberSuffix	= $oblarrServiceAddress->Push (new dataString ('ServiceStreetNumberSuffix'));
				$oblstrServiceStreetName			= $oblarrServiceAddress->Push (new dataString ('ServiceStreetName'));
				$sstServiceStreetType				= $oblarrServiceAddress->Push (new ServiceStreetTypes ());
				$sstServiceStreetSuffixType			= $oblarrServiceAddress->Push (new ServiceStreetSuffixTypes ());
				$oblstrServicePropertyName			= $oblarrServiceAddress->Push (new dataString ('ServicePropertyName'));
				$oblstrServiceLocality				= $oblarrServiceAddress->Push (new dataString ('ServiceLocality'));
				$staServiceStateType				= $oblarrServiceAddress->Push (new ServiceStateTypes ());
				$oblstrServicePostcode				= $oblarrServiceAddress->Push (new dataString ('ServicePostcode'));
				
				if ($_POST ['ServiceAddress'])
				{
					$oblstrBillName						->setValue ($_POST ['ServiceAddress']['BillName']);
					$oblstrBillAddress1					->setValue ($_POST ['ServiceAddress']['BillAddress1']);
					$oblstrBillAddress2					->setValue ($_POST ['ServiceAddress']['BillAddress2']);
					$oblstrBillLocality					->setValue ($_POST ['ServiceAddress']['BillLocality']);
					$oblstrBillPostcode					->setValue ($_POST ['ServiceAddress']['BillPostcode']);
					$oblstrEndUserGivenName				->setValue ($_POST ['ServiceAddress']['EndUserGivenName']);
					$oblstrEndUserFamilyName			->setValue ($_POST ['ServiceAddress']['EndUserFamilyName']);
					$oblstrEndUserCompanyName			->setValue ($_POST ['ServiceAddress']['EndUserCompanyName']);
					$oblstrDateOfBirth_day				->setValue ($_POST ['ServiceAddress']['DateOfBirth-day']);
					$oblstrDateOfBirth_month			->setValue ($_POST ['ServiceAddress']['DateOfBirth-month']);
					$oblstrDateOfBirth_year				->setValue ($_POST ['ServiceAddress']['DateOfBirth-year']);
					$oblstrEmployer						->setValue ($_POST ['ServiceAddress']['Employer']);
					$oblstrOccupation					->setValue ($_POST ['ServiceAddress']['Occupation']);
					$oblstrABN							->setValue ($_POST ['ServiceAddress']['ABN']);
					$oblstrTradingName					->setValue ($_POST ['ServiceAddress']['TradingName']);
					$oblstrServiceAddressTypeNumber		->setValue ($_POST ['ServiceAddress']['ServiceAddressTypeNumber']);
					$oblstrServiceAddressTypeSuffix		->setValue ($_POST ['ServiceAddress']['ServiceAddressTypeSuffix']);
					$oblstrServiceStreetNumberStart		->setValue ($_POST ['ServiceAddress']['ServiceStreetNumberStart']);
					$oblstrServiceStreetNumberEnd		->setValue ($_POST ['ServiceAddress']['ServiceStreetNumberEnd']);
					$oblstrServiceStreetNumberSuffix	->setValue ($_POST ['ServiceAddress']['ServiceStreetNumberSuffix']);
					$oblstrServiceStreetName			->setValue ($_POST ['ServiceAddress']['ServiceStreetName']);
					$oblstrServicePropertyName			->setValue ($_POST ['ServiceAddress']['ServicePropertyName']);
					$oblstrServiceLocality				->setValue ($_POST ['ServiceAddress']['ServiceLocality']);
					$oblstrServicePostcode				->setValue ($_POST ['ServiceAddress']['ServicePostcode']);
					
					$bolEndUserTitle			= $eutEndUserTitle				->setValue ($_POST ['ServiceAddress']['EndUserTitle']);
					$bolServiceAddressType		= $satServiceAddressType		->setValue ($_POST ['ServiceAddress']['ServiceAddressType']);
					$bolServiceStreetType		= $sstServiceStreetType			->setValue ($_POST ['ServiceAddress']['ServiceStreetType']);
					$bolServiceStreetSuffixType	= $sstServiceStreetSuffixType	->setValue ($_POST ['ServiceAddress']['ServiceStreetTypeSuffix']);
					$bolServiceStateType		= $staServiceStateType			->setValue ($_POST ['ServiceAddress']['ServiceState']);
					
					if ($_POST ['ServiceAddress']['EndUserTitle'] && !$bolEndUserTitle)
					{
						$oblstrError->setValue ('ServiceAddress-EndUserTitle');
					}
					else if ($_POST ['ServiceAddress']['ServiceAddressType'] && !$bolServiceAddressType)
					{
						$oblstrError->setValue ('ServiceAddress-ServiceAddressType');
					}
					else if ($_POST ['ServiceAddress']['ServiceStreetType'] && !$bolServiceStreetType)
					{
						$oblstrError->setValue ('ServiceAddress-ServiceStreetType');
					}
					else if ($_POST ['ServiceAddress']['ServiceStreetTypeSuffix'] && !$bolServiceStreetSuffixType)
					{
						$oblstrError->setValue ('ServiceAddress-ServiceStreetSuffixType');
					}
					else if ($_POST ['ServiceAddress']['ServiceState'] && !$bolServiceStateType)
					{
						$oblstrError->setValue ('ServiceAddress-ServiceStateType');
					}
				}
				
				$docDocumentation->Explain ('Service Address');
				
				$Style->Output ('xsl/content/service/add_landline.xsl');
				exit;
			}
		}
	}
	
	$Style->Output ('xsl/content/service/add.xsl');
	
?>

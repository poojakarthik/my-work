<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//

	// call application loader
	require ('config/application_loader.php');
	require ('../../lib/framework/json.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_SERVICE_ADDRESS | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECORD_TYPE | MODULE_COST_CENTRE | MODULE_STATE | MODULE_TITLE | MODULE_PROVISIONING | MODULE_CARRIER;

	// call application
	require ('config/application.php');
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Rate Plan');
	$docDocumentation->Explain ('Service Address');
	$docDocumentation->Explain ('Carrier');
	$docDocumentation->Explain ('Provisioning');
			
	$objResults = AjaxRecieve();
	if ($objResults)
	{
		//$hello = $objResults;
		//$hey = print_r($hello, true);
		// if this page was called by ajax
		// do something with the results
		
		/* $objResults format:
			[$objResults] - [serviceCount]
							[account]
							[service1] - 	[FNN]
											[CostCentre]
											[Plan]
											[inputID] <- to be added
								|
							[serviceN]
							[provisioning]
									- [etc . . .]
		*/
		/* Reply to AJAX format:
			[$arrReply] -	[serviceCount]
							[service1] - 	[saved]
											[inputID] <- to be added
								|
							[serviceN]
							['errorCount']
							[error1] -		[errorDescription]
											[inputID] <- to be added
								|
							[errorN]		
		need to add inputID so that instead of just alerting an error, we
		can highlight the erronous service using getElementById
		*/
		
		//AjaxReply(print_r($objResults), TRUE);
		//exit;
		
		$actAccount = $Style->attachObject (new Account ($objResults->account));
		$arrReply = Array();
		
		$arrReply["serviceCount"] = $objResults->serviceCount;
		$arrReply["errorCount"] = 0;
		
		// Iterate through the services first, to check whether they already exist
		// This prevents one or two services being added successfully, and then
		// the last one failing
		
		$arrFNN = Array();
		
		for ($i=1; $i<=$objResults->serviceCount; $i++)
		{
			$objResults->{"service$i"}->FNN = trim($objResults->{"service$i"}->FNN);
			$Tester = Services::DoesFNNExist($objResults->{"service$i"}->FNN);
			if ($Tester == 0)
			{
				$arrFNN[$objResults->{"service$i"}->FNN] = true;
			}
			else
			{
				 // Reply: !error! FNN already exists
				 $arrReply['errorCount']++;
				 $arrReply["error" . $arrReply['errorCount']] = "The service " . $objResults->{"service$i"}->FNN . " already exists";
			}
		}
		// if any already exist, immediately tell the user
		if ($arrReply['errorCount'] <> 0)
		{
			AjaxReply($arrReply);
			exit;
		}
		
		for ($i=1; $i<=$objResults->serviceCount; $i++)
		{
			
			$strFNN = $objResults->{"service$i"}->FNN;
			if ($arrFNN[$strFNN])
			{
				try
				{
					$rrpPlan = new RatePlan ($objResults->{"service$i"}->Plan);
					$srvService = Services::Add (
						$athAuthentication->AuthenticatedEmployee (),
						$actAccount,
						$rrpPlan,
						Array (
							"FNN"					=> $strFNN,
							"Indial100"				=> (int)$objResults->{"service$i"}->Indial100,
							"CostCentre"			=> ($objResults->{"service$i"}->CostCentre == 0) ? null : $objResults->{"service$i"}->CostCentre,
							"ServiceType"			=> ServiceType($strFNN)
						)
					);
					
					// ELB
					if ((int)$objResults->{"service$i"}->Indial100 && (int)$objResults->{"service$i"}->ELB)
					{
						$GLOBALS['fwkFramework']->EnableELB((int)$srvService->Pull('Id')->getValue());
					}
				}
				catch (Exception $e)
				{
					// Reply: !error! Adding service failed
					$arrReply['errorCount']++;
					$arrReply["error" . $arrReply['errorCount']] = "Service " . $strFNN . " could not be added";
				}
				// This plan was successfuly
				
				// Reply: !success! Service was added
				$arrReply["service" . $i] = true;	
				
				if ((ServiceType($strFNN)) == SERVICE_TYPE_LAND_LINE)
				{
					// Also add provisioning details if applicable (ie, landline)
					// nb. don't really need to check provisioning, java does this first
				
					try
					{
						// Save Information	
						$srvService->ServiceAddressUpdate (
							Array (
								'Residential'					=> $objResults->Provisioning->Residential,
								'BillName'						=> $objResults->Provisioning->BillName,
								'BillAddress1'					=> $objResults->Provisioning->BillAddress1,
								'BillAddress2'					=> $objResults->Provisioning->BillAddress2,
								'BillLocality'					=> $objResults->Provisioning->BillLocality,
								'BillPostcode'					=> $objResults->Provisioning->BillPostcode,
								'EndUserTitle'					=> $objResults->Provisioning->EndUserTitle,
								'EndUserGivenName'				=> $objResults->Provisioning->EndUserGivenName,
								'EndUserFamilyName'				=> $objResults->Provisioning->EndUserFamilyName,
								'EndUserCompanyName'			=> $objResults->Provisioning->EndUserCompanyName,
								'DateOfBirth:day'				=> $objResults->Provisioning->DateOfBirthday,
								'DateOfBirth:month'				=> $objResults->Provisioning->DateOfBirthmonth,
								'DateOfBirth:year'				=> $objResults->Provisioning->DateOfBirthyear,
								'Employer'						=> $objResults->Provisioning->Employer,
								'Occupation'					=> $objResults->Provisioning->Occupation,
								'ABN'							=> $objResults->Provisioning->ABN,
								'TradingName'					=> $objResults->Provisioning->TradingName,
								'ServiceAddressType'			=> $objResults->Provisioning->ServiceAddressType,
								'ServiceAddressTypeNumber'		=> $objResults->Provisioning->ServiceAddressTypeNumber,
								'ServiceAddressTypeSuffix'		=> $objResults->Provisioning->ServiceAddressTypeSuffix,
								'ServiceStreetNumberStart'		=> $objResults->Provisioning->ServiceStreetNumberStart,
								'ServiceStreetNumberEnd'		=> $objResults->Provisioning->ServiceStreetNumberEnd,
								'ServiceStreetNumberSuffix'		=> $objResults->Provisioning->ServiceStreetNumberSuffix,
								'ServiceStreetName'				=> $objResults->Provisioning->ServiceStreetName,
								'ServiceStreetType'				=> $objResults->Provisioning->ServiceStreetType,
								'ServiceStreetTypeSuffix'		=> $objResults->Provisioning->ServiceStreetTypeSuffix,
								'ServicePropertyName'			=> $objResults->Provisioning->ServicePropertyName,
								'ServiceLocality'				=> $objResults->Provisioning->ServiceLocality,
								'ServiceState'					=> $objResults->Provisioning->ServiceState,
								'ServicePostcode'				=> $objResults->Provisioning->ServicePostcode
							)
						);
					}
					catch (Exception $e)
					{
						// Reply: !error! Adding provisioning failed
						$arrReply['errorCount']++;
						$arrReply["error" . $arrReply['errorCount']] = "Provisioning Details for service " . $strFNN . " could not be added";
					}
					
					
					try
					{
						// also do a provisioning request ?huh?
						// could get data from globals, hardcoded for now
						
						// $GLOBALS['*arrConstant']	['Request']	[901]	['Constant']	= 'REQUEST_PRESELECTION';
						// $GLOBALS['*arrConstant']	['Request']	[900]	['Constant']	= 'REQUEST_FULL_SERVICE';
						// $GLOBALS['*arrConstant']	['Carrier']	[2]	['Constant']	= 'CARRIER_OPTUS';
						// $GLOBALS['*arrConstant']	['Carrier']	[1]	['Constant']	= 'CARRIER_UNITEL';
						// full service - unitel
						// preselection - optus
						
						// Get Rate Plan info
						$selRatePlan	= new StatementSelect("RatePlan", "*", "Id = <Id>");
						$selRatePlan->Execute(Array('Id' => $objResults->{"service$i"}->Plan));
						$arrRatePlan	=$selRatePlan->Fetch();
						
						// Check the requested Carrier Exists
						$carCarrier = new Carriers ();
						if (!$carCarrier->setValue ($arrRatePlan['CarrierPreselection']) || !$carCarrier->setValue ($arrRatePlan['CarrierFullService']))
						{	
							// Reply: !error! Provisioning request failed
							$arrReply['errorCount']++;
							$arrReply["error" . $arrReply['errorCount']] ="Provisioning Request for service " . $strFNN . " was not successful";
						}
						
						// Check the requested Provisioning Request Type exists
						$prtRequestType = new ProvisioningRequestTypes ();
						if (!$prtRequestType->setValue (901) || !$prtRequestType->setValue (900))
						{
							// Reply: !error! Provisioning request failed
							$arrReply['errorCount']++;
							$arrReply["error" . $arrReply['errorCount']] ="Provisioning Request for service " . $strFNN . " was not successful";
						}
						
						// Do the Provisioning Request
						$srvService->CreateNewProvisioningRequest ($athAuthentication->AuthenticatedEmployee (), $arrRatePlan['CarrierFullService'], 900);
						$srvService->CreateNewProvisioningRequest ($athAuthentication->AuthenticatedEmployee (), $arrRatePlan['CarrierPreselection'], 901);
					}
					catch (Exception $e)
					{
						// Reply: !error! Provisioning request failed
						$arrReply['errorCount']++;
						$arrReply["error" . $arrReply['errorCount']] ="Provisioning Request for service " . $strFNN . " was not successful";
					}
				} // end of landline provisioning request if
			
			unset($arrFNN[$strFNN]);		
				
			} // end of service duplicate if
				
		} // end of service for-loop		
			
		// Send stuff back to ajax
		AjaxReply($arrReply);
		exit;
	}

	// Try and get the associated account
	try
	{
		$actAccount = $Style->attachObject (new Account (($_GET ['Account']) ? $_GET ['Account'] : $_POST ['Account']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/account/notfound.xsl');
		exit;
	}
		

	// Provisioning Selection Options
	$Style->attachObject (new ServiceAddressTypes		( ));
	$Style->attachObject (new ServiceStreetTypes		( ));
	$Style->attachObject (new ServiceStreetSuffixTypes	( ));
	$Style->attachObject (new TitleTypes				( ));
	$Style->attachObject (new ServiceStateTypes			( ));
	
	// START of the functionality to save service details
	
	// Build a String for Remembering Errors
	$oblstrError 		= $Style->attachObject (new dataString ('Error'));
	
	// Start the UI values storage engine
	$oblarrUIValues		= $Style->attachObject (new dataArray ('ui-values'));
	$srvServiceTypes	= $oblarrUIValues->Push (new ServiceTypes);
	$oblstrFNN_1		= $oblarrUIValues->Push (new dataString ('FNN-1'));
	$oblstrFNN_2		= $oblarrUIValues->Push (new dataString ('FNN-2'));
	if ($_POST ['ServiceType'])
	{
		// Set the FNN for use if there's an error
		$oblstrFNN_1->setValue (preg_replace ("/\s/", "", $_POST ['FNN-1']));
		$oblstrFNN_2->setValue (preg_replace ("/\s/", "", $_POST ['FNN-2']));
		
		// If the Service Type is Invalid, Error
		if (!$srvServiceTypes->setValue ($_POST ['ServiceType']))
		{
			$oblstrError->setValue ("Service Type");
		}
		else
		{
			if ($_POST ['RatePlan'])
			{
				// Get the Rate Plan. If it doesn't exist
				// then this is an error
				try
				{
					$rrpPlan = new RatePlan ($_POST ['RatePlan']);
				}
				catch (Exception $e)
				{
					$oblstrError->setValue ('Rate Plan Invalid');
				}
				
				if ($rrpPlan)
				{
					// This Try will fail if there is an unarchived service
					// with the same FNN already in the database
					try
					{
						$srvService = Services::Add (
							$athAuthentication->AuthenticatedEmployee (),
							$actAccount,
							$rrpPlan,
							Array (
								"FNN"					=> $oblstrFNN_1->getValue (),
								"Indial100"				=> isset ($_POST ['Indial100']) ? TRUE : FALSE,
								"CostCentre"			=> $_POST ['CostCentre'],
								"ServiceType"			=> $_POST ['ServiceType']
							)
						);
						
						header ('Location: ../admin/flex.php/Service/View/?Service.Id=' . $srvService->Pull ('Id')->getValue ());
						exit;
					}
					catch (Exception $e)
					{
						$oblstrError->setValue ($e->getMessage ());
					}
				}
			}
			
			// Get Cost Centres
			$ccrCostCentres = $Style->attachObject (new CostCentres);
			$ccrCostCentres->Constrain ('Account',	'=',	$actAccount->Pull ('Id')->getValue ());
			$ccrCostCentres->Sample ();
			
			// Get the Plans that this ServiceType can have
			$rplRatePlans = $Style->attachObject (new RatePlans);
			$rplRatePlans->Constrain ('ServiceType',	'=', $_POST ['ServiceType']);
			$rplRatePlans->Constrain ('Archived',		'=', 0);
			$rplRatePlans->Order ('Name', TRUE);
			$rplRatePlans->Sample ();
			
			$Style->Output (
				'xsl/content/service/add_2.xsl',
				Array (
					'Account'	=> $actAccount->Pull ('Id')->getValue ()
				)
			);
		}
		
		exit;
	}
	
	
	// END of functionality to save service details
	
	
	// Get the Plans for all ServiceTypes
	$rplRatePlans = $Style->attachObject (new RatePlans);
	$rplRatePlans->Constrain ('Archived',		'=', 0);
	$rplRatePlans->Order ('Name', TRUE);
	$rplRatePlans->Sample ();

	// Get Cost Centres
	$ccrCostCentres = $Style->attachObject (new DOMCostCentres ($actAccount));
	
	// Output the Information
	$Style->Output (
		'xsl/content/service/add_1.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>

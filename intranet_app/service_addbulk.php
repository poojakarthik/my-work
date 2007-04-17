<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//

	// call application loader
	require ('config/application_loader.php');
	require ('../framework/json.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_SERVICE_ADDRESS | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECORD_TYPE | MODULE_COST_CENTRE | MODULE_STATE | MODULE_TITLE;

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
		
		/* $results format:
			[$results] - 	[serviceCount]
							[account]
							[service1] - 	[FNN]
											[CostCentre]
											[Plan]
											[Type]
								|
							[serviceN]
		*/
		$actAccount = $Style->attachObject (new Account ($objResults->account));
		for ($i=1; $i<=$objResults->serviceCount; $i++)
		{		
			$Tester = Services::DoesFNNExist($objResults->{"service$i"}->FNN);
			if ($Tester <> 0)
			{
				AjaxReply("The service " . $objResults->{"service$i"}->FNN . " already exists. Please enter a different number.");
				exit;
			}
			else if (!$objResults->{"service$i"}->Plan)
			{
				AjaxReply("The service " . $objResults->{"service$i"}->FNN . " does not have a plan selected.");
				exit;
			}
			else
			{
				try
				{
					$rrpPlan = new RatePlan ($objResults->{"service$i"}->Plan);
					$srvService = Services::Add (
						$athAuthentication->AuthenticatedEmployee (),
						$actAccount,
						$rrpPlan,
						Array (
							"FNN"					=> $objResults->{"service$i"}->FNN,
							"Indial100"				=> FALSE,
							"CostCentre"			=> $objResults->{"service$i"}->CostCentre,
							"ServiceType"			=> ServiceType($objResults->{"service$i"}->FNN)
						)
					);
				}
				catch (Exception $e)
				{
					AjaxReply("Error");
					exit;
				}
			}
		}
		

		// Send stuff back to ajax
		AjaxReply($objResults);
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
						
						header ("Location: service_view.php?Id=" . $srvService->Pull ('Id')->getValue ());
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

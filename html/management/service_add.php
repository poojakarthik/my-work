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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_SERVICE_ADDRESS | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECORD_TYPE | MODULE_COST_CENTRE;
	
	// call application
	require ('config/application.php');
	//debug($_POST);
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Rate Plan');
	
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
			if ($oblstrFNN_1->getValue () <> $oblstrFNN_2->getValue ())
			{
				$oblstrError->setValue ('Mismatch');
			}
			else if ($oblstrFNN_1->getValue () <> "" && !IsValidFNN ($oblstrFNN_1->getValue ()))
			{
				$oblstrError->setValue ('FNN ServiceType');
			}
			else if ($oblstrFNN_1->getValue () <> "" && ServiceType ($oblstrFNN_1->getValue ()) <> $_POST ['ServiceType'])
			{
				$oblstrError->setValue ('FNN ServiceType');
			}
			else if ($_POST ['RatePlan'])
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
			debug($ccrCostCentres);die;
			
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
	
	// Get Cost Centres
	$ccrCostCentres = $Style->attachObject (new DOMCostCentres ($actAccount));
	//echo($ccrCostCentres);die;
	
	// Output the Information
	$Style->Output (
		'xsl/content/service/add_1.xsl',
		Array (
			'Account'	=> $actAccount->Pull ('Id')->getValue ()
		)
	);
	
?>

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
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_SERVICE_ADDRESS | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information for an Account
	$docDocumentation->Explain ('AccountGroup');
	$docDocumentation->Explain ('Account');
	$docDocumentation->Explain ('Service');
	$docDocumentation->Explain ('Rate Plan');
	
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
	
	
	// Build a String for Remembering Errors
	$oblstrError 		= $Style->attachObject (new dataString ('Error'));
	
	// Start the UI values storage engine
	$oblarrUIValues		= $Style->attachObject (new dataArray ('ui-values'));
	$srvServiceTypes	= $oblarrUIValues->Push (new ServiceTypes);
	$oblstrFNN_1		= $oblarrUIValues->Push (new dataString ('FNN-1'));
	$oblstrFNN_2		= $oblarrUIValues->Push (new dataString ('FNN-2'));
	
	if ($_POST ['ServiceType'])
	{
		// If the Service Type is Invalid, Error
		if (!$srvServiceTypes->setValue ($_POST ['ServiceType']))
		{
			$oblstrError->setValue ("Service Type");
		}
		else
		{
			if ($_POST ['FNN-1'] <> $_POST ['FNN-2'])
			{
				$oblstrError->setValue ('Mismatch');
			}
			else if ($_POST ['RatePlan'])
			{
				// Set the FNN for use if there's an error
				$oblstrFNN_1->setValue ($_POST ['FNN-1']);
				$oblstrFNN_2->setValue ($_POST ['FNN-2']);
				
				// Get the Rate Plan. If it doesn't exist
				// then this is an error
				try
				{
					$rrpPlan = new RatePlan ($_POST ['RatePlan']);
				}
				catch (Exception $e)
				{
					$oblstrError->setValue ('Could not find valid Rate Plan');
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
								"FNN"					=> $_POST ['FNN-1'],
								"Indial100"				=> isset ($_POST ['Indial100']) ? TRUE : FALSE,
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
			
			// Get the Plans that this ServiceType can have
			$rplRatePlans = $Style->attachObject (new RatePlans);
			$rplRatePlans->Constrain ('ServiceType',	'=', $_POST ['ServiceType']);
			$rplRatePlans->Constrain ('Archived',		'=', 0);
			$rplRatePlans->Order ('Name', TRUE);
			$rplRatePlans->Sample ();
			
			$Style->Output ('xsl/content/service/add_2.xsl');
		}
		
		exit;
	}
	
	// Output the Information
	$Style->Output ('xsl/content/service/add_1.xsl');
	
?>

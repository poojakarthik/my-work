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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_PLAN | MODULE_RATE_GROUP | MODULE_RECORD_TYPE | MODULE_RECURRING_CHARGE | MODULE_BILLING;
	
	// call application
	require ('config/application.php');
	
	$docDocumentation->Explain ("Rate Plan");
	$docDocumentation->Explain ("Record Type");
	$docDocumentation->Explain ("Service");
	
	$oblarrRatePlan		= $Style->attachObject (new dataArray ('RatePlan'));
	
	$oblstrName			= $oblarrRatePlan->Push (new dataString ('Name', ''));
	$svtServiceType		= $oblarrRatePlan->Push (new ServiceTypes);
	
	$oblstrError 		= $oblarrRatePlan->Push (new dataString ('Error', ''));
	
	if (isset ($_POST ['Name']) && isset ($_POST ['ServiceType']))
	{
		$oblstrName->setValue ($_POST ['Name']);
		$svtServiceType->setValue ($_POST ['ServiceType']);
		
		if (empty ($_POST ['Name']) || empty ($_POST ['ServiceType']))
		{
			$oblstrError->setValue ('Blank');
		}
		else
		{
			$selRatePlanName = new StatementSelect (
				"RatePlan", 
				"count(*) AS Length", 
				"Name = <Name> AND ServiceType = <ServiceType> AND Archived = 0"
			);
			
			$selRatePlanName->Execute (Array ("Name" => $_POST ['Name'], "ServiceType" => $_POST ['ServiceType']));
			$arrLength = $selRatePlanName->Fetch ();
			
			if ($arrLength ['Length'] <> 0)
			{
				$oblstrError->setValue ('Exists');
			}
			else
			{
				if (isset ($_POST ['RecordType']))
				{
					// Find out if all of the Required RecordTypes are Filled
					$selRatePlanName = new StatementSelect (
						"RecordType", 
						"Id", 
						"Required = 1 AND ServiceType = <ServiceType>"
					);
					
					$selRatePlanName->Execute (Array ("ServiceType" => $_POST ['ServiceType']));
					
					// Loop through each Required Item
					while ($arrRow = $selRatePlanName->Fetch ())
					{
						// If the required record type does not exist, we need 
						// to display an error
						if (!isset ($_POST ['RecordType'][$arrRow ['Id']]) || empty ($_POST ['RecordType'][$arrRow ['Id']]))
						{
							$oblstrError->setValue ('Requirements');
							break;
						}
					}
					
					if ($oblstrError->getValue () == "")
					{
						foreach ($_POST ['RecordType'] AS $intRecordType => $intRateGroup)
						{
							if (!empty ($intRateGroup))
							{
								// Do a check on the Rate Group to see if it exists
								$selRatePlanName = new StatementSelect (
									"RateGroup", 
									"count(*) AS Length", 
									"Id = <Id> AND Archived = 0 AND ServiceType = <ServiceType>"
								);
								
								$selRatePlanName->Execute (
									Array (
										"Id"			=> $intRateGroup,
										"ServiceType"	=> $_POST ['ServiceType']
									)
								);
								
								$arrLength = $selRatePlanName->Fetch ();
								
								if ($arrLength ['Length'] <> 1)
								{
									$oblstrError->setValue ('Mishandled');
									break;
								}
							}
						}
						
						if ($oblstrError->getValue () == "")
						{
							// If there is a list of Selected Recurring Charges, make sure they all exist.
							foreach ($_POST ['SelectedRecurringChargeTypes'] as $intRecurringChargeType)
							{
								try
								{
									$rctRecurringChargeType = new RecurringChargeType ($intRecurringChargeType);
								}
								catch (Exception $e)
								{
									$oblstrError->setValue ('SelectedRecurringChargeType');
									break;
								}
							}
							
							if ($oblstrError->getValue () == "")
							{
								// If we're up to here, we want to insert the information into the database
								// because it's all valid
								
								// Insert the New Rate Plan into the Database and get its InsertID
								
								$arrPlan = Array (
									'Name'			=> $_POST ['Name'],
									'Description'	=> $_POST ['Description'] ,
									'ServiceType'	=> $_POST ['ServiceType'],
									'MinMonthly'	=> $_POST ['MinMonthly'],
									'ChargeCap'		=> $_POST ['ChargeCap'],
									'UsageCap'		=> $_POST ['UsageCap'],
									'Archived'		=> FALSE
								);
								
								$insInsertRatePlan	= new StatementInsert("RatePlan");
								$intRatePlanId		= $insInsertRatePlan->Execute ($arrPlan);
								
								// Foreach RecordType that is Not Blank
								foreach ($_POST ['RecordType'] AS $intRecordType => $intRateGroup)
								{
									if (!empty ($intRateGroup))
									{
										// Insert the RecordType in to the Database
										
										$arrRatePlanRateGroup = Array (
											'RatePlan'		=> $intRatePlanId,
											'RateGroup'		=> $intRateGroup
										);
										
										$insInsertRatePlanRateGroup	= new StatementInsert("RatePlanRateGroup");
										$insInsertRatePlanRateGroup->Execute ($arrRatePlanRateGroup);
									}
								}
								
								// Foreach Selected Recurring Charge
								foreach ($_POST ['SelectedRecurringChargeTypes'] as $intRecurringChargeType)
								{
									// Insert the Selected Recurring Charge in to the Database
									
									$arrRatePlanRecurringChargeType = Array (
										'RatePlan'				=> $intRatePlanId,
										'RecurringChargeType'	=> $intRecurringChargeType
									);
									
									$insRatePlanRecurringChargeType	= new StatementInsert("RatePlanRecurringChargeType");
									$insRatePlanRecurringChargeType->Execute ($arrRatePlanRecurringChargeType);
								}
								
								
								header ("Location: rates_plan_added.php");
								exit;
							}
						}
					}
				}
				
				$rtsRecordTypes = new RecordTypes ();
				$rtsRecordTypes->Constrain ('ServiceType', 'EQUALS', $_POST ['ServiceType']);
				$rtsRecordTypes->Order ('Name', TRUE);
				$rtsRecordTypes->Sample ();
				$oblarrRatePlan->Push ($rtsRecordTypes);
				
				$rtsRateGroups = new RateGroups ();
				$rtsRateGroups->Constrain ('ServiceType', 'EQUALS', $_POST ['ServiceType']);
				$rtsRateGroups->Order ('Name', TRUE);
				$rtsRateGroups->Sample ();
				$oblarrRatePlan->Push ($rtsRateGroups);
				
				$rclRecurringChargeTypes = new RecurringChargeTypes ();
				$rclRecurringChargeTypes->Order ('Description', TRUE);
				$rclRecurringChargeTypes->Sample ();
				$oblarrRatePlan->Push ($rclRecurringChargeTypes);
				
				$Style->Output ("xsl/content/rates/plans/select.xsl");
				exit;
			}
		}
	}
	
	$Style->Output ("xsl/content/rates/plans/add.xsl");
	
?>

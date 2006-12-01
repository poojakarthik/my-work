<?php
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
	$docDocumentation->Explain ("Rate Plan");
	$docDocumentation->Explain ("Service");
	
	$oblarrRatePlan		= $Style->attachObject (new dataArray ('RatePlan'));
	
	$oblstrName			= $oblarrRatePlan->Push (new dataString ('Name', ''));
	$svtServiceType		= $oblarrRatePlan->Push (new NamedServiceType);
	
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
				"Name = <Name> AND Archived = 0"
			);
			
			$selRatePlanName->Execute (Array ("Name" => $_POST ['Name']));
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
								// Log error
								// TODO!!!!
								
								$oblstrError->setValue ('Mishandled');
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
								// Insert the RecordType in to the Database
								
								$arrRatePlanRateGroup = Array (
									'RatePlan'		=> $intRatePlanId,
									'RateGroup'		=> $intRateGroup
								);
								
								$insInsertRatePlanRateGroup	= new StatementInsert("RatePlanRateGroup");
								$insInsertRatePlanRateGroup->Execute ($arrRatePlanRateGroup);
							}
							
							header ("Location: rates_plan_added.php");
							exit;
						}
					}
				}
				
				$rtsRecordTypes = new RecordTypeSearch ();
				$rtsRecordTypes->Constrain ('ServiceType', 'EQUALS', $_POST ['ServiceType']);
				$rtsRecordTypes->Order ('Name', TRUE);
				$rtsRecordTypes->Sample ();
				$oblarrRatePlan->Push ($rtsRecordTypes);
				
				$rtsRateGroups = new RateGroups ();
				$rtsRateGroups->Constrain ('ServiceType', 'EQUALS', $_POST ['ServiceType']);
				$rtsRateGroups->Order ('Name', TRUE);
				$rtsRateGroups->Sample ();
				$oblarrRatePlan->Push ($rtsRateGroups);
				
				$Style->Output ("xsl/content/rates/plans/select.xsl");
				exit;
			}
		}
	}
	
	$Style->Output ("xsl/content/rates/plans/add.xsl");
	
?>

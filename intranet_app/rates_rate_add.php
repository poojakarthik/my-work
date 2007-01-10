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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	$docDocumentation->Explain ("Rate");
	$docDocumentation->Explain ("Record Type");
	$docDocumentation->Explain ("Service");
	
	$rrlRates = new Rates ();
	
	$oblarrRate			= $Style->attachObject (new dataArray ('Rate'));
	
	$oblstrName			= $oblarrRate->Push (new dataString ('Name', ''));
	$svtServiceType		= $oblarrRate->Push (new ServiceTypes);
	
	$oblstrError 		= $oblarrRate->Push (new dataString ('Error', ''));
	
	// If we have a Name and a ServiceType, Validate it to see whether
	// or not they are valid
	if (isset ($_POST ['Name']) && isset ($_POST ['ServiceType']))
	{
		$oblstrName->setValue ($_POST ['Name']);
		
		$rctRecordType		= $oblarrRate->Push (new RecordType ($_POST ['RecordType']));
		
		// If the Name is Empty (or it equals '0'), there's a problem
		if (empty ($_POST ['Name']))
		{
			$oblstrError->setValue ('Blank');
		}
		// If there is a Rate with the Same name that is Not Archived, there's a problem
		else if ($rrlRates->UnarchivedNameExists ($_POST ['Name']))
		{
			$oblstrError->setValue ('Exists');
		}
		// If we can't set the service type, then it does not exist
		else if (!$svtServiceType->setValue ($_POST ['ServiceType']))
		{
			$oblstrError->setValue ('ServiceType');
		}
		// If we reach here, then the Name and ServiceType are valid
		else
		{
			$oblstrDescription		= $oblarrRate->Push (new dataString		('Description',		''));
			$oblintRecordType		= $oblarrRate->Push (new dataInteger	('RecordType',		0));
			$oblbolMonday			= $oblarrRate->Push (new dataBoolean	('Monday',			FALSE));
			$oblbolTuesday			= $oblarrRate->Push (new dataBoolean	('Tuesday',			FALSE));
			$oblbolWednesday		= $oblarrRate->Push (new dataBoolean	('Wednesday',		FALSE));
			$oblbolThursday			= $oblarrRate->Push (new dataBoolean	('Thursday',		FALSE));
			$oblbolFriday			= $oblarrRate->Push (new dataBoolean	('Friday',			FALSE));
			$oblbolSaturday			= $oblarrRate->Push (new dataBoolean	('Saturday',		FALSE));
			$oblbolSunday			= $oblarrRate->Push (new dataBoolean	('Sunday',			FALSE));
			
			$oblintStdUnits			= $oblarrRate->Push (new dataInteger	('StdUnits',		0));
			$oblstrStdChargeType	= $oblarrRate->Push (new dataString		('StdChargeType',	''));
			$oblfltStdRatePerUnit	= $oblarrRate->Push (new dataFloat		('StdRatePerUnit',	0));
			$oblfltStdMarkup		= $oblarrRate->Push (new dataFloat		('StdMarkup',		0));
			$oblfltStdPercentage	= $oblarrRate->Push (new dataFloat		('StdPercentage',	0));
			$oblfltStdMinCharge		= $oblarrRate->Push (new dataFloat		('StdMinCharge',	0));
			$oblfltStdFlagfall		= $oblarrRate->Push (new dataFloat		('StdFlagfall',		0));
			
			$oblstrCapCalculation	= $oblarrRate->Push (new dataString		('CapCalculation',	''));
			
			if (isset ($_POST ['StartTime']) && isset ($_POST ['EndTime']))
			{
				// Do some error checking.
				
				// Check that the start date occurs before the end date
				if (strtotime ($_POST ['StartTime']) >= strtotime ($_POST ['EndTime']))
				{
					$oblstrError->setValue ('Hours');
				}
				// Check StartTime is a valid time
				else if (!preg_match ('/^\d\d\:\d\d$/', $_POST ['StartTime']))
				{
					$oblstrError->setValue ('StartTimeInvalid');
				}
				// Check EndTime is a valid time
				else if (!preg_match ('/^\d\d\:\d\d$/', $_POST ['EndTime']))
				{
					$oblstrError->setValue ('EndTimeInvalid');
				}
				// Check that the Select Units Selection 
				else if  (empty ($_POST ['StdUnits']) || !is_numeric ($_POST ['StdUnits']))
				{
					$oblstrError->setValue ('StandardUnits');
				}
				// Check that the Select Standard Charge Type is a Number and not 0
				else if  (empty ($_POST [$_POST ['StdChargeType']]) || !is_numeric ($_POST [$_POST ['StdChargeType']]))
				{
					$oblstrError->setValue ('StandardRateSelection');
				}
				// If we're using a Cap, make sure the value of the cap exists
				else if (!empty ($_POST ['CapCalculation']) && (!isset ($_POST [$_POST ['CapCalculation']]) || !is_numeric ($_POST [$_POST ['CapCalculation']])))
				{
					$oblstrError->setValue ('CapCalculationSelection');
				}
				// If we're using a Cap and Cap Limit, make sure the value of the cap limit exists
				else if (!empty ($_POST ['CapCalculation']) && !empty ($_POST ['CapLimiting']) && (!isset ($_POST [$_POST ['CapLimiting']]) || !is_numeric ($_POST [$_POST ['CapLimiting']])))
				{
					$oblstrError->setValue ('CapLimitingSelection');
				}
				// If we're using a Cap and a Cap Limit, make sure the value of the Excess Charge Types exists
				else if  ($_POST ['CapCalculation'] && $_POST ['CapLimiting'] && !is_numeric ($_POST [$_POST ['ExsChargeType']]))
				{
					$oblstrError->setValue ('ExcessRateSelection');
				}
				// Check at least one of the Days are Selected
				else if  (!isset ($_POST ['Monday']) && !isset ($_POST ['Tuesday']) && !isset ($_POST ['Wednesday']) &&
				!isset ($_POST ['Thursday']) && !isset ($_POST ['Friday']) && !isset ($_POST ['Saturday']) && !isset ($_POST ['Sunday']))
				{
					$oblstrError->setValue ('Weekday');
				}
				// If all those guards are passed, then you're valid
				else
				{
					$rrlRates->Add (
						Array (
							"Name"				=> $_POST ['Name'],
							"Description"		=> $_POST ['Description'],
							"ServiceType"		=> $_POST ['ServiceType'],
							"RecordType"		=> $_POST ['RecordType'],
							"StartTime"		=> $_POST ['StartTime'],
							"EndTime"			=> $_POST ['EndTime'],
							"Monday"			=> isset ($_POST ['Monday']) ? 1 : 0,
							"Tuesday"			=> isset ($_POST ['Tuesday']) ? 1 : 0,
							"Wednesday"			=> isset ($_POST ['Wednesday']) ? 1 : 0,
							"Thursday"			=> isset ($_POST ['Thursday']) ? 1 : 0,
							"Friday"			=> isset ($_POST ['Friday']) ? 1 : 0,
							"Saturday"			=> isset ($_POST ['Saturday']) ? 1 : 0,
							"Sunday"			=> isset ($_POST ['Sunday']) ? 1 : 0,
							"StdUnits"			=> $_POST ['StdUnits'],
							"StdRatePerUnit"	=> ($_POST ['StdChargeType'] == "StdRatePerUnit") ? $_POST ['StdRatePerUnit'] : 0,
							"StdMarkup"			=> ($_POST ['StdChargeType'] == "StdMarkup") ? $_POST ['StdMarkup'] : 0,
							"StdPercentage"		=> ($_POST ['StdChargeType'] == "StdPercentage") ? $_POST ['StdPercentage'] : 0,
							"StdMinCharge"		=> $_POST ['StdMinCharge'],
							"StdFlagfall"		=> $_POST ['StdFlagfall'],
							"CapUnits"			=> $_POST ['CapUnits'],
							"CapCost"			=> $_POST ['CapCost'],
							"CapUsage"			=> $_POST ['CapUsage'],
							"CapLimit"			=> $_POST ['CapLimit'],
							"ExsUnits"			=> $_POST ['ExsUnits'],
							"ExsRatePerUnit"	=> ($_POST ['ExsChargeType'] == "ExsRatePerUnit") ? $_POST ['ExsRatePerUnit'] : 0,
							"ExsMarkup"			=> ($_POST ['ExsChargeType'] == "ExsMarkup") ? $_POST ['ExsMarkup'] : 0,
							"ExsPercentage"		=> ($_POST ['ExsChargeType'] == "ExsPercentage") ? $_POST ['ExsPercentage'] : 0,
							"ExsFlagfall"		=> $_POST ['ExsFlagfall'],
							"Prorate"			=> isset ($_POST ['Prorate']) ? 1 : 0,
							"Fleet"				=> isset ($_POST ['Fleet']) ? 1 : 0,
							"Uncapped"			=> isset ($_POST ['Uncapped']) ? 1 : 0
						)
					);
					
					header ("Location: rates_rate_added.php");
					exit;
				}
			}
			
			$Style->Output ("xsl/content/rates/rates/select.xsl");
			exit;
		}
	}
	
	$Style->Output ("xsl/content/rates/rates/add.xsl");
	
?>

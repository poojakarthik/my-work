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
	$arrPage['Modules']		= MODULE_BASE | MODULE_RATE_GROUP | MODULE_SERVICE_TYPE | MODULE_RECORD_TYPE;
	
	// call application
	require ('config/application.php');
	
	$rglRateGroups = new RateGroups ();
	
	$docDocumentation->Explain ("Rate Group");
	$docDocumentation->Explain ("Record Type");
	$docDocumentation->Explain ("Service");
	
	$oblarrRateGroup	= $Style->attachObject (new dataArray ('RateGroup'));
	
	$oblstrName			= $oblarrRateGroup->Push (new dataString ('Name', ''));
	$svtServiceType		= $oblarrRateGroup->Push (new ServiceTypes);
	
	$oblstrError 		= $oblarrRateGroup->Push (new dataString ('Error', ''));
	
	if (isset ($_POST ['Name']) && isset ($_POST ['ServiceType']))
	{
		if (empty ($_POST ['Name']))
		{
			// The Name of the Rate Group cannot be empty
			$oblstrError->setValue ('Blank');
		}
		else if (!$svtServiceType->setValue ($_POST ['ServiceType']))
		{
			// Ensure that the Service Type is Valid
			$oblstrError->setValue ('ServiceType');
		}
		else if ($rglRateGroups->UnarchivedNameExists ($_POST ['Name'], $_POST ['ServiceType']))
		{
			// Check that an Item of the same Name that is Not Archived Exists
			$oblstrError->setValue ('Exists');
		}
		else
		{
			// If we're here, we only have to set the name because the ServiceType
			// was set in the IF gaurd about 2 statements ago
			$oblstrName->setValue ($_POST ['Name']);
			
			// Also - set the Record Type
			$rtyRecordType = $oblarrRateGroup->Push (new RecordType ($_POST ['RecordType']));
			
			// If we have selected rates in the post, we want to try to add the stuff 
			// to the database.
			if (isset ($_POST ['SelectedRates']) && is_array ($_POST ['SelectedRates']))
			{
				// Firstly, we need to check that all the rates are valid.
				// Validity is determined by Existing, Not being Archived and having the right Service Type
				
				// Do a check on the Rate to see if it exists
				$selCheckRates = new StatementSelect (
					"Rate", 
					"count(*) AS Length", 
					"Id = <Id> AND Archived = 0 AND ServiceType = <ServiceType>"
				);
				
				// Check that the rate exists
				foreach ($_POST ['SelectedRates'] as $intRate)
				{		
					$selCheckRates->Execute (
						Array (
							"Id"			=> $intRate,
							"ServiceType"	=> $_POST ['ServiceType']
						)
					);
					
					$arrLength = $selCheckRates->Fetch ();
					
					if ($arrLength ['Length'] == 0)
					{
						$oblstrError->setValue ('RateNotFound');
						break;
					}
				}
				
				if ($oblstrError->getValue () == "")
				{
					// Add the information to the Database
					$rglRateGroups->Add (
						Array (
							"Name"			=> $_POST ['Name'],
							"Description"	=> $_POST ['Description'],
							"ServiceType"	=> $_POST ['ServiceType'],
							"RecordType"	=> $_POST ['RecordType'],
							"Archived"		=> 0
						),
						$_POST ['SelectedRates']
					);
					
					header ("Location: rates_group_added.php");
					exit;
				}
			}
			
			$selRates = new StatementSelect ('Rate', 'Id, Name', 'ServiceType = <ServiceType> AND RecordType = <RecordType> AND Archived = 0', 'Name');
			$selRates->Execute (Array ('ServiceType' => $_POST ['ServiceType'], 'RecordType' => $_POST ['RecordType']));
			
			$oblarrRates = $oblarrRateGroup->Push (new dataArray ('Rates'));
			
			while ($arrRate = $selRates->Fetch ())
			{
				$oblarrRate = $oblarrRates->Push (new dataArray ('Rate'));
				$oblarrRate->Push (new dataInteger	('Id',		$arrRate ['Id']));
				$oblarrRate->Push (new dataString	('Name',	$arrRate ['Name']));
			}
			
			$Style->Output ("xsl/content/rates/groups/select.xsl");
			
			exit;
		}
	}
	
	$Style->Output ("xsl/content/rates/groups/add.xsl");
	
?>

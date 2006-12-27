<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ("config/application_loader.php");
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ("Location: login.php"); exit;
	}
	
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
		// The Name of the Rate Group cannot be empty
		if (empty ($_POST ['Name']))
		{
			$oblstrError->setValue ('Blank');
		}
		// Check that an Item of the same Name that is Not Archived Exists
		else if ($rglRateGroups->UnarchivedNameExists ($_POST ['Name']))
		{
			$oblstrError->setValue ('Exists');
		}
		// Ensure that the Service Type is Valid
		else if (!$svtServiceType->setValue ($_POST ['ServiceType']))
		{
			$oblstrError->setValue ('ServiceType');
		}
		// If we're up to here - then we're good for stage 2
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
			
			$rrlRates = new Rates ();
			$rrlRates->Constrain ('ServiceType', 'EQUALS', $_POST ['ServiceType']);
			$rrlRates->Constrain ('RecordType', 'EQUALS', $_POST ['RecordType']);
			$rrlRates->Constrain ('Archived', 'EQUALS', 0);
			$rrlRates->Order ('Name', TRUE);
			$rrlRates->Sample ();
			$oblarrRateGroup->Push ($rrlRates);
			
			$Style->Output ("xsl/content/rates/groups/select.xsl");
			
			exit;
		}
	}
	
	$Style->Output ("xsl/content/rates/groups/add.xsl");
	
?>

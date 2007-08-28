<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// rate_group
//----------------------------------------------------------------------------//
/**
 * rate_group
 *
 * contains all ApplicationTemplate extended classes relating to Rate Group functionality
 *
 * contains all ApplicationTemplate extended classes relating to Rate Group functionality
 *
 * @file		rate_group.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateRateGroup
//----------------------------------------------------------------------------//
/**
 * AppTemplateRateGroup
 *
 * The AppTemplateGroup class
 *
 * The AppTemplateGroup class.  This incorporates all logic for all pages
 * relating to Rate Groups
 *
 *
 * @package	ui_app
 * @class	AppTemplateRateGroup
 * @extends	ApplicationTemplate
 */
class AppTemplateRateGroup extends ApplicationTemplate
{
	
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Rate Group webpage
	 * 
	 * Performs the logic for the Add Rate Group webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Handle form submittion
		if (SubmittedForm('RateGroup', 'Commit') || SubmittedForm('RateGroup', 'Save as Draft'))
		{
			$mixResult = $this->_ValidateRateGroup();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				// The RateGroup did not pass validation and an error message has been returned
				Ajax()->AddCommand("Alert", $mixResult);
				return TRUE;
			}
			elseif ($mixResult === FALSE)
			{
				// The RateGroup did not pass validation.  No error message was specified, so it is assumed appropriate action has already taken place
				return TRUE;
			}
			else
			{
				// The RateGroup passed validation. Save it.
				TransactionStart();
				
				$mixResult = $this->_SaveRateGroup();
				if ($mixResult !== TRUE && $mixResult !== FALSE)
				{
					// Saving the RateGroup failed, and an error message has been returned
					TransactionRollback();
					Ajax()->AddCommand("Alert", $mixResult);
					return TRUE;
				}
				elseif ($mixResult === FALSE)
				{
					// Saving the RateGroup failed, and no error message was specified, so it is assumed approraite actions have already taken place
					TransactionRollback();
					return TRUE;
				}
				else
				{
					// Saving the RateGroup was successfull
					TransactionCommit();
					
					Ajax()->AddCommand("ClosePopup", "{$this->_objAjax->strId}");
					if (SubmittedForm('RateGroup', 'Commit'))
					{
						Ajax()->AddCommand("Alert", "The Rate Group was successfully committed to the database");
					}
					else
					{
						Ajax()->AddCommand("Alert", "The Rate Group was successfully saved as a draft");
					}

					// Check if this popup was called from the "Add Rate Plan" page
					if (DBO()->CallingPage->AddRatePlan->Value)
					{
						// This popup was called from the "Add Rate Plan" page.  We have to update the appropriate combobox within the "Add Rate Plan" page
						$this->_UpdateAddRatePlanPage();
						return TRUE;
					}
					else
					{
						// Close the popup normally
						return TRUE;
					}
				}
			}
		}
		
		// Check if we are to display an existing RateGroup or if we are adding a new one
		if (DBO()->RateGroup->Id->Value)
		{
			// We want to display an existing RateGroup
			if (!DBO()->RateGroup->Load())
			{
				// Could not load the RateGroup
				Ajax()->AddCommand("Alert", "ERROR: The RateGroup could not be found");
				return TRUE;
			}
		}
		else
		{
			// We want to add a new RateGroup
			DBO()->RateGroup->Id = 0;
		}
		
		$this->LoadPage('rate_group_add');

		return TRUE;
	}
	
	// Draws the Rate Selector Control used in the "Add Rate Group" form
	// It is a precondition that DBO()->RecordType->Id->Value has been set
	// DBO()->RateGroup->Id->Value can also be set
	function SetRateSelectorControl()
	{
		$selRates = new StatementSelect("Rate", "Id, Name, Description, Archived", "RecordType=<RecordType> AND Archived != 1", "Description", NULL);
		$selRates->Execute(Array("RecordType" => DBO()->RecordType->Id->Value));
		$arrRecords = $selRates->FetchAll();

		// If a RateGroup.Id has been specified then we want to mark which of these rates belong to it
		if (DBO()->RateGroup->Id->Value)
		{
			$selRateGroupRates = new StatementSelect("RateGroupRate", "Id, RateGroup, Rate", "RateGroup=<RateGroup>", NULL, NULL);
			$selRateGroupRates->Execute(Array("RateGroup" => DBO()->RateGroup->Id->Value));
			$arrRateGroupRates = $selRateGroupRates->FetchAll();
		}

		$arrRates = Array();
		$arrRate = Array();
		foreach ($arrRecords as $arrRecord)
		{
			$arrRate['Id'] = $arrRecord['Id'];
			$arrRate['Name'] = $arrRecord['Name'];
			$arrRate['Description'] = $arrRecord['Description'];

			// If the name contains "BH", "Morning", "Evening" or "Weekend", then we want to add this to the begining of the description
			if (stripos($arrRate['Name'], "BH") !== FALSE)
			{
				// The rate is a "Business Hours" rate
				$arrRate['Description'] = "BH - " . $arrRate['Description'];
			}
			elseif (stripos($arrRate['Name'], "Evening") !== FALSE)
			{
				// The rate is an "Evening" rate
				$arrRate['Description'] = "Evening - " . $arrRate['Description'];
			}
			elseif (stripos($arrRate['Name'], "Morning") !== FALSE)
			{
				// The rate is a "Morning" rate
				$arrRate['Description'] = "Morning - " . $arrRate['Description'];
			}
			elseif (stripos($arrRate['Name'], "Weekend") !== FALSE)
			{
				// The rate is a "Weekend" rate
				$arrRate['Description'] = "Weekend - " . $arrRate['Description'];
			}
			
			if ($arrRate['Archived'] == 2)
			{
				// Flag the rate as being a Draft
				$arrRate['Description'] = "[DRAFT] - " . $arrRate['Description'];
				$arrRate['Draft'] = TRUE;
			}
			else
			{
				$arrRate['Draft'] = FALSE;
			}
			
			// Check if this Rate currently belongs to the specified RateGroup
			$arrRate['Selected'] = FALSE;
			if (DBO()->RateGroup->Id->Value)
			{
				foreach ($arrRateGroupRates as $arrRateGroupRate)
				{
					if ($arrRateGroupRate['Rate'] == $arrRate['Id'])
					{
						// This Rate belongs to the RateGroup
						$arrRate['Selected'] = TRUE;
						break;
					}
				}
			}
			
			$arrRates[] = $arrRate;
		}
		
		DBO()->Rates->ArrRates = $arrRates;
		
		
		Ajax()->RenderHtmlTemplate("RateGroupAdd", HTML_CONTEXT_RATES, "RateSelectorControlDiv");
		return TRUE;
	}
	
	// Validates the Rate Group
	private function _ValidateRateGroup()
	{
		/* 
		 * Validation process:
		 *		Check that a Name and Description have been declared	(implemented)
		 *		Check that a service type has been declared				(implemented)
		 *		Check that a record type has been declared				(implemented)
		 *		For every distination associated with the context of the RecordType of the RateGroup:
		 *			Check that every minute of every day of the week is accounted for by a Rate and there are no overlaps
		 */
	
		// Validate the fields
		if (DBO()->RateGroup->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		if (!DBO()->RateGroup->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: A Service Type must be selected";
		}
		if (!DBO()->RateGroup->RecordType->Value)
		{
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: A Record Type must be selected";
		}
				
		// Make sure there are rates specified (This should be handled by the next validation step (checking that a rate covers all hours of all days))
		if (!DBO()->SelectedRates->ArrId->Value)
		{
			// No rates have been specified
			return "ERROR: No rates have been added to the rate group";
		}
		
		// Check that the selected Rates Cover all hours of the week and don't overlap unless they are destination based
		//TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! 
		$strWhere = "Id IN (";
		foreach (DBO()->SelectedRates->ArrId->Value as $intId)
		{
			$strWhere .= "$intId, ";
		}
		$strWhere = substr($strWhere, 0, -2);
		$strWhere .= ")";
		DBL()->Rate->Where->SetString($strWhere);
		DBL()->Rate->Load();
		
		
		
		// All Validation is complete, the RateGroup is valid
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _SaveRateGroup
	//------------------------------------------------------------------------//
	/**
	 * _SaveRateGroup()
	 *
	 * Performs saving the records to the database, required for defining a RateGroup
	 * 
	 * Performs saving the records to the database, required for defining a RateGroup
	 * This will only work with the "Add Rate Group" popup webpage as it assumes specific DBObjects have been defined within DBO()
	 *
	 * @return		mix				returns TRUE if the new RateGroup saved successfully, else it returns
	 *								a specific error message detailing why the RateGroup could not be saved
	 * @method
	 *
	 */
	private function _SaveRateGroup()
	{
		/* 
		 * Saving process:
		 *		Set up values for properties of the RateGroup object that are not already defined										(DONE)
		 *		Save the record to the RateGroup table 																					(DONE)
		 *		Remove any records in the RateGroupRate table relating to this rate group 												(DONE)
		 *		For each rate belonging to this Rate Group:
		 *			add a record to the RateGroupRate table																				(DONE)
		 *		For each draft rate belonging to this Rate Group:
		 *			update the Archived property of the Rate in the Rate table so that it is now a committed Rate, not a draft rate		(DONE)
		 */
	
	
		// Retrieve the list of rates to add to the rate group (we are now using DBL()->Rate)
		//$arrRates = DBO()->SelectedRates->ArrId->Value;
		
		// Define values for all fields that have not already been specified
		if (SubmittedForm('RateGroup', 'Save as Draft'))
		{
			// Flag it as a draft
			DBO()->RateGroup->Archived = 2;
		}
		else
		{
			DBO()->RateGroup->Archived = 0;
		}
		
		// Declare which fields you want to set
		DBO()->RateGroup->SetColumns("Name, Description, RecordType, ServiceType, Fleet, Archived");
		
		// Add the RateGroup Record
		if (!DBO()->RateGroup->Save())
		{
			return "ERROR: Saving the RateGroup record to the database failed, unexpectedly.<br />The Rate Group has not been saved";
		}
		
		// Remove all records from the RateGroupRate table where RateGroup == DBO()->RateGroup->Id->Value
		$delRateGroupRate = new Query();
		$delRateGroupRate->Execute("DELETE FROM RateGroupRate WHERE RateGroup = " . DBO()->RateGroup->Id->Value);
				
		// Add a record to the RateGroupRate table for each rate associated with this rategroup
		// StatementInsert is being used rather than a DBObject, as it is quicker, and this could require about 1000 records being added
		$insRateGroupRate = new StatementInsert("RateGroupRate");
		$arrInsertValues = Array("RateGroup" => DBO()->RateGroup->Id->Value);
		foreach (DBL()->Rate as $dboRate)
		{
			$arrInsertValues['Rate'] = $dboRate->Id->Value;
			if (!$insRateGroupRate->Execute($arrInsertValues))
			{
				// Inserting one of the records failed
				return "ERROR: Saving a record to the RateGroupRate table of the database failed, unexpectedly.<br />The Rate Group has not been saved";
			}
		}
		
		// If the RateGroup is being committed to the database, as opposed to being saved, make sure all its associated rates are also committed
		if (DBO()->RateGroup->Archived == 0)
		{
			// The Rate Group is being saved
			$arrUpdate = Array("Archived" => 0);
			$updRates = new StatementUpdate("Rate", "Id = <Id>", $arrUpdate);
			foreach (DBL()->Rate as $dboRate)
			{
				if ($dboRate->Archived->Value != 2)
				{
					// The rate is not a draft, so we don't have to update it
					continue;
				}
				
				if ($updRates->Execute($arrUpdate, Array("Id" => $dboRate->Id->Value) === FALSE))
				{
					// Updating the Rate table failed
					return "ERROR: A problem occurred committing draft rates used by this Rate Group. <br />The Rate Group has not been saved";
				}
			}
		}
		
		
		// The Rate Group has been saved successfully
		return TRUE;
	}
	
	// Updates the "Add Rate Plan" page
	function _UpdateAddRatePlanPage()
	{
		$intRateGroupId = DBO()->RateGroup->Id->Value;
		// if the Description contains any double quotes, it will screw up, so convert them to single quotes.  Although I don't know why it would contain any of these in the first place
		$strDescription = str_replace("\"", "'", DBO()->RateGroup->Description->Value);
		$intRecordType = DBO()->RateGroup->RecordType->Value;
		$bolFleet = DBO()->RateGroup->Fleet->Value ? 1 : 0;
		$bolDraft = (DBO()->RateGroup->Archived->Value == 2) ? 1 : 0;
		
		$strJavascript = "Vixen.RatePlanAdd.ChooseRateGroup($intRateGroupId, \"$strDescription\", $intRecordType, $bolFleet, $bolDraft);";
		Ajax()->AddCommand("ExecuteJavascript", $strJavascript);
	}
	
}

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
		if (SubmittedForm('RateGroup', 'Commit'))
		{
			TransactionStart();
			
			$mixResult = $this->_AddRateGroup();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				// Adding the RateGroup failed, and an error message has been returned
				TransactionRollback();
				Ajax()->AddCommand("Alert", $mixResult);
				return TRUE;
			}
			elseif ($mixResult === FALSE)
			{
				// Adding the RateGroup failed, and no error message was specified, so it is assumed approraite actions have already taken place
				TransactionRollback();
				return TRUE;
			}
			else
			{
				// Adding the RateGroup was successfull
				TransactionCommit();
				Ajax()->AddCommand("Alert", "The RateGroup has been successfully added");
				Ajax()->AddCommand("ClosePopup", "RateGroupPopup");
				return TRUE;
			}
		}
		
		$this->LoadPage('rate_group_add');

		return TRUE;
	}
	
	// Draws the Rate Selector Control used in the "Add Rate Group" form
	// It is a precondition that DBO()->RecordType->Id->Value has been set
	function SetRateSelectorControl()
	{
		// Retrieve all available Rates for the specified RecordType
		//NOTE: This was originally done using a DBList, however some retrievals were returning 11000+ records and was exeeding
		//the allocated memory for the script (crashed after allocating about 103MB) the DBList itself required 80MB when
		//returning all properties of all records, and 40MB when just returning the Id and Name of each rate.
		//Now the entire record set is being loaded into a 2D array and placed in a DBObject so that it can be referenced with the Html Template
		/*
		$strWhere = "RecordType = <RecordType> AND Archived != 1";
		DBL()->Rates->SetTable("Rate");
		DBL()->Rates->Where->Set($strWhere, Array("RecordType" => DBO()->RecordType->Id->Value));
		DBL()->Rates->Load();
		*/
		
		$selRates = new StatementSelect("Rate", "Id, Name", "RecordType=<RecordType>", "Name", NULL);
		$selRates->Execute(Array("RecordType" => DBO()->RecordType->Id->Value));
		$arrRecords = $selRates->FetchAll();
		
		$arrRates = Array();
		$arrRate = Array();
		foreach ($arrRecords as $arrRecord)
		{
			$arrRate['Id'] = $arrRecord['Id'];
			$arrRate['Name'] = $arrRecord['Name'];
			
			$arrRates[] = $arrRate;
		}
		
		DBO()->Rates->ArrRates = $arrRates;
		
		
		Ajax()->RenderHtmlTemplate("RateGroupAdd", HTML_CONTEXT_RATES, "RateSelectorControlDiv");
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _AddRateGroup
	//------------------------------------------------------------------------//
	/**
	 * _AddRateGroup()
	 *
	 * Performs validation and saving the records to the database, required for defining a RateGroup
	 * 
	 * Performs validation and saving the records to the database, required for defining a RateGroup
	 * This will only work with the "Add Rate Group" popup webpage as it assumes specific DBObjects have been defined within DBO()
	 *
	 * @return		mix				returns TRUE if the new RateGroup saved successfully, else it returns
	 *								a specific error message detailing why the RateGroup could not be saved
	 * @method
	 *
	 */
	private function _AddRateGroup()
	{
		// Validate the fields
		if (DBO()->RatePlan->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A service type must be selected";
		}
		
		// Make sure the name of the rate plan isn't currently in use
		DBO()->ExistingRatePlan->Where->Name = DBO()->RatePlan->Name->Value;
		DBO()->ExistingRatePlan->SetTable("RatePlan");
		if (DBO()->ExistingRatePlan->Load())
		{
			// A rate plan with the same name already exists
			DBO()->RatePlan->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Rate Plan named '". DBO()->RatePlan->Name->Value ."' already exists.<br>Please choose a unique name";
		}
		
		// Check that a rate group has been defined for each RecordType that has been marked as required
		DBL()->RecordType->ServiceType = DBO()->RatePlan->ServiceType->Value;
		DBL()->RecordType->Load();
		
		$arrRateGroups = Array();

		// Find the declared rate group for each RecordType required of the RatePlan
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$intRateGroup = NULL;
			$intFleetRateGroup = NULL;
			
			// Build the name of the object storing the Rate Group details for this particular record type
			$strObject = "RateGroup" . $dboRecordType->Id->Value;
			if (DBO()->{$strObject}->RateGroupId->IsSet)
			{
				// A RateGroup has been specified for this ServiceType
				$intRateGroup = DBO()->{$strObject}->RateGroupId->Value;
				$intFleetRateGroup = DBO()->{$strObject}->FleetRateGroupId->Value;
				
				// Check if a rate group has not been chosen for this record type, but is required
				if (($intRateGroup == 0) && ($dboRecordType->Required->Value == TRUE))
				{
					// A rate group is required but hasn't been specified
					return "ERROR: Not all required rate groups have been specified";
				}
				elseif ($intRateGroup > 0)
				{
					// add the rategroup to the list of rate groups
					$arrRateGroups[] = $intRateGroup;
				}
				
				if ($intFleetRateGroup > 0)
				{
					// Add the fleet rate group to the list of rate groups
					$arrRateGroups[] = $intFleetRateGroup;
				}
			}
			elseif ($dboRecordType->Required->Value == TRUE)
			{
				// The RatePlan requires a RateGroup of this RecordType, but one has not been declared
				//NOTE! This is only run if the RecordType was not associated with the ServiceType before loading the RateGroupDiv contents
				$this->GetPlanDeclareRateGroupsHtmlTemplate();
				return "ERROR: strObject = '$strObject' intRecCount=$intRecCount objectsChecked=$strObjectsChecked A new record type has been associated with this service type, since you chose the service type of the plan";
			}
			else
			{
				// A RateGroup associated with the RecordType, was not specified and not required
				continue;
			}
		}
		
		// All validation has completed and the fields are valid
		// Setup the remaing fields required of a RatePlan record
		DBO()->RatePlan->MinMonthly	= ltrim(DBO()->RatePlan->MinMonthly->Value, "$");
		DBO()->RatePlan->ChargeCap	= ltrim(DBO()->RatePlan->ChargeCap->Value, "$");
		DBO()->RatePlan->UsageCap	= ltrim(DBO()->RatePlan->UsageCap->Value, "$");
		DBO()->RatePlan->Archived	= 0;
		
		// Save the plan to the database
		if (!DBO()->RatePlan->Save())
		{
			// Saving failed
			return "ERROR: Saving the RatePlan to the RatePlan database table failed, unexpectedly";
		}
		
		// Save each of the RateGroups associated with the RatePlan to the RatePlanRateGroup table
		foreach ($arrRateGroups as $intRateGroup)
		{
			DBO()->RatePlanRateGroup->Id = 0;
			DBO()->RatePlanRateGroup->RatePlan = DBO()->RatePlan->Id->Value;
			DBO()->RatePlanRateGroup->RateGroup = $intRateGroup;
			
			if (!DBO()->RatePlanRateGroup->Save())
			{
				// Saving failed
				return "ERROR: Saving one of the RateGroup - RatePlan associations failed, unexpectedly<br>The RatePlan has not been saved";
			}
		}
		
		// Everything has been saved
		return TRUE;
	}
	
}

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
		//NOTE: This was originally done using a DBList, however some retrievals were returning 11000+ records and was exceeding
		//the allocated memory for the script (crashed after allocating about 103MB) the DBList itself required 80MB when
		//returning all properties of all records, and 40MB when just returning the Id and Name of each rate.
		//Now the entire record set is being loaded into a 2D array and placed in a DBObject so that it can be referenced within the Html Template
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
				
		// Make sure the name of the rate group isn't currently in use
		DBO()->ExistingRateGroup->Where->Name = DBO()->RateGroup->Name->Value;
		DBO()->ExistingRateGroup->SetTable("RateGroup");
		if (DBO()->ExistingRateGroup->Load())
		{
			// A rate group with the same name already exists
			DBO()->RateGroup->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('RateGroupAdd', HTML_CONTEXT_DETAILS, "RateGroupDetailsId");
			return "ERROR: A Rate Group named '". DBO()->RateGroup->Name->Value ."' already exists.<br>Please choose a unique name";
		}
		
		// Make sure there are rates specified (This should be handled by the next validation step (checking that a rate covers all hours of all days))
		if (!DBO()->SelectedRates->ArrId->Value)
		{
			// No rates have been specified
			return "ERROR: No rates have been added to the rate group";
		}
		
		// Check that the selected Rates Cover all hours of the week and don't overlap unless they are destination based
		//TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! TODO! 
		
		
		// All Validation is complete
		
		// Retrieve the list of rates to add to the rate group
		$arrRates = DBO()->SelectedRates->ArrId->Value;
		
		// Add the RateGroup record
		DBO()->RateGroup->Archived = 0;
		
		if (!DBO()->RateGroup->Save())
		{
			return "ERROR: Saving the RateGroup record to the database failed, unexpectedly.<br />The Rate Group has not been saved";
		}
		
		// Add a record to the RateGroupRate table for each rate associated with this rategroup
		// StatementInsert is being used rather than a DBObject, as it is quicker, and this could require about 1000 records being added
		$insRateGroupRate = new StatementInsert("RateGroupRate");
		$arrInsertValues = Array("RateGroup" => DBO()->RateGroup->Id->Value);
		foreach ($arrRates as $intRate)
		{
			$arrInsertValues['Rate'] = $intRate;
			if (!$insRateGroupRate->Execute($arrInsertValues))
			{
				// Inserting one of the records failed
				return "ERROR: Saving a record to the RateGroupRate table of the database failed, unexpectedly.<br />The Rate Group has not been saved";
			}
		}
		
		// The Rate Group has been saved successfully
		return TRUE;
	}
	
}

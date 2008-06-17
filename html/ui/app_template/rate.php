<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// rate
//----------------------------------------------------------------------------//
/**
 * rate
 *
 * contains all ApplicationTemplate extended classes relating to rate functionality
 *
 * contains all ApplicationTemplate extended classes relating to rate functionality
 *
 * @file		rate.php
 * @language	PHP
 * @package		framework
 * @author		Ross Mullen
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplaterate
//----------------------------------------------------------------------------//
/**
 * AppTemplaterate
 *
 * The AppTemplaterate class
 *
 * The AppTemplaterate class.  This incorporates all logic for all pages
 * relating to rates
 *
 *
 * @package	ui_app
 * @class	AppTemplateRate
 * @extends	ApplicationTemplate
 */
class AppTemplateRate extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs all the logic for adding a new rate
	 *
	 * Performs all the logic for adding a new rate, determines which of the 3
	 * buttons has been pressed and process validation dependant upon this
	 *		The user needs PERMISSION_RATE_MANAGEMENT and PERMISSION_ADMIN permissions to view this page
	 *
	 * @param	void
	 * @return	void
	 *
	 * @method
	 */
	function Add()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);

		// Handle form submittion
		if (SubmittedForm("AddRate","Commit") || SubmittedForm("AddRate","Save as Draft"))
		{
			TransactionStart();
			$mixResult = $this->_ValidateAndSaveRate();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				TransactionRollback();
				Ajax()->AddCommand("Alert", $mixResult);
				return TRUE;
			}
			elseif ($mixResult == FALSE)
			{
				TransactionRollback();
				return TRUE;
			}
			else
			{	
				// Commit the database transaction
				TransactionCommit();
				
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				if (SubmittedForm('AddRate', 'Commit'))
				{
					Ajax()->AddCommand("Alert", "The Rate was successfully committed to the database");
				}
				else
				{
					Ajax()->AddCommand("Alert", "The Rate was successfully saved as a draft");
				}
				
				
				// Check if this rate is being added to a rate group
				if (DBO()->CallingPage->AddRateGroup->Value)
				{
					// This popup was called from the "Add Rate Group" page.  We have to update the appropriate combobox within the "Add Rate Group" page
					$this->_UpdateAddRateGroupPage();
					return TRUE;
				}
				else
				{
					// Close the popup normally
					return TRUE;
				}
			}
		}
		 
		// check if the Id of a rate has been supplied and if so load the rate
		if (DBO()->Rate->Id->Value)
		{
			// We want to display an existing Rate
			if (!DBO()->Rate->Load())
			{
				// Could not load the Rate
				Ajax()->AddCommand("Alert", "ERROR: The Rate could not be found");
				return TRUE;
			}
			
			if (DBO()->Action->CreateNewBasedOnOld->Value == TRUE)
			{
				// The user wants to create a new rate based on an old rate
				// The old rate has been loaded, all we have to do is reset its Id to zero
				DBO()->Rate->Id = 0;
			}
			
			// Work out if the Rate is untimed
			if 	((DBO()->Rate->StdRatePerUnit->Value == 0) &&
				(DBO()->Rate->StdMarkup->Value == 0) &&
				(DBO()->Rate->StdPercentage->Value == 0) &&
				(DBO()->Rate->ExsRatePerUnit->Value == 0) &&
				(DBO()->Rate->ExsMarkup->Value == 0) &&
				(DBO()->Rate->ExsPercentage->Value == 0))
			{
				// The Rate is untimed
				DBO()->Rate->Untimed = TRUE;
			}
		}
		else
		{
			DBO()->Rate->Id = 0;
			// Check if a RecordType was passed through to this method
			if (DBO()->RecordType->Id->Value)
			{
				DBO()->Rate->RecordType = DBO()->RecordType->Id->Value;
			}
			
			// Set default values for the time properties
			DBO()->Rate->StartTime		= "00:00:00";
			DBO()->Rate->EndTime		= "23:59:59";
			DBO()->Rate->Monday			= TRUE;
			DBO()->Rate->Tuesday		= TRUE;
			DBO()->Rate->Wednesday		= TRUE;
			DBO()->Rate->Thursday		= TRUE;
			DBO()->Rate->Friday			= TRUE;
			DBO()->Rate->Saturday		= TRUE;
			DBO()->Rate->Sunday			= TRUE;
			
			DBO()->Rate->StdUnits		= 1;
			DBO()->Rate->ExsUnits		= 1;
			
			DBO()->Rate->StdMinCharge	= 0;
			DBO()->Rate->StdFlagfall	= 0;
			DBO()->Rate->ExsFlagfall	= 0;
			DBO()->Rate->CapUnits		= 0;
			DBO()->Rate->CapUsage		= 0;
			
		}

		$this->LoadPage('rate_add');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// _UpdateAddRateGroupPage
	//------------------------------------------------------------------------//
	/**
	 * _UpdateAddRateGroupPage()
	 *
	 * Executes javascript associated with the "Add Rate Group" page, in order to update it, after a Rate has been saved
	 * 
	 * Executes javascript associated with the "Add Rate Group" page, in order to update it, after a Rate has been saved
	 * It is assumed DBO()->Rate contains a valid Rate
	 *
	 * @return		void
	 * @method
	 *
	 */
	private function _UpdateAddRateGroupPage()
	{
		$arrRate['Id']			= DBO()->Rate->Id->Value;
		$arrRate['Description']	= DBO()->Rate->Description->Value;
		$arrRate['Name']		= DBO()->Rate->Name->Value;
		$arrRate['RecordType'] 	= DBO()->Rate->RecordType->Value;
		$arrRate['Draft']		= (DBO()->Rate->Archived->Value == RATE_STATUS_DRAFT) ? 1 : 0;
		$arrRate['Fleet']		= (DBO()->Rate->Fleet->Value == TRUE) ? 1 : 0;
		
		$objRate = Json()->encode($arrRate);
		
		$strJavascript = "Vixen.RateGroupAdd.AddRatePopupOnClose($objRate);";
		Ajax()->AddCommand("ExecuteJavascript", $strJavascript);
	}

	//------------------------------------------------------------------------//
	// _ValidateAndSaveRate
	//------------------------------------------------------------------------//
	/**
	 * _ValidateAndSaveRate()
	 *
	 * Validates the form, and flags any invalid fields
	 *
	 * Validates the form, and flags any invalid fields, also applies masks
	 * before attempting to save to the database
	 *
	 * @param	void
	 * @return	string	a string error message
	 *
	 * @method
	 */
	private function _ValidateAndSaveRate()
	{
		// Strip dollar signs off the fields that can contain them
		DBO()->Rate->StdRatePerUnit			= ltrim(DBO()->Rate->StdRatePerUnit->Value, '$');
		DBO()->Rate->StdMarkup				= ltrim(DBO()->Rate->StdMarkup->Value, '$');
		DBO()->Rate->CapCost				= ltrim(DBO()->Rate->CapCost->Value, '$');
		DBO()->Rate->CapLimit				= ltrim(DBO()->Rate->CapLimit->Value, '$');
		DBO()->Rate->ExsFlagfall			= ltrim(DBO()->Rate->ExsFlagfall->Value, '$');
		DBO()->Rate->ExsRatePerUnit			= ltrim(DBO()->Rate->ExsRatePerUnit->Value, '$');
		DBO()->Rate->ExsMarkup				= ltrim(DBO()->Rate->ExsMarkup->Value, '$');
		DBO()->Rate->StdFlagfall			= ltrim(DBO()->Rate->StdFlagfall->Value, '$');
		DBO()->Rate->StdMinCharge			= ltrim(DBO()->Rate->StdMinCharge->Value, '$');
		DBO()->Rate->discount_percentage	= ltrim(DBO()->Rate->discount_percentage->Value, '$');
	
		// Test initial validation of fields
		if (DBO()->Rate->IsInvalid())
		{
			// The form has not passed initial validation
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
			return "ERROR: Could not save the rate.  Invalid fields are highlighted";
		}
		
		// Nullify fields that can be null
		if ((float)DBO()->Rate->discount_percentage->Value == 0)
		{
			DBO()->Rate->discount_percentage = NULL;			
		}
		
		// Check that a Rate of the same RecordType, is not already using the name of this rate
		if (DBO()->Rate->Id->Value == 0)
		{
			// The Rate name should not be in the database
			$strWhere = "Name LIKE <Name> AND RecordType = <RecordType>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other Rate
			$strWhere = "Name LIKE <Name> And RecordType = <RecordType> AND Id != ". DBO()->Rate->Id->Value;
		}
		$selRateName = new StatementSelect("Rate", "Id", $strWhere);
		if ($selRateName->Execute(Array("Name" => DBO()->Rate->Name->Value, "RecordType" => DBO()->Rate->RecordType->Value)) > 0)
		{
			DBO()->Rate->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
			return "ERROR: This name is already used by another Rate of this Record Type<br />Please choose a unique name";
		}
	
		$intDaySelected = FALSE;
		$arrDaysOfWeek = array('Monday'	=> 'Rate.Monday',
							'Tuesday'	=> 'Rate.Tuesday',
							'Wednesday'	=> 'Rate.Wednesday',
							'Thursday'	=> 'Rate.Thursday',
							'Friday'	=> 'Rate.Friday',
							'Saturday'	=> 'Rate.Saturday',
							'Sunday'	=> 'Rate.Sunday');
		
		// validate that at least one day in the week has been checked
		foreach ($arrDaysOfWeek as $strKey => $strValue)
		{
			if (DBO()->Rate->$strKey->Value)
			{
				$intDaySelected = TRUE;
			}
		}
		if (!$intDaySelected)
		{
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
			return "ERROR: Please specify which days the Rate applies to";			
		}
		
		$bolFormIsInvalid = FALSE;
		// If the passthrough checkbox is checked disable the form elements below it, 
		// but dont perform any validation
		
		// Perform validation for the rate charging and rate capping fields
		// This is not required if the rate is a PassThrough rate, or if the rate is untimed
		if (!DBO()->Rate->PassThrough->Value  && !DBO()->Rate->Untimed->Value)
		{
			// Check that the Standard Units has been specified
			if (!(Validate('Integer', DBO()->Rate->StdUnits->Value) && DBO()->Rate->StdUnits->Value > 0))
			{
				DBO()->Rate->StdUnits->SetToInvalid();
				$bolFormIsInvalid = TRUE;
			}
			
			switch (DBO()->Rate->ChargeType->Value)
			{
				case RATE_CAP_STANDARD_RATE_PER_UNIT:
					// validate rate charge
					if (!Validate('IsMoneyValue', DBO()->Rate->StdRatePerUnit->Value))
					{
						DBO()->Rate->StdRatePerUnit->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					break;
				case RATE_CAP_STANDARD_MARKUP:
					// validate standard markup
					if (!Validate('IsMoneyValue', DBO()->Rate->StdMarkup->Value))
					{
						DBO()->Rate->StdMarkup->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					// If "Markup on cost ($)" is selected, it cannot be 0, as it will not be recognised as a "markup on cost" during the Rating process
					elseif (!(DBO()->Rate->StdMarkup->Value > 0))
					{
						DBO()->Rate->StdMarkup->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					break;
				case RATE_CAP_STANDARD_PERCENTAGE:
					// validate percentage markup
					if (!is_numeric(DBO()->Rate->StdPercentage->Value))
					{
						DBO()->Rate->StdPercentage->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					// If "Markup on cost (%)" is selected, it cannot be 0, as it will not be recognised as a "markup on cost" during the Rating process
					elseif (!(DBO()->Rate->StdPercentage->Value > 0))
					{
						DBO()->Rate->StdPercentage->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					break;
			}
	
			switch (DBO()->Rate->CapCalculation->Value)
			{
				case RATE_CAP_NO_CAP:
					// The Rate is not capped.  Set the appropriate fields to 0
					DBO()->Rate->CapUnits		= 0;
					DBO()->Rate->CapCost		= 0;
					DBO()->Rate->CapLimit		= 0;
					DBO()->Rate->CapUsage		= 0;
					DBO()->Rate->ExsFlagfall	= 0;
					DBO()->Rate->ExsUnits		= 1;
					DBO()->Rate->ExsRatePerUnit	= 0;
					DBO()->Rate->ExsMarkup		= 0;
					DBO()->Rate->ExsPercentage	= 0;
					break;
				case RATE_CAP_CAP_UNITS:
					// validate cap units.  CapUnits must be an integer >= 0
					if (!(Validate('Integer', DBO()->Rate->CapUnits->Value) && DBO()->Rate->CapUnits->Value >= 0))
					{
						DBO()->Rate->CapUnits->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					
					// Set appropriate fields to 0
					DBO()->Rate->CapCost = 0;
					break;
				case RATE_CAP_CAP_COST:
					// validate cap cost.  CapCost must be an integer >= 0
					if (!(Validate('IsMoneyValue', DBO()->Rate->CapCost->Value) && DBO()->Rate->CapCost->Value >= 0))
					{
						DBO()->Rate->CapCost->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					
					// Set appropriate fields to 0
					DBO()->Rate->CapUnits = 0;
					break;
			}
		
			if ((DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST) || (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS))
			{		
				// A Cap has been specified.  Validate the Cap Limitting properties
				switch (DBO()->Rate->CapLimitting->Value)
				{
					case RATE_CAP_NO_CAP_LIMITS:
						// The rate has no cap limit.  Set appropriate fields to 0
						DBO()->Rate->CapLimit		= 0;
						DBO()->Rate->CapUsage		= 0;
						DBO()->Rate->ExsFlagfall	= 0;
						DBO()->Rate->ExsUnits		= 1;
						DBO()->Rate->ExsRatePerUnit	= 0;
						DBO()->Rate->ExsMarkup		= 0;
						DBO()->Rate->ExsPercentage	= 0;
						break;
					case RATE_CAP_CAP_LIMIT:
						// validate cap limit
						if (!(Validate('IsMoneyValue', DBO()->Rate->CapLimit->Value) && DBO()->Rate->CapLimit->Value > 0))
						{
							DBO()->Rate->CapLimit->SetToInvalid();
							$bolFormIsInvalid = TRUE;
						}
						if (!Validate('IsMoneyValue', DBO()->Rate->ExsFlagfall->Value))
						{
							DBO()->Rate->ExsFlagfall->SetToInvalid();
							$bolFormIsInvalid = TRUE;
						}
						
						// Set appropriate fields to 0
						DBO()->Rate->ExsUnits		= 1;
						DBO()->Rate->ExsRatePerUnit	= 0;
						DBO()->Rate->ExsMarkup		= 0;
						DBO()->Rate->ExsPercentage	= 0;
						break;
					case RATE_CAP_CAP_USAGE:
						// validate cap usage and excess
						// flag if invalid
						// but dont return allow to continue through the following lines
						if (!(Validate('Integer', DBO()->Rate->CapUsage->Value) && DBO()->Rate->CapUsage->Value > 0))
						{
							DBO()->Rate->CapUsage->SetToInvalid();
							$bolFormIsInvalid = TRUE;
						}
						if (!(Validate('Integer', DBO()->Rate->ExsUnits->Value) && DBO()->Rate->ExsUnits->Value > 0))
						{
							DBO()->Rate->ExsUnits->SetToInvalid();
							$bolFormIsInvalid = TRUE;
						}
						if (!Validate('IsMoneyValue', DBO()->Rate->ExsFlagfall->Value))
						{
							DBO()->Rate->ExsFlagfall->SetToInvalid();
							$bolFormIsInvalid = TRUE;
						}
						switch (DBO()->Rate->ExsChargeType->Value)
						{
							case RATE_CAP_EXS_RATE_PER_UNIT:
								// validate excess rate
								if (!Validate('IsMoneyValue', DBO()->Rate->ExsRatePerUnit->Value))
								{
									DBO()->Rate->ExsRatePerUnit->SetToInvalid();
									$bolFormIsInvalid = TRUE;
								}
								break;
							case RATE_CAP_EXS_MARKUP:
								// validate markup
								// If an Excess markup has been chosen you cannot also specify a standard markup
								// (see section 2.2 of the Rating document)
								if (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_MARKUP || DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_PERCENTAGE)
								{
									DBO()->Rate->ExsMarkup->SetToInvalid();
									
									Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
									return "ERROR: Currently, you cannot specify both a Standard Markup on Cost and an Excess Markup on Cost as the original cost will be added to the charge twice.  This behaviour will be changed in the future";
								}
								// continue validation
								if (!Validate('IsMoneyValue', DBO()->Rate->ExsMarkup->Value))
								{
									DBO()->Rate->ExsMarkup->SetToInvalid();
									$bolFormIsInvalid = TRUE;
								}
								// If "Excess Markup on cost ($)" is selected, it cannot be 0, as it will not be recognised as a "markup on cost" during the Rating process
								elseif (!(DBO()->Rate->ExsMarkup->Value > 0))
								{
									DBO()->Rate->ExsMarkup->SetToInvalid();
									$bolFormIsInvalid = TRUE;
								}
								break;
							case RATE_CAP_EXS_PERCENTAGE:
								// validate percentage
								// If an Excess markup has been chosen you cannot also specify a standard markup
								// (see section 2.2 of the Rating document)
								if (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_MARKUP || DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_PERCENTAGE)
								{
									DBO()->Rate->ExsPercentage->SetToInvalid();
									
									Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
									return "ERROR: Currently, you cannot specify both a Standard Markup on Cost and an Excess Markup on Cost as the original cost will be added to the charge twice.  This behaviour will be changed in the future";
								}
								// continue validation
								if (!is_numeric(DBO()->Rate->ExsPercentage->Value))
								{
									DBO()->Rate->ExsPercentage->SetToInvalid();
									$bolFormIsInvalid = TRUE;
								}
								// If "Excess Markup on cost (%)" is selected, it cannot be 0, as it will not be recognised as a "markup on cost" during the Rating process
								elseif (!(DBO()->Rate->ExsPercentage->Value > 0))
								{
									DBO()->Rate->ExsPercentage->SetToInvalid();
									$bolFormIsInvalid = TRUE;
								}
								break;
						}
						break;
				}
			}
		}

		if ($bolFormIsInvalid)
		{
			// at least one of the required fields in the form is invalid
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
			return "ERROR: Invalid fields are highlighted";
		}


		// Everything has been validated, now prepare everything for saving
		if (DBO()->Rate->PassThrough->Value)
		{
			// set default values for when the Rate is a Pass Through rate
			DBO()->Rate->StdUnits 		= 0; // This defaults to 0 for PassThrough rates
			DBO()->Rate->StdRatePerUnit = 0;
			DBO()->Rate->StdMarkup 		= 0;
			DBO()->Rate->StdPercentage 	= 0;
			DBO()->Rate->CapUnits 		= 0;
			DBO()->Rate->CapCost 		= 0;
			DBO()->Rate->CapLimit 		= 0;
			DBO()->Rate->CapUsage 		= 0;
			DBO()->Rate->ExsRatePerUnit = 0;
			DBO()->Rate->ExsMarkup 		= 0;
			DBO()->Rate->ExsPercentage 	= 0;
			DBO()->Rate->ExsFlagfall 	= 0;
			DBO()->Rate->ExsUnits 		= 1;  // This defaults to 1.  I don't know why, that's just how it is for every rate in the database
			DBO()->Rate->Prorate		= 0;  // You cannot Prorate a PassThrough Rate
		}
		elseif (DBO()->Rate->Untimed->Value)
		{
			// set default values for when the Rate is an untimed rate
			DBO()->Rate->StdUnits 		= 1; // This defaults to 1 for untimed rates
			DBO()->Rate->StdRatePerUnit = 0;
			DBO()->Rate->StdMarkup 		= 0;
			DBO()->Rate->StdPercentage 	= 0;
			DBO()->Rate->CapUnits 		= 0;
			DBO()->Rate->CapCost 		= 0;
			DBO()->Rate->CapLimit 		= 0;
			DBO()->Rate->CapUsage 		= 0;
			DBO()->Rate->ExsRatePerUnit = 0;
			DBO()->Rate->ExsMarkup 		= 0;
			DBO()->Rate->ExsPercentage 	= 0;
			DBO()->Rate->ExsFlagfall 	= 0;
			DBO()->Rate->ExsUnits 		= 1; // This defaults to 1 for untimed rates
		}
		else
		{		
			//strip all $ signs off values
			DBO()->Rate->StdRatePerUnit	= (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_RATE_PER_UNIT) ? DBO()->Rate->StdRatePerUnit->Value : 0;
			DBO()->Rate->StdMarkup		= (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_MARKUP) ? DBO()->Rate->StdMarkup->Value : 0;
			DBO()->Rate->StdPercentage	= (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_PERCENTAGE) ? DBO()->Rate->StdPercentage->Value : 0;
			
			DBO()->Rate->CapUnits		= (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS) ? DBO()->Rate->CapUnits->Value : 0;
			DBO()->Rate->CapCost		= (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST) ? DBO()->Rate->CapCost->Value : 0;
			
			DBO()->Rate->CapLimit		= (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_LIMIT) ? DBO()->Rate->CapLimit->Value : 0;
			DBO()->Rate->CapUsage		= (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_USAGE) ? DBO()->Rate->CapUsage->Value : 0;
			DBO()->Rate->ExsFlagfall	= (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_USAGE || DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_LIMIT) ? DBO()->Rate->ExsFlagfall->Value : 0;
			
			DBO()->Rate->ExsRatePerUnit	= (DBO()->Rate->ExsChargeType->Value == RATE_CAP_EXS_RATE_PER_UNIT) ? DBO()->Rate->ExsRatePerUnit->Value : 0;
			DBO()->Rate->ExsMarkup		= (DBO()->Rate->ExsChargeType->Value == RATE_CAP_EXS_MARKUP) ? DBO()->Rate->ExsMarkup->Value : 0;
			DBO()->Rate->ExsPercentage	= (DBO()->Rate->ExsChargeType->Value == RATE_CAP_EXS_PERCENTAGE) ? DBO()->Rate->ExsPercentage->Value : 0;
		}
		
		// Minimum Charge and Flagfall must be specified regardless of whether or not the Rate is a PassThrough
		// WTF? The following 2 lines don't do anything
		DBO()->Rate->StdFlagfall	= DBO()->Rate->StdFlagfall->Value;
		DBO()->Rate->StdMinCharge	= DBO()->Rate->StdMinCharge->Value;
		
		if (!DBO()->Rate->Destination->IsSet)
		{
			DBO()->Rate->Destination = 0;
		}
		
		if (SubmittedForm("AddRate","Save as Draft"))
		{
			DBO()->Rate->Archived = RATE_STATUS_DRAFT;
		}
		
		if (SubmittedForm("AddRate","Commit"))
		{
			DBO()->Rate->Archived = RATE_STATUS_ACTIVE;
		}
	
		if (!DBO()->Rate->Save())
		{
			// Saving failed
			return "ERROR: Saving the Rate failed, unexpectedly<br>The Rate has not been saved";
		}
		return TRUE;
	}
	
	
	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the View Rate popup
	 * 
	 * Performs the logic for the View Rate popup
	 * It assumes:
	 * 		DBO()->Rate->Id		is set to the Rate that you want to view
	 *
	 * @return		void
	 * @method		View
	 */
	function View()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		// Load the Rate
		if (!DBO()->Rate->Load())
		{
			// The Rate could not be retrieved
			Ajax()->AddCommand("Alert", "ERROR: Rate could not be found in database");
			return TRUE;
		}
		
		DBO()->RecordType->Id = DBO()->Rate->RecordType->Value;
		DBO()->RecordType->Load();
		
		// If the Rate is Destination based, then load the corressponding Destination record
		if (DBO()->Rate->Destination->Value != 0)
		{
			DBO()->Destination->Where->Code = DBO()->Rate->Destination->Value;
			DBO()->Destination->Load();
		}
		
		$this->LoadPage('rate_view');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Search
	//------------------------------------------------------------------------//
	/**
	 * Search()
	 *
	 * Performs the logic for the searching for rates popup
	 * 
	 * Performs the logic for the searching for rates popup
	 * It assumes:
	 * 		DBO()->Rate->SearchString		This search string is used on the Rate.Name and Rate.Description properties
	 * 		DBO()->RateGroup->Id			if this is specified then it will only check the 
	 * 										Rates belonging to this RateGroup
	 * @return		void
	 * @method		Search
	 */
	function Search()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		$strSearchString = trim(DBO()->Rate->SearchString->Value);
		if ($strSearchString == "")
		{
			// The Search string is empty and considered invalid  
			Ajax()->AddCommand("Alert", "ERROR: Please specify a name or partial name to search");
			return TRUE;
		}
		
		// Escape any special characters
		$strSearchString = str_replace("'", "\'", $strSearchString);
		
		if (DBO()->RateGroup->Id->Value)
		{
			$strLimitToRateGroup = "AND Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup = ". DBO()->RateGroup->Id->Value .")";
		}
		
		$strWhere = "(Name LIKE '%$strSearchString%' OR Description LIKE '%$strSearchString%') $strLimitToRateGroup";
		DBL()->Rate->Where->SetString($strWhere);
		DBL()->Rate->SetLimit(1500);
		DBL()->Rate->Load();
		
		$this->LoadPage('rate_search_results');
		return TRUE;
	}
	
	
	//----- DO NOT REMOVE -----//
	
}
?>

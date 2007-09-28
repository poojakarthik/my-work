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
	 *
	 * @param	void
	 * @return	void
	 *
	 * @method
	 */
	function Add()
	{
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

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
		}
		else
		{
			DBO()->Rate->Id = 0;
			// Check if a ServiceType or RecordType was passed through to this method
			if (DBO()->RecordType->Id->Value)
			{
				DBO()->Rate->RecordType = DBO()->RecordType->Id->Value;
			}
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
		$arrRate['Draft']		= (DBO()->Rate->Archived->Value == 2) ? 1 : 0;
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
		// Test initial validation of fields
		if (DBO()->Rate->IsInvalid())
		{
			// The form has not passed initial validation
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
			return "ERROR: Could not save the rate.  Invalid fields are highlighted";
		}
		
		// Check if a rate with the same name exists
		if (DBO()->Rate->Id->Value == 0)
		{
			// The Rate name should not be in the database
			$strWhere = "Name=<Name>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other Rate
			$strWhere = "Name=<Name> AND Id != ". DBO()->Rate->Id->Value;
		}
		$selRateName = new StatementSelect("Rate", "Id", $strWhere);
		if ($selRateName->Execute(Array("Name" => DBO()->Rate->Name->Value)) > 0)
		{
			DBO()->Rate->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv", $this->_objAjax, $this->_intTemplateMode);
			return "ERROR: This rate name already exists in the Database";
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
		// if the passthrough checkbox is checked disable the form elements below it, 
		// but dont perform any validation
		if (!DBO()->Rate->PassThrough->Value)
		{
			// Check that the Standard Units has been specified
			if (!Validate('Integer', DBO()->Rate->StdUnits->Value))
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
					break;
				case RATE_CAP_STANDARD_PERCENTAGE:
					// validate percentage markup
					if (!is_numeric(DBO()->Rate->StdPercentage->Value))
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
					DBO()->Rate->ExsUnits		= 0;
					DBO()->Rate->ExsRatePerUnit	= 1649;
					DBO()->Rate->ExsMarkup		= 0;
					DBO()->Rate->ExsPercentage	= 0;
					break;
				case RATE_CAP_CAP_UNITS:
					// validate cap units
					if (!Validate('Integer', DBO()->Rate->CapUnits->Value))
					{
						DBO()->Rate->CapUnits->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					break;
				case RATE_CAP_CAP_COST:
					// validate cap cost
					if (!Validate('IsMoneyValue', DBO()->Rate->CapCost->Value))
					{
						DBO()->Rate->CapCost->SetToInvalid();
						$bolFormIsInvalid = TRUE;
					}
					break;
			}
		
			if ((DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST)||(DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS))
			{		
				// validate caplimitting values
				switch (DBO()->Rate->CapLimitting->Value)
				{
					case RATE_CAP_NO_CAP_LIMITS:
						// The rate has no cap limit.  Set appropriate fields to 0
						DBO()->Rate->CapLimit		= 0;
						DBO()->Rate->CapUsage		= 0;
						DBO()->Rate->ExsFlagfall	= 0;
						DBO()->Rate->ExsUnits		= 0;
						DBO()->Rate->ExsRatePerUnit	= 2101;
						DBO()->Rate->ExsMarkup		= 0;
						DBO()->Rate->ExsPercentage	= 0;
						break;
					case RATE_CAP_CAP_LIMIT:
						// validate cap limit
						if (!Validate('IsMoneyValue', DBO()->Rate->CapLimit->Value))
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
						DBO()->Rate->ExsUnits		= 0;
						DBO()->Rate->ExsRatePerUnit	= 1111;
						DBO()->Rate->ExsMarkup		= 0;
						DBO()->Rate->ExsPercentage	= 0;
						break;
					case RATE_CAP_CAP_USAGE:
						// validate cap usage and excess
						// flag if invalid
						// bu dont return allow to continue through the following lines
						if (!Validate('Integer', DBO()->Rate->CapUsage->Value))
						{
							DBO()->Rate->CapUsage->SetToInvalid();
							$bolFormIsInvalid = TRUE;
						}
						if (!Validate('Integer', DBO()->Rate->ExsUnits->Value))
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
								if (!Validate('IsMoneyValue', DBO()->Rate->ExsMarkup->Value))
								{
									DBO()->Rate->ExsMarkup->SetToInvalid();
									$bolFormIsInvalid = TRUE;
								}
								break;
							case RATE_CAP_EXS_PERCENTAGE:
								// validate percentage
								if (!is_numeric(DBO()->Rate->ExsPercentage->Value))
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
			DBO()->Rate->StdUnits 		= 0;
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
		}
		else
		{		
			//strip all $ signs off values
			DBO()->Rate->StdRatePerUnit	= (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_RATE_PER_UNIT) ? ltrim(DBO()->Rate->StdRatePerUnit->Value, '$') : 0;
			DBO()->Rate->StdMarkup		= (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_MARKUP) ? ltrim(DBO()->Rate->StdMarkup->Value, '$') : 0;
			DBO()->Rate->StdPercentage	= (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_PERCENTAGE) ? ltrim(DBO()->Rate->StdPercentage->Value, '$') : 0;
			
			DBO()->Rate->CapUnits		= (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS) ? DBO()->Rate->CapUnits->Value : 0;
			DBO()->Rate->CapCost		= (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST) ? ltrim(DBO()->Rate->CapCost->Value, '$') : 0;
			
			DBO()->Rate->CapLimit		= (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_LIMIT) ? ltrim(DBO()->Rate->CapLimit->Value, '$') : 0;
			DBO()->Rate->CapUsage		= (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_USAGE) ? DBO()->Rate->CapUsage->Value : 0;
			DBO()->Rate->ExsFlagfall	= (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_USAGE || DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_LIMIT) ? ltrim(DBO()->Rate->ExsFlagfall->Value, '$') : 0;
			
			DBO()->Rate->ExsRatePerUnit	= (DBO()->Rate->ExsChargeType->Value == RATE_CAP_EXS_RATE_PER_UNIT) ? ltrim(DBO()->Rate->ExsRatePerUnit->Value, '$') : 0;
			DBO()->Rate->ExsMarkup		= (DBO()->Rate->ExsChargeType->Value == RATE_CAP_EXS_MARKUP) ? ltrim(DBO()->Rate->ExsMarkup->Value, '$') : 0;
			DBO()->Rate->ExsPercentage	= (DBO()->Rate->ExsChargeType->Value == RATE_CAP_EXS_PERCENTAGE) ? DBO()->Rate->ExsPercentage->Value : 0;
		}
		
		// Minimum Charge and Flagfall must be specified regardless of whether or not the Rate is a PassThrough
		DBO()->Rate->StdFlagfall = ltrim(DBO()->Rate->StdFlagfall->Value, '$');
		DBO()->Rate->StdMinCharge = ltrim(DBO()->Rate->StdMinCharge->Value, '$');
		
		if (!DBO()->Rate->Destination->IsSet)
		{
			DBO()->Rate->Destination = 0;
		}
		
		if (SubmittedForm("AddRate","Save as Draft"))
		{
			DBO()->Rate->Archived = ARCHIVE_STATUS_DRAFT;
		}
		
		if (SubmittedForm("AddRate","Commit"))
		{
			DBO()->Rate->Archived = ARCHIVE_STATUS_ACTIVE;
		}
	
		if (!DBO()->Rate->Save())
		{
			// Saving failed
			return "ERROR: Saving the Rate failed, unexpectedly<br>The Rate has not been saved";
		}
		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}

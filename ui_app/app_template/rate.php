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

		if (SubmittedForm("AddRate","Cancel"))
		{
			// cancel code
		}
		
		// The form is being submitted via an AJAX submit the name of the form is 'AddRate'
		// and the method to call wtihin this class is 'Add'
		if (SubmittedForm("AddRate","Commit"))
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
					Ajax()->AddCommand("Alert", "The Rate has been successfully added");
					Ajax()->AddCommand("ClosePopup", "AddRatePopup");
					return TRUE;
				}
			}
		}

		// If the button clicked is 'Save as Draft' perform the same validation as to the 'Add'
		// yet different validation is done within the '_ValidationPlan()'
		if (SubmittedForm("AddRate","Save as Draft"))
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
				TransactionCommit();			
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
					Ajax()->AddCommand("Alert", "The Rate has been successfully saved as a draft");
					Ajax()->AddCommand("ClosePopup", "AddRatePopup");
					return TRUE;
				}			
			}		
		}
		 
		//**********************
		// a removable hard coded value for a record within the rate table, change as necessary
		//DBO()->Rate->Id = 12807;
		//**********************
		
		// check if the Id of a rate has been supplied and if so load the rate
		if (DBO()->Rate->Id->Value)
		{
			DBO()->Rate->Load();
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
	// Preview
	//------------------------------------------------------------------------//
	/**
	 * Preview()
	 *
	 * 
	 * 
	 * 
	 *
	 *
	 * 
	 *
	 */
	function Summary()
	{
		$this->LoadPage('rate_summary');
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
		Ajax()->AddCommand("ClosePopup", "AddRatePopup");
		Ajax()->AddCommand("Alert", "The Rate was successfully saved");
		
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
		if (!DBO()->Rate->PassThrough->Value)
		{
			// test initial validation of fields
			if (DBO()->Rate->IsInvalid())
			{
				// The form has not passed initial validation
				//Ajax()->AddCommand("Alert", "Could not save the rate.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
				return "Could not save the rate.  Invalid fields are highlighted";
			}		
		}
		
		// Check if a rate with the same name and isn't archived exists
		$strWhere = "NAME LIKE \"". DBO()->Rate->Name->Value. "\"";//. "AND ARCHIVED = 0";
		DBL()->Rate->Where->SetString($strWhere);
		DBL()->Rate->Load();
		if ((DBL()->Rate->RecordCount() > 0)&&(!SubmittedForm("AddRate","Save as Draft")))
		{	
			DBO()->Rate->Name->SetToInvalid();
			//Ajax()->AddCommand("Alert", "This RateName already exists in the Database");
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
			return "This RateName already exists in the Database";
		}
	
		$intDaySelected = FALSE;
		$arrDaysofWeek = array('Monday' => 'Rate.Monday',
							'Tuesday' => 'Rate.Tuesday',
							'Wednesday' => 'Rate.Wednesday',
							'Thursday' => 'Rate.Thursday',
							'Friday' => 'Rate.Friday',
							'Saturday' => 'Rate.Saturday',
							'Sunday' => 'Rate.Sunday');
		
		// validate that atleast one day in the week has been checked
		foreach ($arrDaysofWeek as $key => $value)
		{
			if (DBO()->Rate->$key->Value)
			{
				$intDaySelected = TRUE;
			}
		}
		if (!$intDaySelected)
		{
			//Ajax()->AddCommand("Alert", "Atleast one day in the week has to be clicked");
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
			return "Atleast one day in the week has to be clicked";			
		}
		
		// if the passthrough checkbox is checked disable the form elements below it, 
		// but dont perform any validation
		if (!DBO()->Rate->PassThrough->Value)
		{
			switch (DBO()->Rate->ChargeType->Value)
			{
				case RATE_CAP_STANDARD_RATE_PER_UNIT:
					// validate rate charge
					if (!Validate('IsMoneyValue', DBO()->Rate->StdRatePerUnit->Value))
					{
						DBO()->Rate->StdRatePerUnit->SetToInvalid();
						//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return "The value entered is not a correct monetary value";
					}
					break;
				case RATE_CAP_STANDARD_MARKUP:
					// validate standard markup
					if (!Validate('IsMoneyValue', DBO()->Rate->StdMarkup->Value))
					{
						DBO()->Rate->StdMarkup->SetToInvalid();
						//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return "The value entered is not a correct monetary value";
					}
					break;
				case RATE_CAP_STANDARD_PERCENTAGE:
					// validate percentage markup
					if (!Validate('IsMoneyValue', DBO()->Rate->StdPercentage->Value))
					{
						DBO()->Rate->StdPercentage->SetToInvalid();
						//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return "The value entered is not a correct monetary value";
					}
					break;
			}
	
			switch (DBO()->Rate->CapCalculation->Value)
			{
				case RATE_CAP_CAP_COST:
					// validate cap cost
					if (!Validate('IsMoneyValue', DBO()->Rate->CapCost->Value))
					{
						DBO()->Rate->CapCost->SetToInvalid();
						//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return "The value entered is not a correct monetary value";
					}
					break;
				case RATE_CAP_CAP_UNITS:
					// validate cap units
					if (!Validate('Integer', DBO()->Rate->CapUnits->Value))
					{
						DBO()->Rate->CapUnits->SetToInvalid();
						//Ajax()->AddCommand("Alert", "The value entered is not a correct value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return "The value entered is not a correct value";
					}
					break;
			}
		
			if ((DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST)||(DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS))
			{		
				// validate caplimitting values
				switch (DBO()->Rate->CapLimitting->Value)
				{
					case RATE_CAP_NO_CAP_LIMITS:
						// no further validation is required break
						break;
					case RATE_CAP_CAP_LIMIT:
						// validate cap limit
						if (!Validate('IsMoneyValue', DBO()->Rate->CapLimit->Value))
						{
							DBO()->Rate->CapLimit->SetToInvalid();
							//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
							Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
							return "The value entered is not a correct monetary value";
						}
						break;
					case RATE_CAP_CAP_USAGE:
						// validate cap usage and excess
						// flag if invalid
						// bu dont return allow to continue through the following lines
						if (!Validate('Integer', DBO()->Rate->CapUsage->Value))
						{
							DBO()->Rate->CapUsage->SetToInvalid();
							//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
							Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
							return "The value entered is not a correct monetary value";
	
						}
						if (!Validate('Integer', DBO()->Rate->ExsUnits->Value))
						{
							DBO()->Rate->ExsUnits->SetToInvalid();
							//Ajax()->AddCommand("Alert", "The value entered is not a correct value");
							Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
							return "The value entered is not a correct value";
						}
						if (!Validate('IsMoneyValue', DBO()->Rate->ExsFlagfall->Value))
						{
							DBO()->Rate->ExsFlagfall->SetToInvalid();
							//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
							Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
							return "The value entered is not a correct monetary value";
						}
					switch (DBO()->Rate->ExsChargeType->Value)
					{
						case RATE_CAP_EXS_RATE_PER_UNIT:
							// validate excess rate
							if (!Validate('IsMoneyValue', DBO()->Rate->ExsRatePerUnit->Value))
							{
								DBO()->Rate->ExsRatePerUnit->SetToInvalid();
								//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
								Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
								return "The value entered is not a correct monetary value";
							}
							break;
						case RATE_CAP_EXS_MARKUP:
							// validate markup
							if (!Validate('IsMoneyValue', DBO()->Rate->ExsMarkup->Value))
							{
								DBO()->Rate->ExsMarkup->SetToInvalid();
								//Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
								Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
								return "The value entered is not a correct monetary value";
							}
							break;
						case RATE_CAP_EXS_PERCENTAGE:
							// validate percentage
							if (!is_numeric(DBO()->Rate->ExsPercentage->Value))
							{
								DBO()->Rate->ExsPercentage->SetToInvalid();
								//Ajax()->AddCommand("Alert", "The value entered is not a correct value");
								Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
								return "The value entered is not a correct value";
							}
							break;
					}
					break;
				default:
					break;
				}
			}
		}			
		// everything has been validated
		
		if (DBO()->Rate->PassThrough->Value)
		{
			DBO()->Rate->StdUnits 		= 0;
			DBO()->Rate->StdRatePerUnit = 0;
			DBO()->Rate->StdMarkup 		= 0;
			DBO()->Rate->StdPercentage 	= 0;
			DBO()->Rate->StdMinCharge 	= 0;
			DBO()->Rate->StdFlagfall 	= 0;
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
			DBO()->Rate->StdRatePerUnit = (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_RATE_PER_UNIT) ? ltrim(DBO()->Rate->StdRatePerUnit->Value, '$') : 0;
			DBO()->Rate->StdMarkup = (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_MARKUP) ? ltrim(DBO()->Rate->StdMarkup->Value, '$') : 0;
			DBO()->Rate->StdPercentage = (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_PERCENTAGE) ? ltrim(DBO()->Rate->StdPercentage->Value, '$') : 0;
			
			DBO()->Rate->CapUnits = (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS) ? DBO()->Rate->CapUnits->Value : 0;
			DBO()->Rate->CapCost = (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST) ? ltrim(DBO()->Rate->CapCost->Value, '$') : 0;
			
			DBO()->Rate->CapLimit = (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_LIMIT) ? ltrim(DBO()->Rate->CapLimit->Value, '$') : 0;
			DBO()->Rate->CapUsage = (DBO()->Rate->CapLimitting->Value == RATE_CAP_CAP_USAGE) ? DBO()->Rate->CapCost->Value : 0;
			
			DBO()->Rate->ExsRatePerUnit = (DBO()->Rate->ExsRatePerUnit->Value == RATE_CAP_EXS_RATE_PER_UNIT) ? ltrim(DBO()->Rate->ExsRatePerUnit->Value, '$') : 0;
			DBO()->Rate->ExsMarkup = (DBO()->Rate->ExsMarkup->Value == RATE_CAP_EXS_MARKUP) ? ltrim(DBO()->Rate->ExsMarkupt->Value, '$') : 0;
			DBO()->Rate->ExsPercentage = (DBO()->Rate->ExsPercentage->Value == RATE_CAP_EXS_PERCENTAGE) ? DBO()->Rate->CapLimit->Value : 0;
				
			DBO()->Rate->StdRatePerUnit = (Validate('IsMoneyValue', DBO()->Rate->StdRatePerUnit->Value)) ? ltrim(DBO()->Rate->StdRatePerUnit->Value, '$') : 0;
			DBO()->Rate->StdMarkup = (Validate('IsMoneyValue', DBO()->Rate->StdMarkup->Value)) ? ltrim(DBO()->Rate->StdMarkup->Value, '$') : 0;
			DBO()->Rate->StdPercentage = (is_numeric(DBO()->Rate->StdMarkup->Value)) ? DBO()->Rate->StdMarkup->Value : 0;
	
			DBO()->Rate->StdMinCharge = (Validate('IsMoneyValue', DBO()->Rate->StdMinCharge->Value)) ? ltrim(DBO()->Rate->StdMinCharge->Value, '$') : 0;
			DBO()->Rate->StdFlagfall = (Validate('IsMoneyValue', DBO()->Rate->StdFlagfall->Value)) ? ltrim(DBO()->Rate->StdFlagfall->Value, '$') : 0;
			
			DBO()->Rate->CapUnits = (is_numeric(DBO()->Rate->CapUnits->Value)) ? DBO()->Rate->CapUnits->Value : 0;
			DBO()->Rate->CapCost = (Validate('IsMoneyValue', DBO()->Rate->CapCost->Value)) ? ltrim(DBO()->Rate->CapCost->Value, '$') : 0;			
			DBO()->Rate->CapLimit = (Validate('IsMoneyValue', DBO()->Rate->CapLimit->Value)) ? ltrim(DBO()->Rate->CapLimit->Value, '$') : 0;
			DBO()->Rate->CapUsage = (is_numeric(DBO()->Rate->CapUsage->Value)) ? DBO()->Rate->CapUsage->Value : 0;
	
			DBO()->Rate->ExsUnits = (is_numeric(DBO()->Rate->ExsUnits->Value)) ? DBO()->Rate->ExsUnits->Value : 1;  //Defaults to 1 if not included
			DBO()->Rate->ExsRatePerUnit = (Validate('IsMoneyValue', DBO()->Rate->ExsRatePerUnit->Value)) ? ltrim(DBO()->Rate->ExsRatePerUnit->Value, '$') : 0;
			DBO()->Rate->ExsMarkup = (Validate('IsMoneyValue', DBO()->Rate->ExsMarkup->Value)) ? ltrim(DBO()->Rate->ExsMarkup->Value, '$') : 0;
			DBO()->Rate->ExsPercentage = (is_numeric(DBO()->Rate->ExsPercentage->Value)) ? DBO()->Rate->ExsPercentage->Value : 0;
			DBO()->Rate->ExsFlagfall = (Validate('IsMoneyValue', DBO()->Rate->ExsFlagfall->Value)) ? ltrim(DBO()->Rate->ExsFlagfall->Value, '$') : 0;
		}
		
		if (!DBO()->Rate->Destination->IsSet)
		{
			DBO()->Rate->Destination = 0;
		}
		
		if (SubmittedForm("AddRate","Save as Draft"))
		{
			DBO()->Rate->Archived = 2;
		}
		
		if (SubmittedForm("AddRate","Commit"))
		{
			DBO()->Rate->Archived = 0;
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

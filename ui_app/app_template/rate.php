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
 * @version		7.05
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
 * @class	AppTemplaterate
 * @extends	ApplicationTemplate
 */
class AppTemplaterate extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// add
	//------------------------------------------------------------------------//
	/**
	 * add()
	 *
	 * Performs the logic for the rate_add.php webpage
	 * 
	 * Performs the logic for the rate_add.php webpage
	 *
	 * @return		void
	 * @method		add
	 *
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

		if (SubmittedForm("AddRate","Add"))
		{
			TransactionStart();
			$mixResult = $this->_ValidatePlan();
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

		// TODO save as draft
		if (SubmittedForm("AddRate","Save as Draft"))
		{
			TransactionStart();
			$mixResult = $this->_ValidatePlan();
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
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The rate has been successfully added", "Location" => Href()->AdminConsole()));
				return TRUE;			
			}		
		
			/*DBO()->Rate->Archived = 2;
			if (!DBO()->Rate->Save())
			{
				Ajax()->AddCommand("Alert", "ERROR: saving this rate failed, unexpectedly");
				return TRUE;	
			}
			else
			{
				Ajax()->AddCommand("Alert", "rate has been sucessfully archived");
				return TRUE;				
			}*/
			
		}
		 
		// doesnt entirely function correctly when loading rates 
		// hard coded value for a record within the rate table change as necessary
//		DBO()->Rate->Id = 4;
		// check if the Id of a rate has been supplied and if so load the rate
		if (DBO()->Rate->Id->Value)
		{
			DBO()->Rate->Load();
		}
		else
		{
			// Check if a ServiceType or RecordType was passed through to this method
			if (DBO()->RecordType->Id->Value)
			{
				DBO()->Rate->RecordType = DBO()->RecordType->Id->Value;
			}
		}

		$this->LoadPage('rate_add');
		return TRUE;
	}
	
	// This is used to update the "Add Rate Group" page, when a rate has been added and the "Add Rate" popup window is closed
	private function _UpdateAddRateGroupPage()
	{
		Ajax()->AddCommand("ClosePopup", "{$this->_objAjax->strId}");
		Ajax()->AddCommand("Alert", "The Rate was successfully saved");
		
		$intRateId		= DBO()->Rate->Id->Value;
		$strDescription	= str_replace("\"", "'", DBO()->Rate->Description->Value);
		$strName		= str_replace("\"", "'", DBO()->Rate->Name->Value);
		$intRecordType	= DBO()->Rate->RecordType->Value;
		
		$strJavascript = "Vixen.RateGroupAdd.ChooseRate($intRateId, \"$strDescription\", \"$strName\", $intRecordType);";
		Ajax()->AddCommand("ExecuteJavascript", $strJavascript);
	}
	
	private function _ValidatePlan()
	{
		// test initial validation of fields
		if (DBO()->Rate->IsInvalid())
		{
			// The form has not passed initial validation
			//Ajax()->AddCommand("Alert", "Could not save the rate.  Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
			return "Could not save the rate.  Invalid fields are highlighted";
		}		
		
		// Check if a rate with the same name and isn't archived exists
		$strWhere = "NAME LIKE \"". DBO()->Rate->Name->Value . "\"" . "AND ARCHIVED = 0";
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
		// everything has been validated
		
		//strip all $ signs off values
		DBO()->Rate->StdRatePerUnit = (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_RATE_PER_UNIT) ? ltrim(DBO()->Rate->StdRatePerUnit->Value, '$') : 0;
		DBO()->Rate->StdMarkup = (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_MARKUP) ? ltrim(DBO()->Rate->StdMarkup->Value, '$') : 0;
		DBO()->Rate->StdPercentage = (DBO()->Rate->ChargeType->Value == RATE_CAP_STANDARD_PERCENTAGE) ? ltrim(DBO()->Rate->StdPercentage->Value, '$') : 0;
		
		DBO()->Rate->CapUnits = (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_UNITS) ? DBO()->Rate->CapUnits->Value : 0;
		DBO()->Rate->CapCost = (DBO()->Rate->CapCalculation->Value == RATE_CAP_CAP_COST) ? ltrim(DBO()->Rate->CapCost->Value, '$') : 0;
		
		//not trapping for no cap?? does it need to??
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

		DBO()->Rate->ExsUnits = (is_numeric(DBO()->Rate->ExsUnits->Value)) ? DBO()->Rate->ExsUnits->Value : 0;
		DBO()->Rate->ExsRatePerUnit = (Validate('IsMoneyValue', DBO()->Rate->ExsRatePerUnit->Value)) ? ltrim(DBO()->Rate->ExsRatePerUnit->Value, '$') : 0;
		DBO()->Rate->ExsMarkup = (Validate('IsMoneyValue', DBO()->Rate->ExsMarkup->Value)) ? ltrim(DBO()->Rate->ExsMarkup->Value, '$') : 0;
		DBO()->Rate->ExsPercentage = (is_numeric(DBO()->Rate->ExsPercentage->Value)) ? DBO()->Rate->ExsPercentage->Value : 0;
		DBO()->Rate->ExsFlagfall = (Validate('IsMoneyValue', DBO()->Rate->ExsFlagfall->Value)) ? ltrim(DBO()->Rate->ExsFlagfall->Value, '$') : 0;
		
		//$strStatusMessage = '';
		//if (SubmittedForm("AddRate","Save as Draft"))
		//{
		//	DBO()->Rate->Archived = 2;
		//	$strStatusMessage = 'Archived';
		//}
		//else
		//{
		DBO()->Rate->Archived = 0;
		//	$strStatusMessage = 'Saved';
		//}
	
		DBO()->Rate->Destination = 0;
		DBO()->Rate->PassThrough = 0;
	
		if (!DBO()->Rate->Save())
		{
			// Saving failed
			return "ERROR: Saving the Rate failed, unexpectedly<br>The Rate has not been saved";
		}
		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}

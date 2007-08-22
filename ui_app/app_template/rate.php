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

		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// Breadcrumb menu
				
		// Setup all DBO and DBL objects required for the page
		
		/*if (DBO()->Rate->ServiceType->Value == NULL)
		{
			DBO()->Error->Message = "The ServiceType could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		if (DBO()->RecordType->Id->Value == NULL)
		{
			DBO()->Error->Message = "The RecordType could not be found";
			$this->LoadPage('error');
			return FALSE;
		}*/

		if (SubmittedForm("AddRate","Add"))
		{
			// test initial validation of fields
			if (DBO()->Rate->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "Could not save the rate.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
				return TRUE;
			}		
			
			// Check if the name is valid
			if (!trim(DBO()->Rate->Name->Value))
			{
				$mixRateName = DBO()->Rate->Name->Value;
			
				DBO()->Rate->Name->SetToInvalid();
				Ajax()->AddCommand("Alert", "The Name is invalid for this Rate<br>Rate.Name = '$mixRateName'");
				Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
				return TRUE;				
			}
			
			// Check if a rate with the same name and isn't archived exists
			$strWhere = "NAME LIKE \"". DBO()->Rate->Name->Value . "\"" . "AND ARCHIVED = 0";
			DBL()->Rate->Where->SetString($strWhere);
			DBL()->Rate->Load();
			if (DBL()->Rate->RecordCount() > 0)
			{	
				DBO()->Rate->Name->SetToInvalid();
				Ajax()->AddCommand("Alert", "This RateName already exists in the Database");
				Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
				return TRUE;
			}
		
			switch (DBO()->Rate->ChargeType->Value)
			{
				case RATE_CAP_STANDARD_RATE_PER_UNIT:
					// validate rate charge
					if (!Validate('IsMoneyValue', DBO()->Rate->StdRatePerUnit->Value))
					{
						DBO()->Rate->StdRatePerUnit->SetToInvalid();
						Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return TRUE;
					}
					break;
				case RATE_CAP_STANDARD_MARKUP:
					// validate standard markup
					if (!Validate('IsMoneyValue', DBO()->Rate->StdMarkup->Value))
					{
						DBO()->Rate->StdMarkup->SetToInvalid();
						Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return TRUE;
					}
					break;
				case RATE_CAP_STANDARD_PERCENTAGE:
					// validate percentage markup
					if (!Validate('IsMoneyValue', DBO()->Rate->StdPercentage->Value))
					{
						DBO()->Rate->StdPercentage->SetToInvalid();
						Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						return TRUE;
					}
					break;
			}
		
			switch (DBO()->Rate->CapCalculation->Value)
			{
				case RATE_CAP_CAP_COST:
					// validate cap cost
					if (!Validate('IsMoneyValue', DBO()->Rate->CapCost->Value))
					{
						Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						DBO()->Rate->CapCost->SetToInvalid();
						return TRUE;
					}
					break;
				case RATE_CAP_CAP_UNITS:
					// validate cap units
					if (!Validate('IsMoneyValue', DBO()->Rate->CapUnits->Value))
					{
						Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
						Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
						DBO()->Rate->CapUnits->SetToInvalid();
						return TRUE;
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
							Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
							Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
							return TRUE;
						}
						break;
					case RATE_CAP_CAP_USAGE:
						// validate cap usage and excess
						// flag if invalid
						// bu dont return allow to continue through the following lines
						if (!Validate('IsMoneyValue', DBO()->Rate->CapUsage->Value))
						{
							DBO()->Rate->CapUsage->SetToInvalid();
						}
						switch (DBO()->Rate->ExsChargeType->Value)
						{
							case RATE_CAP_EXS_RATE_PER_UNIT:
								// validate excess rate
								if (!Validate('IsMoneyValue', DBO()->Rate->ExsRatePerUnit->Value))
								{
									DBO()->Rate->ExsRatePerUnit->SetToInvalid();
									Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
									Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
									return TRUE;
								}
								break;
							case RATE_CAP_EXS_MARKUP:
								// validate markup
								if (!Validate('IsMoneyValue', DBO()->Rate->ExsMarkup->Value))
								{
									DBO()->Rate->ExsMarkup->SetToInvalid();
									Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
									Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
									return TRUE;
								}
								break;
							case RATE_CAP_EXS_PERCENTAGE:
								// validate percentage
								if (!Validate('IsMoneyValue', DBO()->Rate->ExsPercentage->Value))
								{
									DBO()->Rate->ExsPercentage->SetToInvalid();
									Ajax()->AddCommand("Alert", "The value entered is not a correct monetary value");
									Ajax()->RenderHtmlTemplate("RateAdd", HTML_CONTEXT_DEFAULT, "RateAddDiv");
									return TRUE;
								}
								break;
						}
						break;
				}
			}
		}	
 		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('rate_add');

		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}

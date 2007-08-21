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
 * @author		Sean, Jared 'flame' Herbohn
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
			
			//if ((DBO()->Rate->Name->Value == 0) || (DBO()->Rate->Name->Value == ''))
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
			
		}

		// Validate if the REcordType and/or ServiceType are empty

		
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('rate_add');

		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}

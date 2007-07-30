<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// service
//----------------------------------------------------------------------------//
/**
 * service
 *
 * contains all ApplicationTemplate extended classes relating to service functionality
 *
 * contains all ApplicationTemplate extended classes relating to service functionality
 *
 * @file		service.php
 * @language	PHP
 * @package		framework
 * @author		Sean, Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateservice
//----------------------------------------------------------------------------//
/**
 * AppTemplateservice
 *
 * The AppTemplateservice class
 *
 * The AppTemplateservice class.  This incorporates all logic for all pages
 * relating to services
 *
 *
 * @package	ui_app
 * @class	AppTemplateservice
 * @extends	ApplicationTemplate
 */
class AppTemplateservice extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// view
	//------------------------------------------------------------------------//
	/**
	 * view()
	 *
	 * Performs the logic for the service_view.php webpage
	 * 
	 * Performs the logic for the service_view.php webpage
	 *
	 * @return		void
	 * @method		view
	 *
	 */
	function view()
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
		
		//EXAMPLE:
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->value ."you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . "associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}
		if (DBO()->Service->Indial100->Value)
		{
			DBL()->ServiceExtension->Service = DBO()->Service->Id->Value;
			DBL()->ServiceExtension->Archived = 0;
			DBL()->ServiceExtension->Load();
			DBO()->Service->ELB = (bool)DBL()->ServiceExtension->RecordCount();
		}
		
		// Get the details of the current plan for the service
		DBO()->RatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		if (DBO()->RatePlan->Id->Value !== FALSE)
		{
			DBO()->RatePlan->Load();
		}
		
		// Calculate unbilled charges (this includes all unbilled Adjustments(charges) and CDRs)
		// TODO test that the functionality works on catwalk with the CDR table
		//$fltUnbilledAdjustments					= UnbilledServiceChargeTotal(DBO()->Service->Id->Value);
		$fltUnbilledAdjustments					= UnbilledServiceChargeTotal(33260);
		$fltUnbilledCDRs						= UnbilledServiceCDRTotal(DBO()->Service->Id->Value);
		DBO()->Service->TotalUnbilledCharges 	= AddGST($fltUnbilledAdjustments + $fltUnbilledCDRs);
		
		
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_view');

		return TRUE;
	}
	
	function edit()
	{
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		if (SubmittedForm("EditService","Apply Changes"))
		{
			// Context menu

			DBO()->Status->Message = "entered";
			return TRUE;
			// Breadcrumb menu
					
			// Setup all DBO and DBL objects required for the page
			
			//EXAMPLE:
			// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable

			//return TRUE;
		}

		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->value ."you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}

		// All required data has been retrieved from the database so now load the page template
		return $this->LoadPage('service_edit');
	}	
	
	//----- DO NOT REMOVE -----//
	
}

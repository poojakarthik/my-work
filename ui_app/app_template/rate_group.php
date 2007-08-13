<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// plans
//----------------------------------------------------------------------------//
/**
 * plans
 *
 * contains all ApplicationTemplate extended classes relating to RateGroup functionality
 *
 * contains all ApplicationTemplate extended classes relating to RateGroup functionality
 *
 * @file		plans.php
 * @language	PHP
 * @package		framework
 * @author		Ross
 * @version		
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
 * The AppTemplateRateGroup class
 *
 * The AppTemplateRateGroup class.  This incorporates all logic for all pages
 * relating to Available RateGroups
 *
 *
 * @package	ui_app
 * @class	AppTemplateRateGroup
 * @extends	ApplicationTemplate
 */
class AppTemplateRateGroup extends ApplicationTemplate
{
	function ViewGroup()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();

		//DBO()->RatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		//if (DBO()->RatePlan->Id->Value !== FALSE)
		//{
		
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

		
		// Load context menu items specific to the View Service page
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		//BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		
		
		// Setup all DBO and DBL objects required for the page
		//TODO!
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		/*if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}*/
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		//DBL()->RatePlan->Load();
	
		$this->LoadPage('view_rate_group');

		return TRUE;
	
	
	}
	
}

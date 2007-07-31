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
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateService
//----------------------------------------------------------------------------//
/**
 * AppTemplateService
 *
 * The AppTemplateAccount class
 *
 * The AppTemplateAccount class.  This incorporates all logic for all pages
 * relating to services
 *
 *
 * @package	web_app
 * @class	AppTemplateService
 * @extends	ApplicationTemplate
 */
class AppTemplateService extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// ViewUnbilledCharges
	//------------------------------------------------------------------------//
	/**
	 * ViewUnbilledCharges()
	 *
	 * Performs the logic for the service_view_unbilled_charges.php webpage
	 * 
	 * Performs the logic for the service_view_unbilled_charges.php webpage
	 *
	 * @return		void
	 * @method		ViewUnbilledCharges
	 *
	 */
	function ViewUnbilledCharges()
	{
		// Check user authorization
		AuthenticatedUser()->CheckClientAuth();

		// Context menu
		//ContextMenu()->Admin_Console();
		//ContextMenu()->Logout();
		
				
		// Load the account
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The service with service id: ". DBO()->Service->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Check that the user can view this service
		$bolUserCanViewService = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Service->AccountGroup->Value)
			{
				$bolUserCanViewService = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Service->Account->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewService = TRUE;
		}
		
		if (!$bolUserCanViewService)
		{
			// The user does not have permission to view the requested Service
			DBO()->Error->Message = "ERROR: The user does not have permission to view service# ". DBO()->Service->Id->Value ." as it does not belong to any of their available accounts";
			$this->LoadPage('Error');
			return FALSE;
		}
		
		// Retrieve all unbilled adjustments for the service
		$strWhere  = "(Account = ". DBO()->Service->Account->Value .")";
		$strWhere .= " AND (Service = ". DBO()->Service->Id->Value .")";
		$strWhere .= " AND (Status = ". CHARGE_APPROVED .")";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		// Retrieve the first 20 unbilled CDRs for the service, starting with the oldest
		// NOTE! rich's code to calculate the total unbilled CDRS only retrieves CDRs with status == CDR_RATED
		// where as the current client_app (when displying unbilled CDRs) will also retrieve all CDRs with status == CDR_TEMP_INVOICE
		$strWhere  = "(Service = ". DBO()->Service->Id->Value .")";
		$strWhere .= " AND (Status = ". CDR_RATED .")";
		$strWhere .= " AND (Status = ". CDR_TEMP_INVOICE .")";		
		DBL()->CDR->Where->SetString($strWhere);
		DBL()->CDR->OrderBy("StartDatetime DESC, Id DESC");
		DBL()->CDR->SetLimit(20, 0);
		DBL()->CDR->Load();

		// Breadcrumb menu
		BreadCrumbMenu()->LoadAccountInConsole(DBO()->Service->Account->Value);
		BreadCrumbMenu()->ViewUnbilledChargesForAccount(DBO()->Service->Account->Value);
		


		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_view_unbilled_charges');
		
		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}

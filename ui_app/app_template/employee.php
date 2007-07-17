<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// Employee
//----------------------------------------------------------------------------//
/**
 * Employee
 *
 * contains all ApplicationTemplate extended classes relating to Available Employee functionality
 *
 * contains all ApplicationTemplate extended classes relating to Available Employee functionality
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
// AppTemplateEmployee
//----------------------------------------------------------------------------//
/**
 * AppTemplateEmployee
 *
 * The AppTemplateEmployee class
 *
 * The AppTemplateEmployee class.  This incorporates all logic for all pages
 * relating to Employees
 *
 *
 * @package	ui_app
 * @class	AppTemplateAccount
 * @extends	ApplicationTemplate
 */
class AppTemplateEmployee extends ApplicationTemplate
{
	function View()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();
		// context menu
		//TODO! define what goes in the context menu
		//ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		//ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		//ContextMenu()->Contact_Retrieve->Service->Invoices_And_Payments(DBO()->Account->Id->Value);
		//ContextMenu()->Contact_Retrieve->Service->View_Account(DBO()->Account->Id->Value);
		//ContextMenu()->Contact_Retrieve->Add_Adjustment(DBO()->Account->Id->Value);
		//ContextMenu()->Contact_Retrieve->View_Notes(DBO()->Account->Id->Value);
		
		// Console and logout should appear by default, no?
		ContextMenu()->Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		//BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		
		
		// Setup all DBO and DBL objects required for the page
		//TODO!
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		/*DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC");
		DBL()->Invoice->Load();
		*/
		if ($_POST['Archived'] != 1)
		{
			DBL()->Employee->Archived = 0;
		}
		
		DBL()->Employee->Load();
		
	
		$this->LoadPage('view_employees');

		return TRUE;
	
	}

	function Edit()
	{
		AuthenticatedUser()->CheckAuth();
		//check if the form was submitted
		if (SubmittedForm('Employee', 'Save'))
		{
			//Save the employee
			if (!DBO()->Employee->IsInvalid())
			{
				//echo "Employee is NOT invalid Employee would be saved";
				DBO()->Employee->Save();
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", "The information was successfully saved.");
			}
		}
		DBO()->Employee->Load();
		$this->LoadPage('edit_employee');
		
		return TRUE;
	}

}

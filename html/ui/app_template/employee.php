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
 * @file		employee.php
 * @language	PHP
 * @package		framework
 * @author		Ross
 * @version		7.06
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
	
	//------------------------------------------------------------------------//
	// ViewRecentCustomers
	//------------------------------------------------------------------------//
	/**
	 * ViewRecentCustomers()
	 *
	 * Performs the logic for the View Recent Customers popup window
	 * 
	 * Performs the logic for the View Recent Customers popup window
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ViewRecentCustomers()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Retrieve that last 20 customers that were verified by the user
		$arrColumns	= Array(	"RequestedOn"	=> "EAA.RequestedOn",
								"AccountId"		=> "A.Id",
								"BusinessName"	=> "A.BusinessName",
								"TradingName"	=> "A.TradingName",
								"ContactId"		=> "C.Id",
								"Title"			=> "C.Title",
								"FirstName"		=> "C.FirstName",
								"LastName"		=> "C.LastName"
							);
		$strTables	= "Contact AS C RIGHT JOIN EmployeeAccountAudit AS EAA ON EAA.Contact = C.Id INNER JOIN Account AS A ON EAA.Account = A.Id";
		$strWhere	= "EAA.Employee = <UserId>";
		$arrWhere	= array("UserId" => AuthenticatedUser()->_arrUser['Id']);
		$strOrderBy	= "EAA.RequestedOn DESC";
		$strLimit	= "20";
		
		DBL()->RecentCustomers->SetColumns($arrColumns);
		DBL()->RecentCustomers->SetTable($strTables);
		DBL()->RecentCustomers->Where->Set($strWhere, $arrWhere);
		DBL()->RecentCustomers->OrderBy($strOrderBy);
		DBL()->RecentCustomers->SetLimit($strLimit);

		DBL()->RecentCustomers->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('recent_customers_view');

		return TRUE;
	}
	
	
	// This is not currently used and was probably coded in June of 2007
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// context menu
		//ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Employee_Console();		
		ContextMenu()->Contact_Retrieve->Service->Add_Service(DBO()->Account->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->Edit_Service(DBO()->Service->Id->Value);		
		ContextMenu()->Contact_Retrieve->Service->Change_Plan(DBO()->Service->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->Change_of_Lessee(DBO()->Service->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);	

		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Invoice_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->View_Service_Notes(DBO()->Service->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Service_Note(DBO()->Service->Id->Value);
		if ($bolUserHasAdminPerm)
		{
			// User must have admin permissions to view the Administrative Console
			ContextMenu()->Admin_Console();
		}
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

	// This is not currently used and was probably coded in June of 2007
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
?>
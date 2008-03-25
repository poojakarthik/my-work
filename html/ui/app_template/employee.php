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


	//------------------------------------------------------------------------//
	// Logout
	//------------------------------------------------------------------------//
	/**
	 * Logout()
	 *
	 * Performs the logout procedure for the employee
	 * 
	 * Performs the logout procedure for the employee
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Logout()
	{
		// Logout the employee
		AuthenticatedUser()->Logout();
		// Redirect the user to the login page
		header("Location: " . preg_replace("/flex.php\/.*$/", "index.php", $_SERVER['REQUEST_URI']));
		die;
	}


	//------------------------------------------------------------------------//
	// EmployeeList
	//------------------------------------------------------------------------//	
	/**
	 * EmployeeList()
	 * 
	 * Loads all employees into a list
	 *
	 * @param void
	 * 
	 * @return boolean	TRUE
	 * 
	 * @method
	 */
	function EmployeeList()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		// Context menu
		// (Nothing to add)
		
		// Breadcrumb menu
		Breadcrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Employee List");
		
		// Retrieve all Employees
		
		if ($_POST['Archived'] != 1)
		{
			DBL()->Employee->Archived = 0;
		}

		DBL()->Employee->OrderBy("LastName, FirstName, UserName");

		DBL()->Employee->Load();
		
		$this->LoadPage('employee_list');

		return TRUE;
	}

	//------------------------------------------------------------------------//
	// EmployeeListAjax
	//------------------------------------------------------------------------//	
	/**
	 * EmployeeList()
	 * 
	 * Loads all employees into a table (rest of page not wanted for ajax response)
	 *
	 * @param void
	 * 
	 * @return boolean	TRUE
	 * 
	 * @method
	 */
	function EmployeeListAjax()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		// Context menu
		// (Nothing to add)
		
		// Breadcrumb menu
		Breadcrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Employee List");
		
		// If only want non-archived Employees...
		if (DBO()->Search->Archived->Value == "0")
		{
			DBL()->Employee->Archived = 0;
		}

		$orderBy = "LastName, FirstName, UserName";
		if (DBO()->Search->OrderBy->Value != "")
		{
			$orderBy = DBO()->Search->OrderBy->Value;
			if (DBO()->Search->OrderDesc->Value)
			{
				$orderBy .= " desc";
			}
		}

		DBL()->Employee->OrderBy($orderBy);

		DBL()->Employee->Load();
		
		$this->LoadPage('employee_view_list');

		return TRUE;
	}
	
	function EmployeeDetils()
	{
		AuthenticatedUser()->CheckAuth();
		DBO()->Employee->Id = AuthenticatedUser()->GetUserId();
		DBO()->Employee->Load();
		return $this->Edit(TRUE);
	}
	

	//------------------------------------------------------------------------//
	// Edit
	//------------------------------------------------------------------------//	
	/**
	 * Edit()
	 * 
	 * Edits or Creates a user
	 *
	 * @param void
	 * 
	 * @return boolean	TRUE
	 * 
	 * @method
	 */
	function Edit($bolEditSelf = FALSE)
	{
		AuthenticatedUser()->CheckAuth();
		$bolAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		if (DBO()->Employee->Id->Value != AuthenticatedUser()->GetUserId())
		{
			AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		}

		$bolEditSelf = $bolEditSelf || DBO()->Employee->EditSelf->Value;

		//check if the form was submitted
		if (SubmittedForm('Employee', 'Save'))
		{
			// Determine if this is to be a new employee
			$bolCreateNew = DBO()->Employee->Id->Value < 0;
			
			$arrValidationErrors = array();

			if ($bolCreateNew)
			{
				DBO()->Employee->UserName = trim(DBO()->Employee->UserName->Value);
				DBO()->Employee->UserName->ValidateProperty($arrValidationErrors, true);
				if (!DBO()->Employee->UserName->IsInvalid())
				{
					$strWhere	= "Employee.UserName = <UserName>";
					$arrWhere	= array("UserName" => DBO()->Employee->UserName->Value);
					DBL()->RecentCustomers->SetColumns(array("UserName"));
					DBL()->RecentCustomers->SetTable("Employee");
					DBL()->RecentCustomers->Where->Set($strWhere, $arrWhere);
					DBL()->RecentCustomers->SetLimit(1);
					DBL()->RecentCustomers->Load();
					if (DBL()->RecentCustomers->RecordCount() > 0)
					{
						DBO()->Employee->UserName->SetToInvalid();
						$strLabel = DBO()->Employee->UserName->strGetLabel();
						$arrValidationErrors[] = "$strLabel \"" . DBO()->Employee->UserName->Value . "\" is already in use.";
					}
				}
			}
			
			if (!$bolEditSelf && $bolAdminUser)
			{
				DBO()->Employee->FirstName = trim(DBO()->Employee->FirstName->Value);
				DBO()->Employee->FirstName->ValidateProperty($arrValidationErrors, true, CONTEXT_DEFAULT, "IsNotEmptyString");
				DBO()->Employee->LastName = trim(DBO()->Employee->LastName->Value);
				DBO()->Employee->LastName->ValidateProperty	($arrValidationErrors, true, CONTEXT_DEFAULT, "IsNotEmptyString");
				
				DBO()->Employee->DOB = trim(DBO()->Employee->DOB->Value);
				DBO()->Employee->DOB = UnmaskShortDate(DBO()->Employee->DOB->Value);
				DBO()->Employee->DOB->ValidateProperty		($arrValidationErrors, true, CONTEXT_DEFAULT, "IsValidDate");
				DBO()->Employee->DOB->ValidateProperty		($arrValidationErrors, true, CONTEXT_DEFAULT, "IsValidDateInPast", "<label> must be in the past.");
			}
			
			DBO()->Employee->Email = trim(DBO()->Employee->Email->Value);
			DBO()->Employee->Email->ValidateProperty	($arrValidationErrors, false, CONTEXT_DEFAULT, "IsValidEmail");
			DBO()->Employee->Extension = trim(DBO()->Employee->Extension->Value);
			DBO()->Employee->Extension->ValidateProperty($arrValidationErrors, false, CONTEXT_DEFAULT);

			DBO()->Employee->Phone = trim(DBO()->Employee->Phone->Value);
			DBO()->Employee->Phone->ValidateProperty	($arrValidationErrors, false, CONTEXT_DEFAULT, "IsValidPhoneNumber");

			DBO()->Employee->Mobile = trim(DBO()->Employee->Mobile->Value);
			DBO()->Employee->Mobile->ValidateProperty	($arrValidationErrors, false, CONTEXT_DEFAULT, "IsValidPhoneNumber");

			// Check that the password has been entered and confirmed, as appropriate
			$this->_ValidatePassword($arrValidationErrors, $bolCreateNew);
			
			if (!$bolEditSelf && $bolAdminUser)
			{
				// Sanitize the permissions that have been set
				$this->_SetPrivileges();
			}
			
			if (!$bolCreateNew)
			{
				// Restrict the fields that can be updated
				$updatedColumns = array();
				$updatedColumns[] = "Email";
				$updatedColumns[] = "Extension";
				$updatedColumns[] = "Phone";
				$updatedColumns[] = "Mobile";
				
				// Only change the following through the admin console, not when editing self
				if (!$bolEditSelf && $bolAdminUser)
				{
					$updatedColumns[] = "FirstName";
					$updatedColumns[] = "LastName";
					$updatedColumns[] = "DOB";
					$updatedColumns[] = "Archived";
					$updatedColumns[] = "Privileges";
				}

				// If changing the password, allow  it to be updated
				if (!DBO()->Employee->Password->IsInvalid() && strlen(DBO()->Employee->Password->Value) > 0)
				{
					$updatedColumns[] = "PassWord";
				}

				// Fill in any blanks from the original
				DBO()->Employee->LoadMerge();
				
				// Need to specifiy the columns to be updated to ensure no other values are changed
				DBO()->Employee->SetColumns($updatedColumns);
			}
			else
			{
				DBO()->Employee->DOB = GetCurrentDateForMySQL();
				
				// Apply default values for non-nullable fields
				DBO()->Employee->SessionId = "";
				DBO()->Employee->SessionExpire = GetCurrentDateAndTimeForMySQL();
				DBO()->Employee->Session = "";
				DBO()->Employee->Karma = 0;
				DBO()->Employee->PabloSays = PABLO_TIP_POLITE;
				DBO()->Employee->Archived = 0;
			}
			
			//Save the employee
			if (!DBO()->Employee->IsInvalid())
			{
				//echo "Employee is NOT invalid Employee would be saved";
				if (DBO()->Employee->Save())
				{
					$scriptInit = "";
					$scriptOnClose = "";
					if ($bolEditSelf)
					{
						$scriptInit = "document.getElementsByTagName('TABLE')[0].style.display = 'none';";
						$scriptOnClose .= "Vixen.Popup.Close('CloseFlexModalWindow');";
					}
					else
					{
						$scriptInit .= "Vixen.Popup.Close('" . $this->_objAjax->strId . "');";
						//$scriptInit .= "EmployeeView.Update();";
						$scriptOnClose .= "EmployeeView.Update();";
					}

					$arrParams = array();
					$arrParams["Message"] = "The information was successfully saved.";
					$arrParams["ScriptInit"] = $scriptInit;
					$arrParams["ScriptOnClose"] = $scriptOnClose;

					Ajax()->AddCommand("AlertAndExecuteJavascript", $arrParams);
					return TRUE;
				}
			}
			
			Ajax()->AddCommand("Alert", "The information could not be saved.<br />Please correct the following: -<br />" . implode("<br />\n", $arrValidationErrors));
			return TRUE;			
		}
		DBO()->Employee->Load();
		DBO()->Employee->EditSelf = $bolEditSelf;
		error_log("\$bolEditSelf = " . $bolEditSelf);
		
		if (!$this->IsModal())
		{
			$this->LoadPage('employee_edit');			
		}
		else
		{
			$this->LoadPage('flex_modal_window');
			
			// set the page title
			$this->Page->SetName('Employee Detail');

			$this->Page->AddObject('EmployeeEdit', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);
			
		}
		
		return TRUE;
	}


	//------------------------------------------------------------------------//
	// Create
	//------------------------------------------------------------------------//	
	/**
	 * Create()
	 * 
	 * Creates a new user
	 *
	 * @param void
	 * 
	 * @return boolean	TRUE
	 * 
	 * @method
	 */
	function Create()
	{
		 return $this->Edit();
	}
	
	//------------------------------------------------------------------------//
	// _SetPrivileges
	//------------------------------------------------------------------------//	
	/**
	 * _SetPrivileges()
	 * 
	 * Set the privileges on the user
	 * 
	 * Set the privileges on the user, converting from an array to a single value
	 * and preserving any privileges that should not be changed by the current user
	 *
	 * @param void
	 * 
	 * @return void
	 * 
	 * @method
	 */
	private function _SetPrivileges()
	{
		$proposedPrivileges = array_sum(DBO()->Employee->Privileges->Value);
		$originalPrivileges = 0;
		if (DBO()->Employee->Id->Value >= 0)
		{
			// Need to get the current privileges of the user
			DBO()->CurrentEmployee->SetTable("Employee");
			DBO()->CurrentEmployee->Id = DBO()->Employee->Id->Value;
			DBO()->CurrentEmployee->Load();
			$originalPrivileges = DBO()->CurrentEmployee->Privileges->Value;
		}
		
		// Don't allow super admin, god or debug privileges to be changed
		$proposed = $this->_PreservePrivileges($originalPrivileges, $proposedPrivileges, PERMISSION_SUPER_ADMIN | PERMISSION_DEBUG);
		
		// If user is not an admin, don't allow them to change the rate admin, credit card or admin privileges
		if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			$proposed = $this->_PreservePrivileges($originalPrivileges, $proposedPrivileges, PERMISSION_CREDIT_CARD | PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		}
		
		DBO()->Employee->Privileges = $proposed;
	}
	
	//------------------------------------------------------------------------//
	// _PreservePrivileges
	//------------------------------------------------------------------------//	
	/**
	 * _PreservePrivileges()
	 * 
	 * Preserve privileges that exist in an original set and prevent addition in a proposed set
	 * 
	 * @param int $original		Original set of privileges containing those to be preserved
	 * @param int $proposed		Proposed set of privileges
	 * @param int $privileges	Privileges to be preserved
	 * 
	 * @return int privileges containing only those $privileges that existed in $original privileges
	 * 
	 * @method
	 */
	private function _PreservePrivileges($original, $proposed, $privileges)
	{
		// Prevent the privilege being added by proposition
		$proposed = $proposed - ($proposed & $privileges);
		// Add the privileges if originally present
		$proposed = $proposed | ($original & $privileges);
		// Return the proposed privileges with the preserved values
		return $proposed;
	}
	
	
	//------------------------------------------------------------------------//
	// _ValidatePassword
	//------------------------------------------------------------------------//	
	/**
	 * _ValidatePassword()
	 * 
	 * Validates the passwords array stored in the DBO()->Employee->Password property 
	 * 
	 * @param array		&$arrValidationErrors	Array to which and error messages will be added
	 * @param boolean	$bolCreateNew			Whether the validation is for a new user or not
	 * 
	 * @return void
	 * 
	 * @method
	 */	
	private function _ValidatePassword(&$arrValidationErrors, $bolCreateNew)
	{
		// Validate that the password has been submitted as a 2 value array
		if (!is_array(DBO()->Employee->Password->Value) || count(DBO()->Employee->Password->Value) != 2)
		{
			DBO()->Employee->Password = "";
			DBO()->Employee->Password->SetToInvalid();
			$arrValidationErrors[] = "Password must be entered twice.";
		}
		// Check that neither value is empty
		else if (strlen(DBO()->Employee->Password->Value[0]) == 0 || strlen(DBO()->Employee->Password->Value[1]) == 0)
		{
			$bolPasswordEntered = (strlen(DBO()->Employee->Password->Value[0]) + strlen(DBO()->Employee->Password->Value[1])) > 0;
			DBO()->Employee->Password = "";
			if ($bolCreateNew || $bolPasswordEntered)
			{
				DBO()->Employee->Password->SetToInvalid();
				$arrValidationErrors[] = "Both Password and Password Confirmation are required.";
			}
		}
		// Check that the values are the same
		else if (DBO()->Employee->Password->Value[0] != DBO()->Employee->Password->Value[1])
		{
			DBO()->Employee->Password = "";
			DBO()->Employee->Password->SetToInvalid();
			$arrValidationErrors[] = "Password does not match Password Confirmation.";
		}
		// Set the validated password value into the password property
		DBO()->Employee->PassWord = sha1(DBO()->Employee->Password->Value[0]);
		DBO()->Employee->Password = DBO()->Employee->Password->Value[0];
	}

}
?>
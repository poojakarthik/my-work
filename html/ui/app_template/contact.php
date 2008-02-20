<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// contact
//----------------------------------------------------------------------------//
/**
 * contact
 *
 * contains all ApplicationTemplate extended classes relating to contact functionality
 *
 * contains all ApplicationTemplate extended classes relating to contact functionality
 *
 * @file		contact.php
 * @language	PHP
 * @package		framework
 * @author		Sean, Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateContact
//----------------------------------------------------------------------------//
/**
 * AppTemplateContact
 *
 * The AppTemplateContact class
 *
 * The AppTemplateContact class.  This incorporates all logic for all pages
 * relating to contacts
 *
 *
 * @package	ui_app
 * @class	AppTemplateContact
 * @extends	ApplicationTemplate
 */
class AppTemplateContact extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the contact_view.php webpage
	 * 
	 * Performs the logic for the contact_view.php webpage
	 *
	 * @return		void
	 * @method		View
	 *
	 */
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		// Breadcrumb menu
				
		// Setup all DBO and DBL objects required for the page
		
		//EXAMPLE:
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Contact->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
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
		
		// Retrieve all accounts that the contact is allowed to view
		if (DBO()->Contact->CustomerContact->Value)
		{
			// retrieve all accounts from the contact's account group
			DBL()->Account->AccountGroup = DBO()->Contact->AccountGroup->Value;
		}
		else
		{
			DBL()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		DBL()->Account->Archived = 0;
		
		DBL()->Account->Load();
		
		foreach (DBL()->Account as $dboAccount)
		{
			// Calculate the Account Overdue Amount
			$dboAccount->Overdue = $this->Framework->GetOverdueBalance($dboAccount->Id->Value);
		}
		
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('contact_view');


		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Edit
	//------------------------------------------------------------------------//
	/**
	 * Edit()
	 *
	 * Performs the logic for the contact_edit.php webpage
	 * 
	 * Performs the logic for the contact_edit.php webpage
	 *
	 * @return		void
	 * @method		Edit
	 *
	 */
	function Edit()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// handle form submittion if the user is editing an existing contact
		if (SubmittedForm("EditContact", "Apply Changes"))
		{
			// The user is trying to edit an existing contact
			if (DBO()->Contact->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "Could not save the contact.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_EDIT, "ContactEditDiv");
				return TRUE;
			}
			
			// The form passed initial validation, however we must manually perform validation for the Phone and Mobile properties
			$bolPhonePresent = Validate("IsNotEmptyString", DBO()->Contact->Phone->Value);
			$bolMobilePresent = Validate("IsNotEmptyString", DBO()->Contact->Mobile->Value);
			if (!$bolPhonePresent && !$bolMobilePresent)
			{
				// Neither a phone number nor mobile number were specified
				
				// Flag the properties as being invalid
				DBO()->Contact->Phone->SetToInvalid();
				DBO()->Contact->Mobile->SetToInvalid();
				
				Ajax()->AddCommand("Alert", "Please include either a phone number or mobile number");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_EDIT, "ContactEditDiv");
				Ajax()->AddCommand("SetFocus", "Contact.Phone");
				return TRUE;
			}
			
			// Phone or Mobile or both are present.  Now make sure they are valid numbers
			$bolPhoneValid = Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value);
			$bolMobileValid = Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value);
			if ($bolPhonePresent)
			{
				DBO()->Contact->Phone = trim(DBO()->Contact->Phone->Value);
				if(!Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value))
				{
					DBO()->Contact->Phone->SetToInvalid();
					Ajax()->AddCommand("Alert", "Invalid phone number");
					Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_EDIT, "ContactEditDiv");
					Ajax()->AddCommand("SetFocus", "Contact.Phone");
					return TRUE;
				}
			}
			if ($bolMobilePresent)
			{
				DBO()->Contact->Mobile = trim(DBO()->Contact->Mobile->Value);
				if(!Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value))
				{
					DBO()->Contact->Mobile->SetToInvalid();
					Ajax()->AddCommand("Alert", "Invalid mobile number");
					Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_EDIT, "ContactEditDiv");
					Ajax()->AddCommand("SetFocus", "Contact.Mobile");
					return TRUE;
				}
			}
			
			// Check that the contact's username is not currently being used by another contact
			$strWhere = "UserName LIKE \"". DBO()->Contact->UserName->Value ."\" AND Id != " . DBO()->Contact->Id->Value;
			DBL()->Contact->Where->SetString($strWhere);
			DBL()->Contact->Load();
			if (DBL()->Contact->RecordCount() > 0)
			{
				// the username is currently being used by another contact.
				DBO()->Contact->UserName->SetToInvalid();
				Ajax()->AddCommand("Alert", "This username is currently being used by another contact");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_EDIT, "ContactEditDiv");
				Ajax()->AddCommand("SetFocus", "Contact.UserName");
				return TRUE;
			}
			
			// Everything has been validated on the form, so commit it to the database
			
			// Convert the DOB to the standard MySql Date format
			DBO()->Contact->DOB = ConvertUserDateToMySqlDate(DBO()->Contact->DOB->Value);

			// Set which columns to update in the Contact table
			if (Validate("IsNotEmptyString", DBO()->Contact->PassWord->Value))
			{
				// A new password has been declared.  Hash it.
				DBO()->Contact->PassWord = sha1(DBO()->Contact->PassWord->Value);
				$strIncludePasswordProperty = ", PassWord";
			}
			else
			{
				// don't include the password when updating the contact's details in the database
				$strIncludePasswordProperty = "";
			}
			$strColumnsToUpdate = "Title, FirstName, LastName, DOB, JobTitle, Email, CustomerContact, Phone, Mobile, Fax, UserName, Archived $strIncludePasswordProperty";
			DBO()->Contact->SetColumns($strColumnsToUpdate);
			if (!DBO()->Contact->Save())
			{
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Updating the contact details failed, unexpectedly", "Location" => Href()->ViewContact(DBO()->Contact->Id->Value)));
				return TRUE;
			}
			
			// The contact details were successfully saved so go back to the last page
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The contact's details were successfully updated", "Location" => Href()->ViewContact(DBO()->Contact->Id->Value)));
			return TRUE;
		}
		
		// Load the page
		// If a form hasn't been submitted then we are displaying the page for the first time, and need to load the contact
		if (!(SubmittedForm("EditContact")) && (!DBO()->Contact->Load()))
		{
			DBO()->Error->Message = "The contact with contact id: ". DBO()->Contact->Id->Value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Load context menu items specific to the Edit Contact page
		//TODO! What would go here anyway?
	
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
		
		// Bread Crumb Menu
		BreadCrumb()->View_Contact(DBO()->Contact->Id->Value);
		
		// Declare which page to use
		$this->LoadPage('contact_edit');

		return TRUE;
	}	

	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the contact_add.php webpage
	 * 
	 * Performs the logic for the contact_add.php webpage
	 *
	 * @return		void
	 * @method		view
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// handle form submittion if the user is adding a contact
		if (SubmittedForm("AddContact", "Add Contact"))
		{
			// The user is adding a contact
			if (DBO()->Contact->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "Could not save the contact.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");
				return TRUE;
			}
		
			// The form passed initial validation, however we must manually perform validation for the Phone and Mobile properties
			$bolPhonePresent = Validate("IsNotEmptyString", DBO()->Contact->Phone->Value);
			$bolMobilePresent = Validate("IsNotEmptyString", DBO()->Contact->Mobile->Value);
			if (!$bolPhonePresent && !$bolMobilePresent)
			{
				// Neither a phone number nor mobile number were specified
				
				// Flag the properties as being invalid
				DBO()->Contact->Phone->SetToInvalid();
				DBO()->Contact->Mobile->SetToInvalid();
				
				Ajax()->AddCommand("Alert", "Please include either a phone number or mobile number");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");
				Ajax()->AddCommand("SetFocus", "Contact.Phone");
				return TRUE;
			}
			
			// Phone or Mobile or both are present.  Now make sure they are valid numbers
			$bolPhoneValid = Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value);
			$bolMobileValid = Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value);
			if ($bolPhonePresent)
			{
				DBO()->Contact->Phone = trim(DBO()->Contact->Phone->Value);
				if(!Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value))
				{
					DBO()->Contact->Phone->SetToInvalid();
					Ajax()->AddCommand("Alert", "Invalid phone number");
					Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");
					Ajax()->AddCommand("SetFocus", "Contact.Phone");
					return TRUE;
				}
			}
			if ($bolMobilePresent)
			{
				DBO()->Contact->Mobile = trim(DBO()->Contact->Mobile->Value);
				if(!Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value))
				{
					DBO()->Contact->Mobile->SetToInvalid();
					Ajax()->AddCommand("Alert", "Invalid mobile number");
					Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");
					Ajax()->AddCommand("SetFocus", "Contact.Mobile");
					return TRUE;
				}
			}
			
			// Check that the contact's username is not currently being used by another contact
			$strWhere = "UserName LIKE '". DBO()->Contact->UserName->Value ."'";
			DBL()->Contact->Where->SetString($strWhere);
			DBL()->Contact->Load();
			if (DBL()->Contact->RecordCount() > 0)
			{
				// the username is currently being used by another contact.
				DBO()->Contact->UserName->SetToInvalid();
				Ajax()->AddCommand("Alert", "This username is currently being used by another contact");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");
				Ajax()->AddCommand("SetFocus", "Contact.UserName");
				return TRUE;
			}

			// Validate the password
			$bolIsValidPassword = Validate("IsNotEmptyString", DBO()->Contact->PassWord->Value);
			if (!$bolIsValidPassword)
			{
				// the password is invalid
				DBO()->Contact->PassWord->SetToInvalid();
				Ajax()->AddCommand("Alert", "The current password is invalid");
				Ajax()->RenderHtmlTemplate("ContactEdit", HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");
				Ajax()->AddCommand("SetFocus", "Contact.PassWord");
				return TRUE;
			}

			// Everything has been validated on the form, so insert it into the database
			
			// Convert the DOB to the standard MySql Date format
			DBO()->Contact->DOB = ConvertUserDateToMySqlDate(DBO()->Contact->DOB->Value);
			
			// Hash the password
			DBO()->Contact->PassWord = sha1(DBO()->Contact->PassWord->Value);

			// Set values for the properties of the Contact object that do not already have values set
			DBO()->Contact->SessionId = "";
			DBO()->Contact->SessionExpire = "";
			
			// Load the Account object
			if (!DBO()->Account->Load())
			{
				$strErrorMessage = "ERROR: The account with account id: ". DBO()->Account->Id->Value ." could not be found";
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => $strErrorMessage, "Location" => Href()->EmployeeConsole()));
				return TRUE;
			}
			
			// set the contact's account details
			DBO()->Contact->Account = DBO()->Account->Id->Value;
			DBO()->Contact->AccountGroup = DBO()->Account->AccountGroup->Value;
			
			// save the new contact
			if (!DBO()->Contact->Save())
			{
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: Saving the new contact failed, unexpectedly", "Location" => Href()->ViewAccount(DBO()->Account->Id->Value)));
				return TRUE;
			}
			
			// The new contact was successfully saved so go back to the view account page
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The new contact was successfully saved", "Location" => Href()->ViewAccount(DBO()->Account->Id->Value)));
			return TRUE;
		}
		
		// Load the page
		// Load the Account object which this contact will be associated with
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The Account with Account id: ". DBO()->Account->Id->Value ." could not be found.";
			$this->LoadPage('error');
			return FALSE;
		}

		// Load context menu items specific to the Edit Contact page
		//TODO!
		
		if ($bolUserHasAdminPerm)
		{
			// User must have admin permissions to view the Administrative Console
			ContextMenu()->Admin_Console();
		}
		ContextMenu()->Logout();
		
		// Bread Crumb Menu
		BreadCrumb()->View_Account(DBO()->Account->Id->Value);
		
		// Declare which page to use
		$this->LoadPage('contact_add');
	
		return TRUE;
	}


	//------------------------------------------------------------------------//
	// SetPrimaryForAccount
	//------------------------------------------------------------------------//
	/**
	 * SetPrimaryForAccount()
	 *
	 * Handles the logic for updating the primary contact of an Account
	 * 
	 * Handles the logic for updating the primary contact of an Account
	 * It assumes:
	 *			DBO()->PrimaryContact->Id	defines the new primary contact for the account
	 *			DBO()->Account->Id			defines the account of which the Primary Contact is being set
	 * If the Account's primary contact is successfully updated, a system note is generated
	 * This method fires the OnAccountPrimaryContactUpdate and OnNewNote events, when appropriate
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetPrimaryForAccount()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		DBO()->Account->Load();

		// Load and validate the Contact
		DBO()->PrimaryContact->SetTable("Contact");
		$strErrorMessage = "";
		if (!DBO()->PrimaryContact->Load())
		{
			$strErrorMessage = "ERROR: This contact could not be found in the database";
		}
		elseif (DBO()->PrimaryContact->Archived->Value == 1)
		{	
			$strErrorMessage = "ERROR: This contact is currently archived";
		}
		elseif (DBO()->PrimaryContact->CustomerContact->Value)
		{
			// The contact can view any accounts associated with the AccountGroup that the contact is associated with
			if (DBO()->PrimaryContact->AccountGroup->Value != DBO()->Account->AccountGroup->Value)
			{
				$strErrorMessage = "ERROR: This contact is not associated with this Account Group";
			}
		}
		else
		{
			// The contact can only view the 1 account
			if (DBO()->PrimaryContact->Account->Value != DBO()->Account->Id->Value)
			{
				$strErrorMessage = "ERROR: This contact can only view Account ". DBO()->PrimaryContact->Account->Value ." and therefore cannot be made the primary contact of this account";
			}
		}
		
		if ($strErrorMessage)
		{
			// The contact is invalid.  Exit gracefully
			Ajax()->AddCommand("Alert", $strErrorMessage);
			return TRUE;
		}
		
		if (DBO()->Account->PrimaryContact->Value == DBO()->PrimaryContact->Id->Value)
		{
			// The contact is already the primary contact
			$strErrorMessage = "This contact is already the primary contact for this account";
		}
		if ($strErrorMessage)
		{
			Ajax()->AddCommand("Alert", $strErrorMessage);
			return TRUE;
		}
		
		// Store a reference to the old primary contact
		DBO()->OldPrimaryContact->Id = DBO()->Account->PrimaryContact->Value;
		
		// Update the PrimaryContact for the account
		DBO()->Account->SetColumns("PrimaryContact");
		DBO()->Account->PrimaryContact = DBO()->PrimaryContact->Id->Value;
		
		if (!DBO()->Account->Save())
		{
			// Updating the account record failed, unexpectedly.  Exit gracefully
			Ajax()->AddCommand("Alert", "ERROR: Updating the primary contact failed, unexpectedly");
			return TRUE;
		}
		
		// Load the details of the old primary contact, if there was one
		$strOldContact = NULL;
		if (DBO()->OldPrimaryContact->Id->Value)
		{
			DBO()->OldPrimaryContact->SetTable("Contact");
			if (DBO()->OldPrimaryContact->Load())
			{
				// The old contact was found
				$strOldContact = ucwords(strtolower(trim(DBO()->OldPrimaryContact->Title->Value ." ". DBO()->OldPrimaryContact->FirstName->Value ." ". DBO()->OldPrimaryContact->LastName->Value)));
			}
		}
		
		// Build the contact name for the new primary contact
		$strNewContact = ucwords(strtolower(trim(DBO()->PrimaryContact->Title->Value ." ". DBO()->PrimaryContact->FirstName->Value ." ". DBO()->PrimaryContact->LastName->Value)));
		
		// Build the system note
		if ($strOldContact)
		{
			$strNote = "The primary contact has been changed from $strOldContact to $strNewContact";
		}
		else
		{
			$strNote = "The primary contact has been changed to $strNewContact";
		}
		
		// Save the system note
		$bolNoteSaved = SaveSystemNote($strNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value);
		
		// The primary contact has been successfully changed
		Ajax()->AddCommand("Alert", "The primary contact has been successfully changed");
		
		// Fire Events
		$arrEvent['Account']['Id'] = DBO()->Account->Id->Value;
		Ajax()->FireEvent(EVENT_ON_ACCOUNT_PRIMARY_CONTACT_UPDATE, $arrEvent);
		
		if ($bolNoteSaved)
		{
			Ajax()->FireOnNewNoteEvent(DBO()->Account->Id->Value);
		}
		return TRUE;
	}

	//----- DO NOT REMOVE -----//
}
?>
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
// AppTemplatecontact
//----------------------------------------------------------------------------//
/**
 * AppTemplatecontact
 *
 * The AppTemplatecontact class
 *
 * The AppTemplatecontact class.  This incorporates all logic for all pages
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
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		
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
		
		// Context menu
		ContextMenu()->Contact_Retrieve->Notes->View_Contact_Notes(DBO()->Contact->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Contact_Note(DBO()->Contact->Id->Value);
		ContextMenu()->Contact_Retrieve->Edit_Contact(DBO()->Contact->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Associated_Account(DBO()->Contact->Account->Value);
		ContextMenu()->Admin_Console();
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
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		// handle form submittion if the user is editing an existing contact
		if (SubmittedForm("EditContact", "Apply Changes"))
		{
			// The user is trying to edit an existing contact
			// Entered via url http://localhost/ross/vixen/intranet_app/vixen.php/Contact/Edit/?Contact.Id=14286
			if (!DBO()->Contact->IsInvalid())
			{
				// The form passed initial validation, however we must manually perform validation for the Phone and Mobile properties
				$bolPhonePresent = Validate("IsNotEmptyString", DBO()->Contact->Phone->Value);
				$bolMobilePresent = Validate("IsNotEmptyString", DBO()->Contact->Mobile->Value);
				if (!$bolPhonePresent && !$bolMobilePresent)
				{
					// Neither a phone number nor mobile number were specified
					
					DBO()->Status->Message = "Please include either a phone number or mobile number";
					
					// Flag the properties as being invalid
					DBO()->Contact->Phone->SetToInvalid();
					//DBO()->Contact->Mobile->SetToInvalid();
					DBO()->Contact->SetToInvalid();
					
					// Set up the remaining page characteristics
					return $this->_LoadEditContactPage();
				}
				
				// Phone or Mobile or both are present.  Now make sure they are valid numbers
				$bolPhoneValid = Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value);
				$bolMobileValid = Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value);
				if ($bolPhonePresent)
				{
					DBO()->Contact->Phone = trim(DBO()->Contact->Phone->Value);
					if(!Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value))
					{
						DBO()->Status->Message = "invalid phone number";
						DBO()->Contact->Phone->SetToInvalid();
						return $this->_LoadEditContactPage();
					}
				}
				if ($bolMobilePresent)
				{
					DBO()->Contact->Mobile = trim(DBO()->Contact->Mobile->Value);
					if(!Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value))
					{
						DBO()->Status->Message = "invalid mobile number";
						DBO()->Contact->Mobile->SetToInvalid();
						return $this->_LoadEditContactPage();
					}
				}
				
				// Check that the contact's username is not currently being used by another contact
				$strWhere = "UserName LIKE \"". DBO()->Contact->UserName->Value ."\" AND Id != " . DBO()->Contact->Id->Value;
				DBL()->Contact->Where->SetString($strWhere);
				DBL()->Contact->Load();
				if (DBL()->Contact->RecordCount() > 0)
				{
					// the username is currently being used by another contact.
					DBO()->Status->Message = "This username is currently being used by another contact";
					DBO()->Contact->UserName->SetToInvalid();
					return $this->_LoadEditContactPage();
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
					DBO()->Contact->SetToInvalid();
					DBO()->Status->Message = "Updating the contact details failed";
					return $this->_LoadEditContactPage();
				}
				
				// The contact details were successfully loaded so go back to the last page
				//TODO!
				// I think eventually this method will be executed via AjaxLoad, in which case specifying a new page to load is easy.
				// But first we have to work out how to load a page in javascript, if you have the html for the page stored as a string.
				// This will be handled by an ajax command in the ReplyHandler defined in ajax.js
				
				//HACK! HACK! HACK! 
				// Possibly use ajax to display success message and then re-direct?
				DBO()->Status->Message = "The Contact's details were successfully updated.";
				DBO()->Status->FormSubmitted = TRUE;
				return $this->_LoadEditContactPage();
				//HACK! HACK! HACK! 
			}
			else
			{
				// Some of the fields were invalid
				DBO()->Status->Message = "Could not save the contact.  Invalid fields are highlighted.";
				return $this->_LoadEditContactPage();
			}
		}
		
		// Load the page
		return $this->_LoadEditContactPage();
	}	
	
	// Handles loading DB objects, context menu, breadcrumb menu, for the edit contact page
	private function _LoadEditContactPage()
	{
		// If a form hasn't been submitted then we are displaying the page for the first time, and need to load the contact
		if (!(SubmittedForm("EditContact")) && (!DBO()->Contact->Load()))
		{
			DBO()->Error->Message = "The contact with contact id:". DBO()->Contact->Id->Value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Load context menu items specific to the Edit Contact page
		//TODO!
		
		// Declare which page to use
		$this->LoadPage('contact_edit');

	
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		return TRUE;
	}

	// Handles loading DB objects, context menu, breadcrumb menu, for the add contact page
	private function _LoadAddContactPage()
	{
		// Load the Account object which this contact will be associated with
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The Account with Account id:". DBO()->Account->Id->Value ."could not be found.";
			$this->LoadPage('error');
			return FALSE;
		}

		// Load context menu items specific to the Edit Contact page
		//TODO!
		
		// Declare which page to use
		$this->LoadPage('contact_add');
	
		// Context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
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
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		// handle form submittion if the user is adding a contact
		if (SubmittedForm("AddContact", "Add Contact"))
		{
			// The user is adding a contact
			// Entered via url http://localhost/ross/vixen/intranet_app/vixen.php/Contact/Edit/
			if (!DBO()->Contact->IsInvalid())
			{
				// The form passed initial validation, however we must manually perform validation for the Phone and Mobile properties
				$bolPhonePresent = Validate("IsNotEmptyString", DBO()->Contact->Phone->Value);
				$bolMobilePresent = Validate("IsNotEmptyString", DBO()->Contact->Mobile->Value);
				if (!$bolPhonePresent && !$bolMobilePresent)
				{
					// Neither a phone number nor mobile number were specified
					
					DBO()->Status->Message = "Please include either a phone number or mobile number";
					
					// Flag the properties as being invalid
					DBO()->Contact->Phone->SetToInvalid();
					//DBO()->Contact->Mobile->SetToInvalid();
					DBO()->Contact->SetToInvalid();
					
					// Set up the remaining page characteristics
					return $this->_LoadAddContactPage();
				}
				
				// Phone or Mobile or both are present.  Now make sure they are valid numbers
				$bolPhoneValid = Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value);
				$bolMobileValid = Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value);
				if ($bolPhonePresent)
				{
					DBO()->Contact->Phone = trim(DBO()->Contact->Phone->Value);
					if(!Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value))
					{
						DBO()->Status->Message = "invalid phone number";
						DBO()->Contact->Phone->SetToInvalid();
						return $this->_LoadAddContactPage();
					}
				}
				if ($bolMobilePresent)
				{
					DBO()->Contact->Mobile = trim(DBO()->Contact->Mobile->Value);
					if(!Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value))
					{
						DBO()->Status->Message = "invalid mobile number";
						DBO()->Contact->Mobile->SetToInvalid();
						return $this->_LoadAddContactPage();
					}
				}
				
				// Check that the contact's username is not currently being used by another contact
				$strWhere = "UserName LIKE '". DBO()->Contact->UserName->Value ."'";
				DBL()->Contact->Where->SetString($strWhere);
				DBL()->Contact->Load();
				if (DBL()->Contact->RecordCount() > 0)
				{
					// the username is currently being used by another contact.
					DBO()->Status->Message = "This username is currently being used by another contact";
					DBO()->Contact->UserName->SetToInvalid();
					return $this->_LoadAddContactPage();
				}

				// Validate the password
				$bolIsValidPassword = Validate("IsNotEmptyString", DBO()->Contact->PassWord->Value);
				if (!$bolIsValidPassword)
				{
					// the password is invalid
					DBO()->Status->Message = "The current password is invalid.";
					DBO()->Contact->PassWord->SetToInvalid();
					return $this->_LoadAddContactPage();
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
					DBO()->Error->Message = "The Account with Account id:". DBO()->Account->Id->Value ."could not be found.";
					$this->LoadPage('error');
					return FALSE;
				}
				
				DBO()->Contact->Account = DBO()->Account->Id->Value;
				DBO()->Contact->AccountGroup = DBO()->Account->AccountGroup->Value;
				
				if (!DBO()->Contact->Save())
				{
					DBO()->Contact->SetToInvalid();
					DBO()->Status->Message = "Adding this contact failed";
					return $this->_LoadAddContactPage();
				}
				
				// The contact details were successfully saved so go back to the last page
				//TODO!
				// I think eventually this method will be executed via AjaxLoad, in which case specifying a new page to load is easy.
				// But first we have to work out how to load a page in javascript, if you have the html for the page stored as a string.
				// This will be handled by an ajax command in the ReplyHandler defined in ajax.js
				
				//HACK! HACK! HACK! 
				// Possibly use ajax to display success message and then re-direct?
				DBO()->Status->Message = "The new contact was successfully saved.  You should now be redirected to the last page you were at";
				return $this->_LoadAddContactPage();
				//HACK! HACK! HACK! 
			}
			else
			{
				// Some of the fields were invalid
				DBO()->Status->Message = "Could not save the contact.  Invalid fields are highlighted.";
				return $this->_LoadAddContactPage();
			}
		}
		
		// Load the page
		return $this->_LoadAddContactPage();
	}

	//----- DO NOT REMOVE -----//
	
}

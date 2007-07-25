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
 * @class	AppTemplatecontact
 * @extends	ApplicationTemplate
 */
class AppTemplateContact extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// view
	//------------------------------------------------------------------------//
	/**
	 * view()
	 *
	 * Performs the logic for the contact_view.php webpage
	 * 
	 * Performs the logic for the contact_view.php webpage
	 *
	 * @return		void
	 * @method		view
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
	// edit
	//------------------------------------------------------------------------//
	/**
	 * edit()
	 *
	 * Performs the logic for the contact_edit.php webpage
	 * 
	 * Performs the logic for the contact_edit.php webpage
	 *
	 * @return		void
	 * @method		view
	 *
	 */
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

		// handle form submittion if the user is editing an existing contact
		if (SubmittedForm("EditContact", "Apply Changes"))
		{
			// The user is trying to edit an existing contact
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
					DBO()->Contact->Mobile->SetToInvalid();
					DBO()->Contact->SetToInvalid();
					
					
				}
				else
				{
					// Phone or Mobile or both are present.  Now make sure they are valid numbers
					$bolPhoneValid = Validate("IsValidPhoneNumber", DBO()->Contact->Phone->Value);
					$bolMobileValid = Validate("IsValidMobileNumber", DBO()->Contact->Mobile->Value);
					if ($bolPhonePresent)
					{
						Validate("IsValidePhoneNumber", DBO()->Contact->Phone->Value);
					}
					// Work out what needs to be set to update a contact
					//TODO!
					// Work out what needs to be set to add a new contact
					//TODO!
					DBO()->Status->Message = "Everything is A Okay";
				}
			}
			else
			{
				// Some of the fields were invalid
				DBO()->Status->Message = "Could not save the contact.  Invalid fields are highlighted.";
			}
		}
		
		// handle form submittion if the user is editing an existing contact
		if (SubmittedForm("EditContact", "Add Contact"))
		{
			//TODO!
		}
		
		
		// Breadcrumb menu
				
		// Setup all DBO and DBL objects required for the page
		
		// Check if we are editing an existing contact or adding a new one
		if (DBO()->Contact->Id->Value)
		{
			// we are editing an existing contact
			// If a form hasn't been submitted then we are displaying the page for the first time, and need to load the contact
			if (!(SubmittedForm("EditContact")) && (!DBO()->Contact->Load()))
			{
				DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
				$this->LoadPage('error');
				return FALSE;
			}
			$this->LoadPage('contact_edit');
		}
		else
		{
			//we are adding a new contact
			$this->LoadPage('contact_add');
		}

	
		// Context menu
		ContextMenu()->Contact_Retrieve->Notes->View_Contact_Notes(DBO()->Contact->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Contact_Note(DBO()->Contact->Id->Value);
		ContextMenu()->Contact_Retrieve->Edit_Contact(DBO()->Contact->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Associated_Account(DBO()->Contact->Account->Value);
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		

		return TRUE;
	}	
	
	//----- DO NOT REMOVE -----//
	
}

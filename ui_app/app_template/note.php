<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// note.php
//----------------------------------------------------------------------------//
/**
 * note
 *
 * contains all ApplicationTemplate extended classes relating to Note functionality
 *
 * contains all ApplicationTemplate extended classes relating to Note functionality
 *
 * @file		note.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateNote
//----------------------------------------------------------------------------//
/**
 * AppTemplateNote
 *
 * The AppTemplateNote class
 *
 * The AppTemplateNote class.  This incorporates all logic for all pages
 * relating to notes
 *
 *
 * @package	ui_app
 * @class	AppTemplateNote
 * @extends	ApplicationTemplate
 */
class AppTemplateNote extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the View Notes popup window
	 * 
	 * Performs the logic for the View Notes popup window
	 *
	 * @return		void
	 * @method
	 *
	 */
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// Setup all DBO and DBL objects required for the page
		
		// Check what sort of notes you want to retrieve (ie Account notes, Contact Notes or Service Notes)
		switch (DBO()->Note->NoteClass->Value)
		{
			case NOTE_CLASS_ACCOUNT_NOTES:
				$strWhere = "Account=" . DBO()->Note->NoteGroupId->Value;
				break;
			case NOTE_CLASS_CONTACT_NOTES:
				$strWhere = "Contact=" . DBO()->Note->NoteGroupId->Value;
				break;
			case NOTE_CLASS_SERVICE_NOTES:
				$strWhere = "Service=" . DBO()->Note->NoteGroupId->Value;
				break;
			default:
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "ERROR: Note class was not defined");
				return TRUE;
				break;
		}
		
		// Check if the user wants to filter the notes
		if (SubmittedForm("NoteTypeForm"))
		{
			switch (DBO()->Note->NoteType->Value)
			{
				case "All":
					break;
				case "System":
					$strWhere .= " AND NoteType = ". SYSTEM_NOTE;
					break;
				case "User":
					$strWhere .= " AND NoteType != ". SYSTEM_NOTE;
					break;
			}
		}
		
		// Retrieve the notes
		DBL()->Note->Where->SetString($strWhere);
		DBL()->Note->OrderBy("Datetime DESC");
		DBL()->Note->Load();
		DBL()->NoteType->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('view_notes');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// AddAccount
	//------------------------------------------------------------------------//
	/**
	 * AddAccount()
	 *
	 * Performs the logic for the Add Account Note popup window
	 * 
	 * Performs the logic for the Add Account Note popup window
	 *
	 * @return		void
	 * @method
	 *
	 */
	function AddAccount()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("AlertReload", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}
		
		// check if a new note is being submitted
		if (SubmittedForm('AddNote', 'Add Note'))
		{
			// Only add the note if it is not invalid
			if (!DBO()->Note->IsInvalid())
			{
				// Set the properties for the new note
				DBO()->Note->AccountGroup	= DBO()->Account->AccountGroup->Value;
				DBO()->Note->Account		= DBO()->Account->Id->Value;
				
				// User's details
				$dboUser = GetAuthenticatedUserDBObject();
				DBO()->Note->Employee = $dboUser->Id->Value;
				
				// Time stamp
				DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
								
				// DBO()->Note->Note should already be set
				// DBO()->Note->NoteType should already be set
				// DBO()->Note->Contact is not set
				// DBO()->Note->Service is not set
				
				// Save the note to the Note table of the vixen database
				if (!DBO()->Note->Save())
				{
					// The note could not be saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "ERROR: The note did not save.");
					return TRUE;
				}
				else
				{
					// The note was successfully saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The note has been successfully added.");
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				DBO()->Status->Message = "The Note could not be saved. Invalid fields are highlighted.";
			}
		}
		
		// Load DBO and DBL objects required of the page
		// Get all Note Types
		DBL()->AvailableNoteTypes->SetTable("NoteType");
		DBL()->AvailableNoteTypes->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('note_add');

		return TRUE;
	}
	
	function AddContact()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// The account should already be set up as a DBObject
		if (!DBO()->Contact->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("AlertReload", "The contact with contact id: '". DBO()->Contact->Id->value ."' could not be found");
			return TRUE;
		}
		
		// check if a new note is being submitted
		if (SubmittedForm('AddNote', 'Add Note'))
		{
			// Only add the note if it is not invalid
			if (!DBO()->Note->IsInvalid())
			{
				// Set the properties for the new note
				DBO()->Note->AccountGroup	= DBO()->Contact->AccountGroup->Value;
				
				if (DBO()->Note->IsAccountNote->Value)
				{
					DBO()->Note->Account	= DBO()->Contact->Account->Value;
				}
				
				// User's details
				DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
				
				// Time stamp
				DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
				
				// Set the Note's contact
				DBO()->Note->Contact = DBO()->Contact->Id->Value;
								
				// DBO()->Note->Note should already be set
				// DBO()->Note->NoteType should already be set
				// DBO()->Note->Service is not set
				
				// Save the note to the Note table of the vixen database
				if (!DBO()->Note->Save())
				{
					// The note could not be saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "ERROR: The note did not save.");
					return TRUE;
				}
				else
				{
					// The note was successfully saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The note has been successfully added.");
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				DBO()->Status->Message = "The Note could not be saved. Invalid fields are highlighted.";
			}
		}
		
		// Load DBO and DBL objects required of the page
		// Get all Note Types
		DBL()->AvailableNoteTypes->SetTable("NoteType");
		DBL()->AvailableNoteTypes->Load();
		
		// Get the contact's primary account
		DBO()->Account->Id = DBO()->Contact->Account->Value;
		DBO()->Account->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('note_add_contact');

		return TRUE;
	}	

	function AddService()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// The account should already be set up as a DBObject
		if (!DBO()->Service->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("AlertReload", "The service with service id: '". DBO()->Service->Id->Value ."' could not be found");
			return TRUE;
		}
		
		
		// check if a new note is being submitted
		if (SubmittedForm('AddNote', 'Add Note'))
		{
			// Only add the note if it is not invalid
			if (!DBO()->Note->IsInvalid())
			{
				// Set the properties for the new note
				DBO()->Note->AccountGroup	= DBO()->Service->AccountGroup->Value;
				
				if (DBO()->Note->IsAccountNote->Value)
				{
					DBO()->Note->Account	= DBO()->Service->Account->Value;
				}
				
				// User's details
				DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
				
				// Time stamp
				DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();
				
				// Set the Note's contact
				DBO()->Note->Contact = DBO()->Service->Id->Value;
								
				// DBO()->Note->Note should already be set
				// DBO()->Note->NoteType should already be set
				// DBO()->Note->Service is not set
				
				// Save the note to the Note table of the vixen database
				if (!DBO()->Note->Save())
				{
					// The note could not be saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "ERROR: The note did not save.");
					return TRUE;
				}
				else
				{
					// The note was successfully saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "The note has been successfully added.");
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				DBO()->Status->Message = "The Note could not be saved. Invalid fields are highlighted.";
			}
		}
		
		// Load DBO and DBL objects required of the page
		// Get all Note Types
		DBL()->AvailableNoteTypes->SetTable("NoteType");
		DBL()->AvailableNoteTypes->Load();
		
		// Get the services's account details
		DBO()->Account->Id = DBO()->Service->Account->Value;
		DBO()->Account->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('note_add_service');

		return TRUE;
	}	

}

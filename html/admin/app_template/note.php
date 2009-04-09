<?php
//DEPRECATED!
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		// These are used by the LoadNotes function
		$intAccountId = NULL;
		$intServiceId = NULL;
		$intContactId = NULL;

		// Check what sort of note it is
		if (DBO()->Service->Id->IsSet)
		{
			// Service Note
			$intServiceId = DBO()->Service->Id->Value;
			
			// Load the Service
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", "The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}

			// Set the service's primary account
			DBO()->Account->Id = DBO()->Service->Account->Value;
		}
		elseif (DBO()->Contact->Id->IsSet)
		{
			// Contact Note
			$intContactId = DBO()->Contact->Id->Value;
			
			// Load the Contact
			if (!DBO()->Contact->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "The contact with contact id: '". DBO()->Contact->Id->value ."' could not be found");
				return TRUE;
			}

			// Set the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		else
		{
			// Account Note
			$intAccountId = DBO()->Account->Id->Value;
			
		}

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}
		
		DBO()->NoteDetails->MaxNotes = 50;
		DBO()->NoteDetails->FilterOption = NOTE_FILTER_ALL;
		
		LoadNotes($intAccountId, $intServiceId, $intContactId);
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('view_notes');

		return TRUE;
	}


	//------------------------------------------------------------------------//
	// SaveNewNote
	//------------------------------------------------------------------------//
	/**
	 * SaveNewNote()
	 *
	 * Performs the logic for saving a note, regardless of whether the note form is embeded a page or popup
	 * 
	 * Performs the logic for saving a note, regardless of whether the note form is embeded a page or popup
	 * This can handle adding Account/Service/Contact notes
	 *		if DBO()->NoteDetails->AccountNotes is set then it assumes it is an Account Note
	 *		if DBO()->NoteDetails->ServiceNotes is set then it assumes it is an Service Note
	 *		if DBO()->NoteDetails->ContactNotes is set then it assumes it is an Contact Note
	 * When a note is successfully added it fires an "OnNewNote" Event
	 * passing the following Event Object Data:
	 *		Account.Id		= id of the Account the new note belongs to, if it is an account note
	 *		Service.Id		= id of the Service the new note belongs to, if it is a service note
	 *		Contact.Id		= id of the Contact the new note refers to, if it is a contact note
	 *		Note.NoteType	= the note's NoteType property
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SaveNewNote()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Validate the note
		if (DBO()->Note->IsInvalid())
		{
			// The note is only invalid if it equates to an empty string
			Ajax()->AddCommand("Alert", "ERROR: No note has been specified");
			return TRUE;
		}
		
		// Setup the remaining properties for the note
		// User's details
		DBO()->Note->Employee = AuthenticatedUser()->_arrUser['Id'];
		
		// Time stamp
		DBO()->Note->Datetime = GetCurrentDateAndTimeForMySQL();

		if (DBO()->NoteDetails->AccountNotes->Value)
		{
			// The note is an account note
			DBO()->Account->Load();
			DBO()->Note->AccountGroup	= DBO()->Account->AccountGroup->Value;
			DBO()->Note->Account		= DBO()->Account->Id->Value;
			DBO()->Note->Service		= NULL;
			DBO()->Note->Contact		= NULL;
		}
		elseif (DBO()->NoteDetails->ServiceNotes->Value)
		{
			// The note is a service note
			DBO()->Service->Load();
			DBO()->Note->AccountGroup	= DBO()->Service->AccountGroup->Value;
			DBO()->Note->Service		= DBO()->Service->Id->Value;
			DBO()->Note->Contact		= NULL;
			// Check if this note is also an account note
			DBO()->Note->Account		= (DBO()->Note->IsAccountNote->Value == TRUE) ? DBO()->Service->Account->Value : NULL;
		}
		elseif (DBO()->NoteDetails->ContactNotes->Value)
		{
			// The note is a contact note
			DBO()->Contact->Load();
			DBO()->Note->AccountGroup	= DBO()->Contact->AccountGroup->Value;
			DBO()->Note->Contact		= DBO()->Contact->Id->Value;
			DBO()->Note->Service		= NULL;
			// Check if this note is also an account note
			DBO()->Note->Account		= (DBO()->Note->IsAccountNote->Value == TRUE) ? DBO()->Contact->Account->Value : NULL;
		}
		
		// Save the note
		if (!DBO()->Note->Save())
		{
			// Saving the note failed, unexpectedly
			Ajax()->AddCommand("Alert", "ERROR: Saving the note failed, unexpectedly");
			return TRUE;
		}

		// The note was saved successfully
		Ajax()->AddCommand("Alert", "The note has been successfully added");
		
		//Fire the OnNewNote event
		Ajax()->FireOnNewNoteEvent(DBO()->Note->Account->Value, DBO()->Note->Service->Value, DBO()->Note->Contact->Value, DBO()->Note->NoteType->Value);
		return TRUE;
	}


	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Note popup window
	 * 
	 * Performs the logic for the Add Note popup window
	 * This can handle adding Account/Service/Contact notes
	 *		if DBO()->Account->Id is set then it assumes it is an Account Note
	 *		if DBO()->Service->Id is set then it assumes it is an Service Note
	 *		if DBO()->Contact->Id is set then it assumes it is an Contact Note
	 * The actual logic used to save a note is found in the method SaveNewNote()
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Check what sort of note it is
		if (DBO()->Service->Id->IsSet)
		{
			// Service Note
			DBO()->NoteDetails->ServiceNotes = TRUE;
			
			// Load the Service
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("Alert", "The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}

			// Set the service's primary account
			DBO()->Account->Id = DBO()->Service->Account->Value;
		}
		elseif (DBO()->Contact->Id->IsSet)
		{
			// Contact Note
			DBO()->NoteDetails->ContactNotes = TRUE;
			
			// Load the Contact
			if (!DBO()->Contact->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "The contact with contact id: '". DBO()->Contact->Id->value ."' could not be found");
				return TRUE;
			}

			// Set the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		else
		{
			// Account Note
			DBO()->NoteDetails->AccountNotes = TRUE;
		}

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('note_add');

		return TRUE;
	}
	
	// This should only ever be called via an ajax request
	// It will render its output to the div defined by DBO()->NoteDetails->ContainerDivId
	/*
	 * It assumes 	DBO()->NoteDetails->FilterOption is set
	 *				DBO()->NoteDetails->MaxNotes is set
	 *				DBO()->NoteDetails->ContainerDivId is set
	 *				DBO()->NoteDetails->UpdateCookies is set to TRUE if you want the Note cookies updated
	 * 				DBO()->Account->Id || DBO()->Service->Id || DBO()->Contact->Id
	 *
	 */
	function ListWithFilter()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		// Load the notes
		$bolUpdateCookies = (DBO()->NoteDetails->UpdateCookies->Value == TRUE) ? TRUE : FALSE;
		LoadNotes(DBO()->Account->Id->Value, DBO()->Service->Id->Value, DBO()->Contact->Id->Value, $bolUpdateCookies);
		
		// Load the HtmlTemplate
		Ajax()->RenderHtmlTemplate("NoteList", HTML_CONTEXT_DEFAULT, DBO()->NoteDetails->ContainerDivId->Value);
	}
}
?>
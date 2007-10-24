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
	 * When a note is successfully added it fires an "OnNewNote" Event
	 * passing the following Event Object Data:
	 *		Account.Id		= id of the Account the new note belongs to, if it is an account note
	 *		Service.Id		= id of the Service the new note belongs to, if it is a service note
	 *		Contact.Id		= id of the Contact the new note refers to, if it is a contact note
	 *		Note.Id			= id of the new note
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
			$intContext = HTML_CONTEXT_SERVICE_NOTE;
			
			// Load the Service
			if (!DBO()->Service->Load())
			{
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "The service with service id: '". DBO()->Service->Id->value ."' could not be found");
				return TRUE;
			}

			// Set the service's primary account
			DBO()->Account->Id = DBO()->Service->Account->Value;
		}
		elseif (DBO()->Contact->Id->IsSet)
		{
			// Contact Note
			$intContext = HTML_CONTEXT_CONTACT_NOTE;
			
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
			$intContext = HTML_CONTEXT_ACCOUNT_NOTE;
		}

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("AlertReload", "The account with account id: '". DBO()->Account->Id->value ."' could not be found");
			return TRUE;
		}

		// Get all Note Types
		DBL()->AvailableNoteTypes->SetTable("NoteType");
		DBL()->AvailableNoteTypes->Load();

		// Check if a new note is being submitted
		if (SubmittedForm('AddNote', 'Add Note'))
		{
			// Only add the note if it is not invalid
			if (!DBO()->Note->IsInvalid())
			{
				// Set the properties for the new note
				
				// AccountGroup is always set
				DBO()->Note->AccountGroup	= DBO()->Account->AccountGroup->Value;
				
				// User's details
				DBO()->Note->Employee 		= AuthenticatedUser()->_arrUser['Id'];
				
				// Time stamp
				DBO()->Note->Datetime 		= GetCurrentDateAndTimeForMySQL();
								
				// DBO()->Note->Note should already be set
				// DBO()->Note->NoteType should already be set
				// DBO()->Note->Contact should already be set if it is a Contact note
				// DBO()->Note->Service should already be set if it is a Service note
				
				if (($intContext == HTML_CONTEXT_SERVICE_NOTE || $intContext == HTML_CONTEXT_CONTACT_NOTE))
				{
					// The note is a service note or a contact note
					if (DBO()->Note->IsAccountNote->Value)
					{
						// Register this as an account note as well
						DBO()->Note->Account = DBO()->Account->Id->Value;
					}
				}
				else
				{
					// The note is an Account Note
					DBO()->Note->Account = DBO()->Account->Id->Value;
				}
				
				
				
				// Save the note to the Note table of the vixen database
				if (!DBO()->Note->Save())
				{
					// The note could not be saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("AlertReload", "ERROR: The note did not save");
					return TRUE;
				}
				else
				{
					// The note was successfully saved
					Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
					Ajax()->AddCommand("Alert", "The note has been successfully added");
					
					// Fire the "OnNoteAdded" Event
					// Build event object
					// The contents of this object should be declared in the doc block of this method
					if (DBO()->Service->Id->IsSet)
					{
						$arrEvent['Service']['Id']	= DBO()->Service->Id->Value;
					}
					if (DBO()->Account->Id->IsSet)
					{
						$arrEvent['Account']['Id']	= DBO()->Account->Id->Value;
					}
					if (DBO()->Contact->Id->IsSet)
					{
						$arrEvent['Contact']['Id']	= DBO()->Contact->Id->Value;
					}
					$arrEvent['Note']['Id']			= DBO()->Note->Id->Value;
		
					Ajax()->FireEvent("OnNewNote", $arrEvent);
					
					return TRUE;
				}
			}
			else
			{
				// Something was invalid
				Ajax()->RenderHtmlTemplate("NoteAdd", $intContext, $this->_objAjax->strContainerDivId, $this->_objAjax);
				Ajax()->AddCommand("Alert", "ERROR: The note could not be saved. Invalid fields are highlighted");
				return TRUE;
			}
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('note_add');

		return TRUE;
	}
}

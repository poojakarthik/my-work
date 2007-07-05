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
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		
		DBL()->Note->Account = DBO()->Account->Id->Value;
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
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();

		// The account should already be set up as a DBObject
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: '". DBO()->Account->Id->value ."' could not be found";
			$this->LoadPage('error');
			return FALSE;
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
					DBO()->Status->Message = "The note did not save";
				}
				else
				{
					DBO()->Status->Message = "The note was successfully saved";
				}
			}
			else
			{
				// Something was invalid 
				DBO()->Status->Message = "The Note could not be saved. Invalid fields are shown in red";
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
	
}

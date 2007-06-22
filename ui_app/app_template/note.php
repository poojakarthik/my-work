<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// note
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
class AppTemplateAccount extends ApplicationTemplate
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
}

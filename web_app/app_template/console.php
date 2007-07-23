<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// console
//----------------------------------------------------------------------------//
/**
 * console
 *
 * contains the ApplicationTemplate extended class AppTempalteConsole
 *
 * contains the ApplicationTemplate extended class AppTempalteConsole
 *
 * @file		console.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateConsole
//----------------------------------------------------------------------------//
/**
 * AppTemplateConsole
 *
 * The AppTemplateConsole class
 *
 * The AppTemplateConsole class.
 *
 *
 * @package	web_app
 * @class	AppTemplateConsole
 * @extends	ApplicationTemplate
 */
class AppTemplateConsole extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// Console
	//------------------------------------------------------------------------//
	/**
	 * Console()
	 *
	 * Performs the logic for the console webpage
	 * 
	 * Performs the logic for the console webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Console()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			DBO()->Error->Message = "The contact with contact id: ". DBO()->Contact->Id->Value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// retrive the client's account details
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// Retrieve all accounts from the AccountGroup that the user belongs to
			// NOTE: Each account has a primary contact, and each AccountGroup can have a contact assocciated with it, defined as its manager
			// But each contact can only have one AccountGroup, so it would not be feasible for a contact to manage more than one AccountGroup
			// or accounts not belonging to the contact's account group.
			DBL()->Account->AccountGroup = AuthenticatedUser()->_arrUser['AccountGroup'];
		}
		else
		{
			// The user can only access their specified account
			DBL()->Account->Account = AuthenticatedUser()->_arrUser['Account'];
		}
		
		// only retrieve accounts that are not archived
		DBL()->Account->Archived = 0;
				
		$this->LoadPage('console');

		return TRUE;
	}

    //----- DO NOT REMOVE -----//
	
}

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
		//AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		echo "[INSERT PAGE HERE]\n";
		die;
		
		
		$this->LoadPage('invoices_and_payments');

		return TRUE;
	}

    //----- DO NOT REMOVE -----//
	
}

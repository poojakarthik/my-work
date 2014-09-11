<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// User
//----------------------------------------------------------------------------//
/**
 * User
 *
 * contains all ApplicationTemplate extended classes relating to 'User' functionality
 *
 * contains all ApplicationTemplate extended classes relating to 'User' functionality
 *
 * @file		user.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateUser
//----------------------------------------------------------------------------//
/**
 * AppTemplateUser
 *
 * The AppTemplateUser class
 *
 * The AppTemplateUser class
 *
 *
 * @package	ui_app
 * @class	AppTemplateUser
 * @extends	ApplicationTemplate
 */
class AppTemplateUser extends ApplicationTemplate
{
	
	//------------------------------------------------------------------------//
	// DisplayLoginPopup
	//------------------------------------------------------------------------//
	/**
	 * DisplayLoginPopup()
	 *
	 * Displays the login popup
	 * 
	 * Displays the login popup
	 *
	 * @return		void
	 * @method
	 */
	function DisplayLoginPopup()
	{
		$this->LoadPage('generic_popup');
		$this->Page->SetName("Login");
		$this->Page->AddObject('UserLogin', COLUMN_ONE, HTML_CONTEXT_POPUP);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// SubmitLoginDetails
	//------------------------------------------------------------------------//
	/**
	 * SubmitLoginDetails()
	 *
	 * Handles submittion of login details (attempts to log the user in)
	 * 
	 * Handles submittion of login details (attempts to log the user in)
	 *
	 * @return		void
	 * @method
	 */
	function SubmitLoginDetails()
	{
		// Login logic is handled by Application::CheckAuth method
		AuthenticatedUser()->CheckAuth();
	}	
}
?>
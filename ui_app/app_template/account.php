<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// account
//----------------------------------------------------------------------------//
/**
 * account
 *
 * contains all ApplicationTemplate extended classes relating to Account functionality
 *
 * contains all ApplicationTemplate extended classes relating to Account functionality
 *
 * @file		account.php
 * @language	PHP
 * @package		framework
 * @author		Sean, Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateAccount
//----------------------------------------------------------------------------//
/**
 * AppTemplateAccount
 *
 * The AppTemplateAccount class
 *
 * The AppTemplateAccount class.  This incorporates all logic for all pages
 * relating to accounts
 *
 *
 * @package	ui_app
 * @class	AppTemplateAccount
 * @extends	ApplicationTemplate
 */
class AppTemplateAccount extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// View INCOMPLETE
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the account_view.php webpage
	 * 
	 * Performs the logic for the account_view.php webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function View()
	{	
		/*
		// Check perms
		$this->PermissionOrDie($pagePerms)	// dies if no permissions
		$this->UserHasPerm($pagePerms) 		// returns false if none, true if they do
		*/
		
		/*
		if ($this->Dbo->Account->Id->IsValid())
			//Load account + stuff
			$this->Dbo->Account->Load()
			$this->Dbo->MyObject->Account = $this->Dbo->Account->Id->Value
			$this->Dbo->MyObject->Load()
		
			// Context menu options
			$this->ContextMenu->Account->ViewAccount($this->Dbo->Account-Id->Value)
			Menu
			   |--Account
				|--View Account
			// Load page
			$this->LoadPage('AccountView')
		
		else
			// Load error page
			$this->LoadPage('AccountError')
		*/
		/*
		//for additional functionality like change of lessee
		$someThing = $this->Module->Account->Function()
		
		*/
		$this->Module->Account->Method();
		
		$this->LoadPage('account_view');
		
	}
}

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
		
		$pagePerms = PERMISSION_ADMIN;
		
		AuthenticatedUser()->CheckAuth();
		// Check perms
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PUBLIC);	// dies if no permissions
		//AuthenticatedUser()->PermissionOrDie(USER_PERMISSION_GOD);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			echo "God!";
			// add in debug info
		}

		if (DBO()->Account->Id->Valid())
		{
			//Load account + stuff
			DBO()->Account->Load();
			DBO()->Service->Account = DBO()->Account->Id->Value;
			DBO()->Service->Load();
		
			// Context menu options
			//$this->ContextMenu->Account->ViewAccount($this->Dbo->Account-Id->Value);
			// context menu
			ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
			
			// add to breadcrumb menu
			BreadCrumb()->ViewAccount(DBO()->Account->Id->Value);
			//BreadCrumb()->ViewService(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);
			/*Menu
			   |--Account
				|--View Account
				*/
			// Load page
			$this->LoadPage('Account_View');
		}
		else
		{		
			// Load error page
			$this->LoadPage('Account_Error');
		}
		/*
		//for additional functionality like change of lessee
		$someThing = $this->Module->Account->Function()
		
		*/
		//$this->Module->Account->Method();	
	}
	
	
	//------------------------------------------------------------------------//
	// Ledger
	//------------------------------------------------------------------------//
	/**
	 * Ledger()
	 *
	 * Performs the logic for the account_ledger.php webpage
	 * 
	 * Performs the logic for the account_ledger.php webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Ledger()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation

		// context menu
		//TODO! define what goes in the context menu
		ContextMenu()->Contact_Retrieve->Account->Account_Ledger(DBO()->Account->Id->Value);
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		BreadCrumb()->Account_Ledger(DBO()->Account->Id->Value);
		
		
		// Setup all DBO and DBL objects required for the page
		//TODO!
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// * @param	string	$strWhere		[optional]	WHERE Clause with <> placeholders
		// * @param	array	$arrWhere		[optional]	WHERE parameter data
		
		// the DBList storing the invoices should be order so that the most recent is first
		// same with the payments list
		
		DBL()->Invoice->Where->Set("Account = <id>", Array('id'=>DBO()->Account->Id->Value));
		DBL()->Invoice->Load();
		
		// Calculate the Account Balance
		//TODO!
		DBO()->Account->Balance = 50000;
		
		// Calculate the Account Overdue Amount
		//TODO!
		DBO()->Account->Overdue = 450;
		
		// Calculate the Account's total unbilled adjustments
		//TODO!
		DBO()->Account->TotalUnbilledAdjustments = 1200;
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('account_ledger');

		return TRUE;
	
	}
	
}

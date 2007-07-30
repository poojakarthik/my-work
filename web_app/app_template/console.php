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
		
		if (DBO()->Account->Id->Value)
		{
			// A specific account has been specified, so load the details of it
			// DBO()->Account->Id has already been initialised
		}
		else
		{
			// no specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();
		
		// add to breadcrumb menu
		//TODO!
		//BreadCrumb()->ViewAccount(DBO()->Account->Id->Value);
		
		// Add a context menu
		//TODO!

		// Calculate the Account Balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Calculate the Account Overdue Amount
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled adjustments
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// If the user can view all accounts in their account group then load these too
		if (DBO()->Contact->CustomerContact->Value)
		{
			DBL()->Account->AccountGroup = DBO()->Contact->AccountGroup->Value;
			DBL()->Account->Archived = 0;
			DBL()->Account->Load();
		}
		
		// Make sure that the Account requested belongs to the account group that the contact belongs to
		$bolUserCanViewAccount = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Account->AccountGroup->Value)
			{
				$bolUserCanViewAccount = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Account->Id->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewAccount = TRUE;
		}
		
		if (!$bolUserCanViewAccount)
		{
			// The user does not have permission to view the requested account
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value ." as it is not part of their Account Group";
			$this->LoadPage('Error');
			return FALSE;
		}
				
		$this->LoadPage('console');

		return TRUE;
	}

    //----- DO NOT REMOVE -----//
	
}

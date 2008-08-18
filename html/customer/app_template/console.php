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
	
	 // Make a payment.
	 function Pay()
	 {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// Retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
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
			// No specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();

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
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Calculate the Account Balance
		DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account Overdue Amount
		$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		if ($fltOverdue < 0)
		{
			$fltOverdue = 0;
		}
		DBO()->Account->Overdue = $fltOverdue;
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
		DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
		
		// Setup BreadCrumb Menu
		# $strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
		# BreadCrumb()->SetCurrentPage($strWelcome);

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		if (DBO()->Account->BusinessName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Make Payment - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Make Payment - " . substr(DBO()->Account->TradingName->Value, 0, 60));
		}
		else
		{
			// Don't display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Make Payment");
		}


		$this->LoadPage('pay');

		return TRUE;	 	
	 }
	 
	 
	 
	//------------------------------------------------------------------------//
	// Edit account details.
	//------------------------------------------------------------------------//
	/**
	 * Home()
	 *
	 * Performs the logic for the Homepage of the website
	 * 
	 * Performs the logic for the Homepage of the website
	 *
	 * @return		void
	 * @method
	 *
	 */
	 function Edit()
	 {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// Retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
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
			// No specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();

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
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Calculate the Account Balance
		DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account Overdue Amount
		$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		if ($fltOverdue < 0)
		{
			$fltOverdue = 0;
		}
		DBO()->Account->Overdue = $fltOverdue;
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
		DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
		
		// Setup BreadCrumb Menu
		# $strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
		# BreadCrumb()->SetCurrentPage($strWelcome);
		
		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		if (DBO()->Account->BusinessName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Edit Account - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Edit Account - " . substr(DBO()->Account->TradingName->Value, 0, 60));
		}
		else
		{
			// Don't display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Edit Account");
		}
		
		$this->LoadPage('edit');

		return TRUE;	 	
	 }
	 
	 
	function Home()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckClientAuth();
		
		// Retrieve the client's details
		DBO()->Contact->Id = AuthenticatedUser()->_arrUser['Id'];
		if (!DBO()->Contact->Load())
		{
			// This should never actually occur because if the contact can't be loaded then AuthenticatedUser()->CheckClientAuth() would have failed
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
			// No specific account has been specified, so load the contact's primary account
			DBO()->Account->Id = DBO()->Contact->Account->Value;
		}
		
		// Load the clients primary account
		DBO()->Account->Load();

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
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Calculate the Account Balance
		DBO()->Account->CustomerBalance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account Overdue Amount
		$fltOverdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		if ($fltOverdue < 0)
		{
			$fltOverdue = 0;
		}
		DBO()->Account->Overdue = $fltOverdue;
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->UnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account (inc GST), omitting Credit CDRs
		DBO()->Account->UnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value, TRUE));
		
		// Setup BreadCrumb Menu
		$strWelcome = "Welcome " . DBO()->Contact->Title->Value ." " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
		BreadCrumb()->SetCurrentPage($strWelcome);
		
		$this->LoadPage('console');

		return TRUE;
	}

	//------------------------------------------------------------------------//
	// Logout
	//------------------------------------------------------------------------//
	/**
	 * Logout()
	 *
	 * Performs the logic for logging out the user
	 * 
	 * Performs the logic for logging out the user
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Logout()
	{
		if ($this->_objAjax != NULL)
		{
			// This method was executed via an ajax call.  Use a popup to notify the user, that they have been logged out
			AuthenticatedUser()->LogoutClient();
			
			//TODO! Check if they were successfully logged out, or if their session is not the most recent.
			// I have done this for the case, where this method is executed from a url.  It can be found in HtmlTemplateLoggedOut
			
			// Redirect the user to the main page of the website
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "Logout successful", "Location" => Href()->MainPage()));
		}
		else
		{
			// This method was executed via a url.  Use a page to notify the user, that they have been logged out
			AuthenticatedUser()->LogoutClient();
			
			$this->LoadPage('logged_out');
		}
		
		return TRUE;
	}


    //----- DO NOT REMOVE -----//
	
}

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
			//echo "God!";
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
			ContextMenu()->Logout();
			
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
	// InvoicesAndPayments
	//------------------------------------------------------------------------//
	/**
	 * InvoicesAndPayments()
	 *
	 * Performs the logic for the invoices_and_payments.php webpage
	 * 
	 * Performs the logic for the invoices_and_payments.php webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function InvoicesAndPayments()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();
		
		//handle saving of data on this screen (the admin fee checkbox and the payment fee radio buttons)
		//check if the form was submitted
		if (SubmittedForm('AccountDetails', 'Apply Changes'))
		{
			//Save the AccountDetails
			if (!DBO()->Account->IsInvalid())
			{
				// update the record in the Account table
				DBO()->Account->SetColumns("DisableDDR, DisableLatePayment");
				DBO()->Account->Save();
				
				// reset the columns, so that they are all retrived when the data is loaded
				DBO()->Account->SetColumns();
			}
		}
		
		// context menu
		ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Service->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Service->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->View_Notes(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Note(DBO()->Account->Id->Value);
		
		
		ContextMenu()->Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Invoice->Load();
		
		
		DBL()->Payment->Account = DBO()->Account->Id->Value;
		$strWhere = "(Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " AND ((Status = ". PAYMENT_WAITING .")";
		$strWhere .= " OR (Status = ". PAYMENT_PAYING .")";
		$strWhere .= " OR (Status = ". PAYMENT_FINISHED .")";
		$strWhere .= " OR (Status = ". PAYMENT_REVERSED ."))";
		DBL()->Payment->Where->SetString($strWhere);
		DBL()->Payment->OrderBy("PaidOn DESC, Id DESC");
		DBL()->Payment->Load();
		
		DBL()->InvoicePayment->Account = DBO()->Account->Id->Value;
		DBL()->InvoicePayment->OrderBy("Id DESC");
		DBL()->InvoicePayment->Load();
		
		DBL()->Charge->Account = DBO()->Account->Id->Value;
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		DBL()->RecurringCharge->Account = DBO()->Account->Id->Value;
		DBL()->RecurringCharge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->RecurringCharge->Load();
		
		DBL()->Note->Account = DBO()->Account->Id->Value;
		DBL()->Note->OrderBy("Datetime DESC");
		DBL()->Note->Load();
		DBL()->NoteType->Load();
		
		
		// Calculate the Account Balance
		//TODO!
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Calculate the Account Overdue Amount
		//TODO!
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled adjustments
		//TODO!
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('invoices_and_payments');

		return TRUE;
	
	}
	

    //----- DO NOT REMOVE -----//
	
}

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
	// ViewServices
	//------------------------------------------------------------------------//
	/**
	 * ViewServices()
	 *
	 * Performs the logic for viewing the Services belonging to this account
	 * 
	 * Performs the logic for viewing the Services belonging to this account
	 * This is a popup which will only ever be executed via an Ajax request
	 * either	DBO()->Account->Id	must be specified
	 * or		DBO()->Service->Id	must be specified, in which case, it will work out the Service Id
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ViewServices()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		// If Account.Id is not set, but Service.Id is, then find the account that the service belongs to
		if ((!DBO()->Account->Id->Value) && (DBO()->Service->Id->Value))
		{
			if (!DBO()->Service->Load())
			{
				// The service could not be found
				
				// For when it is used as a popup
				//Ajax()->AddCommand("AlertReload", "The service with Id: ". DBO()->Service->Id->Value ." could not be found");
				
				// For when it is used as a page
				DBO()->Error->Message = "The service with id: ". DBO()->Service->Id->value ." could not be found";
				$this->LoadPage('error');
				return TRUE;
			}
			
			// We want to view all services belonging to the account that this service belongs to
			DBO()->Account->Id = DBO()->Service->Account->Value;
		}
		
		// Attempt to load the account
		if (!DBO()->Account->Load())
		{
			// For when it is used as a popup
			//Ajax()->AddCommand("AlertReload", "The account ". DBO()->Account->Id->Value ." could not be found");
			
			// For when it is used as a page
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Services");
		
		// context menu
		ContextMenu()->Account_Menu->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Adjustment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account_Menu->Account->View_Account_Notes(DBO()->Account->Id->Value);
		
		/*  Currently Operators can view archived services
		if (!$bolUserHasAdminPerm)
		{
			// User does not have admin privileges and therefore cannot view archived services
			$strWhere = "Account = <Account> AND Status != ". SERVICE_ARCHIVED;
		}
		else
		{
			// User has admin privileges and can view all services regardless of their status
			$strWhere = "Account = <Account>";
		}
		*/
		$strWhere = "Account = <Account>";
		
		// Load all the services belonging to the account, that the user has permission to view
		DBL()->Service->Where->Set($strWhere, Array("Account"=>DBO()->Account->Id->Value));
		DBL()->Service->OrderBy("FNN");
		DBL()->Service->Load();
		
		$this->LoadPage('account_services');
		return TRUE;
	}	

	//------------------------------------------------------------------------//
	// ViewContacts
	//------------------------------------------------------------------------//
	/**
	 * ViewContacts()
	 *
	 * Performs the logic for viewing the Services belonging to this account
	 * 
	 * Performs the logic for viewing the Services belonging to this account
	 * This is a popup which will only ever be executed via an Ajax request
	 * DBO()->Account->Id		Id of the Account to view the contacts of
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ViewContacts()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		// Attempt to load the account
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "The account ". DBO()->Account->Id->Value ." could not be found");
			return TRUE;
		}
		
		// Load all the contacts who belong to the AccountGroup and can view the Account
		$strWhere = "(AccountGroup = <AccountGroup> AND CustomerContact = 1) OR Account = <Account>";
		$arrWhere = array("AccountGroup"=>DBO()->AccountGroup->Id->Value, "Account"=>DBO()->Account->Id->Value);
		DBL()->Contact->Where->Set($strWhere, $arrWhere);
		DBL()->Contact->OrderBy("LastName, FirstName");
		DBL()->Contact->Load();
		
		$this->LoadPage('account_contacts');
		return TRUE;
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
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		//handle saving of data on this screen (the admin fee checkbox and the payment fee radio buttons)
		//check if the form was submitted
		/* DEPRICATED
		if (SubmittedForm('AccountDetails', 'Apply Changes') && $bolUserHasOperatorPerm)
		{
			DBO()->CurrentAccount->Id = DBO()->Account->Id->Value;
			DBO()->CurrentAccount->SetTable("Account");
			DBO()->CurrentAccount->Load();
			
			// if DisableLatePayment === NULL, then, in this context, it logically equals 0
			if (DBO()->CurrentAccount->DisableLatePayment->Value === NULL)
			{
				DBO()->CurrentAccount->DisableLatePayment = 0;
			}
			
			// Check that the user can edit the account
			$intAccountStatus = DBO()->Account->Archived->Value;
			if (	($intAccountStatus == ACCOUNT_ARCHIVED || $intAccountStatus == ACCOUNT_DEBT_COLLECTION 
					|| $intAccountStatus == ACCOUNT_SUSPENDED) && (!$bolUserHasAdminPerm))
			{
				// The user can't edit the Account
				Ajax()->AddCommand("AlertReload", "ERROR: Due to the account's status, and your permissions, you cannot edit this account");
				return TRUE;
			}
		
			//Save the AccountDetails
			if (!DBO()->Account->IsInvalid())
			{
				// Define what will go in the system generated note
				if (DBO()->Account->DisableDDR->Value != DBO()->CurrentAccount->DisableDDR->Value)
				{
					$strChangesNote .= "This account is ". ((DBO()->Account->DisableDDR->Value == 1) ? "no longer" : "now") ." charged an admin fee\n";
				}		
				if (DBO()->Account->DisableLatePayment->Value != DBO()->CurrentAccount->DisableLatePayment->Value)
				{
					$intCurrentValue = DBO()->CurrentAccount->DisableLatePayment->Value;
					if ($intCurrentValue === NULL)
					{
						$intCurrentValue = 0;
					}
					$strChangesNote .=	"Charging of Late Payment Fee was changed from '".
										DBO()->Account->DisableLatePayment->FormattedValue(CONTEXT_DEFAULT, $intCurrentValue).
										"' to '". DBO()->Account->DisableLatePayment->FormattedValue() ."'\n";	
				}
				if (DBO()->Account->DisableLateNotices->Value != DBO()->CurrentAccount->DisableLateNotices->Value)
				{
					$intCurrentValue = DBO()->CurrentAccount->DisableLateNotices->Value;
					$strChangesNote .=	"Sending of Late Notices was changed from '".
										DBO()->Account->DisableLateNotices->FormattedValue(CONTEXT_DEFAULT, $intCurrentValue).
										"' to '". DBO()->Account->DisableLateNotices->FormattedValue() ."'\n";
										
					// When this property is changed you have to update the LatePaymentAmnesty property
					switch (DBO()->Account->DisableLateNotices->Value)
					{
						case 0:
						case 1:
							DBO()->Account->LatePaymentAmnesty = NULL;
							break;
							
						case (-1):
							// This account is ineligible to receive late notices, until after the due date of the current bill
							DBO()->Account->LatePaymentAmnesty = $this->GetLatePaymentAmnestyDate(DBO()->CurrentAccount->PaymentTerms->Value);
							//$intPaymentTerms					= DBO()->CurrentAccount->PaymentTerms->Value;
							//DBO()->Account->LatePaymentAmnesty	= date("Y-m-d", strtotime("+{$intPaymentTerms} days", GetStartDateTimeForNextBillingPeriod()));
							$strChangesNote .= "Late Notices will not be generated until after ". date("d/m/Y", strtotime(DBO()->Account->LatePaymentAmnesty->Value));
							break;
					}
				}
				else
				{
					// Retain the current value of Account.LateNoticeAmnesty
					DBO()->Account->LatePaymentAmnesty = DBO()->CurrentAccount->LatePaymentAmnesty->Value;
				}
				
				
				// Update the record in the Account table
				DBO()->Account->SetColumns("DisableDDR, DisableLatePayment, DisableLateNotices, LatePaymentAmnesty");
				
				// Save the payment to the payment table of the vixen database
				if (!DBO()->Account->Save())
				{
					// The account details could not be updated
					Ajax()->AddCommand("AlertReload", "ERROR: Updating the Account failed, unexpectedly");
					return TRUE;
				}
				
				// The account details were successfully updated
				if ($strChangesNote)
				{
					$strSystemChangesNote = "Account details have been edited.  The following changes have been made:\n$strChangesNote";
					SaveSystemNote($strSystemChangesNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value, NULL, NULL);
				}
				Ajax()->AddCommand("Alert", "The Account details have been successfully updated");
				
				// Fire the OnNewNote Event
				Ajax()->FireOnNewNoteEvent(DBO()->Account->Id->Value);
				
				// Fire the OnAccountDetailsUpdate Event
				$arrEvent['Account']['Id'] = DBO()->Account->Id->Value;
				Ajax()->FireEvent(EVENT_ON_ACCOUNT_DETAILS_UPDATE, $arrEvent);
				
				return TRUE;
			}
		}
		*/
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Invoices and Payments");
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		/* Currently Operators can view Archived accounts
		// If the account is archived, check that the user has permission to view it
		if (DBO()->Account->Archived->Value == ACCOUNT_ARCHIVED && !$bolUserHasAdminPerm)
		{
			// The user does not have permission to view this account
			DBO()->Error->Message = "You do not have permission to view account: ". DBO()->Account->Id->value ." because its status = " . GetConstantDescription(DBO()->Account->Archived->Value, "Account");
			$this->LoadPage('error');
			return FALSE;
		}
		*/

		// context menu
		ContextMenu()->Account_Menu->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Adjustment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account_Menu->Account->View_Account_Notes(DBO()->Account->Id->Value);
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Invoice->Load();
		
		// Retrieve the Payments
		//"WHERE ((Account = <accId>) OR (AccountGroup = <accGrpId> AND Account IS NULL)) AND (Status conditions)"
		$strWhere  = "((Payment.Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " OR (Payment.AccountGroup = ". DBO()->Account->AccountGroup->Value ." AND Payment.Account IS NULL))";
		$strWhere .= " AND Payment.Status IN (". PAYMENT_WAITING .", ". PAYMENT_PAYING .", ". PAYMENT_FINISHED .", ". PAYMENT_REVERSED .")";
		DBL()->Payment->Where->SetString($strWhere);
		
		$arrColumns = Array(	"Id"			=> "Payment.Id",
								"AccountGroup"	=> "Payment.AccountGroup",
								"Account"		=> "Payment.Account",
								"Status"		=> "Payment.Status",
								"Balance"		=> "Payment.Balance",
								"PaidOn"		=> "Payment.PaidOn",
								"Amount"		=> "Payment.Amount",
								"PaymentType"	=> "Payment.PaymentType",
								"EnteredBy"		=> "Payment.EnteredBy",
								"ImportedOn"	=> "FileImport.ImportedOn"
							);
		DBL()->Payment->SetColumns($arrColumns);
		DBL()->Payment->SetTable("Payment LEFT OUTER JOIN FileImport ON Payment.File = FileImport.Id");
		DBL()->Payment->OrderBy("Payment.PaidOn DESC, Payment.Id DESC");
		DBL()->Payment->Load();
		
		DBL()->InvoicePayment->Account = DBO()->Account->Id->Value;
		DBL()->InvoicePayment->OrderBy("Id DESC");
		DBL()->InvoicePayment->Load();
		
		// Build the list of columns to use for the Charge DBL (as it is pulling this information from 2 tables)
		$arrColumns = Array(	'Id' => 'C.Id',	'AccountGroup'=>'C.AccountGroup',	'Account'=>'C.Account',	'Service'=>'C.Service',
								'InvoiceRun'=>'C.InvoiceRun',	'CreatedBy'=>'C.CreatedBy', 'CreatedOn'=>'C.CreatedOn', 'ApprovedBy'=>'C.ApprovedBy',
								'ChargeType'=>'C.ChargeType', 'Description'=>'C.Description', 'ChargedOn'=>'C.ChargedOn', 'Nature'=>'C.Nature',
								'Amount'=>'C.Amount', 'Invoice'=>'C.Invoice', 'Notes'=>'C.Notes', 'Status'=>'C.Status', 'LinkType' => 'C.LinkType',
								'LinkId' => 'C.LinkId', 'FNN'=>'S.FNN');
		DBL()->Charge->SetColumns($arrColumns);
		DBL()->Charge->SetTable("Charge AS C LEFT OUTER JOIN Service AS S ON C.Service = S.Id");
		
		//"WHERE (Account = <accId>) AND (Status conditions)"
		$strWhere  = "C.Account = ". DBO()->Account->Id->Value;
		$strWhere .= " AND C.Status IN (". CHARGE_WAITING .", ". CHARGE_APPROVED .", ". CHARGE_TEMP_INVOICE .", ". CHARGE_INVOICED .")";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("ChargedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		// Build the list of columns to use for the RecurringCharge DBL (as it is pulling this information from 2 tables)
		$arrColumns = Array(	'Id' => 'RC.Id',	'AccountGroup'=>'RC.AccountGroup',	'Account'=>'RC.Account',	'Service'=>'RC.Service',
								'CreatedBy'=>'RC.CreatedBy', 'ApprovedBy'=>'RC.ApprovedBy', 'ChargeType'=>'RC.ChargeType',
								'Description'=>'RC.Description', 'Nature'=>'RC.Nature', 'CreatedOn'=>'RC.CreatedOn',
								'StartedOn'=>'RC.StartedOn', 'LastChargedOn'=>'RC.LastChargedOn', 'RecurringFreqType'=>'RC.RecurringFreqType',
								'RecurringFreq'=>'RC.RecurringFreq', 'MinCharge'=>'RC.MinCharge', 'RecursionCharge'=>'RC.RecursionCharge',
								'CancellationFee'=>'RC.CancellationFee', 'Continuable'=>'RC.Continuable', 'PlanCharge'=>'RC.PlanCharge',
								'UniqueCharge'=>'RC.UniqueCharge', 'TotalCharged'=>'RC.TotalCharged', 'TotalRecursions'=>'RC.TotalRecursions',
								'Archived'=>'RC.Archived', 'FNN'=>'S.FNN');
		DBL()->RecurringCharge->SetColumns($arrColumns);
		DBL()->RecurringCharge->SetTable("RecurringCharge AS RC LEFT OUTER JOIN Service AS S ON RC.Service = S.Id");
		
		// I can't directly use a DBObject property or method as a parameter of another DBObject or DBList method
		// On account of how the Property token works 
		$intAccountId = DBO()->Account->Id->Value;
		DBL()->RecurringCharge->Where->Set("RC.Account = <Account> AND RC.Archived = 0", Array("Account"=>$intAccountId));
		DBL()->RecurringCharge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->RecurringCharge->Load();
		
		// Calculate the Account Balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Calculate the Account Overdue Amount
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled adjustments
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Load the primary contact
		if (DBO()->Account->PrimaryContact->Value)
		{
			DBL()->Contact->Id = DBO()->Account->PrimaryContact->Value;
			DBL()->Contact->Load();
		}
		
		LoadNotes(DBO()->Account->Id->Value);

		// Flag the Account as being shown in the InvoicesAndPayments Page
		DBO()->Account->InvoicesAndPaymentsPage = 1;
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('invoices_and_payments');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Overview
	//------------------------------------------------------------------------//
	/**
	 * Overview()
	 *
	 * Performs the logic for the Account Overview webpage
	 * 
	 * Performs the logic for the Account Overview webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Overview()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Account");
		
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// context menu
		ContextMenu()->Account_Menu->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Adjustment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account_Menu->Account->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account_Menu->Account->View_Account_Notes(DBO()->Account->Id->Value);

		// The DBList storing the invoices should be ordered so that the most recent is first
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Invoice->SetLimit(3);
		DBL()->Invoice->Load();
		
		// Calculate the Account Balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Calculate the Account Overdue Amount
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled adjustments
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Load the primary contact
		if (DBO()->Account->PrimaryContact->Value)
		{
			DBL()->Contact->Id = DBO()->Account->PrimaryContact->Value;
			DBL()->Contact->Load();
		}
		
		// Load the last looked at contact, if this page was triggered from the Contact View page and the last contact viewed is
		// different to the Primary Contact
		// TODO: While this functionality is complete in this method, the contact is not currently displayed in the HtmlTemplate
		// as I can't work out where the account_view.php link is in the contact_view.php file
		// This means there is no way of specifying DBO()->LastContact->Id unless you explicitly write it into the browser's address bar
		if ((DBO()->LastContact->Id->Value) && (DBO()->LastContact->Id->Value != DBO()->Account->PrimaryContact->Value))
		{
			DBO()->LastContact->SetTable("Contact");
			DBO()->LastContact->Load();
			
			// Make sure this contact is associated with this account
			if (DBO()->LastContact->Account->Value != DBO()->Account->Id->Value)
			{
				// It's not associated with the account
				DBO()->LastContact->Id = NULL;
			}
		}
		
		// Load the List of services
		// Load all the services belonging to the account, that the user has permission to view (which is currently all of them)
		DBL()->Service->Where->Set("Account = <Account>", Array("Account"=>DBO()->Account->Id->Value));
		DBL()->Service->OrderBy("FNN");
		DBL()->Service->Load();
		
		// Load the user notes
		LoadNotes(DBO()->Account->Id->Value);
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('account_overview');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// RenderAccountServicesTable
	//------------------------------------------------------------------------//
	/**
	 * RenderAccountServicesTable()
	 *
	 * Renders just the VixenTable storing the services belonging to the account
	 * 
	 * Renders just the VixenTable storing the Account Services
	 * It expects	DBO()->Account->Id 			The account Id 
	 *				DBO()->TableContainer->Id	The id of the container div of the VixenTable 
	 *											that displays the Services of the Account
	 *
	 * @return		void
	 * @method
	 *
	 */
	function RenderAccountServicesTable()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// Load all the services belonging to the account, that the user has permission to view
		DBL()->Service->Where->Set("Account = <Account>", Array("Account"=>DBO()->Account->Id->Value));
		DBL()->Service->OrderBy("FNN");
		DBL()->Service->Load();
		
		//Render the AccountServices table
		Ajax()->RenderHtmlTemplate("AccountServicesList", HTML_CONTEXT_DEFAULT, DBO()->TableContainer->Id->Value);

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// RenderAccountDetailsForViewing
	//------------------------------------------------------------------------//
	/**
	 * RenderAccountDetailsForViewing()
	 *
	 * Renders the AccountDetails Html Template for viewing
	 * 
	 * Renders the AccountDetails Html Template for viewing
	 * It expects	DBO()->Account->Id 							account Id 
	 *				DBO()->Account->InvoicesAndPaymentsPage		set to TRUE if the HtmlTemplate is to be rendered
	 *															on the InvoicesAndPayments page 
	 *				DBO()->Container->Id						id of the container div in which to place the 
	 *															Rendered HtmlTemplate
	 *
	 * @return		void
	 * @method
	 *
	 */
	function RenderAccountDetailsForViewing()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// Load the account
		DBO()->Account->LoadMerge();
		
		// Calculate the Balance, Amount Overdue, and the Total Un-billed adjustments
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Render the AccountDetails HtmlTemplate for Viewing
		Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_VIEW, DBO()->Container->Id->Value);

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// RenderAccountDetailsForEditing
	//------------------------------------------------------------------------//
	/**
	 * RenderAccountDetailsForEditing()
	 *
	 * Renders the AccountDetails Html Template for editing
	 * 
	 * Renders the AccountDetails Html Template for editing
	 * It expects	DBO()->Account->Id 							account Id 
	 *				DBO()->Account->InvoicesAndPaymentsPage		set to TRUE if the HtmlTemplate is to be rendered
	 *															on the InvoicesAndPayments page 
	 *				DBO()->Container->Id						id of the container div in which to place the 
	 *															Rendered HtmlTemplate
	 * @return		void
	 * @method
	 *
	 */
	function RenderAccountDetailsForEditing()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Load the account
		DBO()->Account->LoadMerge();
		
		// Render the AccountDetails HtmlTemplate for Viewing
		Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT, DBO()->Container->Id->Value);

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// SaveDetails
	//------------------------------------------------------------------------//
	/**
	 * SaveDetails()
	 *
	 * Handles the logic of validating and saving the details of an account
	 * 
	 * Handles the logic of validating and saving the details of an account
	 * This works with the HtmlTemplateAccountDetails object, when rendered in Edit mode (HTML_CONTEXT_EDIT)
	 * It fires the OnAccountDetailsUpdate, OnAccountServicesUpdate and OnNewNote Events if relevent to the
	 * changes made to the account
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SaveDetails()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// If the validation has failed display the invalid fields
		if (DBO()->Account->IsInvalid())
		{
			Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return TRUE;
		}
		
		// Merge the Account data from the database with the newly defined details
		DBO()->Account->LoadMerge();
		
		// Load the current account details, so you can work out what has been changed, and include these details in the system note
		DBO()->CurrentAccount->Id = DBO()->Account->Id->Value;
		DBO()->CurrentAccount->SetTable("Account");
		DBO()->CurrentAccount->Load();

		if (DBO()->Account->BusinessName->Value != DBO()->CurrentAccount->BusinessName->Value)
		{
			$strChangesNote .= "Business Name was changed from '". DBO()->CurrentAccount->BusinessName->Value ."' to '" . DBO()->Account->BusinessName->Value . "'\n";
		}
		if (DBO()->Account->TradingName->Value != DBO()->CurrentAccount->TradingName->Value)
		{
			$strChangesNote .= "Trading Name was changed from '". DBO()->CurrentAccount->TradingName->Value ."' to '" . DBO()->Account->TradingName->Value . "'\n";
		}	
		if (DBO()->Account->ABN->Value != DBO()->CurrentAccount->ABN->Value)
		{
			$strChangesNote .= "ABN was changed from ". DBO()->CurrentAccount->ABN->Value ." to " . DBO()->Account->ABN->Value . "\n";
		}
		if (DBO()->Account->ACN->Value != DBO()->CurrentAccount->ACN->Value)
		{
			$strChangesNote .= "ACN was changed from ". DBO()->CurrentAccount->ACN->Value ." to " . DBO()->Account->ACN->Value . "\n";
		}
		if (DBO()->Account->Address1->Value != DBO()->CurrentAccount->Address1->Value)
		{
			$strChangesNote .= "Address Line 1 was changed from '". DBO()->CurrentAccount->Address1->Value ."' to '" . DBO()->Account->Address1->Value . "'\n";
		}
		if (DBO()->Account->Address2->Value != DBO()->CurrentAccount->Address2->Value)
		{
			$strChangesNote .= "Address Line 2 was changed from '". DBO()->CurrentAccount->Address2->Value ."' to '" . DBO()->Account->Address2->Value . "'\n";
		}
		if (DBO()->Account->Suburb->Value != DBO()->CurrentAccount->Suburb->Value)
		{
			$strChangesNote .= "Suburb was changed from '". DBO()->CurrentAccount->Suburb->Value ."' to '" . DBO()->Account->Suburb->Value . "'\n";
		}
		if (DBO()->Account->Postcode->Value != DBO()->CurrentAccount->Postcode->Value)
		{
			$strChangesNote .= "Postcode was changed from ". DBO()->CurrentAccount->Postcode->Value ." to " . DBO()->Account->Postcode->Value . "\n";
		}
		if (DBO()->Account->State->Value != DBO()->CurrentAccount->State->Value)
		{
			$strChangesNote .= "State was changed from ". DBO()->CurrentAccount->State->Value ." to " . DBO()->Account->State->Value . "\n";
		}
		if (DBO()->Account->BillingMethod->Value != DBO()->CurrentAccount->BillingMethod->Value)
		{
			$strChangesNote .= "Billing Method was changed from ". GetConstantDescription(DBO()->CurrentAccount->BillingMethod->Value, 'BillingMethod') ." to " . GetConstantDescription(DBO()->Account->BillingMethod->Value, 'BillingMethod') . "\n";
		}
		if (DBO()->Account->CustomerGroup->Value != DBO()->CurrentAccount->CustomerGroup->Value)
		{
			$selCustomerGroup = new StatementSelect("CustomerGroup", "Id, InternalName", "Id = <Id>");
			$selCustomerGroup->Execute(Array("Id" => DBO()->CurrentAccount->CustomerGroup->Value));
			$arrCurrentCustomerGroup = $selCustomerGroup->Fetch();
			$selCustomerGroup->Execute(Array("Id" => DBO()->Account->CustomerGroup->Value));
			$arrNewCustomerGroup = $selCustomerGroup->Fetch();
			
			$strChangesNote .= "Customer Group was changed from {$arrCurrentCustomerGroup['InternalName']} to {$arrNewCustomerGroup['InternalName']}\n";
		}
		DBO()->Account->DisableDDR = !(DBO()->Account->ChargeAdminFee->Value);
		if (DBO()->Account->DisableDDR->Value != DBO()->CurrentAccount->DisableDDR->Value)
		{
			$strChangesNote .= "This account is ". ((DBO()->Account->DisableDDR->Value == 1) ? "no longer" : "now") ." charged an admin fee\n";
		}
		
		// if DisableLatePayment === NULL, then, in this context, it logically equals 0
		if (DBO()->CurrentAccount->DisableLatePayment->Value === NULL)
		{
			DBO()->CurrentAccount->DisableLatePayment = 0;
		}
		if (DBO()->Account->DisableLatePayment->Value != DBO()->CurrentAccount->DisableLatePayment->Value)
		{
			$intCurrentValue = DBO()->CurrentAccount->DisableLatePayment->Value;
			if ($intCurrentValue === NULL)
			{
				$intCurrentValue = 0;
			}
			$strChangesNote .= "Charging of Late Payment Fee was changed from '". 
								DBO()->Account->DisableLatePayment->FormattedValue(CONTEXT_DEFAULT, $intCurrentValue) .
								"' to '" . DBO()->Account->DisableLatePayment->FormattedValue() . "'\n";	
		}
		if (DBO()->Account->Sample->Value != DBO()->CurrentAccount->Sample->Value)
		{
			$intCurrentValue = DBO()->CurrentAccount->Sample->Value;
			$strChangesNote .= "Sample was changed from '". 
								DBO()->Account->Sample->FormattedValue(CONTEXT_DEFAULT, $intCurrentValue) .
								"' to '" . DBO()->Account->Sample->FormattedValue() . "'\n";
		}
		if (DBO()->Account->LatePaymentAmnesty->Value != DBO()->CurrentAccount->LatePaymentAmnesty->Value)
		{
			// When refering to END_OF_TIME, we just want the date part, not the time part
			$strEndOfTime = substr(END_OF_TIME, 0, 10);
			
			if (DBO()->Account->LatePaymentAmnesty->Value == NULL)
			{
				// Explicity set it to NULL, if it loosely equals NULL
				DBO()->Account->LatePaymentAmnesty = NULL;
			}
			
			if (DBO()->CurrentAccount->LatePaymentAmnesty->Value != $strEndOfTime)
			{
				if (DBO()->CurrentAccount->LatePaymentAmnesty->Value < date("Y-m-d"))
				{
					// The account is currently eligable for late notices
					$bolAmnestyExpired = TRUE;
					$strOldSetting = "Send late notices";
				}
				else
				{
					// The account currently has an explicit late notice amnesty
					$bolAmnestyExpired = FALSE;
					$strOldSetting = "Not eligible for late notices until after ". date("jS F, Y", strtotime(DBO()->CurrentAccount->LatePaymentAmnesty->Value));
				}
			}
			else
			{
				// The account is currently set to "Never send late notices"
				$bolAmnestyExpired = FALSE;
				$strOldSetting = "Never send late notices";
			}
			
			// Interpret the new LatePaymentAmnesty value
			if (DBO()->Account->LatePaymentAmnesty->Value == NULL)
			{
				// The account has been set to "Send late notices"
				$strNewSetting = "Send late Notices";
			}
			elseif (DBO()->Account->LatePaymentAmnesty->Value == $strEndOfTime)
			{
				// The account has been set to "Never send late notices"
				$strNewSetting = "Never send late notices"; 
			}
			else
			{
				// An explicit date has been set for the LatePaymentAmnesty
				$strNewSetting = "Not eligible for late notices until after ". date("jS F, Y", strtotime(DBO()->Account->LatePaymentAmnesty->Value));
			}
			
			if (DBO()->Account->LatePaymentAmnesty->Value == NULL && $bolAmnestyExpired)
			{
				// The user has set the property to "Send late notices", however the existing amnesty has expired which means it is logically
				// already set to "Send late notices", so don't bother logging this change in the system note
			}
			else
			{
				// Update the content of the system note
				$strChangesNote .= "Sending of late notices was changed from '$strOldSetting' to '$strNewSetting'\n";
			}
		}
		
		/* OLD way of handling LateNotice exemptions
		if (DBO()->Account->DisableLateNotices->Value != DBO()->CurrentAccount->DisableLateNotices->Value)
		{
			$intCurrentValue = DBO()->CurrentAccount->DisableLateNotices->Value;
			$strChangesNote .=	"Sending of Late Notices was changed from '". 
								DBO()->Account->DisableLateNotices->FormattedValue(CONTEXT_DEFAULT, $intCurrentValue) .
								"' to '" . DBO()->Account->DisableLateNotices->FormattedValue() . "'\n";

			// When this property is changed you have to update the LateNoticeAmnesty property
			switch (DBO()->Account->DisableLateNotices->Value)
			{
				case 0:
				case 1:
					DBO()->Account->LatePaymentAmnesty = NULL;
					break;
					
				case (-1):
					// This account is ineligible to receive late notices until after the due date of the next bill
					DBO()->Account->LatePaymentAmnesty = $this->GetLatePaymentAmnestyDate(DBO()->CurrentAccount->PaymentTerms->Value);
					//DBO()->Account->LatePaymentAmnesty = date("Y-m-d", strtotime("+1 month {$intPaymentTerms} days", GetStartDateTimeForNextBillingPeriod()));
					$strChangesNote .= "Late Notices will not be generated until after ". date("d/m/Y", strtotime(DBO()->Account->LatePaymentAmnesty->Value));
					break;
			}
		}
		else
		{
			// Retain the current value of Account.LateNoticeAmnesty
			DBO()->Account->LatePaymentAmnesty = DBO()->CurrentAccount->LatePaymentAmnesty->Value;
		}
*/
		// Start the transaction
		TransactionStart();

		// Check if the Status property has been changed
		if (DBO()->Account->Archived->Value != DBO()->CurrentAccount->Archived->Value)
		{
			// Define one variable for MYSQL date/time and one of the EmployeeID
			$strTodaysDate = GetCurrentDateForMySQL();
			$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];
		
			// This is a Flag for checking if any of the account's services had to be updated
			// because of the Account's status being changed
			$bolServicesUpdated = FALSE;
		
			$strChangesNote .= "Account Status was changed from ". GetConstantDescription(DBO()->CurrentAccount->Archived->Value, 'Account') ." to ". GetConstantDescription(DBO()->Account->Archived->Value, 'Account') . "\n";
	
			switch (DBO()->Account->Archived->Value)
			{
				case ACCOUNT_ACTIVE:
					// If user has selected Active for the account status no subsequent actions have to take place
					break;
				case ACCOUNT_CLOSED:
				case ACCOUNT_DEBT_COLLECTION:
				case ACCOUNT_SUSPENDED:
					// If user has selected "Closed", "Debt Collection", "Suspended" for the account status, only Active services have their Status and 
					// ClosedOn/CloseBy properties changed
					// Active Services are those that have their Status set to Active or (their status is set to Disconnected and 
					// their ClosedOn date is in the future (signifying a change of lessee) or today).  We don't have to worry about 
					// the Services where their status is set to Disconnected and their ClosedOn Date is set to today, because that 
					// is how we are going to update the records anyway.
					
					$strWhere = "Account = <AccountId> AND (Status = <ServiceActive> OR (Status = <ServiceDisconnected> AND ClosedOn > NOW()))";
					$arrWhere = Array("AccountId" => DBO()->Account->Id->Value, "ServiceActive" => SERVICE_ACTIVE, "ServiceDisconnected" => SERVICE_DISCONNECTED);

					// Retrieve all services attached to this Account where the Status is Active
					DBL()->Service->Where->Set($strWhere, $arrWhere);
					DBL()->Service->Load();
					
					// If there are no records retrieved append to note stating this, stops confusion on notes
					if (!DBL()->Service->RecordCount() > 0)
					{
						$strChangesNote .= "No services have been affected";
					}
					else
					{
						$strChangesNote .= "The following services have been set to ". GetConstantDescription(SERVICE_DISCONNECTED, "Service") ." :\n\n";
						
						// Update the services
						foreach (DBL()->Service as $dboService)
						{
							// For each service attached to this account append information onto the note being generated
							
							$strChangesNote .= "Service Id: " . $dboService->Id->Value . ", FNN: " . $dboService->FNN->Value . ", Type: " . GetConstantDescription($dboService->ServiceType->Value, 'ServiceType') . "\n";
							// Set the service ClosedOn, ClosedBy and Status properties and save
							// Set the Service Status to SERVICE_DISCONNECTED
							$dboService->ClosedOn = $strTodaysDate;
							$dboService->ClosedBy = $intEmployeeId;
							$dboService->Status = SERVICE_DISCONNECTED;
							
							if (!$dboService->Save())
							{
								// An error occured in updating one of the services
								TransactionRollback();
								Ajax()->AddCommand("Alert", "ERROR: Updating one of the corresponding Services failed, unexpectedly.  The account has not been updated");
								return TRUE;
							}
						}
						
						// At least one service has been modified
						$bolServicesUpdated = TRUE;
					}
					break;
				case ACCOUNT_ARCHIVED:
					// If user has selected "Archived" for the account status only Active and Disconnected services have their Status and 
					// ClosedOn/CloseBy properties changed						
					$strWhere = "Account = <AccountId> AND (Status = <ServiceActive> OR Status = <ServiceDisconnected>)";
					$arrWhere = Array("AccountId" => DBO()->Account->Id->Value, "ServiceActive" => SERVICE_ACTIVE, "ServiceDisconnected" => SERVICE_DISCONNECTED);
					
					// Retrieve all services attached to this Account where the Status is Active/Disconnected								
					DBL()->Service->Where->Set($strWhere, $arrWhere);
					DBL()->Service->Load();
					
					// If their are no records retrieved append to note stating this, stops confusion on notes
					if (!DBL()->Service->RecordCount() > 0)
					{
						$strChangesNote .= "No services have been affected\n\n";
					}
					else
					{
						$strChangesNote .= "The following services have been set to ". GetConstantDescription(SERVICE_ARCHIVED, "Service") ." :\n\n";
						
						// Update the services
						foreach (DBL()->Service as $dboService)
						{
							// For each service attached to this account append information onto the note being generated
							$strChangesNote .= "Service Id: " . $dboService->Id->Value . ", FNN: " . $dboService->FNN->Value . ", Type: " . GetConstantDescription($dboService->ServiceType->Value, 'ServiceType') . "\n";

							// Set the service ClosedOn, ClosedBy and Status properties and save							
							$dboService->ClosedOn = $strTodaysDate;
							$dboService->ClosedBy = $intEmployeeId;
							$dboService->Status = SERVICE_ARCHIVED;
							if (!$dboService->Save())
							{
								// An error occured in updating one of the services
								TransactionRollback();
								Ajax()->AddCommand("Alert", "ERROR: Updating one of the corresponding Services failed, unexpectedly.  The account has not been updated");
								return TRUE;
							}
						}
						
						// At least one service has been modified
						$bolServicesUpdated = TRUE;
					}
					break;
			}
		}
		
		if ($strChangesNote)
		{
			$strChangesNote = "Account details have been modified.  The following changes were made:\n$strChangesNote";
			SaveSystemNote($strChangesNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value);
		}

		// Set the columns to save
		DBO()->Account->SetColumns("BusinessName, TradingName, ABN, ACN, Address1, Address2, Suburb, Postcode, State, BillingMethod, CustomerGroup, DisableLatePayment, Archived, DisableDDR, Sample, DisableLateNotices, LatePaymentAmnesty");
														
		if (!DBO()->Account->Save())
		{
			// Saving the account record failed
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Updating the account details failed, unexpectedly");
			return TRUE;
		}
		
		// All Database interactions were successfull
		TransactionCommit();
		
		// Fire the OnAccountDetailsUpdate Event
		$arrEvent['Account']['Id'] = DBO()->Account->Id->Value;
		Ajax()->FireEvent(EVENT_ON_ACCOUNT_DETAILS_UPDATE, $arrEvent);
		
		// Fire the OnNewNote event
		if ($strChangesNote)
		{
			Ajax()->FireOnNewNoteEvent(DBO()->Account->Id->Value);
		}
		
		// Fire the OnAccountServicesUpdate Event
		if ($bolServicesUpdated)
		{
			Ajax()->FireEvent(EVENT_ON_ACCOUNT_SERVICES_UPDATE, $arrEvent);
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// DeleteRecord
	//------------------------------------------------------------------------//
	/**
	 * DeleteRecord()
	 *
	 * Creates a generic Delete Popup for either a Payment, Adjustment or Recurring Adjustment record
	 * 
	 * Creates a generic Delete Popup for either a Payment, Adjustment or Recurring Adjustment record
	 *
	 * @return		void
	 * @method
	 *
	 */
	function DeleteRecord()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Non of these records can be deleted/cancelled/reversed while the invoicing process is running
		$bolIsInvoicing = IsInvoicing();

		// Check what sort of record is being deleted
		switch (DBO()->DeleteRecord->RecordType->Value)
		{
			case "Payment":
				if ($bolIsInvoicing)
				{
					$strErrorMsg = "ERROR: The Invoicing process is currently running.  Payments cannot be reversed at this time.  Please try again later";
					break;
				}
				DBO()->DeleteRecord->Application = "Payment";
				DBO()->DeleteRecord->Method = "Delete";
				DBO()->Payment->Load();
				break;
			case "Adjustment":
				if ($bolIsInvoicing)
				{
					$strErrorMsg = "ERROR: The Invoicing process is currently running.  Adjustments cannot be deleted at this time.  Please try again later";
					break;
				}
				DBO()->DeleteRecord->Application = "Adjustment";
				DBO()->DeleteRecord->Method = "DeleteAdjustment";
				DBO()->Charge->Load();
				break;
			case "RecurringAdjustment":
				if ($bolIsInvoicing)
				{
					$strErrorMsg = "ERROR: The Invoicing process is currently running.  Recurring Adjustments cannot be cancelled at this time.  Please try again later";
					break;
				}
				DBO()->DeleteRecord->Application = "Adjustment";
				DBO()->DeleteRecord->Method = "DeleteRecurringAdjustment";
				DBO()->RecurringCharge->Load();
				break;
			default:
				Ajax()->AddCommand("Alert", "ERROR: No record type has been declared to be deleted");
				return FALSE;
		}
		
		if ($bolIsInvoicing)
		{
			// Records cannot be deleted while the Invoicing process is running
			Ajax()->AddCommand("Alert", $strErrorMsg);
			return FALSE;
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('delete_record');

		return TRUE;
	}
	
	// $intPaymentTerms is the number of days the customer has to pay their bill
	// Returns the LatePaymentAmnesty Date as a string "dd/mm/yyyy"
	function GetLatePaymentAmnestyDate($intPaymentTerms)
	{
		// This date should be 1 month after the due date of the most recently committed bill
		// If the bill was committed today, then you would probably be refering to last month's bill
		// however the DisableLateNotices property only gets revereted from -1 to 0 when the bill is committed
		
		// Retrieve the date that the most recent bill was committed
		$selBillDate = new StatementSelect("InvoiceRun", Array("BillingDate"=>"MAX(BillingDate)"), "TRUE");
		$selBillDate->Execute();
		$arrBillDate = $selBillDate->Fetch();
		$intBillDate = strtotime($arrBillDate['BillingDate']);
		
		/*
		if (date("d/m/Y", $intBillDate) == date("d/m/Y"))
		{
			// The most recent bill was committed today
			$strDaysToAdd = "+ $intPaymentTerms days";
		}
		else
		{
			// The most recent bill was committed earlier than today
			$strDaysToAdd = "+ 1 month $intPaymentTerms days";
		}
		*/
		
		$strDaysToAdd = "+ 1 month $intPaymentTerms days";
		
		$strAmnestyDate = date("Y-m-d", strtotime($strDaysToAdd, $intBillDate));
		
		return $strAmnestyDate;
	}

    //----- DO NOT REMOVE -----//
	
	
}
?>
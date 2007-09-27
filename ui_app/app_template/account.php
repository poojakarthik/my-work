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
	// Edit
	//------------------------------------------------------------------------//
	/**
	 * Edit()
	 *
	 * Performs the logic for the Account/Edit
	 * 
	 * Performs the logic for the Account/Edit
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Edit()
	{


		if (SubmittedForm('EditAccount', 'Apply Changes'))
		{
			//Ajax()->AddCommand("Alert", DBO()->Account->CurrentStatus->Value);
			if (DBO()->Account->IsInvalid())
			{
				// The form has not passed initial validation
				//Ajax()->AddCommand("Alert", "Could not save the service.  Invalid fields are highlighted");
				//Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DEFAULT, "AccountDetailDiv");
				//return TRUE;
				//Ajax()->AddCommand("Alert", "invalid account");
			}

			if (!Validate("IsNotEmptyString", DBO()->Account->BusinessName->Value))
			{
				DBO()->Account->BusinessName->SetToInvalid();
				Ajax()->AddCommand("Alert", "Could not save the account.  BusinessName cannot be nothing");
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
				return TRUE;
			}
			$arrUpdateProperties[] = "BusinessName";

			if (Validate("Integer", DBO()->Account->ABN->Value))
			{
				DBO()->Account->ABN->SetToInvalid();
				Ajax()->AddCommand("Alert", "Could not save the account.  Not a valid ABN number");
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
				return TRUE;
			}
			$arrUpdateProperties[] = "ABN";

			if (!Validate("IsNotEmptyString", DBO()->Account->Address1->Value))
			{
				DBO()->Account->Address1->SetToInvalid();
				Ajax()->AddCommand("Alert", "Could not save the account.  Address1 cannot be nothing");
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
				return TRUE;
			}
			$arrUpdateProperties[] = "Address1";

			if (!Validate("IsNotEmptyString", DBO()->Account->Suburb->Value))
			{
				DBO()->Account->Suburb->SetToInvalid();
				Ajax()->AddCommand("Alert", "Could not save the account.  Suburb cannot be nothing");
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
				return TRUE;
			}
			$arrUpdateProperties[] = "Suburb";

			if (!Validate("IsNotEmptyString", DBO()->Account->Postcode->Value))
			{
				DBO()->Account->Postcode->SetToInvalid();
				Ajax()->AddCommand("Alert", "Could not save the account.  Postcode cannot be nothing");
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
				return TRUE;
			}
			$arrUpdateProperties[] = "Postcode";			

			if (DBO()->Account->Archived->Value != DBO()->Account->CurrentStatus->Value)
			{
				// Define system generated note
				$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
				$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
				$strNote = "Account Status was changed on $strDateTime by $strUserName with status of ".DBO()->Account->Archived->Value;				
				SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
				
				switch (DBO()->Account->Archived->Value)
				{
					case ACCOUNT_ACTIVE:
						break;
					case ACCOUNT_CLOSED:
						// get all the records DBL
						// loop through
						// change status
						$strWhere = "Account = '". DBO()->Service->Account->Value ."'";
						$strWhere .= " AND Status = '". SERVICE_ACTIVE . "'";
						DBL()->Service->Where->SetString($strWhere);
						DBL()->Service->Load();
						
						foreach (DBL()->Service as $dboService)
						{
							// set the Service Status to SERVICE_DISCONNECTED
							$dboService->Status = SERVICE_DISCONNECTED;
							$dboService->Save();
						}
						break;
					case ACCOUNT_DEBT_COLLECTION:
						$strWhere = "Account = '". DBO()->Service->Account->Value ."'";
						$strWhere .= " AND Status = '". SERVICE_ACTIVE . "'";
						DBL()->Service->Where->SetString($strWhere);
						DBL()->Service->Load();
						
						foreach (DBL()->Service as $dboService)
						{
							// set the Service Status to SERVICE_DISCONNECTED
							$dboService->Status = SERVICE_DISCONNECTED;
							$dboService->Save();
						}
						break;
					case ACCOUNT_ARCHIVED:
						// get all the records DBL
						// loop through
						// change status
						$strWhere = "Account = '". DBO()->Service->Account->Value ."'";
						$strWhere .= " AND Status = '". SERVICE_ACTIVE . "'";
						$strWhere .= " AND Status = '". SERVICE_DISCONNECTED . "'";
						DBL()->Service->Where->SetString($strWhere);
						DBL()->Service->Load();
						
						foreach (DBL()->Service as $dboService)
						{
							// set the Service Status to SERVICE_ARCHIVED
							$dboService->Status = SERVICE_ARCHIVED;
							$dboService->Save();
						}
						break;
				}
			}

			if (DBO()->Account->TradingName->Value)
			{
				$arrUpdateProperties[] = "TradingName";	
			}
			if (DBO()->Account->ACN->Value)
			{
				$arrUpdateProperties[] = "ACN";	
			}
			if (DBO()->Account->Address2->Value)
			{
				$arrUpdateProperties[] = "Address2";	
			}			

			$arrUpdateProperties[] = "State";	
			$arrUpdateProperties[] = "BillingMethod";
			$arrUpdateProperties[] = "CustomerGroup";
			$arrUpdateProperties[] = "DisableLatePayment";
			$arrUpdateProperties[] = "Archived";
			$arrUpdateProperties[] = "DisableDDR";

			DBO()->Account->SetColumns($arrUpdateProperties);			
			if (!DBO()->Account->Save())
			{
				Ajax()->AddCommand("Alert", "ERROR: Updating the account details failed, unexpectedly");
				return TRUE;
			}
			else
			{
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_FULL_DETAIL, "AccountDetailDiv");	
				return TRUE;
			}
		}

		DBO()->Account->SetColumns();
		DBO()->Account->Load();
		DBO()->Service->Account = DBO()->Account->Id->Value;
		DBO()->Service->Load();	
		
		Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
	}

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
	 
function Render_View()
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
			//BreadCrumb()->ViewAccount(DBO()->Account->Id->Value);
			//BreadCrumb()->ViewService(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);
			/*Menu
			   |--Account
				|--View Account
				*/
			// Load page
			$this->LoadPage('Account_View');
			//Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_FULL_DETAIL, "AccountDetailDiv");
			
			//Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
		}
		//else
		//{		
			// Load error page
		//	$this->LoadPage('Account_Error');
		//}
		/*
		//for additional functionality like change of lessee
		$someThing = $this->Module->Account->Function()
		
		*/
		//$this->Module->Account->Method();	
	}	 
	 
	function View()
	{	
		$pagePerms = PERMISSION_ADMIN;

		AuthenticatedUser()->CheckAuth();
		// Check perms
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PUBLIC);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
		}

		if (DBO()->Account->Id->Valid())
		{
			//Load account + stuff
			DBO()->Account->Load();
			DBO()->Service->Account = DBO()->Account->Id->Value;
			DBO()->Service->Load();
		
			// Context menu options
			// context menu
			ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
			ContextMenu()->Logout();
			
			// add to breadcrumb menu
			//BreadCrumb()->ViewAccount(DBO()->Account->Id->Value);
			//BreadCrumb()->ViewService(DBO()->Service->Id->Value, DBO()->Service->FNN->Value);
			/*Menu
			   |--Account
				|--View Account
				*/
			// Load page
			//$this->LoadPage('Account_View');
			Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_FULL_DETAIL, "AccountDetailDiv");
		}
		//else
		//{		
		// Load error page
		//	$this->LoadPage('Account_Error');
		//}
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		//handle saving of data on this screen (the admin fee checkbox and the payment fee radio buttons)
		//check if the form was submitted
		if (SubmittedForm('AccountDetails', 'Apply Changes'))
		{
			//Save the AccountDetails
			if (!DBO()->Account->IsInvalid())
			{
				// update the record in the Account table
				DBO()->Account->SetColumns("DisableDDR, DisableLatePayment");
				
				// Save the payment to the payment table of the vixen database
				if (!DBO()->Account->Save())
				{
					// The account details could not be updated
					Ajax()->AddCommand("AlertReload", "ERROR: The Account could not be updated.");
					return TRUE;
				}
				else
				{
					// The account details were successfully updated
					Ajax()->AddCommand("AlertReload", "The Account details have been successfully updated.");
					return TRUE;
				}
			}
		}
		
		// context menu
		ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		//BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		BreadCrumb()->View_Account(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Invoices and Payments");
		
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Invoice->Load();
		
		// Retrieve the Payments
		//"WHERE ((Account = <accId>) OR (AccountGroup = <accGrpId>) AND Account IS NULL) AND (Status conditions)"
		$strWhere  = "((Payment.Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " OR (Payment.AccountGroup = ". DBO()->Account->AccountGroup->Value .") AND (Payment.Account IS NULL))";
		$strWhere .= " AND ((Payment.Status = ". PAYMENT_WAITING .")";
		$strWhere .= " OR (Payment.Status = ". PAYMENT_PAYING .")";
		$strWhere .= " OR (Payment.Status = ". PAYMENT_FINISHED .")";
		$strWhere .= " OR (Payment.Status = ". PAYMENT_REVERSED ."))";
		DBL()->Payment->Where->SetString($strWhere);
		
		$arrColumns = Array(	"Id"=>"Payment.Id",
									"AccountGroup"=>"Payment.AccountGroup",
									"Account"=>"Payment.Account",
									"Status"=>"Payment.Status",
									"Balance"=>"Payment.Balance",
									"PaidOn"=>"Payment.PaidOn",
									"Amount"=>"Payment.Amount",
									"PaymentType"=>"Payment.PaymentType",
									"EnteredBy"=>"Payment.EnteredBy",
									"ImportedOn"=>"FileImport.ImportedOn"
								);
		DBL()->Payment->SetColumns($arrColumns);
		DBL()->Payment->SetTable("Payment LEFT OUTER JOIN FileImport ON Payment.File = FileImport.Id");
		DBL()->Payment->OrderBy("Payment.PaidOn DESC, Payment.Id DESC");
		DBL()->Payment->Load();
		
		DBL()->InvoicePayment->Account = DBO()->Account->Id->Value;
		DBL()->InvoicePayment->OrderBy("Id DESC");
		DBL()->InvoicePayment->Load();
		
		//"WHERE (Account = <accId>) AND (Status conditions)"
		$strWhere  = "(Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " AND ((Status = ". CHARGE_WAITING .")";
		$strWhere .= " OR (Status = ". CHARGE_APPROVED .")";
		$strWhere .= " OR (Status = ". CHARGE_TEMP_INVOICE .")";
		$strWhere .= " OR (Status = ". CHARGE_INVOICED ."))";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		DBL()->RecurringCharge->Account = DBO()->Account->Id->Value;
		DBL()->RecurringCharge->Archived = 0;
		DBL()->RecurringCharge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->RecurringCharge->Load();
		
		DBL()->Note->Account = DBO()->Account->Id->Value;
		DBL()->Note->OrderBy("Datetime DESC");
		DBL()->Note->Load();
		DBL()->NoteType->Load();
		
		// Calculate the Account Balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Calculate the Account Overdue Amount
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled adjustments
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('invoices_and_payments');

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
		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		

		//Check what sort of record is being deleted
		switch (DBO()->DeleteRecord->RecordType->Value)
		{
			case "Payment":
				DBO()->DeleteRecord->Application = "Payment";
				DBO()->DeleteRecord->Method = "Delete";
				DBO()->Payment->Load();
				break;
			case "Adjustment":
				DBO()->DeleteRecord->Application = "Adjustment";
				DBO()->DeleteRecord->Method = "DeleteAdjustment";
				DBO()->Charge->Load();
				break;
			case "RecurringAdjustment":
				DBO()->DeleteRecord->Application = "Adjustment";
				DBO()->DeleteRecord->Method = "DeleteRecurringAdjustment";
				DBO()->RecurringCharge->Load();
				break;
			default:
				Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
				Ajax()->AddCommand("AlertReload", "ERROR: No record type has been declared to be deleted");
				return FALSE;
				break;
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('delete_record');

		return TRUE;
	}

    //----- DO NOT REMOVE -----//
	
}

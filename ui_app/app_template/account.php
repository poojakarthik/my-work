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
	 * Performs the logic for viewing a service
	 * 
	 * Performs the logic for viewing a service linked to an account
	 *
	 * @return		void
	 * @method		View
	 *
	 */
	function ViewServices()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolIsAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		// Attempt to load the account
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("AlertReload", "The account ". DBO()->Account->Id->Value ." could not be found");
			return TRUE;
		}
		
		if (!$bolIsAdminUser)
		{
			// User does not have admin privileges and therefore cannot view archived services
			$strWhere = "Account = <Account> AND Status != ". SERVICE_ARCHIVED;
		}
		else
		{
			// User has admin privileges and can view all services regardless of their status
			$strWhere = "Account = <Account>";
		}
		
		// Load all the services belonging to the account, that the user has permission to view
		DBL()->Service->Where->Set($strWhere, Array("Account"=>DBO()->Account->Id->Value));
		DBL()->Service->OrderBy("FNN");
		DBL()->Service->Load();
		
		if (DBL()->Service->RecordCount() > 0)
		{
			DBO()->Note->NoteType = "All";
			$this->LoadPage('services_view');
			return TRUE;
		}
		else
		{
			Ajax()->AddCommand("Alert", "This account has no viewable services");
			return TRUE;
		}
	}	

	//------------------------------------------------------------------------//
	// EditDetails
	//------------------------------------------------------------------------//
	/**
	 * EditDetails()
	 *
	 * Performs the logic for the Account/Edit
	 * 
	 * Performs the logic for the Account/Edit
	 *
	 * @return		void
	 * @method
	 *
	 */
	function EditDetails()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		DBO()->Account->Load();
		
		/*if (DBO()->Service->Id->Value == NULL)
		{
			//may need to add date time stamp to get most recent record!
			$strWhere = "Account = \"".DBO()->Account->Id->Value."\"";
			DBO()->Service->Where->SetString($strWhere);
			DBO()->Service->Load();
			Ajax()->AddCommand("Alert", "entered from editdetails-->>>>".DBO()->Service->Id->Value);
		}*/
		
		Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
	}

	//------------------------------------------------------------------------//
	// ValidateDetails
	//------------------------------------------------------------------------//
	/**
	 * ValidateDetails()
	 *
	 * Validates the submitted form data
	 * 
	 * Validates the submitted form data
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ValidateDetails()
	{
		if (SubmittedForm('EditAccount', 'Apply Changes'))
		{
			if (DBL()->Service->RecordCount() == 0)
			{
				$strWhere = "Account = \"".DBO()->Account->Id->Value."\"";
				$strWhere .= " AND (ClosedOn > NOW() OR ClosedOn IS NULL)";
				DBL()->Service->Where->SetString($strWhere);
				DBL()->Service->Load();
			}
			
			if (DBO()->Account->IsInvalid())
			{
				Ajax()->AddCommand("Alert", "ERROR: the form didnt pass initial validation, invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
				return TRUE;			
			}

			if (DBO()->Account->Archived->Value != DBO()->Account->CurrentStatus->Value)
			{
				// Define system generated note
				$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
				$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);

				$strNote = "Account Status was changed to " . GetConstantDescription(DBO()->Account->Archived->Value, 'Account') . "\non $strDateTime by $strUserName Services Affected Are :\n\n";
				foreach (DBL()->Service as $dboService)
				{
					$strNote .= "Service Id : " . $dboService->Id->Value . ", FNN : " . $dboService->FNN->Value . ", Service Type : " . GetConstantDescription($dboService->ServiceType->Value, 'ServiceType') . "\n";
				}
				SaveSystemNote($strNote, $dboService->AccountGroup->Value, $dboService->Account->Value, NULL, $dboService->Id->Value);
			
				$mixTodaysDate = GetCurrentDateForMySQL();
				$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];
			

				$strNote = "Account Status was changed on $strDateTime by $strUserName with status of ".DBO()->Account->Archived->Value;				
				//TODO! DBO()->Service is undefined
				SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
				
				switch (DBO()->Account->Archived->Value)
				{
					case ACCOUNT_ACTIVE:
						break;
					case ACCOUNT_CLOSED:
						$strWhere = "Account = '". DBO()->Service->Account->Value ."'";
						$strWhere .= " AND Status = '". SERVICE_ACTIVE . "'";
						DBL()->Service->Where->SetString($strWhere);
						DBL()->Service->Load();
						
						foreach (DBL()->Service as $dboService)
						{
							// set the Service Status to SERVICE_DISCONNECTED
							$dboService->ClosedOn = $mixTodaysDate;
							$dboService->ClosedBy = $intEmployeeId; 
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
							$dboService->ClosedOn = $mixTodaysDate;
							$dboService->ClosedBy = $intEmployeeId;							
							$dboService->Status = SERVICE_DISCONNECTED;
							$dboService->Save();
						}
						break;
					case ACCOUNT_ARCHIVED:
						$strWhere = "Account = '". DBO()->Service->Account->Value ."'";
						$strWhere .= " AND Status = '". SERVICE_ACTIVE . "'";
						$strWhere .= " AND Status = '". SERVICE_DISCONNECTED . "'";
						DBL()->Service->Where->SetString($strWhere);
						DBL()->Service->Load();
						
						foreach (DBL()->Service as $dboService)
						{
							// set the Service Status to SERVICE_ARCHIVED
							$dboService->ClosedOn = $mixTodaysDate;
							$dboService->ClosedBy = $intEmployeeId;							
							$dboService->Status = SERVICE_ARCHIVED;
							$dboService->Save();
						}
						break;
				}
			}
	
			DBO()->Account->SetColumns("BusinessName,TradingName,ABN,ACN,Address1,Address2,Suburb,Postcode,State,BillingMethod,CustomerGroup,DisableLatePayment,Archived,DisableDDR");
															
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
	}

	//------------------------------------------------------------------------//
	// ViewDetails
	//------------------------------------------------------------------------//
	/**
	 * ViewDetails()
	 *
	 * Performs the logic for the Account Details popup
	 * 
	 * Performs the logic for the Account Details popup
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ViewDetails()
	{	
		AuthenticatedUser()->CheckAuth();
		// Check permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PUBLIC | PERMISSION_OPERATOR);

		if (DBO()->Account->Id->Value)
		{
			//Load account and service
			DBO()->Account->Load();
			DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
			if (DBL()->Service->RecordCount() == 0)
			{
				$strWhere = "Account = \"".DBO()->Account->Id->Value."\"";
				$strWhere .= " AND (ClosedOn > NOW() OR ClosedOn IS NULL)";
				DBL()->Service->Where->SetString($strWhere);
				DBL()->Service->Load();
			}
			// Load page
			$this->LoadPage('account_view');
		}
		else
		{
			Ajax()->AddCommand("Alert", "ERROR: could not load the page as no Account Id was specified");
			return TRUE;			
		}
	}	 
	
	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the account/view page
	 * 
	 * Performs the logic for the account/view page, loads the account and service
	 * and renders the AccountDetails
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
		}

		if (DBO()->Account->Id->Value)
		{
			//Load account and service
			DBO()->Account->Load();
			DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);
			Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_FULL_DETAIL, "AccountDetailDiv");
		}
		else
		{
			Ajax()->AddCommand("Alert", "ERROR: could not load the page as no Account Id was specified");
			return TRUE;			
		}
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
		//ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Services->View_Services(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
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

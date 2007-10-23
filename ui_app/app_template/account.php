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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolIsAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
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
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->InvoicesAndPayments(DBO()->Account->Id->Value);
		BreadCrumb()->SetCurrentPage("Services");
		
		// context menu
		ContextMenu()->Account_Menu->Account->View_Account_Details(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// Attempt to load the account
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("AlertReload", "The account ". DBO()->Account->Id->Value ." could not be found");
			return TRUE;
		}
		
		// Load all the services belonging to the account, that the user has permission to view
		
		DBL()->Contact->Account = DBO()->Account->Id->Value;
		DBL()->Contact->OrderBy("LastName, FirstName");
		DBL()->Contact->Load();
		
		$this->LoadPage('account_contacts');
		return TRUE;
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
		Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
	}

	//------------------------------------------------------------------------//
	// ValidateAndSaveDetails
	//------------------------------------------------------------------------//
	/**
	 * ValidateAndSaveDetails()
	 *
	 * Validates the submitted form data
	 * 
	 * Validates the submitted form and saves the data, executed
	 * when 'Apply Changes' is clicked on the Edit Account, this also builds
	 * a system note identifying the services that have been affected if any
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ValidateAndSaveDetails()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
	
		// If the validation has failed display the invalid fields			
		if (DBO()->Account->IsInvalid())
		{
			Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT_DETAIL, "AccountDetailDiv");
			return TRUE;			
		}

		// if Account Archived value does not equal the currect archived status of the account it 
		// has therefor been changed by the user 
		if (DBO()->Account->Archived->Value != DBO()->Account->CurrentStatus->Value)
		{
			// Define one variable to the current data and time
			$strDateTime = OutputMask()->LongDateAndTime(GetCurrentDateAndTimeForMySQL());
			$strUserName = GetEmployeeName(AuthenticatedUser()->_arrUser['Id']);
		
			// Define one variable for MYSQL data/time and one of the EmployeeID
			$mixTodaysDate = GetCurrentDateForMySQL;
			$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];
		
			// Beginning of the System Note
			$strNote = "Account Status was changed to " . GetConstantDescription(DBO()->Account->Archived->Value, 'Account') . "\non $strDateTime by $strUserName\n";
	
			switch (DBO()->Account->Archived->Value)
			{
				case ACCOUNT_ACTIVE:
					// If user has selected Active for the account status no subsequent service is changed
					break;
				case ACCOUNT_CLOSED:
					// If user has selected Closed for the account status only Active services have their Status and 
					// ClosedOn/CloseBy properties changed
					$strWhere = "Account = <AccountId>";
					$strWhere .= " AND Status = <ServiceStatus>";
					// Retrieve all services attached to this Account where the Status is Active
					DBL()->Service->Where->Set($strWhere, Array("AccountId" => DBO()->Account->Id->Value, "ServiceStatus" => SERVICE_ACTIVE));
					DBL()->Service->Load();
					
					// If their are no records retrieved append to note stating this, stops confusion on notes
					if (!DBL()->Service->RecordCount() > 0)
					{
						$strNote .= "No Services have been affected\n\n";
					}
					else
					{
						$strNote .= "Services Affected Are :\n\n";
						foreach (DBL()->Service as $dboService)
						{
							// For each service attached to this account append information onto the note being generated
							// Set the service ClosedOn, ClosedBy and Status properties and save
							$strNote .= "Service Id : " . $dboService->Id->Value . ", FNN : " . $dboService->FNN->Value . ", Service Type : " . GetConstantDescription($dboService->ServiceType->Value, 'ServiceType') . "\n";
							// set the Service Status to SERVICE_DISCONNECTED
							$dboService->ClosedOn = $mixTodaysDate;
							$dboService->ClosedBy = $intEmployeeId; 
							$dboService->Status = SERVICE_DISCONNECTED;
							$dboService->Save();
						}
					}
					break;
				case ACCOUNT_DEBT_COLLECTION:
					// If user has selected Debt Collection for the account status only Active services have their Status and 
					// ClosedOn/CloseBy properties changed					
					$strWhere = "Account = <AccountId>";
					$strWhere .= " AND Status = ". SERVICE_ACTIVE;
					// Retrieve all services attached to this Account where the Status is Active						
					DBL()->Service->Where->Set($strWhere, Array("AccountId" => DBO()->Account->Id->Value));
					DBL()->Service->Load();

					// If their are no records retrieved append to note stating this, stops confusion on notes
					if (!DBL()->Service->RecordCount() > 0)
					{
						$strNote .= "No Services have been affected\n\n";
					}
					else
					{
						$strNote .= "Services Affected Are :\n\n";
						foreach (DBL()->Service as $dboService)
						{
							// For each service attached to this account append information onto the note being generated
							// Set the service ClosedOn, ClosedBy and Status properties and save						
							$strNote .= "Service Id : " . $dboService->Id->Value . ", FNN : " . $dboService->FNN->Value . ", Service Type : " . GetConstantDescription($dboService->ServiceType->Value, 'ServiceType') . "\n";
							// set the Service Status to SERVICE_DISCONNECTED
							$dboService->ClosedOn = $mixTodaysDate;
							$dboService->ClosedBy = $intEmployeeId;							
							$dboService->Status = SERVICE_DISCONNECTED;
							$dboService->Save();
						}
					}
					break;
				case ACCOUNT_ARCHIVED:
					// If user has selected Archived for the account status only Active and Disconnected services have their Status and 
					// ClosedOn/CloseBy properties changed						
					$strWhere = "Account = <AccountId>";
					$strWhere .= " AND (Status = " . SERVICE_ACTIVE;
					$strWhere .= " OR Status = " . SERVICE_DISCONNECTED . ")";
					// Retrieve all services attached to this Account where the Status is Active/Disconnected								
					DBL()->Service->Where->Set($strWhere, Array("AccountId" => DBO()->Account->Id->Value));
					DBL()->Service->Load();
					
					// If their are no records retrieved append to note stating this, stops confusion on notes
					if (!DBL()->Service->RecordCount() > 0)
					{
						$strNote .= "No Services have been affected\n\n";
					}
					else
					{
						$strNote .= "Services Affected Are :\n\n";
						foreach (DBL()->Service as $dboService)
						{
							// For each service attached to this account append information onto the note being generated
							// Set the service ClosedOn, ClosedBy and Status properties and save							
							$strNote .= "Service Id : " . $dboService->Id->Value . ", FNN : " . $dboService->FNN->Value . ", Service Type : " . GetConstantDescription($dboService->ServiceType->Value, 'ServiceType') . "\n";
							// set the Service Status to SERVICE_ARCHIVED
							$dboService->ClosedOn = $mixTodaysDate;
							$dboService->ClosedBy = $intEmployeeId;							
							$dboService->Status = SERVICE_ARCHIVED;
							$dboService->Save();
						}
					}
					break;
			}
			// Save the system note
			SaveSystemNote($strNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value, NULL, NULL);
		}

		// Set the columns to save
		DBO()->Account->SetColumns("BusinessName,TradingName,ABN,ACN,Address1,Address2,Suburb,Postcode,State,BillingMethod,CustomerGroup,DisableLatePayment,Archived,DisableDDR");
														
		if (!DBO()->Account->Save())
		{
			Ajax()->AddCommand("Alert", "ERROR: Updating the account details failed, unexpectedly");
			return TRUE;
		}
		else
		{
			// If properties have saved successfully display the account details page, and calculate the account balance
			DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);				
			Ajax()->AddCommand("AlertReload", "The details have been successfully saved");
			//return TRUE;
			//Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_FULL_DETAIL, "AccountDetailDiv");	
			return TRUE;
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
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// Load the Account
		if (!DBO()->Account->Load())
		{
			// The account could not be loaded
			Ajax()->AddCommand("Alert", "ERROR: Account ". DBO()->Account->Id->Value ." could not be loaded");
			return TRUE;
		}

		// If the account is archived, make sure the user has permission to view it
		if (DBO()->Account->Archived->Value == ACCOUNT_ARCHIVED && !AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			// The user does not have permission to view this account
			Ajax()->AddCommand("Alert",	"ERROR: You do not have permission to view account ". DBO()->Account->Id->Value .
										" because its status is set to " . GetConstantDescription(DBO()->Account->Archived->Value, "Account"));
			return TRUE;
		}

		// Calculate the account balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Load page
		$this->LoadPage('account_view');
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
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
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
					Ajax()->AddCommand("AlertReload", "ERROR: The Account could not be updated");
					return TRUE;
				}
				else
				{
					// The account details were successfully updated
					Ajax()->AddCommand("AlertReload", "The Account details have been successfully updated");
					return TRUE;
				}
			}
		}
		
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Invoices and Payments");
		
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// If the account is archived, check that the user has permission to view it
		if (DBO()->Account->Archived->Value == ACCOUNT_ARCHIVED && !$bolUserHasAdminPerm)
		{
			// The user does not have permission to view this account
			DBO()->Error->Message = "You do not have permission to view account: ". DBO()->Account->Id->value ." because its status = " . GetConstantDescription(DBO()->Account->Archived->Value, "Account");
			$this->LoadPage('error');
			return FALSE;
		}

		// context menu
		ContextMenu()->Account_Menu->Account->View_Account_Details(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Contact(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Change_Payment_Method(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Account->Add_Associated_Account(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		ContextMenu()->Account_Menu->Notes->Add_Account_Note(DBO()->Account->Id->Value);

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
		
		// Build the list of columns to use for the Charge DBL (as it is pulling this information from 2 tables)
		$arrColumns = Array(	'Id' => 'C.Id',	'AccountGroup'=>'C.AccountGroup',	'Account'=>'C.Account',	'Service'=>'C.Service',
								'InvoiceRun'=>'C.InvoiceRun',	'CreatedBy'=>'C.CreatedBy', 'CreatedOn'=>'C.CreatedOn', 'ApprovedBy'=>'C.ApprovedBy',
								'ChargeType'=>'C.ChargeType', 'Description'=>'C.Description', 'ChargedOn'=>'C.ChargedOn', 'Nature'=>'C.Nature',
								'Amount'=>'C.Amount', 'Invoice'=>'C.Invoice', 'Notes'=>'C.Notes', 'Status'=>'C.Status', 'FNN'=>'S.FNN');
		DBL()->Charge->SetColumns($arrColumns);
		DBL()->Charge->SetTable("Charge AS C LEFT OUTER JOIN Service AS S ON C.Service = S.Id");
		
		//"WHERE (Account = <accId>) AND (Status conditions)"
		$strWhere  = "(C.Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " AND ((C.Status = ". CHARGE_WAITING .")";
		$strWhere .= " OR (C.Status = ". CHARGE_APPROVED .")";
		$strWhere .= " OR (C.Status = ". CHARGE_TEMP_INVOICE .")";
		$strWhere .= " OR (C.Status = ". CHARGE_INVOICED ."))";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
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
		$intAccountId = DBO()->Account->Id->Value;
		DBL()->RecurringCharge->Where->Set("RC.Account = <Account> AND RC.Archived = 0", Array("Account"=>$intAccountId));
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
		// Check user authorization
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

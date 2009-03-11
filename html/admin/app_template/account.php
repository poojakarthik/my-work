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
	public static function BuildContextMenu($intAccountId)
	{
		$bolUserHasOperatorPerm		= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		
		ContextMenu()->Account->Account_Overview($intAccountId);
		ContextMenu()->Account->Invoices_And_Payments($intAccountId);
		ContextMenu()->Account->Services->List_Services($intAccountId);
		ContextMenu()->Account->Contacts->List_Contacts($intAccountId);
		ContextMenu()->Account->View_Cost_Centres($intAccountId);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account->Services->Add_Services($intAccountId);
			ContextMenu()->Account->Contacts->Add_Contact($intAccountId);
			ContextMenu()->Account->Payments->Make_Payment($intAccountId);
			ContextMenu()->Account->Adjustments->Add_Adjustment($intAccountId);
			ContextMenu()->Account->Adjustments->Add_Recurring_Adjustment($intAccountId);
			ContextMenu()->Account->Payments->Change_Payment_Method($intAccountId);
			ContextMenu()->Account->Add_Associated_Account($intAccountId);
			ContextMenu()->Account->Provisioning->Provisioning(NULL, $intAccountId);
			ContextMenu()->Account->Provisioning->ViewProvisioningHistory(NULL, $intAccountId);
			ContextMenu()->Account->Notes->Add_Account_Note($intAccountId);
			if (Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL) && count(FlexSale::listForAccountId($intAccountId, NULL, 1)))
			{
				// The account has sales associated with it
				ContextMenu()->Account->Sales->ViewSalesForAccount($intAccountId);
			}
		}
		ContextMenu()->Account->Notes->View_Account_Notes($intAccountId);
		if (Flex_Module::isActive(FLEX_MODULE_TICKETING) && Ticketing_User::currentUserIsTicketingUser())
		{
			ContextMenu()->Account->Tickets->ViewTicketsForAccount($intAccountId);
			ContextMenu()->Account->Tickets->AddTicket($intAccountId);
		}
		
	}
	
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
		
		$intAccountId = DBO()->Account->Id->Value;
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview($intAccountId, TRUE);
		BreadCrumb()->SetCurrentPage("Services");
		
		// context menu
		self::BuildContextMenu($intAccountId);
		
		// Load all the services belonging to the account, that the user has permission to view
		DBO()->Account->Services = $this->GetServices(DBO()->Account->Id->Value, SERVICE_ACTIVE);
		
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
		$strWhere = "(AccountGroup = <AccountGroup> AND CustomerContact = 1) OR Account = <Account> OR Id = <AccountPrimaryContact>";
		$arrWhere = array("AccountGroup"=>DBO()->Account->AccountGroup->Value, "Account"=>DBO()->Account->Id->Value, "AccountPrimaryContact"=>DBO()->Account->PrimaryContact->Value);
		DBL()->Contact->Where->Set($strWhere, $arrWhere);
		DBL()->Contact->OrderBy("FirstName, LastName");
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
		
		// breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value, TRUE);
		BreadCrumb()->SetCurrentPage("Invoices and Payments");
		
		// Setup all DBO and DBL objects required for the page
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		$intAccountId = DBO()->Account->Id->Value;
		
		// context menu
		self::BuildContextMenu($intAccountId);
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		$this->loadInvoices();
		
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
								'invoice_run_id'=>'C.invoice_run_id',	'CreatedBy'=>'C.CreatedBy', 'CreatedOn'=>'C.CreatedOn', 'ApprovedBy'=>'C.ApprovedBy',
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
								'Archived'=>'RC.Archived', 'in_advance'=>'RC.in_advance', 'FNN'=>'S.FNN');
		DBL()->RecurringCharge->SetColumns($arrColumns);
		DBL()->RecurringCharge->SetTable("RecurringCharge AS RC LEFT OUTER JOIN Service AS S ON RC.Service = S.Id");
		
		// I can't directly use a DBObject property or method as a parameter of another DBObject or DBList method
		// On account of how the Property token works 
		DBL()->RecurringCharge->Where->Set("RC.Account = <Account> AND RC.Archived = 0", Array("Account"=>$intAccountId));
		DBL()->RecurringCharge->OrderBy("StartedOn DESC, Id DESC");
		DBL()->RecurringCharge->Load();
		
		// Calculate the Account Balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance(DBO()->Account->Id->Value);

		// Calculate the Account Overdue Amount
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled adjustments
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Load all contacts, with the primary being listed first, and then those belonging to this account specifically, then those belonging to the account group who can access this account
		if (DBO()->Account->PrimaryContact->Value)
		{
			// Make sure the contact specified belongs to the AccountGroup
			$intPrimaryContactId	= DBO()->Account->PrimaryContact->Value;
			$intAccountGroupId		= DBO()->Account->AccountGroup->Value;
			
			DBL()->Contact->Where->SetString("Id = $intPrimaryContactId OR Account = $intAccountId OR (CustomerContact = 1 AND AccountGroup = $intAccountGroupId)");
			DBL()->Contact->OrderBy("(Id = $intPrimaryContactId) DESC, (Account = $intAccountId) DESC, FirstName ASC, LastName ASC");
			DBL()->Contact->SetLimit(3);
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
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
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
		$intAccountId = DBO()->Account->Id->Value;
		
		// context menu
		self::BuildContextMenu($intAccountId);

		$this->loadInvoices(3);

		// Calculate the Account Balance
		DBO()->Account->Balance = $this->Framework->GetAccountBalance($intAccountId);

		// Calculate the Account Overdue Amount
		DBO()->Account->Overdue = $this->Framework->GetOverdueBalance($intAccountId);
		
		// Calculate the Account's total unbilled adjustments
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges($intAccountId);
		
		// Make sure the contact specified belongs to the AccountGroup
		$intPrimaryContactId		= DBO()->Account->PrimaryContact->Value;
		$intAccountGroupId			= DBO()->Account->AccountGroup->Value;
		$strContactWhereClause		= "Account = $intAccountId OR (CustomerContact = 1 AND AccountGroup = $intAccountGroupId)";
		$strContactOrderByClause	= "(Account = $intAccountId) DESC, FirstName ASC, LastName ASC";
		if ($intPrimaryContactId !== NULL)
		{
			$strContactWhereClause		= "Id = $intPrimaryContactId OR " .$strContactWhereClause;
			$strContactOrderByClause	= "(Id = $intPrimaryContactId) DESC, " .$strContactOrderByClause;
		}
		DBL()->Contact->Where->SetString($strContactWhereClause);
		DBL()->Contact->OrderBy($strContactOrderByClause);
		DBL()->Contact->SetLimit(3);
		DBL()->Contact->Load();
		
		// Load the List of services
		// Load all the services belonging to the account, that the user has permission to view (which is currently all of them)
		DBO()->Account->Services = $this->GetServices(DBO()->Account->Id->Value, SERVICE_ACTIVE);
		
		// Load the user notes
		LoadNotes(DBO()->Account->Id->Value);
		
		// Retrieve the Account_Group object
		DBO()->Account->AccountGroupObject = Account_Group::getForId(DBO()->Account->AccountGroup->Value);
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('account_overview');

		return TRUE;
	}
	
	function loadInvoices($limit=FALSE)
	{
		$intAccountId = DBO()->Account->Id->Value;

		// The DBList storing the invoices should be ordered so that the most recent is first
		$arrInvoiceColumns = array(	"Id"				=> "I.Id",
									"AccountGroup"		=> "I.AccountGroup",
									"Account"			=> "I.Account",
									"CreatedOn"			=> "I.CreatedOn",
									"DueOn"				=> "I.DueOn",
									"SettledOn"			=> "I.SettledOn",
									"Credits"			=> "I.Credits",
									"Debits"			=> "I.Debits",
									"Total"				=> "I.Total",
									"Tax"				=> "I.Tax",
									"TotalOwing"		=> "I.TotalOwing",
									"Balance"			=> "I.Balance",
									"Disputed"			=> "I.Disputed",
									"AccountBalance"	=> "I.AccountBalance",
									"DeliveryMethod"	=> "I.DeliveryMethod",
									"Status"			=> "I.Status",
									"invoice_run_id"	=> "I.invoice_run_id"
									);
		
		
		$arrPermittedTypes = array(INVOICE_RUN_TYPE_SAMPLES, INVOICE_RUN_TYPE_LIVE, INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL);
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
		{
			$arrPermittedTypes[] = INVOICE_RUN_TYPE_INTERNAL_SAMPLES;
		}
		$strInvoiceTables = "Invoice AS I INNER JOIN InvoiceRun AS ir ON I.invoice_run_id = ir.Id";
		
		$strInvoiceWhere = "I.Account = $intAccountId AND I.Status != ". INVOICE_TEMP ." AND ir.invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED ." AND ir.invoice_run_type_id IN (". INVOICE_RUN_TYPE_LIVE.", ".INVOICE_RUN_TYPE_INTERIM.", ".INVOICE_RUN_TYPE_FINAL.")";

		DBL()->InvoicedInvoice->SetTable($strInvoiceTables);
		DBL()->InvoicedInvoice->SetColumns($arrInvoiceColumns);
		DBL()->InvoicedInvoice->Where->SetString($strInvoiceWhere);
		DBL()->InvoicedInvoice->OrderBy("I.CreatedOn DESC, I.Id DESC");
		if ($limit !== FALSE)
		{
			DBL()->InvoicedInvoice->SetLimit(intval($limit));
		}
		DBL()->InvoicedInvoice->Load();


		$strInvoiceTables .= " LEFT OUTER JOIN invoice_run_schedule irs ON ir.invoice_run_schedule_id = irs.id LEFT JOIN invoice_run_type ON ir.invoice_run_type_id = invoice_run_type.id";
		$arrInvoiceColumns['Status'] = 'CASE WHEN irs.description IS NULL THEN CASE WHEN invoice_run_type.description LIKE "%Sample%" THEN invoice_run_type.description ELSE CONCAT(invoice_run_type.description, " Sample") END ELSE irs.description END';

		$strInvoiceWhere = "" .
			"       I.Account = $intAccountId " .
			"   AND I.Status = ". INVOICE_TEMP .
            "   AND ir.invoice_run_status_id = ". INVOICE_RUN_STATUS_TEMPORARY .
            "   AND ir.invoice_run_type_id IN (". implode(",", $arrPermittedTypes) . ") " . 
			"   AND ir.Id > (" .
			"     SELECT MAX(Id) " .
			"       FROM (" .
			"          SELECT Irx.Id AS Id " .
			"            FROM Invoice AS Inv " .
			"                 INNER JOIN InvoiceRun AS Irx ON Inv.invoice_run_id = Irx.Id " .
			"           WHERE Inv.Account = $intAccountId " .
			"             AND Irx.invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED .
			"             AND Irx.invoice_run_type_id IN (". implode(",", $arrPermittedTypes) . ") " .
            "          UNION " .
            "          SELECT 0 " .
            "            FROM database_version " .
			"       ) invoice_run_ids" .
			")";
		DBL()->Invoice->SetTable($strInvoiceTables);
		DBL()->Invoice->SetColumns($arrInvoiceColumns);
		DBL()->Invoice->Where->SetString($strInvoiceWhere);
		DBL()->Invoice->OrderBy("I.CreatedOn DESC, I.Id DESC");
		DBL()->Invoice->Load();
		
		foreach (DBL()->InvoicedInvoice as $dboInvoicedInvoice)
		{
			DBL()->Invoice->AddRecord($dboInvoicedInvoice->AsArray());
		}
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
		DBO()->Account->Services = $this->GetServices(DBO()->ServiceList->Account->Value, DBO()->ServiceList->Filter->Value);
		DBO()->Account->Id = DBO()->ServiceList->Account->Value;
		
		//Render the AccountServices table
		Ajax()->RenderHtmlTemplate("AccountServicesList", HTML_CONTEXT_DEFAULT, DBO()->ServiceList->ContainerDivId->Value);

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetServices
	//------------------------------------------------------------------------//
	/**
	 * GetServices()
	 *
	 * Builds an array structure defining every service belonging to the account, and a history of their status, and their plan details
	 * 
	 * Builds an array structure defining every service belonging to the account, and a history of their status, and their plan details
	 * The history details when the service was activated(or created) and Closed(disconnected or archived)
	 * It will always have at least one record
	 * On Success the returned array will be of the format:
	 * $arrServices[]	['Id']			This will be the Id of the latest service record to model this FNN on this account
	 * 					['FNN']
	 * 					['ServiceType']
	 * 					['CurrentPlan']	['Id']
	 * 									['Name']
	 * 					['FuturePlan']	['Id']
	 * 									['Name']
	 * 									['StartDatetime']
	 * 					['History'][]	['ServiceId']		These will be ordered from Latest to Earliest Service records modelling this Service for this account
	 * 									['CreatedOn']
	 * 									['ClosedOn']
	 * 									['CreatedBy']
	 * 									['ClosedBy']
	 * 									['NatureOfCreation']
	 * 									['NatureOfClosure']
	 * 									['LastOwner']
	 * 									['NextOwner']
	 * 									['Status']
	 * 									['LineStatus']
	 * 									['LineStatusDate']
	 * 
	 * @param	int		$intAccount		Id of the Account to retrieve the services of
	 * @param	int		$intFilter		optional, Filter constant.  Defaults to SERVICE_ACTIVE
	 * 									0 						:	Retrieve all Services
	 * 									SERVICE_ACTIVE			:	Retrieve all Services with ClosedOn == NULL or ClosedOn >= NOW()
	 * 									SERVICE_DISCONNECTED	:	Retrieve all Services with Status == SERVICE_DISCONNECTED AND ClosedOn in the past 
	 *									SERVICE_ARCHIVED		:	Retrieve all Services with Status == SERVICE_ARCHIVED AND ClosedOn in the past
	 *
	 * @return	mixed					FALSE:	On database error
	 * 									Array:  $arrServices
	 * 	
	 * @method
	 */
	function GetServices($intAccount, $intFilter=SERVICE_ACTIVE)
	{
		// Load all the services belonging to the account
		// OLD method
		//DBL()->Service->Where->Set("Account = <Account>", Array("Account"=>DBO()->Account->Id->Value));
		//DBL()->Service->OrderBy("ServiceType ASC, FNN ASC, Id DESC");
		//DBL()->Service->Load();
		
		// Retrieve all the services belonging to the account
		$strTables	= "	Service AS S 
						LEFT JOIN ServiceRatePlan AS SRP1 ON S.Id = SRP1.Service AND SRP1.Id = (SELECT SRP2.Id 
								FROM ServiceRatePlan AS SRP2 
								WHERE SRP2.Service = S.Id AND NOW() BETWEEN SRP2.StartDatetime AND SRP2.EndDatetime
								ORDER BY SRP2.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP1 ON SRP1.RatePlan = RP1.Id
						LEFT JOIN ServiceRatePlan AS SRP3 ON S.Id = SRP3.Service AND SRP3.Id = (SELECT SRP4.Id 
								FROM ServiceRatePlan AS SRP4 
								WHERE SRP4.Service = S.Id AND SRP4.StartDatetime BETWEEN NOW() AND SRP4.EndDatetime
								ORDER BY SRP4.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP2 ON SRP3.RatePlan = RP2.Id";
		$arrColumns	= Array("Id" 						=> "S.Id",
							"FNN"						=> "S.FNN",
							"Indial100"					=> "S.Indial100",
							"ServiceType"				=> "S.ServiceType", 
							"Status"		 			=> "S.Status",
							"LineStatus"				=> "S.LineStatus",
							"LineStatusDate"			=> "S.LineStatusDate",
							"CreatedOn"					=> "S.CreatedOn", 
							"ClosedOn"					=> "S.ClosedOn",
							"CreatedBy"					=> "S.CreatedBy", 
							"ClosedBy"					=> "S.ClosedBy",
							"NatureOfCreation"			=> "S.NatureOfCreation",
							"NatureOfClosure"			=> "S.NatureOfClosure",
							"LastOwner"					=> "S.LastOwner",
							"NextOwner"					=> "S.NextOwner",
							"CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"CurrentPlanBrochureId"		=> "RP1.brochure_document_id",
							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanBrochureId"		=> "RP2.brochure_document_id",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime");
		$strWhere	= "S.Account = <AccountId> AND (S.ClosedOn IS NULL OR S.CreatedOn <= S.ClosedOn)";
		$arrWhere	= Array("AccountId" => $intAccount);
		$strOrderBy	= ("S.ServiceType ASC, S.FNN ASC, S.Id DESC");
		
		$selServices = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
		if ($selServices->Execute($arrWhere) === FALSE)
		{
			// An error occurred
			return FALSE;
		}
		
		$arrServices	= Array();
		$arrRecord		= $selServices->Fetch();
		while ($arrRecord !== FALSE)
		{
			// Create the Service Array
			$arrService = Array (
									"Id"			=> $arrRecord['Id'],
									"FNN"			=> $arrRecord['FNN'],
									"Indial100"		=> $arrRecord['Indial100'],
									"ServiceType"	=> $arrRecord['ServiceType']
								);

			// Add details about the Service's current plan, if it has one
			if ($arrRecord['CurrentPlanId'] != NULL)
			{
				$arrService['CurrentPlan'] = Array	(
														"Id"					=> $arrRecord['CurrentPlanId'],
														"Name"					=> $arrRecord['CurrentPlanName'],
														"brochure_document_id"	=> $arrRecord['CurrentPlanBrochureId']
													);
			}
			else
			{
				$arrService['CurrentPlan'] = NULL;
			}
			
			// Add details about the Service's Future scheduled plan, if it has one
			if ($arrRecord['FuturePlanId'] != NULL)
			{
				$arrService['FuturePlan'] = Array	(
														"Id"					=> $arrRecord['FuturePlanId'],
														"Name"					=> $arrRecord['FuturePlanName'],
														"brochure_document_id"	=> $arrRecord['FuturePlanBrochureId'],
														"StartDatetime"			=> $arrRecord['FuturePlanStartDatetime']
													);
			}
			else
			{
				$arrService['FuturePlan'] = NULL;
			}
			
			// Add this record's details to the history array
			$arrService['History']		= Array();
			$arrService['History'][]	= Array	(
													"ServiceId"			=> $arrRecord['Id'],
													"CreatedOn"			=> $arrRecord['CreatedOn'],
													"ClosedOn"			=> $arrRecord['ClosedOn'],
													"CreatedBy"			=> $arrRecord['CreatedBy'],
													"ClosedBy"			=> $arrRecord['ClosedBy'],
													"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
													"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
													"LastOwner"			=> $arrRecord['LastOwner'],
													"NextOwner"			=> $arrRecord['NextOwner'],
													"Status"			=> $arrRecord['Status'],
													"LineStatus"		=> $arrRecord['LineStatus'],
													"LineStatusDate"	=> $arrRecord['LineStatusDate']
												);
			 
			
			// If multiple Service records relate to the one actual service then they will be consecutive in the RecordSet
			// Find each one and add it to the Status history
			while (($arrRecord = $selServices->Fetch()) !== FALSE)
			{
				if ($arrRecord['FNN'] == $arrService['FNN'])
				{
					// This record relates to the same Service
					$arrService['History'][]	= Array	(
															"ServiceId"	=> $arrRecord['Id'],
															"CreatedOn"			=> $arrRecord['CreatedOn'],
															"ClosedOn"			=> $arrRecord['ClosedOn'],
															"CreatedBy"			=> $arrRecord['CreatedBy'],
															"ClosedBy"			=> $arrRecord['ClosedBy'],
															"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
															"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
															"LastOwner"			=> $arrRecord['LastOwner'],
															"NextOwner"			=> $arrRecord['NextOwner'],
															"Status"			=> $arrRecord['Status'],
															"LineStatus"		=> $arrService['LineStatus'],
															"LineStatusDate"	=> $arrService['LineStatusDate']
														);
				}
				else
				{
					// We have moved on to the next Service
					break;
				}
			}
			
			// Add the Service to the array of Services
			$arrServices[] = $arrService;
		}
		
		// Apply the filter
		$strNow = GetCurrentISODateTime();
		if ($intFilter)
		{
			$arrTempServices	= $arrServices;
			$arrServices		= Array();
			
			foreach ($arrTempServices as $arrService)
			{
				switch ($intFilter)
				{
					case SERVICE_ACTIVE:
						// Only keep the Service if ClosedOn IS NULL OR NOW() OR in the future
						if ($arrService['History'][0]['ClosedOn'] == NULL || $arrService['History'][0]['ClosedOn'] >= $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
					
					case SERVICE_DISCONNECTED:
						// Only keep the Service if Status == Disconnected AND ClosedOn < NOW()
						if ($arrService['History'][0]['Status'] == SERVICE_DISCONNECTED && $arrService['History'][0]['ClosedOn'] < $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
					
					case SERVICE_ARCHIVED:
						// Only keep the Service if Status == Archived AND ClosedOn < NOW()
						if ($arrService['History'][0]['Status'] == SERVICE_ARCHIVED && $arrService['History'][0]['ClosedOn'] < $strNow)
						{
							// Keep it
							$arrServices[] = $arrService;
						}
						break;
				}
			}
		}
		
		return $arrServices;
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
		
		// Accounts can not have their details editted while an invoice run is processing
		if (Invoice_Run::checkTemporary(DBO()->Account->CustomerGroup->Value, DBO()->Account->Id->Value))
		{
			Ajax()->AddCommand("Alert", "This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			return TRUE;
		}
		
		// Render the AccountDetails HtmlTemplate for Editing
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
	 */
	function SaveDetails()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolIsSuperAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);

		$qryQuery	= new Query();
		
		// Accounts can not have their details editted while an invoice run is processing
		if (Invoice_Run::checkTemporary(DBO()->Account->CustomerGroup->Value, DBO()->Account->Id->Value))
		{
			Ajax()->AddCommand("Alert", "This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			return TRUE;
		}

		if (DBO()->Account->WithTIO->Value == TRUE)
		{
			// Validate the tio reference number
			DBO()->Account->tio_reference_number = str_replace(" ", "", DBO()->Account->tio_reference_number->Value);
			
			if (!IsValidTIOReferenceNumber(DBO()->Account->tio_reference_number->Value))
			{
				DBO()->Account->tio_reference_number->SetToInvalid();
			}
		}
		else
		{
			DBO()->Account->tio_reference_number = NULL;
		}

		// If the validation has failed display the invalid fields
		if (DBO()->Account->IsInvalid())
		{
			Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("AccountDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return TRUE;
		}
		
		// Merge the Account data from the database with the newly defined details
		DBO()->Account->LoadMerge();

		// This will store the properties that have been changed and have to cascade 
		// to tables other than the Account table, which I believe is only the 
		// ServiceAddress table at the moment
		$arrCascadingFields = Array();
		
		// Load the current account details, so you can work out what has been changed, and include these details in the system note
		DBO()->CurrentAccount->Id = DBO()->Account->Id->Value;
		DBO()->CurrentAccount->SetTable("Account");
		DBO()->CurrentAccount->Load();

		if (DBO()->Account->BusinessName->Value != DBO()->CurrentAccount->BusinessName->Value)
		{
			$strChangesNote .= "Business Name was changed from '". DBO()->CurrentAccount->BusinessName->Value ."' to '" . DBO()->Account->BusinessName->Value . "'\n";
			$arrCascadingFields[] = "Business Name";
		}
		if (DBO()->Account->TradingName->Value != DBO()->CurrentAccount->TradingName->Value)
		{
			$strChangesNote .= "Trading Name was changed from '". DBO()->CurrentAccount->TradingName->Value ."' to '" . DBO()->Account->TradingName->Value . "'\n";
			$arrCascadingFields[] = "Trading Name";
		}	
		if (DBO()->Account->ABN->Value != DBO()->CurrentAccount->ABN->Value)
		{
			$strChangesNote .= "ABN was changed from ". DBO()->CurrentAccount->ABN->Value ." to " . DBO()->Account->ABN->Value . "\n";
			$arrCascadingFields[] = "ABN";
		}
		if (DBO()->Account->ACN->Value != DBO()->CurrentAccount->ACN->Value)
		{
			$strChangesNote .= "ACN was changed from ". DBO()->CurrentAccount->ACN->Value ." to " . DBO()->Account->ACN->Value . "\n";
		}
		if (DBO()->Account->Address1->Value != DBO()->CurrentAccount->Address1->Value)
		{
			$strChangesNote .= "Address Line 1 was changed from '". DBO()->CurrentAccount->Address1->Value ."' to '" . DBO()->Account->Address1->Value . "'\n";
			$arrCascadingFields[] = "Address Line 1";
		}
		if (DBO()->Account->Address2->Value != DBO()->CurrentAccount->Address2->Value)
		{
			$strChangesNote .= "Address Line 2 was changed from '". DBO()->CurrentAccount->Address2->Value ."' to '" . DBO()->Account->Address2->Value . "'\n";
			$arrCascadingFields[] = "Address Line 2";
		}
		if (DBO()->Account->Suburb->Value != DBO()->CurrentAccount->Suburb->Value)
		{
			$strChangesNote .= "Suburb was changed from '". DBO()->CurrentAccount->Suburb->Value ."' to '" . DBO()->Account->Suburb->Value . "'\n";
			$arrCascadingFields[] = "Suburb";
		}
		if (DBO()->Account->Postcode->Value != DBO()->CurrentAccount->Postcode->Value)
		{
			$strChangesNote .= "Postcode was changed from ". DBO()->CurrentAccount->Postcode->Value ." to " . DBO()->Account->Postcode->Value . "\n";
			$arrCascadingFields[] = "Postcode";
		}
		if (DBO()->Account->State->Value != DBO()->CurrentAccount->State->Value)
		{
			$strChangesNote .= "State was changed from ". DBO()->CurrentAccount->State->Value ." to " . DBO()->Account->State->Value . "\n";
		}
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT) && DBO()->Account->vip->Value != DBO()->CurrentAccount->vip->Value)
		{
			$strChangesNote .= "VIP status was changed from ". (DBO()->CurrentAccount->vip->Value ? '' :  'in') ."active to " . (DBO()->Account->vip->Value ? '' :  'in') . "active\n";
		}
		if (DBO()->Account->BillingMethod->Value != DBO()->CurrentAccount->BillingMethod->Value)
		{
			$strChangesNote .= "Billing Method was changed from ". GetConstantDescription(DBO()->CurrentAccount->BillingMethod->Value, 'delivery_method') ." to " . GetConstantDescription(DBO()->Account->BillingMethod->Value, 'delivery_method') . "\n";
		}
		if (!$bolIsSuperAdminUser)
		{
			// Only Super Admins can change the CustomerGroup of an Account
			DBO()->Account->CustomerGroup->Value = DBO()->CurrentAccount->CustomerGroup->Value;
		}
		
		if (DBO()->Account->CustomerGroup->Value != DBO()->CurrentAccount->CustomerGroup->Value)
		{
			// Check the current CustomerGroup does not have a live invoice run outstanding
			if (Invoice_Run::checkTemporary(DBO()->CurrentAccount->CustomerGroup->Value, DBO()->Account->Id->Value))
			{
				Ajax()->AddCommand("Alert", "This action is temporarily unavailable because a related, live invoice run is currently outstanding");
				return TRUE;
			}
			
			$selCustomerGroup = new StatementSelect("CustomerGroup", "Id, internal_name", "Id = <Id>");
			$selCustomerGroup->Execute(Array("Id" => DBO()->CurrentAccount->CustomerGroup->Value));
			$arrCurrentCustomerGroup = $selCustomerGroup->Fetch();
			$selCustomerGroup->Execute(Array("Id" => DBO()->Account->CustomerGroup->Value));
			$arrNewCustomerGroup = $selCustomerGroup->Fetch();
			
			$strChangesNote .= "Customer Group was changed from {$arrCurrentCustomerGroup['internal_name']} to {$arrNewCustomerGroup['internal_name']}\n";
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
			elseif ($intCurrentValue < -1)
			{
				$intCurrentValue = abs($intCurrentValue);
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
		if (DBO()->Account->tio_reference_number->Value != DBO()->CurrentAccount->tio_reference_number->Value)
		{
			$strChangesNote .= "T.I.O Reference Number was changed from '". DBO()->CurrentAccount->tio_reference_number->Value ."' to '". DBO()->Account->tio_reference_number->Value ."'\n";
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
		else if (DBO()->Account->LatePaymentAmnesty->Value == NULL)
		{
			DBO()->Account->LatePaymentAmnesty = NULL;
		}

		// Start the transaction
		TransactionStart();

		if (DBO()->Account->credit_control_status->Value != DBO()->CurrentAccount->credit_control_status->Value)
		{
			DBO()->credit_control_status_original->SetTable('credit_control_status');
			DBO()->credit_control_status_original->Id = DBO()->CurrentAccount->credit_control_status->Value;
			DBO()->credit_control_status_original->Load();
			DBO()->credit_control_status_new->SetTable('credit_control_status');
			DBO()->credit_control_status_new->Id = DBO()->Account->credit_control_status->Value;
			DBO()->credit_control_status_new->Load();
			$strChangesNote .= "Credit control status was changed from ". DBO()->credit_control_status_original->name->Value . "(" . DBO()->CurrentAccount->credit_control_status->Value . ") to ". DBO()->credit_control_status_new->name->Value . "(" . DBO()->Account->credit_control_status->Value . ")\n";
			DBO()->Account->credit_control_status = intval(DBO()->Account->credit_control_status->Value);

			DBO()->credit_control_status_history->account = DBO()->Account->Id->Value;
			DBO()->credit_control_status_history->from_status = DBO()->CurrentAccount->credit_control_status->Value;
			DBO()->credit_control_status_history->to_status = DBO()->Account->credit_control_status->Value;
			DBO()->credit_control_status_history->employee = AuthenticatedUser()->GetUserId();
			DBO()->credit_control_status_history->change_datetime = GetCurrentISODateTime();
			if (!DBO()->credit_control_status_history->Save())
			{
				// Saving the credit control status history record failed
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Recording credit control status change history failed");
				return TRUE;
			}
		}
		
		// Set the columns to save
		DBO()->Account->SetColumns("BusinessName, TradingName, ABN, ACN, Address1, Address2, Suburb, Postcode, State, BillingMethod, CustomerGroup, DisableLatePayment, Archived, DisableDDR, Sample, credit_control_status, LatePaymentAmnesty, tio_reference_number" . (AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT) ? ", vip" : ""));
														
		if (!DBO()->Account->Save())
		{
			// Saving the account record failed
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Updating the account details failed, unexpectedly");
			return TRUE;
		}
		
		// Check if the Status property has been changed
		$arrModifiedServices = array();
		if (DBO()->Account->Archived->Value != DBO()->CurrentAccount->Archived->Value)
		{
			// Define one variable for MYSQL date/time and one of the EmployeeID
			$strTodaysDate = GetCurrentDateForMySQL();
			$intEmployeeId = AuthenticatedUser()->GetUserId();
		
			// This will store the Status that services are changed to, due to the changing of the account status
			$intModifiedServicesNewStatus	= NULL;
			
			// Stores details of services that should have been automatically provisioned, but failed
			$arrServicesFailedToProvision	= array();
		
			$strChangesNote .= "Account Status was changed from ". GetConstantDescription(DBO()->CurrentAccount->Archived->Value, 'account_status') ." to ". GetConstantDescription(DBO()->Account->Archived->Value, 'account_status') . "\n";

			DBO()->account_status_history->account			= DBO()->Account->Id->Value;
			DBO()->account_status_history->from_status		= DBO()->CurrentAccount->Archived->Value;
			DBO()->account_status_history->to_status		= DBO()->Account->Archived->Value;
			DBO()->account_status_history->employee			= AuthenticatedUser()->GetUserId();
			DBO()->account_status_history->change_datetime	= GetCurrentISODateTime();
			if (!DBO()->account_status_history->Save())
			{
				// Saving the account status history record failed
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Recording account status change history failed");
				return TRUE;
			}
	
			switch (DBO()->Account->Archived->Value)
			{
				case ACCOUNT_STATUS_ACTIVE:
					$intModifiedServicesNewStatus = SERVICE_ACTIVE;
					if (DBO()->CurrentAccount->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
					{
						// The account is being activated for the first time
						// Activate all services that are pending activation
						$arrServices = $this->GetServices(DBO()->Account->Id->Value);
						
						foreach ($arrServices as $arrService)
						{
							// Check that the service is pending activation (they all should be in this scenario)
							if ($arrService['History'][0]['Status'] == SERVICE_PENDING)
							{
								$objService = ModuleService::GetServiceById($arrService['Id'], $arrService['ServiceType']);
								if ($objService === FALSE || $objService === NULL)
								{
									// The service object could not be created
									TransactionRollback();
									Ajax()->AddCommand("Alert", "ERROR: Unexpected problem occurred when trying to activate Service: {$arrService['FNN']}.  The account has not been updated");
									return TRUE;
								}
								
								// Activate the service
								if (!$objService->ChangeStatus($intModifiedServicesNewStatus))
								{
									// Updating the status failed
									TransactionRollback();
									Ajax()->AddCommand("Alert", "ERROR: Could not activate Service: {$arrService['FNN']}.  {$objService->GetErrorMsg()}.  The account has not been updated.");
									return TRUE;
								}
								
								// Do FullService and Preselection provisioning requests
								if ($objService->CanBeProvisioned())
								{
									if (!$objService->MakeFullServiceProvisioningRequest() || !$objService->MakePreselectionProvisioningRequest())
									{
										// Failed to make the FullService provisioning Request or the Preselection provisioning Request
										$arrServicesFailedToProvision[] = array("FNN"			=> $arrService['FNN'],
																				"ServiceType"	=> $arrService['ServiceType']);
									}
								}
								
								// Add the details of the service to the list of modified services
								$arrModifiedServices[] = array(	"FNN"			=> $arrService['FNN'],
																"ServiceType"	=> $arrService['ServiceType']);
							}
						}
					}
					break;
					
				case ACCOUNT_STATUS_CLOSED:
				case ACCOUNT_STATUS_DEBT_COLLECTION:
				case ACCOUNT_STATUS_SUSPENDED:
					$intModifiedServicesNewStatus = SERVICE_DISCONNECTED;
					// If user has selected "Closed", "Debt Collection", "Suspended" for the account status, only Active services have their Status and 
					// ClosedOn/CloseBy properties changed
					// Active Services are those that have their Status set to Active or (their status is set to Disconnected and 
					// their ClosedOn date is in the future (signifying a change of lessee) or today).  We don't have to worry about 
					// the Services where their status is set to Disconnected and their ClosedOn Date is set to today, because that 
					// is how we are going to update the records anyway.
					
					//$strWhere = "Account = <AccountId> AND (Status = <ServiceActive> OR (Status = <ServiceDisconnected> AND ClosedOn > NOW()))";
					//$arrWhere = Array("AccountId" => DBO()->Account->Id->Value, "ServiceActive" => SERVICE_ACTIVE, "ServiceDisconnected" => SERVICE_DISCONNECTED);
					$strWhere = "Account = <AccountId> AND (Status IN (<ServiceActive>, <ServicePending>) OR (Status = <ServiceDisconnected> AND ClosedOn > NOW())) AND Id = (SELECT MAX(S2.Id) FROM Service AS S2 WHERE S2.Account = <AccountId> AND Service.FNN = S2.FNN) AND (ClosedOn IS NULL OR (ClosedOn >= CreatedOn))";
					$arrWhere = Array("AccountId" => DBO()->Account->Id->Value, "ServiceActive" => SERVICE_ACTIVE, "ServicePending" => SERVICE_PENDING, "ServiceDisconnected" => SERVICE_DISCONNECTED);

					// Retrieve all services attached to this Account where the Status is Active
					DBL()->Service->SetColumns("Id, FNN, ServiceType, Status");
					DBL()->Service->Where->Set($strWhere, $arrWhere);
					DBL()->Service->Load();
					
					// Iterate through the services and try to disconnect each one
					foreach (DBL()->Service as $dboService)
					{
						$objService = ModuleService::GetServiceById($dboService->Id->Value, $dboService->ServiceType->Value);
						if ($objService === FALSE || $objService === NULL)
						{
							// An error occurred
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Unexpected problem occurred when trying to disconnect Service: {$dboService->FNN->Value}.  The account has not been updated");
							return TRUE;
						}
						
						if ($objService->ChangeStatus($intModifiedServicesNewStatus) === FALSE)
						{
							// Could not change the status of the service
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Could not disconnect service: {$dboService->FNN->Value}.  {$objService->GetErrorMsg()}<br />The account has not been updated");
							return TRUE;
						}
						
						// The service has been successfully updated
						// Add the details of the service to the list of modified services
						$arrModifiedServices[] = array(	"FNN"			=> $dboService->FNN->Value,
														"ServiceType"	=> $dboService->ServiceType->Value);
					}
					break;
					
				case ACCOUNT_STATUS_ARCHIVED:
					$intModifiedServicesNewStatus = SERVICE_ARCHIVED;
					// If user has selected "Archived" for the account status only Active, Pending and Disconnected services have their Status and 
					// ClosedOn/CloseBy properties changed						
					$strWhere = "Account = <AccountId> AND Status IN (<ServiceActive>, <ServicePending>, <ServiceDisconnected>) AND Id = (SELECT MAX(S2.Id) FROM Service AS S2 WHERE S2.Account = <AccountId> AND Service.FNN = S2.FNN) AND (ClosedOn IS NULL OR (ClosedOn >= CreatedOn))";
					$arrWhere = Array("AccountId" => DBO()->Account->Id->Value, "ServiceActive" => SERVICE_ACTIVE, "ServicePending" => SERVICE_PENDING, "ServiceDisconnected" => SERVICE_DISCONNECTED);
					
					// Retrieve all services attached to this Account where the Status is Active/Disconnected/Pending
					DBL()->Service->SetColumns("Id, FNN, ServiceType, Status");
					DBL()->Service->Where->Set($strWhere, $arrWhere);
					DBL()->Service->Load();
					
					// Iterate through the services and try to disconnect each one
					foreach (DBL()->Service as $dboService)
					{
						$objService = ModuleService::GetServiceById($dboService->Id->Value, $dboService->ServiceType->Value);
						if ($objService === FALSE || $objService === NULL)
						{
							// An error occurred
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Unexpected problem occurred when trying to archive Service: {$dboService->FNN->Value}.  The account has not been updated");
							return TRUE;
						}
						
						if ($objService->ChangeStatus($intModifiedServicesNewStatus) === FALSE)
						{
							// Could not change the status of the service
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Could not archive service: {$dboService->FNN->Value}.  {$objService->GetErrorMsg()}<br />The account has not been updated");
							return TRUE;
						}
						
						// The service has been successfully updated
						// Add the details of the service to the list of modified services
						$arrModifiedServices[] = array(	"FNN"			=> $dboService->FNN->Value,
														"ServiceType"	=> $dboService->ServiceType->Value);
					}
					break;
					
				case ACCOUNT_STATUS_PENDING_ACTIVATION:
					// The account's status should never be changed to this
				default:
					// Unknown Status
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Invalid Account Status");
					return;
			}
		}
		
		// Changes have been made
		if ($strChangesNote)
		{
			$strChangesNote = "Account details have been modified.\n$strChangesNote";
			if (count($arrModifiedServices) > 0)
			{
				// Some services have had their status updated
				$strChangesNote .= "\nThe following services have been set to ". GetConstantDescription($intModifiedServicesNewStatus, "service_status") .":";
				foreach ($arrModifiedServices as $arrService)
				{
					$strChangesNote .= "\n". GetConstantDescription($arrService['ServiceType'], "service_type") .": {$arrService['FNN']}";
				}
			}
			if (count($arrServicesFailedToProvision) > 0)
			{
				// Some services that should be able to be provisioned automatically, failed
				$strChangesNote .= "\n\nProvisioning requests failed to be automatically generated for the following services:";
				foreach ($arrServicesFailedToProvision as $arrService)
				{
					$strChangesNote .= "\n". GetConstantDescription($arrService['ServiceType'], "service_type") .": {$arrService['FNN']}";
				}
			}
			
			SaveSystemNote($strChangesNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value);
		}

		// Record any changes in the account_history table
		try
		{
			Account_History::recordCurrentState(DBO()->Account->Id->Value, AuthenticatedUser()->GetUserId(), GetCurrentISODateTime());
		}
		catch (Exception $e)
		{
			// The state could not be recorded
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Could not save state of account.  ". $e->getMessage());
			return;
		}

		// All Database interactions were successfull
		TransactionCommit();
		
		// Email the Credit Control Manager about any Credit Control Status Changes
		if (DBO()->Account->credit_control_status->Value != DBO()->CurrentAccount->credit_control_status->Value)
		{
			$objEmailNotification	= new Email_Notification(EMAIL_NOTIFICATION_CREDIT_CONTROL_STATUS_CHANGE, DBO()->Account->CustomerGroup);
			
			$objCCStatuses	= Constant_Group::getConstantGroup('credit_control_status');
			
			$objNewEmployee			= Employee::getForId(Flex::getUserId());
			$strNewEmployeeName		= $objNewEmployee->firstName . (($objNewEmployee->lastName) ? " {$objNewEmployee->lastname}" : '');
			$strNewTimestamp		= date("H:i:s", strtotime(GetCurrentISODateTime()));
			$strNewDatestamp		= date("d/m/Y", strtotime(GetCurrentISODateTime()));
			$strNewCCStatus			= $objCCStatuses->getConstantName(DBO()->Account->credit_control_status->Value);
			
			$resPreviousCCHistory	= $qryQuery->Execute("SELECT * FROM credit_control_status_history WHERE account = ".DBO()->Account->Id->Value." ORDER BY id DESC LIMIT 1 OFFSET 1");
			if ($resPreviousCCHistory === false)
			{
				throw new Exception($qryQuery->Error());
			}
			$arrPreviousCCHistory	= $resPreviousCCHistory->fetch_assoc();
			
			$objPreviousEmployee		= Employee::getForId($arrPreviousCCHistory['employee']);
			$strPreviousEmployeeName	= $objPreviousEmployee->firstName . (($objPreviousEmployee->lastName) ? " {$objPreviousEmployee->lastname}" : '');
			$strPreviousTimestamp		= date("H:i:s", strtotime($arrPreviousCCHistory['change_datetime']));
			$strPreviousDatestamp		= date("d/m/Y", strtotime($arrPreviousCCHistory['change_datetime']));
			$strPreviousCCStatus		= $objCCStatuses->getConstantName(DBO()->CurrentAccount->credit_control_status->Value);
			
			$strMessage			= "{$strNewEmployeeName} changed the Credit Control Status for Account number ".DBO()->Account->Id->Value." from '{$strPreviousCCStatus}' to '{$strNewCCStatus}' at {$strNewTimestamp} on {$strNewDatestamp}.";
			
			$strTHStyle			= "text-align: right; color: #eee; background-color: #333; width: 15em;";
			$strTDStyle			= "text-align: left; color: #333; background-color: #eee;";
			$strTDWidthStyle	= "min-width: 15em; max-width: 15em;";
			$strHTMLContent	=	"<body>\n" .
								"	<div style='font-family: calibri arial sans-serif;'>\n" .
								"		{$strMessage}<br /><br />\n" .
								"		<table style='width=99%; border: .1em solid #333; border-spacing: 0; border-collapse: collapse;' >\n" .
								"			<tr>\n" .
								"				<th style='{$strTHStyle}'>Account Number : </th>\n" .
								"				<td colspan='2' style='{$strTDStyle}'>".DBO()->Account->Id->Value."</td>\n" .
								"			</tr>\n" .
								"			<tr>\n" .
								"				<th style='{$strTHStyle}'>Business Name : </th>\n" .
								"				<td colspan='2' style='{$strTDStyle}'>".DBO()->Account->Id->Value."</td>\n" .
								"			</tr>\n" .
								"			<tr>\n" .
								"				<th style='{$strTHStyle}'>Account Status : </th>\n" .
								"				<td colspan='2' style='{$strTDStyle}'>".GetConstantDescription(DBO()->Account->Archived->Value, 'account_status')."</td>\n" .
								"			</tr>\n" .
								"			<tr>\n" .
								"				<th style='{$strTHStyle}'>Previous Credit Control Status : </th>\n" .
								"				<td style='{$strTDStyle}'>{$strPreviousCCStatus}</td>\n" .
								"				<td style='{$strTDStyle}{$strTDWidthStyle}'>set on {$strPreviousTimestamp} on {$strPreviousDatestamp} by {$strPreviousEmployeeName}</td>\n" .
								"			</tr>\n" .
								"			<tr>\n" .
								"				<th style='{$strTHStyle}'>New Credit Control Status : </th>\n" .
								"				<td style='{$strTDStyle}'>{$strNewCCStatus}</td>\n" .
								"				<td style='{$strTDStyle}{$strTDWidthStyle}'>set on {$strNewTimestamp} on {$strNewDatestamp} by {$strNewEmployeeName}</td>\n" .
								"			</tr>\n" .
								"		</table><br /><br />\n" .
								"		Regards<br />\n" .
								"		<strong>Flexor</strong>\n" .
								"	</div>\n" .
								"</body>";
			$objEmailNotification->setBodyHtml($strHTMLContent);
			
			$strTextContent	=	"{$strMessage}\n\n" .
								"Regards\n" .
								"Flexor";
			$objEmailNotification->setBodyText($strTextContent);
			
			$objEmailNotification->setSubject("[NOTICE] Flex Account #".DBO()->Account->Id->Value." Credit Control Status changed from {$strPreviousCCStatus} to {$strNewCCStatus}");
			$objEmailNotification->send();
		}

		// Handle cascading for values that can cascade
		if (count($arrCascadingFields) > 0)
		{
			$strAlertMsg = "The account details were successfully updated.<br />The following modified fields could compromise the integrity of the Address details defined for the services belonging to this account.<br />Please update these address details accordingly.";
			$strAlertMsg .= "<br />Fields: ". implode(", ", $arrCascadingFields);
			Ajax()->AddCommand("Alert", $strAlertMsg);
		}
		
		// Fire the OnAccountDetailsUpdate Event
		$arrEvent['Account']['Id'] = DBO()->Account->Id->Value;
		Ajax()->FireEvent(EVENT_ON_ACCOUNT_DETAILS_UPDATE, $arrEvent);
		
		// Fire the OnNewNote event
		if ($strChangesNote)
		{
			Ajax()->FireOnNewNoteEvent(DBO()->Account->Id->Value);
		}
		
		// Fire the OnAccountServicesUpdate Event
		if (count($arrModifiedServices) > 0)
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
		$bolUserHasProperAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolHasCreditManagementPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT);
		$bolHasAdminPerm			= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		
		$bolCanDeleteAdjustments			= ($bolUserHasProperAdminPerm || $bolHasCreditManagementPerm);
		$bolCanReversePayments				= $bolHasAdminPerm;
		$bolCanCancelRecurringAdjustments	= $bolHasAdminPerm;


		// Check what sort of record is being deleted
		switch (DBO()->DeleteRecord->RecordType->Value)
		{
			case "Payment":
				if (!$bolCanReversePayments)
				{
					Ajax()->AddCommand("Alert", "You do not have the required permissions to reverse payments");
					return TRUE;
				}
				DBO()->DeleteRecord->Application = "Payment";
				DBO()->DeleteRecord->Method = "Delete";
				DBO()->Payment->Load();
				DBO()->Account->Id = DBO()->Payment->Account->Value;
				break;
			case "Adjustment":
				if (!$bolCanDeleteAdjustments)
				{
					Ajax()->AddCommand("Alert", "You do not have the required permissions to delete an adjustment");
					return TRUE;
				}
				DBO()->DeleteRecord->Application = "Adjustment";
				DBO()->DeleteRecord->Method = "DeleteAdjustment";
				DBO()->Charge->Load();
				DBO()->Account->Id = DBO()->Charge->Account->Value;
				break;
			case "RecurringAdjustment":
				if (!$bolCanCancelRecurringAdjustments)
				{
					Ajax()->AddCommand("Alert", "You do not have the required permissions to cancel a recurring adjustment");
					return TRUE;
				}
				DBO()->DeleteRecord->Application = "Adjustment";
				DBO()->DeleteRecord->Method = "DeleteRecurringAdjustment";
				DBO()->RecurringCharge->Load();
				DBO()->Account->Id = DBO()->RecurringCharge->Account->Value;
				break;
			default:
				Ajax()->AddCommand("Alert", "ERROR: No record type has been declared to be deleted");
				return TRUE;
		}
		
		if (DBO()->Account->Id->Value && DBO()->Account->Load())
		{
			$intCustomerGroupId	= DBO()->Account->CustomerGroup->Value;
			$intAccountId		= DBO()->Account->Id->Value;
		}
		else
		{
			$intCustomerGroupId	= NULL;
			$intAccountId		= NULL;
		}
		
		
		if (Invoice_Run::checkTemporary($intCustomerGroupId, $intAccountId))
		{
			// Records cannot be deleted while the Invoicing process is running
			Ajax()->AddCommand("Alert", "This action is temporarily unavailable because a related, live invoice run is currently outstanding");
			return TRUE;
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
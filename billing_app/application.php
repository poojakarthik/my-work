<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		Billing
 * @author		Jared 'flame' Herbohn, Rich "Waste" Davis
 * @version		7.01
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 

//----------------------------------------------------------------------------//
// ApplicationBilling
//----------------------------------------------------------------------------//
/**
 * ApplicationBilling
 *
 * Billing Module
 *
 * Billing Module
 *
 *
 * @prefix		app
 *
 * @package		billing_app
 * @class		ApplicationBilling
 */
 class ApplicationBilling extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		// Initiate Reports
		$this->_rptBillingReport 	= new Report("Billing Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au", TRUE, "dispatch@voiptelsystems.com.au");
		$this->_rptAuditReport		= new Report("Bill Audit Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au", FALSE, "dispatch@voiptelsystems.com.au");
		
		// Report headers
		$this->_rptBillingReport->AddMessage(MSG_HORIZONTAL_RULE);
		$this->_rptAuditReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		// Construct the Bill Output objects
		$this->_arrBillOutput[BILL_PRINT]		= new BillingModulePrint(&$this->db, $arrConfig);
		$this->_arrBillOutput[BILL_PRINT_ETECH]	= new BillingModuleEtech(&$this->db, $arrConfig);
		
		// Init Statements
		$this->_selPayments = new StatementSelect(	"Payment",
													"Id, Balance",
													"Balance > 0 AND AccountGroup = <AccountGroup> AND " .
													"(Account = <Account> OR ISNULL(Account))",
													"PaidOn",
													NULL);
		$this->_insInvoicePayment = new StatementInsert("InvoicePayment");
		
		$arrCols['Status']	= NULL;
		$arrCols['Balance']	= NULL;
		$this->_ubiPayment = new StatementUpdateById("Payment", $arrCols);
	}
	
	//------------------------------------------------------------------------//
	// Execute
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Execute the billing run 
	 *
	 * Generates temporary Invoices. This proccess is scheduled to run once each
	 * day at around 4am. After temporary invoices are created they can be checked
	 * and if there are no problems they can be commited. This allows testing of
	 * the billing run. Bill printing file is also produced here.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Execute()
 	{
		// Start the stopwatch
		$this->Framework->StartWatch();

		// Empty the temporary invoice table
		// This is safe, because there should be no CDRs with CDR_TEMP_INVOICE status anyway
		if (!$this->Revoke())
		{
			return;		// Return if this fails
		}
		
		// Init Select Statements
		$arrColumns = Array();
		$arrColumns['Shared']			= "RatePlan.Shared";
		$arrColumns['MinMonthly']		= "RatePlan.MinMonthly";
		$arrColumns['ChargeCap']		= "RatePlan.ChargeCap";
		$arrColumns['UsageCap']			= "RatePlan.UsageCap";
		$arrColumns['FNN']				= "Service.FNN";
		$arrColumns['CappedCharge']		= "Service.CappedCharge";
		$arrColumns['UncappedCharge']	= "Service.UncappedCharge";
		$arrColumns['Service']			= "Service.Id";
		$selServices					= new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, " .
																"RatePlan",
																$arrColumns,
																"Service.Account = <Account> AND RatePlan.Id = ServiceRatePlan.RatePlan AND " .
																"Service.CreatedOn <= NOW() AND (ISNULL(Service.ClosedOn) OR Service.ClosedOn > NOW()) AND (NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime)",
																"RatePlan.Id");
		$strTestAccounts =		" AND " .
																"Id = 1000009145 OR " .
																"Id = 1000007460 OR " .
																"Id = 1000008407 OR " .
																"Id = 1000157133 OR " .
																"Id = 1000161583 OR " .
																"Id = 1000158216 OR " .
																"Id = 1000157698 OR " .
																"Id = 1000160393 OR " .
																"Id = 1000158098 OR " .
																"Id = 1000155964 OR " .
																"Id = 1000160897";	//  limited to 11 specified accounts
		$selAccounts					= new StatementSelect("Account", "*", "Archived = 0"); 
										
		$selCalcAccountBalance			= new StatementSelect("Invoice", "SUM(Balance) AS AccountBalance", "Status = ".INVOICE_COMMITTED." AND Account = <Account>");
		$selDebitsCredits				= new StatementSelect("Charge",
															  "Nature, SUM(Amount) AS Amount",
															  "Service = <Service> AND Status = ".CHARGE_TEMP_INVOICE." AND InvoiceRun = <InvoiceRun>",
															  NULL,
															  "2",
															  "Nature");
		// generate an InvoiceRun Id
		$strInvoiceRun = uniqid();
		$this->_strInvoiceRun = $strInvoiceRun;
		
		// Init Update Statements
		$arrCDRCols = Array();
		$arrCDRCols['Status']			= CDR_TEMP_INVOICE;
		$arrCDRCols['InvoiceRun']		= $strInvoiceRun;
		$updCDRs						= new StatementUpdate("CDR", "Account = <Account> AND Credit = 0 AND Status = ".CDR_RATED, $arrCDRCols);
		
		// Init Insert Statements
		$arrInvoiceData 					= Array();
		$arrInvoiceData['AccountGroup']		= NULL;
		$arrInvoiceData['Account']			= NULL;
		$arrInvoiceData['CreatedOn']		= /*new MySQLFunction("NOW()")*/NULL;
		$arrInvoiceData['DueOn']			= /*new MySQLFunction("DATE_ADD(NOW(), INTERVAL <Days> DAY")*/NULL;
		$arrInvoiceData['Credits']			= NULL;
		$arrInvoiceData['Debits']			= NULL;
		$arrInvoiceData['Total']			= NULL;
		$arrInvoiceData['Tax']				= NULL;
		$arrInvoiceData['Balance']			= NULL;
		$arrInvoiceData['Disputed']			= NULL;
		$arrInvoiceData['AccountBalance']	= NULL;
		$arrInvoiceData['Status']			= NULL;
		$arrInvoiceData['InvoiceRun']		= NULL;
		$insTempInvoice						= new StatementInsert("InvoiceTemp", $arrInvoiceData);
		$insServiceTotal					= new StatementInsert("ServiceTotal");
		
		$intPassed = 0;
		$intFailed = 0;
		
		// get a list of all accounts that require billing today
		//TODO-LATER : Make this work with daily and 1/2 monthly billing
		if ($selAccounts->Execute() === FALSE)
		{

		}
		$arrAccounts = $selAccounts->FetchAll();

		// Report Title
		$this->_rptBillingReport->AddMessage("\n".MSG_BILLING_TITLE."\n");
		
		// prepare (clean) billing files
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			$this->_arrBillOutput[$strKey]->clean();
		}
		
		// Loop through the accounts we're billing
		foreach ($arrAccounts as $arrAccount)
		{
			$this->_rptBillingReport->AddMessageVariables(MSG_ACCOUNT_TITLE, Array('<AccountNo>' => $arrAccount['Id']));
			$this->_rptBillingReport->AddMessage(MSG_LINK_CDRS, FALSE);
			
			// Set status of CDR_RATED CDRs for this account to CDR_TEMP_INVOICE
			if(!$updCDRs->Execute($arrCDRCols, Array('Account' => $arrAccount['Id'])))
			{
				// Report and continue
				$this->_rptBillingReport->AddMessageVariables("\t\t".MSG_IGNORE.MSG_LINE_FAILED, Array('<Reason>' => "No billable CDRs for this account"));
			}
			else
			{
				$this->_rptBillingReport->AddMessage(MSG_OK);
			}
			
			// SERVICE TYPE TOTALS
			
			// build query
			$strQuery  = "INSERT INTO ServiceTypeTotal (FNN, AccountGroup, Account, Service, InvoiceRun, RecordType, Charge, Units, Records)";
			$strQuery .= " SELECT FNN, AccountGroup, Account, Service, '".$strInvoiceRun."' AS InvoiceRun,";
			$strQuery .= " RecordType, SUM(Charge) AS Charge, SUM(Units) AS Units, COUNT(Charge) AS Records";
			$strQuery .= " FROM CDR";
			$strQuery .= " WHERE FNN IS NOT NULL AND RecordType IS NOT NULL";
			$strQuery .= " AND Status = ".CDR_TEMP_INVOICE;
			$strQuery .= " AND Account = ".$arrAccount['Id'];
			$strQuery .= " AND CDR.Credit = 0";
			$strQuery .= " GROUP BY Service, RecordType";
			
			// run query
			$qryServiceTypeTotal = new Query();
			$qryServiceTypeTotal->Execute($strQuery);
			
			// zero out totals
			$fltDebits			= 0.0;
			$fltTotalCharge		= 0.0;
			$fltTotalCredits	= 0.0;
			$fltTotalDebits		= 0.0;

			$this->_rptBillingReport->AddMessage(MSG_GET_SERVICES, FALSE);

			// Retrieve list of services for this account
			$selServices->Execute(Array('Account' => $arrAccount['Id']));
			if(!$arrServices = $selServices->FetchAll())
			{
				// Report and continue
				$this->_rptBillingReport->AddMessageVariables(MSG_LINE_FAILED, Array('<Reason>' => "No Services for this Account"));
				continue;
			}
			$this->_rptBillingReport->AddMessage(MSG_OK);
			
			// Get a list of shared plans for this account
			$arrSharedPlans = Array();
			foreach($arrServices as $arrService)
			{
				if ($arrService['Shared'])
				{
					$arrSharedPlans[$arrService['RatePlan']]['Count']++;
					$arrSharedPlans[$arrService['RatePlan']]['MinMonthly']	= $arrService['MinMonthly'];
					$arrSharedPlans[$arrService['RatePlan']]['UsageCap']	= $arrService['UsageCap'];
					$arrSharedPlans[$arrService['RatePlan']]['ChargeCap']	= $arrService['ChargeCap'];
				}
			}
			
			// for each service belonging to this account
			foreach ($arrServices as $arrService)
			{
				$fltServiceCredits	= 0.0;
				$fltServiceDebits	= 0.0;
				$fltTotalCharge		= 0.0;
				
				$this->_rptBillingReport->AddMessageVariables(MSG_SERVICE_TITLE, Array('<FNN>' => $arrService['FNN']));
				
				if ($arrService['Shared'] > 0)
				{
					// this is a shared plan, add to rateplan count
					$arrSharedPlans[$arrService['RatePlan']]['ServicesBilled']++;
					
					// is this the last Service for this RatePlan?
					if ($arrSharedPlans[$arrService['RatePlan']]['ServicesBilled'] == $arrSharedPlans[$arrService['RatePlan']]['Count'])
					{
						// this is the last service, add min monthly to this service
						$fltMinMonthly 	= max($arrSharedPlans[$arrService['RatePlan']]['MinMonthly'], 0);
					}
					else
					{
						$fltMinMonthly 	= 0;
					}
					$fltUsageCap 		= max($arrSharedPlans[$arrService['RatePlan']]['UsageCap'], 0);
					$fltChargeCap 		= max($arrSharedPlans[$arrService['RatePlan']]['ChargeCap'], 0);
				}
				else
				{
					// this is not a shared plan
					$fltMinMonthly 		= $arrService['MinMonthly'];
					$fltUsageCap 		= $arrService['UsageCap'];
					$fltChargeCap 		= $arrService['ChargeCap'];
				}
				
				// add capped charges
				if ($arrService['ChargeCap'] > 0.0)
				{
					// this is a capped plan
					if ($fltChargeCap > $arrService['CappedCharge'])
					{
						// under the Charge Cap : add the Full Charge
						$fltTotalCharge = (float)$arrService['CappedCharge'];
					}
					elseif ($arrService['UsageCap'] > 0 && $fltUsageCap < $arrService['CappedCharge'])
					{
						// over the Usage Cap : add the Charge Cap + Charge - Usage Cap
						$fltTotalCharge = (float)$fltChargeCap + (float)$arrService['CappedCharge'] - (float)$fltUsageCap;
					}
					else
					{
						// over the Charge Cap, Under the Usage Cap : add Charge Cap
						$fltTotalCharge = (float)$fltChargeCap;
					}
				}
				else
				{
					// this is not a capped plan
					$fltTotalCharge = (float)$arrService['CappedCharge'];
				}
				
				// add uncapped charges
				$fltTotalCharge += (float)$arrService['UncappedCharge'];

				// If there is a minimum monthly charge, apply it
				if ($fltMinMonthly > 0)
				{
					$fltTotalCharge = max($fltMinMonthly, $fltTotalCharge);
				}
				
				// if this is a shared plan
				if ($arrService['Shared'] > 0)
				{
					// remove total charged from min monthly
					$arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] = $arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] - $fltTotalCharge;
					
					// reduce caps
					$arrSharedPlans[$arrService['RatePlan']]['ChargeCap'] -= (float)$arrService['UncappedCharge'];
					$arrSharedPlans[$arrService['RatePlan']]['UsageCap'] -= (float)$arrService['UncappedCharge'];
				}
				
				// Charges and Recurring Charges (Credits and Debits) are not included
				// in caps or minimum monthlys (above)
				
				// Add Recurring Charges
				// this is done in the Recurring Charges engine, so there is nothing to do here
				
				// Mark Credits and Debits to this Invoice Run
				$this->_rptBillingReport->AddMessage(MSG_UPDATE_CHARGES, FALSE);
				
				$arrUpdateData = Array();
				$arrUpdateData['InvoiceRun']	= $strInvoiceRun;
				$arrUpdateData['Status']		= CHARGE_TEMP_INVOICE;
				$updChargeStatus = new StatementUpdate("Charge", "Status = ".CHARGE_TEMP_INVOICE." OR Status = ".CHARGE_APPROVED, $arrUpdateData);
				if($updChargeStatus->Execute($arrUpdateData, Array()) === FALSE)
				{
					// Report and fail out
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
					continue;
				}
				else
				{
					// Report and continue
					$this->_rptBillingReport->AddMessage(MSG_OK);
				}
				
				// Calculate Debit and Credit Totals
				$this->_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
				$mixResult = $selDebitsCredits->Execute(Array('Service' => $arrService['Id'], 'InvoiceRun' => $this->_strInvoiceRun));
				if($mixResult > 2 || $mixResult === FALSE)
				{
					if ($mixResult === FALSE)
					{

					}
					
					// Incorrect number of rows returned or an error
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
					continue;
				}
				else
				{
					$arrDebitsCredits = $selDebitsCredits->FetchAll();
					foreach($arrDebitsCredits as $arrCharge)
					{
						if ($arrCharge['Nature'] == "DR")
						{
							$fltServiceDebits	+= $arrCharge['Amount'];
						}
						else
						{
							$fltServiceCredits	+= $arrCharge['Amount'];
						}
					}
					$this->_rptBillingReport->AddMessage(MSG_OK);
				}
				
				
				// service total
				$fltServiceTotal	= $fltTotalCharge + $fltServiceDebits - $fltServiceCredits;
				
				// insert into ServiceTotal
				$this->_rptBillingReport->AddMessage(MSG_SERVICE_TOTAL, FALSE);
				$arrServiceTotal = Array();
				$arrServiceTotal['FNN']				= $arrService['FNN'];
				$arrServiceTotal['AccountGroup']	= $arrAccount['AccountGroup'];
				$arrServiceTotal['Account']			= $arrAccount['Id'];
				$arrServiceTotal['Service']			= $arrService['Service'];
				$arrServiceTotal['InvoiceRun']		= $strInvoiceRun;
				$arrServiceTotal['CappedCharge']	= $arrService['CappedCharge'];
				$arrServiceTotal['UncappedCharge']	= $arrService['UncappedCharge'];
				$arrServiceTotal['TotalCharge']		= $fltTotalCharge;
				$arrServiceTotal['Credit']			= $fltServiceCredits;
				$arrServiceTotal['Debit']			= $fltServiceDebits;
				
				if (!$insServiceTotal->Execute($arrServiceTotal))
				{
					Debug($insServiceTotal->Error());
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
					continue;
				}
				$this->_rptBillingReport->AddMessage(MSG_OK);
				
				// add to invoice totals
				$fltTotalDebits		+= $fltServiceDebits + $fltTotalCharge;
				$fltTotalCredits	+= $fltServiceCredits;
			}
			$this->_rptBillingReport->AddMessage(MSG_TEMP_INVOICE, FALSE);
			
			// calculate invoice total
			$fltTotal	= $fltTotalDebits - $fltTotalCredits;
			$fltTax		= ceil(($fltTotal / TAX_RATE_GST) * 100) / 100;
			$fltBalance	= $fltTotal + $fltTax;

			// calculate account debit balance
			if(!$selCalcAccountBalance->Execute(Array('Account' => $arrAccount['Id'])))
			{				
				// Report and fail out
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t\t-Reason: Cannot retrieve Account Balance");
				$intFailed++;
				continue;
			}
			$arrAccountBalance = $selCalcAccountBalance->Fetch();
			if (($fltAccountDebitBalance = $arrAccountBalance['AccountBalance']) == NULL)
			{
				$fltAccountDebitBalance = 0.0;
			}
			
			// calculate and apply account credit balance
			$arrCreditData['Account']		= $arrAccount['Id'];
			$arrCreditData['AccountGroup']	= $arrAccount['AccountGroup'];
			$this->_selPayments->Execute($arrCreditData);
			$arrPayments = $this->_selPayments->FetchAll();
			foreach($arrPayments as $arrPayment)
			{
				$fltPayment						= min($arrPayment['Balance'], $fltAccountDebitBalance);
				$arrUpdatePayment['Balance']	= $arrPayment['Balance'] - $fltPayment;
				$fltAccountDebitBalance			= $fltAccountDebitBalance - $fltPayment;
				
				// Make Invoice Payments
				$arrInvoicePaymentData['InvoiceRun']	= $this->_strInvoiceRun;
				$arrInvoicePaymentData['Account']		= $arrAccount['Id'];
				$arrInvoicePaymentData['AccountGroup']	= $arrAccount['AccountGroup'];
				$arrInvoicePaymentData['Payment']		= $arrPayment['Id'];
				$arrInvoicePaymentData['Amount']		= $fltPayment;
				$this->_insInvoicePayment->Execute($arrInvoicePaymentData);

				// Update Payment table
				if ($arrUpdatePayment['Balance'] == 0)
				{
					$arrUpdatePayment['Status'] = PAYMENT_FINISHED;
				}
				else
				{
					$arrUpdatePayment['Status'] = PAYMENT_WAITING;
				}
				$this->_ubiPayment->Execute($arrUpdatePayment);
				
				//reduce balance of the invoice
				$fltBalance -= $fltPayment;
			}

			
			// calculate account balance
			// NOTE : we don't show credit balance on the bill
			$fltAccountBalance = $fltAccountDebitBalance;
			
			// write to temporary invoice table
			$arrInvoiceData = Array();
			$arrInvoiceData['AccountGroup']		= $arrAccount['AccountGroup'];
			$arrInvoiceData['Account']			= $arrAccount['Id'];
			//$arrInvoiceData['CreatedOn']		= new MySQLFunction("NOW()");
			$arrInvoiceData['CreatedOn']		= date("Y-m-d H:i:s");
			//$arrInvoiceData['DueOn']			= new MySQLFunction("DATE_ADD(NOW(), INTERVAL <Days> DAY", Array("Days"=>$arrAccount['PaymentTerms']));
			$arrInvoiceData['DueOn']			= date("Y-m-d H:i:s", strtotime("+ ". $arrAccount['PaymentTerms'] ." days"));
			$arrInvoiceData['Credits']			= $fltTotalCredits;
			$arrInvoiceData['Debits']			= $fltTotalDebits;
			$arrInvoiceData['Total']			= $fltTotal;
			$arrInvoiceData['Tax']				= $fltTax;
			$arrInvoiceData['Balance']			= $fltBalance;
			$arrInvoiceData['Disputed']			= 0;
			$arrInvoiceData['AccountBalance']	= $fltAccountBalance;
			$arrInvoiceData['Status']			= INVOICE_TEMP;
			$arrInvoiceData['InvoiceRun']		= $strInvoiceRun;
			
			// report error or success
			if(!$insTempInvoice->Execute($arrInvoiceData))
			{				
				// Report and fail out
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t\t-Reason: Insert failed");
				$intFailed++;
				continue;
			}
			
			// work out the bill printing target
			// TODO - LATER : fake it for now
			$intPrintTarget = BILL_PRINT_ETECH;
			
			// build billing output for this invoice
			$this->_arrBillOutput[$intPrintTarget]->AddInvoice($arrInvoiceData);
			
			$intPassed++;
			
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK."\n");
		}
		
		
		
		// BILLING VALIDATION
		// Make sure all of our totals add up
		/*
			Direct SQL.......
			
			SELECT InvoiceTemp.Account AS Account, InvoiceTemp.Id AS TempInvoice, SUM( ServiceTypeTotal.Charge ) AS SumServiceTypeTotal, ServiceTotal.TotalCharge AS ServiceTypeTotal, SUM( ServiceTotal.TotalCharge ) AS AccountTotal, InvoiceTemp.Total AS InvoiceTotal
			FROM ServiceTotal, ServiceTypeTotal, InvoiceTemp
			WHERE ServiceTypeTotal.Service = ServiceTotal.Service
			AND ServiceTotal.Account = InvoiceTemp.Account
			GROUP BY InvoiceTemp.Account
			HAVING (
			SUM( ServiceTypeTotal.Charge ) != ServiceTotal.TotalCharge
			OR SUM( ServiceTotal.TotalCharge ) != InvoiceTemp.Total
			)
		 */
		$this->_rptBillingReport->AddMessage("Validating Invoice Totals...\t\t\t", FALSE);
		$strSelect	=	"InvoiceTemp.Account AS Account, InvoiceTemp.Id AS TempInvoice, SUM(ServiceTypeTotal.Charge) AS SumServiceTypeTotal, ServiceTotal.TotalCharge AS ServiceTypeTotal, SUM(ServiceTotal.TotalCharge) AS AccountTotal, InvoiceTemp.Total AS InvoiceTotal";
		$strFrom	=	"ServiceTotal, ServiceTypeTotal, InvoiceTemp";
		$strWhere	=	"ServiceTypeTotal.Service = ServiceTotal.Service AND " .
						"ServiceTotal.Account = InvoiceTemp.Account";
		$strHaving	=	"(SUM(ServiceTypeTotal.Charge) != ServiceTotal.TotalCharge OR " .
						"SUM(ServiceTotal.TotalCharge) != InvoiceTemp.Total)";
		$selBillValidate = new StatementSelect($strFrom, $strSelect, $strWhere, NULL, NULL, "InvoiceTemp.Account\nHAVING ".$strHaving);
		$intInvalid = $selBillValidate->Execute();
		$arrBillValid = $selBillValidate->Fetch();
		if($intInvalid === FALSE)
		{
			// Error
		}
		if ($intInvalid == 0)
		{
			// Report Success
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		else
		{
			// Report Bad Match & how many didn't match
			$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t- $intInvalid invalid invoices");
		}
		
		// BILLING OUTPUT
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			$this->_rptBillingReport->AddMessage("Generating sample invoices for output type $strKey...\t\t", FALSE);
			
			// build billing output sample
			if ($this->_arrBillOutput[$strKey]->BuildSample($strInvoiceRun) === FALSE)
			{
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t- Reason: Building Sample Invoices failed");
				continue;
			}
			
			// send billing output sample
			if ($this->_arrBillOutput[$strKey]->SendSample() === FALSE)
			{
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t- Reason: Sending Sample Invoices failed");
				continue;
			}
			
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// Finish off Billing Report
		$arrReportLines['<Total>']	= $intPassed + $intFailed;
		$arrReportLines['<Time>']	= $this->Framework->SplitWatch();
		$arrReportLines['<Pass>']	= $intPassed;
		$arrReportLines['<Fail>']	= $intFailed;
		$this->_rptBillingReport->AddMessageVariables(MSG_BUILD_REPORT, $arrReportLines);
		
		// FIXME: Remove if we want to do the bill audit
		return;
		
		// Generate the Bill Audit Report
		$this->_rptBillingReport->AddMessage(MSG_GENERATE_AUDIT, FALSE);
		$mixResponse = $this->_GenerateBillAudit();
		if($mixResponse === FALSE)
		{
			// Error out
			$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\tReason: There was an error retrieving from the database", FALSE);
		}
		elseif($mixResponse < 0)
		{
			// There was no invoice data
			$this->_rptAuditReport->AddMessage("There was no invoice data to generate this report from.");
			$this->_rptBillingReport->AddMessage(MSG_IGNORE."\n\t- There was no invoice data", FALSE);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK, FALSE);
		}
		// Add Footer and Send off the audit report
		$this->_rptAuditReport->AddMessage(MSG_HORIZONTAL_RULE);
		$this->_rptAuditReport->Finish();
		

	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit()
	 *
	 * Commit temporary invoices 
	 *
	 * Commit temporary invoices. Once invoices have been commited they can not
	 * be revoked.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Commit()
 	{
		// Report Title
		$this->_rptBillingReport->AddMessage(MSG_COMMIT_TITLE."\n");
		
		// FAIL if there are temporary invoices in the invoice table
		$this->_rptBillingReport->AddMessage(MSG_CHECK_TEMP_INVOICES, FALSE);
		$selCheckTempInvoices = new StatementSelect("Invoice", "Id", "Status = ".INVOICE_TEMP);
		if($selCheckTempInvoices->Execute() === FALSE)
		{

		}
		if($selCheckTempInvoices->Fetch() !== FALSE)
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED."\n".MSG_FAILED_LINE, Array('<Reason>' => "Failed invoices found"));
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// Get InvoiceRun of the current Temporary Invoice Run
		$this->_rptBillingReport->AddMessage("Retrieving InvoiceRun Id to commit...\t", FALSE);
		$selGetInvoiceRun = new StatementSelect("InvoiceTemp", "InvoiceRun", "1", NULL, "1");
		$mixResult = $selGetInvoiceRun->Execute();
		if ($mixResult === FALSE)
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED.MSG_FAILED_LINE, Array('<Reason>' => "There was a database error"));
			return;
		}
		elseif ($mixResult == 0)
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED.MSG_FAILED_LINE, Array('<Reason>' => "There was no temporary invoice run"));
			return;
		}
		$arrInvoiceRun	= $selGetInvoiceRun->Fetch();
		$strInvoiceRun	= $arrInvoiceRun['InvoiceRun'];
		$this->_rptBillingReport->AddMessage(MSG_OK);
		
		
		// copy temporary invoices to invoice table
		$this->_rptBillingReport->AddMessage(MSG_COMMIT_TEMP_INVOICES, FALSE);
		$siqInvoice = new QuerySelectInto();
		if(!$siqInvoice->Execute('Invoice', 'InvoiceTemp', "Status = ".INVOICE_TEMP))
		{			
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// change status of invoices in the temp invoice table
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_TEMP_INVOICE_STATUS, FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = INVOICE_COMMITTED;
		$updTempInvoiceStatus = new StatementUpdate("InvoiceTemp", "Status = ".INVOICE_TEMP, $arrUpdateData);
		if($updTempInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
		{			
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// change status of temp invoice CDRs
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_CDRS."\t", FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = CDR_INVOICED;
		$updCDRStatus = new StatementUpdate("CDR", "Status = ".CDR_TEMP_INVOICE, $arrUpdateData);
		if($updCDRStatus->Execute($arrUpdateData, Array()) === FALSE)
		{			
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// update Account LastBilled date
		$this->_rptBillingReport->AddMessage(MSG_LAST_BILLED."\t", FALSE);
		$strQuery  = "UPDATE Account INNER JOIN Invoice on (Account.Id = Invoice.Account)";
		$strQuery .= " SET Account.LastBilled = NOW()";
		$strQuery .= " WHERE Invoice.InvoiceRun = '$strInvoiceRun'";
		$qryAccountLastBilled = new Query();
		if(!$qryAccountLastBilled->Execute($strQuery))
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// update Charge Status
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_CHARGE."\t", FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = CHARGE_INVOICED;
		$updChargeStatus = new StatementUpdate("Charge", "Status = ".CHARGE_TEMP_INVOICE, $arrUpdateData);
		if($updChargeStatus->Execute($arrUpdateData, Array()) === FALSE)
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// update Service CappedCharge and UncappedCharge
		$this->_rptBillingReport->AddMessage("Updating Service Capped and Uncapped Totals...", FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Service.CappedCharge']		= new MySQLFunction("Service.CappedCharge - ServiceTotal.CappedCharge");
		$arrUpdateData['Service.UncappedCharge']	= new MySQLFunction("Service.UncappedCharge - ServiceTotal.UncappedCharge");
		$updServiceCharges = new StatementUpdate(	"Service JOIN ServiceTotal ON (Service.Id = ServiceTotal.Service)",
													"ServiceTotal.InvoiceRun = '$strInvoiceRun'",
													$arrUpdateData);
		if($updServiceCharges->Execute($arrUpdateData, Array()) === FALSE)
		{
			Debug($updServiceCharges->Error());
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// update Invoice Status
		// If the invoice balance is zero mark it as settled
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_INVOICE_STATUS, FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = new MySQLFunction("IF(Balance > 0, ".INVOICE_COMMITTED.", ".INVOICE_SETTLED.")");
		$updInvoiceStatus = new StatementUpdate("Invoice", "InvoiceRun = '$strInvoiceRun' AND Status = ".INVOICE_TEMP, $arrUpdateData);
		if($updInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
		{
			Debug($updInvoiceStatus->Error());
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		
		// BILLING OUTPUT
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			$this->_rptBillingReport->AddMessageVariables(MSG_BUILD_SEND_OUTPUT, Array('<Run>' => $strValue), FALSE);
			// build billing output
			if (!$this->_arrBillOutput[$strKey]->BuildOutput($strInvoiceRun))
			{
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\t- Reason: Building failed");
				continue;
			}
			
			// send billing output
			if (!$this->_arrBillOutput[$strKey]->SendOutput(FALSE))
			{
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\t- Reason: Sending failed");
				continue;
			}
			
			// Report success
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		
	}
	
	//------------------------------------------------------------------------//
	// Revoke
	//------------------------------------------------------------------------//
	/**
	 * Revoke()
	 *
	 * Revoke temporary invoices 
	 *
	 * Revoke all temporary invoices. Once invoices have been commited they can not
	 * be revoked.
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function Revoke()
 	{
		// Report Title
		$this->_rptBillingReport->AddMessage(MSG_REVOKE_TITLE."\n");
		
		// Get InvoiceRun of the current Temporary Invoice Run
		$selGetInvoiceRun = new StatementSelect("InvoiceTemp", "InvoiceRun", "1", NULL, "1");
		if ($selGetInvoiceRun->Execute() === FALSE)
		{

		}
		$arrInvoiceRun = $selGetInvoiceRun->Fetch();
		$strInvoiceRun = $arrInvoiceRun['InvoiceRun'];
		
		// empty temp invoice table
		$this->_rptBillingReport->AddMessage(MSG_CLEAR_TEMP_TABLE, FALSE);
		$trqTruncateTempTable = new QueryTruncate();
		if(!$trqTruncateTempTable->Execute("InvoiceTemp"))
		{			
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return FALSE;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// change status of CDR_TEMP_INVOICE status CDRs to CDR_RATED
		$this->_rptBillingReport->AddMessage(MSG_REVERT_CDRS, FALSE);
		$arrColumns = Array();
		$arrColumns['Status']		= CDR_RATED;
		$arrColumns['InvoiceRun']	= NULL;
		$updCDRStatus = new StatementUpdate("CDR", "CDR.Credit = 0 AND Status = ".CDR_TEMP_INVOICE, $arrColumns);
		if($updCDRStatus->Execute($arrColumns, Array()) === FALSE)
		{
			Debug($updCDRStatus->Error());
			
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return FALSE;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// update Charge Status
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_CHARGE."\t", FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = CHARGE_APPROVED;
		$updChargeStatus = new StatementUpdate("Charge", "Status = ".CHARGE_TEMP_INVOICE, $arrUpdateData);
		if($updChargeStatus->Execute($arrUpdateData, Array()) === FALSE)
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// clean up ServiceTotal table
		$this->_rptBillingReport->AddMessage("Cleaning ServiceTotal table...\t\t\t\t", FALSE);
		$qryCleanServiceTotal = new Query();
		if($qryCleanServiceTotal->Execute("DELETE FROM ServiceTotal WHERE InvoiceRun = '$strInvoiceRun'") === FALSE)
		{
			Debug($qryCleanServiceTotal);
			
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}

		// clean up ServiceTypeTotal table
		$this->_rptBillingReport->AddMessage("Cleaning ServiceTypeTotal table...\t\t\t", FALSE);
		$qryCleanServiceTotal = new Query();
		if($qryCleanServiceTotal->Execute("DELETE FROM ServiceTypeTotal WHERE InvoiceRun = '$strInvoiceRun'") === FALSE)
		{
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		// reverse payments
		$selInvoicePayments = new StatementSelect("InvoicePayment", "*", "InvoiceRun = '$strInvoiceRun'");
		$selPayments		= new StatementSelect("Payment", "*", "Id = <Id>");
		$arrCols['Status']	= NULL;
		$arrCols['Balance']	= new MySQLFunction("Balance + <Balance>");
		$ubiPayments		= new StatementUpdateById("Payment", $arrCols);
		$qryDeletePayment	= new Query();
		$selInvoicePayments->Execute();
		$arrInvoicePayments = $selInvoicePayments->FetchAll();
		foreach ($arrInvoicePayments as $arrInvoicePayment)
		{
			// update total and status of Payment
			$arrPayment['Balance']	= new MySQLFunction("Balance + <Balance>", Array('Balance' => $arrInvoicePayment['Amount']));
			$arrPayment['Status']	= PAYMENT_PAYING;
			$ubiPayments->Execute($arrPayment);
			
			// remove InvoicePayment
			$qryDeletePayment->Execute("DELETE FROM InvoicePayment WHERE Id = ".$arrInvoicePayment['Id']);
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// FinaliseReport
	//------------------------------------------------------------------------//
	/**
	 * FinaliseReport()
	 *
	 * Finalises the Billing Report
	 *
	 * Adds a footer to the report and sends it off
	 * 
	 *
	 * @return		integer		No of emails sent
	 *
	 * @method
	 */
 	function FinaliseReport()
 	{
		// Add Footer
		$this->_rptBillingReport->AddMessageVariables("\n".MSG_HORIZONTAL_RULE.MSG_BILLING_FOOTER, Array('<Time>' => $this->Framework->SplitWatch()));
		
		// Send off the report
		return $this->_rptBillingReport->Finish();
	}
	
	//------------------------------------------------------------------------//
	// _GenerateBillAudit()
	//------------------------------------------------------------------------//
	/**
	 * _GenerateBillAudit()
	 *
	 * Generates the Bill Audit Report
	 *
	 * Generates the Bill Audit Report and sends it off
	 * 
	 *
	 * @return		mixed		integer	: No of emails sent
	 * 							FALSE	: Generation failed
	 *
	 * @method
	 */
 	function _GenerateBillAudit()
 	{
		// Initiate and Execute Invoice Summary Statement
		$arrInvoiceColumns['TotalInvoices']			= "COUNT(DISTINCT InvoiceTemp.Id)";
		$arrInvoiceColumns['TotalInvoicedExGST']	= "SUM(InvoiceTemp.Total)";
		$arrInvoiceColumns['TotalInvoicedIncGST']	= "SUM(InvoiceTemp.Total) + SUM(InvoiceTemp.Tax)";
		$arrInvoiceColumns['TotalCDRCost']			= "SUM(CDR.Cost)";
		$arrInvoiceColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrInvoiceColumns['TotalCDRs']				= "COUNT(DISTINCT CDR.Id)";
		$selInvoiceSummary	= new StatementSelect(	"InvoiceTemp, CDR",
													$arrInvoiceColumns,
													"CDR.Credit = 0 AND CDR.Status = ".CDR_TEMP_INVOICE,
													NULL,
													NULL,
													"InvoiceTemp.InvoiceRun");
		if ($selInvoiceSummary->Execute() === FALSE)
		{
			// Error out
			return FALSE;
		}
		if (!$arrInvoiceSummary = $selInvoiceSummary->Fetch())
		{
			// No data, return ERROR_NO_INVOICE_DATA
			return ERROR_NO_INVOICE_DATA;
		}

		// Initiate and Execute Carrier Summary Statement
		$arrCarrierColumns['CarrierId']				= "CDR.Carrier";
		$arrCarrierColumns['TotalCost']				= "SUM(CDR.Cost)";
		$arrCarrierColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrCarrierColumns['TotalCDRs']				= "COUNT(CDR.Id)";
		$selCarrierSummary = new StatementSelect(	"CDR",
													$arrCarrierColumns,
													"CDR.Credit = 0 AND CDR.Status = ".CDR_TEMP_INVOICE,
													"CDR.Carrier",
													NULL,
													"CDR.Carrier");
		if ($selCarrierSummary->Execute() === FALSE)
		{
			// Error out
			return FALSE;
		}
		if (!$arrCarrierSummarys = $selCarrierSummary->FetchAll())
		{
			// No data, return ERROR_NO_INVOICE_DATA
			return ERROR_NO_INVOICE_DATA;
		}
		
		// Initiate and Execute ServiceType Summary Statement
		$arrServiceTypeColumns['ServiceType']			= "CDR.ServiceType";
		$arrServiceTypeColumns['TotalCost']				= "SUM(CDR.Cost)";
		$arrServiceTypeColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrServiceTypeColumns['TotalCharged']			= "SUM(ServiceTotal.TotalCharge)";
		$arrServiceTypeColumns['TotalCDRs']				= "COUNT(DISTINCT CDR.Id)";
		$selServiceTypeSummary = new StatementSelect(	"CDR, Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service",
														$arrServiceTypeColumns,
														"CDR.Credit = 0 AND CDR.Status = ".CDR_TEMP_INVOICE." AND ServiceTotal.InvoiceRun = '$this->_strInvoiceRun'",
														"CDR.ServiceType",
														NULL,
														"CDR.ServiceType");
		if ($selServiceTypeSummary->Execute() === FALSE)
		{
			// Error out
			return FALSE;
		}
		if (!$arrServiceTypeSummarys = $selServiceTypeSummary->FetchAll())
		{
			// No data, return ERROR_NO_INVOICE_DATA
			return ERROR_NO_INVOICE_DATA;
		}
		
		// Initiate RecordType Breakdown Statement
		$arrCarrierRecordTypeColumns['RecordType']		= "RecordType.Name";
		$arrCarrierRecordTypeColumns['TotalCost']		= "SUM(CDR.Cost)";
		$arrCarrierRecordTypeColumns['TotalRated']		= "SUM(CDR.Charge)";
		$arrCarrierRecordTypeColumns['TotalCDRs']		= "COUNT(CDR.Id)";
		$selRecordTypes 			= new StatementSelect("CDR JOIN RecordType ON CDR.RecordType = RecordType.Id",
														  $arrCarrierRecordTypeColumns,
														  "CDR.Credit = 0 AND CDR.Status = ".CDR_TEMP_INVOICE." AND (CDR.Carrier = <Carrier> OR CDR.ServiceType = <ServiceType>)",
														  "RecordType.Name",
														  NULL,
														  "CDR.RecordType");
		
		// Generate the the Audit Report
		$arrInvoiceSummaryVars['<TotalInvoices>']		= number_format((int)$arrInvoiceSummary['TotalInvoices']);
		$arrInvoiceSummaryVars['<TotalInvoicedExGST>']	= number_format((float)$arrInvoiceSummary['TotalInvoicedExGST'], 2);
		$arrInvoiceSummaryVars['<TotalInvoicedIncGST>']	= number_format((float)$arrInvoiceSummary['TotalInvoicedIncGST'], 2);
		$arrInvoiceSummaryVars['<TotalCDRCost>']		= number_format((float)$arrInvoiceSummary['TotalCDRCost'], 2);
		$arrInvoiceSummaryVars['<TotalRated>']			= number_format((float)$arrInvoiceSummary['TotalRated'], 2);
		$arrInvoiceSummaryVars['<TotalCDRs>']			= number_format((int)$arrInvoiceSummary['TotalCDRs']);
		$this->_rptAuditReport->AddMessageVariables(MSG_INVOICE_SUMMARY, $arrInvoiceSummaryVars);
		
		// Generate Carrier Summaries
		$strSummaries = "";
		foreach($arrCarrierSummarys as $arrCarrierSummary)
		{
			$arrInvoiceSummaryVars['<Carrier>']			= GetCarrierName($arrCarrierSummary['CarrierId']);
			$arrInvoiceSummaryVars['<TotalCDRCost>']	= number_format((float)$arrCarrierSummary['TotalCost'], 2);
			$arrInvoiceSummaryVars['<TotalRated>']		= number_format((float)$arrCarrierSummary['TotalRated'], 2);
			$arrInvoiceSummaryVars['<TotalCDRs>']		= number_format((int)$arrCarrierSummary['TotalCDRs']);
			
			// Generate Carrier's Record Type Breakdowns
			$strRecordTypes = "";
			if ($selRecordTypes->Execute(Array('Carrier' => $arrCarrierSummary['CarrierId'], 'ServiceType' => DONKEY)) === FALSE)
			{

			}
			while($arrRecordType = $selRecordTypes->Fetch())
			{
				$arrRecordTypeVars['<RecordType>']		= $arrRecordType['RecordType'];
				$arrRecordTypeVars['<TotalCDRCost>']	= number_format((float)$arrRecordType['TotalCost'], 2);
				$arrRecordTypeVars['<TotalRated>']		= number_format((float)$arrRecordType['TotalRated'], 2);
				$arrRecordTypeVars['<TotalCDRs>']		= number_format((int)$arrRecordType['TotalCDRs']);
				
				$strRecordTypes .= ReplaceAliases(MSG_RECORD_TYPES, $arrRecordTypeVars);
			}
			
			$arrInvoiceSummaryVars['<RecordTypes>']		= $strRecordTypes;
			
			$strSummaries .= ReplaceAliases(MSG_CARRIER_BREAKDOWN, $arrInvoiceSummaryVars);
		}
		
		$this->_rptAuditReport->AddMessageVariables(MSG_CARRIER_SUMMARY, Array('<Summaries>' => $strSummaries));
		
		// Generate Service Type Summaries
		$strSummaries = "";
		foreach($arrServiceTypeSummarys as $arrServiceTypeSummary)
		{
			$arrServiceTypeSummaryVars['<ServiceType>']		= $GLOBALS['ServiceTypes'][$arrServiceTypeSummary['ServiceType']];
			$arrServiceTypeSummaryVars['<TotalCDRCost>']	= number_format((float)$arrServiceTypeSummary['TotalCost'], 2);
			$arrServiceTypeSummaryVars['<TotalRated>']		= number_format((float)$arrServiceTypeSummary['TotalRated'], 2);
			$arrServiceTypeSummaryVars['<TotalCharged>']	= number_format((float)$arrServiceTypeSummary['TotalCharged'], 2);
			$arrServiceTypeSummaryVars['<TotalCDRs>']		= number_format((int)$arrServiceTypeSummary['TotalCDRs']);
			
			// Generate Carrier's Record Type Breakdowns
			$strRecordTypes = "";
			if ($selRecordTypes->Execute(Array('Carrier' => DONKEY, 'ServiceType' => $arrServiceTypeSummary['ServiceType'])) === FALSE)
			{

			}
			while($arrRecordType = $selRecordTypes->Fetch())
			{
				$arrRecordTypeVars['<RecordType>']		= $arrRecordType['RecordType'];
				$arrRecordTypeVars['<TotalCDRCost>']	= number_format((float)$arrRecordType['TotalCost'], 2);
				$arrRecordTypeVars['<TotalRated>']		= number_format((float)$arrRecordType['TotalRated'], 2);
				$arrRecordTypeVars['<TotalCDRs>']		= number_format((int)$arrRecordType['TotalCDRs']);
				
				$strRecordTypes .= ReplaceAliases(MSG_RECORD_TYPES, $arrRecordTypeVars);
			}
			$arrServiceTypeSummaryVars['<RecordTypes>']		= $strRecordTypes;
			
			$strSummaries .= ReplaceAliases(MSG_SERVICE_TYPE_BREAKDOWN, $arrServiceTypeSummaryVars);
		}
	
		$this->_rptAuditReport->AddMessageVariables(MSG_SERVICE_TYPE_SUMMARY, Array('<Summaries>' => $strSummaries));
	}
 }


?>

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
		$this->_rptAuditReport		= new Report("Bill Audit Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au", TRUE, "dispatch@voiptelsystems.com.au");
		
		// Report headers
		$this->_rptBillingReport->AddMessage(MSG_HORIZONTAL_RULE);
		$this->_rptAuditReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		// Construct the Bill Output objects
		$this->_arrBillOutput[BILL_FLEX_XML]	= new BillingModuleInvoiceXML(&$this->db, $arrConfig);
		//$this->_arrBillOutput[BILL_PRINT]		= new BillingModulePrint(&$this->db, $arrConfig);
		//$this->_arrBillOutput[BILL_PRINT_ETECH]	= new BillingModuleEtech(&$this->db, $arrConfig);
		
		// Init Statements
		$this->_selPayments = new StatementSelect(	"Payment",
													"Id, Balance",
													"Balance > 0 AND AccountGroup = <AccountGroup> AND " .
													"ISNULL(Account)",
													"PaidOn",
													NULL);
		$this->_insInvoicePayment = new StatementInsert("InvoicePayment");
		
		$arrCols['Status']	= NULL;
		$arrCols['Balance']	= NULL;
		$this->_ubiPayment = new StatementUpdateById("Payment", $arrCols);
		
		$this->selGetInvoice = new StatementSelect("Invoice", "*", "Id = <Id>", "CreatedOn DESC", 1);
		
		// Init Select Statements
		$this->arrServiceColumns = Array();
		$this->arrServiceColumns['Shared']			= "RatePlan.Shared";
		$this->arrServiceColumns['MinMonthly']		= "RatePlan.MinMonthly";
		$this->arrServiceColumns['InAdvance']		= "RatePlan.InAdvance";
		$this->arrServiceColumns['ChargeCap']		= "RatePlan.ChargeCap";
		$this->arrServiceColumns['UsageCap']		= "RatePlan.UsageCap";
		$this->arrServiceColumns['FNN']				= "Service.FNN";
		$this->arrServiceColumns['CappedCharge']	= "Service.CappedCharge";
		$this->arrServiceColumns['UncappedCharge']	= "Service.UncappedCharge";
		$this->arrServiceColumns['Service']			= "Service.Id";
		$this->arrServiceColumns['RatePlan']		= "RatePlan.Id";
		$this->arrServiceColumns['CreatedOn']		= "Service.CreatedOn";
		$this->arrServiceColumns['Indial100']		= "Service.Indial100";
		$this->arrServiceColumns['LastChargedOn']	= "ServiceRatePlan.LastChargedOn";
		$this->arrServiceColumns['ServiceRatePlan']	= "ServiceRatePlan.Id";
		$this->selServices					= new StatementSelect(	"Service LEFT JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service, RatePlan",
																	$this->arrServiceColumns,
																	"Service.Account = <Account> AND RatePlan.Id = ServiceRatePlan.RatePlan AND " .
																	"Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.") " .
																	" AND ServiceRatePlan.Id = ( SELECT Id FROM ServiceRatePlan WHERE Service = Service.Id AND NOW() >= StartDatetime AND Active = 1 ORDER BY CreatedOn DESC LIMIT 1)",
																	"RatePlan.Id");
		$this->strTestAccounts =		" AND " .
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
		$this->selAccounts					= new StatementSelect("Account", "*", "Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")"); 
		
		//$this->selCalcAccountBalance		= new StatementSelect("Invoice", "SUM(Balance) AS AccountBalance", "Status = ".INVOICE_COMMITTED." AND Account = <Account>");
		
		// service debits and credits
		$this->selDebitsCredits				= new StatementSelect(	"Charge",
																 	"Nature, SUM(Amount) AS Amount",
															 		"Service = <Service> AND Status = ".CHARGE_TEMP_INVOICE." AND InvoiceRun = <InvoiceRun>",
															  		NULL,
															  		"2",
															  		"Nature");
		
		// account debits and credits		
		$this->selAccountDebitsCredits		= new StatementSelect(	"Charge",
																 	"Nature, SUM(Amount) AS Amount",
															 		"Account = <Account> AND ISNULL(Service) AND Status = ".CHARGE_TEMP_INVOICE." AND InvoiceRun = <InvoiceRun>",
															  		NULL,
															  		"2",
															  		"Nature");
		
		// Init Update Statements
		$this->arrCDRCols = Array();
		$this->arrCDRCols['Status']			= CDR_TEMP_INVOICE;
		$this->arrCDRCols['InvoiceRun']		= NULL;
		$this->updCDRs						= new StatementUpdate("CDR USE INDEX (Account_2)", "Account = <Account> AND Credit = 0 AND Status = ".CDR_RATED, $this->arrCDRCols);
		
		// Init Insert Statements
		$this->arrInvoiceData 						= Array();
		$this->arrInvoiceData 	['AccountGroup']	= NULL;
		$this->arrInvoiceData 	['Account']			= NULL;
		$this->arrInvoiceData 	['CreatedOn']		= /*new MySQLFunction("NOW()")*/NULL;
		$this->arrInvoiceData 	['DueOn']			= /*new MySQLFunction("DATE_ADD(NOW(), INTERVAL <Days> DAY")*/NULL;
		$this->arrInvoiceData 	['Credits']			= NULL;
		$this->arrInvoiceData 	['Debits']			= NULL;
		$this->arrInvoiceData 	['Total']			= NULL;
		$this->arrInvoiceData 	['Tax']				= NULL;
		$this->arrInvoiceData 	['TotalOwing']		= NULL;
		$this->arrInvoiceData 	['Balance']			= NULL;
		$this->arrInvoiceData 	['Disputed']		= NULL;
		$this->arrInvoiceData 	['AccountBalance']	= NULL;
		$this->arrInvoiceData 	['Status']			= NULL;
		$this->arrInvoiceData 	['InvoiceRun']		= NULL;
		$this->arrInvoiceData 	['DeliveryMethod']	= NULL;
		$this->insTempInvoice						= new StatementInsert("InvoiceTemp", $this->arrInvoiceData 	);
		$this->insServiceTotal						= new StatementInsert("ServiceTotal");
		
		$this->_selServiceTotalCheckAll	= new StatementSelect(	"ServiceTypeTotal STT LEFT JOIN ServiceTotal ST USING (InvoiceRun, Service)",
																"STT.Account",
																"InvoiceRun = <InvoiceRun> AND ST.Id IS NULL",
																"ST.Id ASC");
																
		$this->_selServiceTotalCheck	= new StatementSelect("ServiceTotal", "Id", "Service = <Service> AND InvoiceRun = <InvoiceRun>");
		
		$this->_selEarliestCDR		= new StatementSelect("Service", "EarliestCDR", "Id = <Service>");
		$this->_selPlanDate			= new StatementSelect("ServiceRatePlan", "StartDatetime", "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime AND Active = 1", "CreatedOn DESC", 1);
		$this->_selHasInvoicedCDRs	= new StatementSelect("ServiceTotal", "Id", "Service = <Service> AND (UncappedCost > 0.0 OR CappedCost > 0.0)");
		
		// Init Charge Modules
		$this->_arrBillingChargeModules	= Billing_Charge::getModules();
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
 	function Execute($strMode = 'gold')
 	{
		// Start the stopwatch
		$this->Framework->StartWatch();

		// Empty the temporary invoice table
		// This is safe, because there should be no CDRs with CDR_TEMP_INVOICE status anyway
		if (!$this->Revoke())
		{
			// Return if this fails
			CliEcho("Revoke() failed: aborting Execute()");
			return;
		}
		
		// generate an InvoiceRun Id
		$strInvoiceRun					= date("YmdHis");
		switch (strtolower($strMode))
		{
			case 'gold':
				$this->_strInvoiceRun	= $strInvoiceRun;
				break;
			
			case 'silver':
			case 'bronze':
			case 'internalinitial':
			case 'internalfinal':
				$this->_strInvoiceRun	= $strInvoiceRun.'-'.strtolower($strMode);
				break;
			
			default:
				// Unhandled type
				CliEcho("'$strMode' is not a valid Billing::Execute() type!\n");
				return FALSE;
				break;
		}
		
		$intPassed = 0;
		$intFailed = 0;
		
		// Get a list of all accounts that require billing today
		if ($this->selAccounts->Execute() === FALSE)
		{
			CliEcho("Unable to retrieve list of Accounts: ".$this->selAccounts->Error());
		}
		$arrAccounts = $this->selAccounts->FetchAll();
		
		// Generate the Invoices
		$this->GenerateInvoices($arrAccounts);
		
		// Finish off Billing Report
		$arrReportLines['<Total>']	= $this->intPassed + $this->intFailed;
		$arrReportLines['<Time>']	= $this->Framework->SplitWatch();
		$arrReportLines['<Pass>']	= $this->intPassed;
		$arrReportLines['<Fail>']	= $this->intFailed;
		$this->_rptBillingReport->AddMessageVariables(MSG_BUILD_REPORT, $arrReportLines);
	}




	//------------------------------------------------------------------------//
	// GenerateInvoices
	//------------------------------------------------------------------------//
	/**
	 * GenerateInvoices()
	 *
	 * Generates Invoices for an array of accounts
	 *
	 * Generates Invoices for an array of accounts
	 *
	 * @param	array	$arrAccount		Indexed array of accounts to generate invoices for
	 * @param	bool	$bolReturnData	Return the Invoice data as an array instead of inserting into the
	 * 									database.
	 * @param	bool	$bolRegenerate	Regenerate a Temp Invoice instead of create a new one
	 *
	 * @return			bool
	 *
	 * @method
	 */
	function GenerateInvoices($arrAccounts, $bolReturnData = FALSE, $bolRegenerate = FALSE)
	{
		$arrReturnData = Array();
		
		if (!is_array($arrAccounts))
		{
			return FALSE;
		}
		
		// Report Title
		//$this->_rptBillingReport->AddMessage("\n".MSG_BILLING_TITLE."\n");
		
		// prepare (clean) billing files
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			$this->_arrBillOutput[$strKey]->clean();
		}
		
		// setup statements
		$arrUpdateData = Array();
		$arrUpdateData['InvoiceRun']	= '';
		$arrUpdateData['Status']		= '';
		$updChargeStatus	= new StatementUpdate("Charge", "Account = <Account> AND (Status = ".CHARGE_TEMP_INVOICE." OR Status = ".CHARGE_APPROVED.")", $arrUpdateData);
		$selCDRTotals		= new StatementSelect(	"CDR USE INDEX (Service_2) JOIN Rate ON (CDR.Rate = Rate.Id)",
													"SUM(CASE WHEN Rate.Uncapped THEN CDR.Charge ELSE 0 END) AS UncappedCharge, " .
													"SUM(CASE WHEN Rate.Uncapped THEN CDR.Cost ELSE 0 END) AS UncappedCost, " .
													"SUM(CASE WHEN Rate.Uncapped THEN 0 ELSE CDR.Charge END) AS CappedCharge, " .
													"SUM(CASE WHEN Rate.Uncapped THEN 0 ELSE CDR.Cost END) AS CappedCost, " .
													"CDR.RecordType AS RecordType",
													"CDR.Service = <Service> AND " .
													"CDR.Credit = 0".
													" AND CDR.Status = ".CDR_TEMP_INVOICE ,
													NULL,
													NULL,
													"CDR.RecordType, Rate.Uncapped");
		
		// Loop through the accounts we're billing
		foreach ($arrAccounts as $arrAccount)
		{
			$arrAccountReturn = Array();
			
			$this->_rptBillingReport->AddMessageVariables(MSG_ACCOUNT_TITLE, Array('<AccountNo>' => $arrAccount['Id']));
			
			// Link CDRs if creating a new Invoice
			$qryUpdateCDRs	= new Query();
			if (!$bolRegenerate)
			{
				//$this->_rptBillingReport->AddMessage(MSG_LINK_CDRS, FALSE);
				
				// Set status of CDR_RATED CDRs for this account to CDR_TEMP_INVOICE
				if($qryUpdateCDRs->Execute("UPDATE CDR USE INDEX (Account_2) JOIN Service ON Service.Id = CDR.Service SET InvoiceRun = '{$this->_strInvoiceRun}', CDR.Status = ".CDR_TEMP_INVOICE." WHERE CDR.Status = ".CDR_RATED." AND CDR.Account = {$arrAccount['Id']} AND CDR.Credit = 0 AND Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.")") === FALSE)
				{
					CliEcho("\n".__LINE__." >> Unable to Update Account CDRs: ".$qryUpdateCDRs->Error());
					exit(1);
				}
				else
				{
					//$this->_rptBillingReport->AddMessage(MSG_OK);
				}
			}
			
			// SERVICE TYPE TOTALS			
			// build query (with Service Extensions)
			$strExtensionsQuery  = "INSERT INTO ServiceTypeTotal (FNN, AccountGroup, Account, Service, InvoiceRun, RecordType, Charge, Units, Records, RateGroup, Cost)";
			$strExtensionsQuery .= " SELECT CDR.FNN, CDR.AccountGroup, CDR.Account, CDR.Service, '".$this->_strInvoiceRun."' AS InvoiceRun,";
			$strExtensionsQuery .= " CDR.RecordType, SUM(CDR.Charge) AS Charge, SUM(CDR.Units) AS Units, COUNT(CDR.Charge) AS Records, ServiceRateGroup.RateGroup AS RateGroup, SUM(CDR.Cost) AS Cost";
			$strExtensionsQuery .= " FROM CDR USE INDEX (Account_2) JOIN Service ON Service.Id = CDR.Service, ServiceRateGroup";
			$strExtensionsQuery .= " WHERE CDR.FNN IS NOT NULL AND CDR.RecordType IS NOT NULL";
			$strExtensionsQuery .= " AND CDR.Status = ".CDR_TEMP_INVOICE;
			$strExtensionsQuery .= " AND CDR.Account = ".$arrAccount['Id'];
			$strExtensionsQuery .= " AND CDR.Credit = 0 ";
			$strExtensionsQuery .= " AND Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.") ";
			$strExtensionsQuery .= " AND ServiceRateGroup.Id = (SELECT SRG.Id FROM ServiceRateGroup SRG WHERE NOW() BETWEEN SRG.StartDatetime AND SRG.EndDatetime AND SRG.Service = CDR.Service ORDER BY CreatedOn DESC LIMIT 1) ";
			$strExtensionsQuery .= " GROUP BY CDR.Service, CDR.FNN, CDR.RecordType";
			
			// run query
			$qryServiceTypeTotal = new Query();
			if ($qryServiceTypeTotal->Execute($strExtensionsQuery) === FALSE)
			{
				CliEcho("\n".__LINE__."Unable to calculate ServiceTypeTotals for Account #{$arrAccount['Id']}");
				exit(1);
			}
			
			// zero out totals
			$fltDebits			= 0.0;
			$fltTotalCharge		= 0.0;
			$fltTotalCredits	= 0.0;
			$fltTotalDebits		= 0.0;

			//$this->_rptBillingReport->AddMessage(MSG_GET_SERVICES, FALSE);

			// Retrieve list of services for this account
			$this->selServices->Execute(Array('Account' => $arrAccount['Id']));
			if(!$arrServices = $this->selServices->FetchAll())
			{
				// Report and continue
				//$this->_rptBillingReport->AddMessageVariables(MSG_LINE_FAILED, Array('<Reason>' => "No Services for this Account"));
				//continue;
			}
			//$this->_rptBillingReport->AddMessage(MSG_OK);
			
			
			// When was the Billing Period supposed to start?
			$strBillingDate			= '01';
			$intDate				= strtotime(date("Y-m-01", time()));
			$intLastBillDate		= strtotime("-{$arrAccount['BillingFreq']} month", strtotime(date("Y-m-$strBillingDate", $intDate)));
			$arrSharedPlans			= Array();
			$intServicesComplete	= 0;
			foreach($arrServices as $mixIndex=>$arrService)
			{
				if ((float)$arrService['MinMonthly'] > 0)
				{
					// Prorate Minimum Monthly
					$this->_selEarliestCDR->Execute($arrService);
					$this->_selPlanDate->Execute($arrService);
					$arrEarliestCDR	= $this->_selEarliestCDR->Fetch();
					$arrPlanDate	= $this->_selPlanDate->Fetch();
					
					$intCDRDate		= strtotime($arrEarliestCDR['EarliestCDR']);
					$intServiceDate	= strtotime($arrService['CreatedOn']);
					$intPlanDate	= strtotime($arrPlanDate['StartDatetime']);
					
					// If the Service is tolling (has an EarliestCDR)
					if ($intCDRDate)
					{
						$bolHasInvoicedCDRs	= (bool)$this->_selHasInvoicedCDRs->Execute($arrService);
						
						// If this is the first invoice for this plan, add in "Charge in Advance" Adjustment
						if ((!$arrService['LastChargedOn'] || !$bolHasInvoicedCDRs) && $arrService['InAdvance'])
						{
							$arrAdvanceCharge = Array();
							$arrAdvanceCharge['AccountGroup']	= $arrAccount['AccountGroup'];
							$arrAdvanceCharge['Account']		= $arrAccount['Id'];
							$arrAdvanceCharge['Service']		= $arrService['Service'];
							//$arrAdvanceCharge['ChargeType']		= 'PCA'.round($arrService['MinMonthly'], 2);
							$arrAdvanceCharge['ChargeType']		= 'PCA';
							$arrAdvanceCharge['Description']	= "Plan Charge in Advance from ".date("01/m/Y")." to ".date("d/m/Y", strtotime("-1 day", strtotime("+1 month", strtotime(date("Y-m-01")))));
							$arrAdvanceCharge['ChargedOn']		= date("Y-m-d");
							$arrAdvanceCharge['Nature']			= 'DR';
							$arrAdvanceCharge['Amount']			= $arrService['MinMonthly'];
							$this->Framework->AddCharge($arrAdvanceCharge);
						}
						
						// If the first CDR is unbilled, Pro Rata
						//if ($intCDRDate > $intLastBillDate)
						if (!$bolHasInvoicedCDRs)
						{
							$fltMinMonthly	= $arrService['MinMonthly'];
							
							// Prorate the Minimum Monthly
							$intProratePeriod						= TruncateTime(time(), 'd', 'floor') - TruncateTime($intCDRDate, 'd', 'floor');
							$intBillingPeriod						= TruncateTime(time(), 'd', 'floor') - TruncateTime($intLastBillDate, 'd', 'floor');
							$fltProratedMinMonthly					= ($arrService['MinMonthly'] / $intBillingPeriod) * $intProratePeriod;
							$arrService['MinMonthly']				= round($fltProratedMinMonthly, 2);
							$arrServices[$mixIndex]['MinMonthly']	= 0.0;
							
							// For now, add an adjustment instead of actually changing the min monthly
							$arrProrataCharge = Array();
							$arrProrataCharge['AccountGroup']	= $arrAccount['AccountGroup'];
							$arrProrataCharge['Account']		= $arrAccount['Id'];
							$arrProrataCharge['Service']		= $arrService['Service'];
							//$arrProrataCharge['ChargeType']		= 'PCP'.round($arrService['MinMonthly'], 2);
							$arrProrataCharge['ChargeType']		= 'PCP';
							$arrProrataCharge['Description']	= "Plan Charge in Arrears from ".date("d/m/Y", $intCDRDate)." to ".date("d/m/Y", strtotime("-1 day", time()));
							$arrProrataCharge['ChargedOn']		= date("Y-m-d");
							$arrProrataCharge['Nature']			= 'DR';
							$arrProrataCharge['Amount']			= $arrService['MinMonthly'];
							$this->Framework->AddCharge($arrProrataCharge);
						}
					}
					else
					{
						// No CDRs
						$arrService['MinMonthly']				= 0;
						$arrServices[$mixIndex]['MinMonthly']	= 0;
					}
				}
				
				// Special Shared Plan Handling
				if ($arrService['Shared'])
				{
					$arrSharedPlans[$arrService['RatePlan']]['Count']++;
					$arrSharedPlans[$arrService['RatePlan']]['MinMonthly']	= max($arrService['MinMonthly'], $arrSharedPlans[$arrService['RatePlan']]['MinMonthly']);
					$arrSharedPlans[$arrService['RatePlan']]['UsageCap']	= $arrService['UsageCap'];
					$arrSharedPlans[$arrService['RatePlan']]['ChargeCap']	= $arrService['ChargeCap'];
				}
			}
			$arrAccountReturn['SharedPlans']	= $arrSharedPlans;
			
			// Mark Credits and Debits for this account to this Invoice Run
			//$this->_rptBillingReport->AddMessage(MSG_UPDATE_CHARGES, FALSE);
			if (!$bolRegenerate)
			{
				$arrUpdateData					= Array();
				$arrUpdateData['InvoiceRun']	= $this->_strInvoiceRun;
				$arrUpdateData['Status']		= CHARGE_TEMP_INVOICE;
				if($updChargeStatus->Execute($arrUpdateData, Array('Account' => $arrAccount['Id'])) === FALSE)
				{
					// Report and fail out
					$this->_rptBillingReport->AddMessageVariables(MSG_ACCOUNT_TITLE, Array('<AccountNo>' => $arrAccount['Id']));
					$this->_rptBillingReport->AddMessage(MSG_UPDATE_CHARGES, FALSE);
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
					CliEcho("\n".__LINE__." >> Unable to Mark Credits & Debits for Account #{$arrAccount['Id']}");
					exit(1);
				}
				else
				{
					// Report and continue
					//$this->_rptBillingReport->AddMessage(MSG_OK);
				}
			}
			
			// for each service belonging to this account
			$arrUniqueServiceList = Array();
			foreach ($arrServices as $arrService)
			{
				$arrServiceReturn = Array();
				
				$fltServiceCredits		= 0.0;
				$fltServiceDebits		= 0.0;
				$fltTotalCharge			= 0.0;
				$fltUncappedCDRCharge	= 0.0;
				$fltCappedCDRCharge		= 0.0;
				$fltUncappedCDRCost		= 0.0;
				$fltCappedCDRCost		= 0.0;
				
				// get capped & uncapped charges
				$selCDRTotals->Execute($arrService);
				$arrCDRTotals	= $selCDRTotals->FetchAll();
				foreach($arrCDRTotals as $arrCDRTotal)
				{
					$fltCappedCDRCost		+= $arrCDRTotal['CappedCost'];
					$fltUncappedCDRCost		+= $arrCDRTotal['UncappedCost'];
					$fltUncappedCDRCharge	+= $arrCDRTotal['UncappedCharge'];
					$fltCappedCDRCharge		+= $arrCDRTotal['CappedCharge'];
				}
				
				//$this->_rptBillingReport->AddMessageVariables(MSG_SERVICE_TITLE, Array('<FNN>' => $arrService['FNN']));
				
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
					if ($fltChargeCap > $fltCappedCDRCharge)
					{
						// under the Charge Cap : add the Full Charge
						$fltTotalCharge = $fltCappedCDRCharge;
					}
					elseif ($arrService['UsageCap'] > 0 && $fltUsageCap < $fltCappedCDRCharge)
					{
						// over the Usage Cap : add the Charge Cap + Charge - Usage Cap
						$fltTotalCharge = (float)$fltChargeCap + $fltCappedCDRCharge - (float)$fltUsageCap;
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
					$fltTotalCharge = $fltCappedCDRCharge;
				}
				
				// If there is a minimum monthly charge, apply it
				if ($fltMinMonthly > 0)
				{
					$fltTotalCharge = max($fltMinMonthly, $fltTotalCharge);
				}
				
				// add uncapped charges
				$fltTotalCharge += $fltUncappedCDRCharge;
				
				// if this is a shared plan
				if ($arrService['Shared'] > 0)
				{
					// remove total charged from min monthly
					$arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] = $arrSharedPlans[$arrService['RatePlan']]['MinMonthly'] - $fltTotalCharge;
					
					// reduce caps
					$arrSharedPlans[$arrService['RatePlan']]['ChargeCap'] -= $fltUncappedCDRCharge;
					$arrSharedPlans[$arrService['RatePlan']]['UsageCap'] -= $fltUncappedCDRCharge;
				}
				
				// Add in Service modular charges
				if (!$bolRegenerate)
				{
					foreach ($this->_arrBillingChargeModules[$arrAccount['CustomerGroup']]['Billing_Charge_Service'] as $chgModule)
					{
						// Generate charge
						$mixResult = $chgModule->Generate(Array('InvoiceRun' => $this->_strInvoiceRun), $arrService);
					}
				}
				
				// Calculate Service Debit and Credit Totals
				//$this->_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
				$mixResult = $this->selDebitsCredits->Execute(Array('Service' => $arrService['Service'], 'InvoiceRun' => $this->_strInvoiceRun));
				if($mixResult > 2 || $mixResult === FALSE)
				{
					if ($mixResult === FALSE)
					{
						CliEcho("\n".__LINE__." >> Unable to Calculate Service Debit & Credit Totals Account #{$arrAccount['Id']}::{$arrService['FNN']}");
						CliEcho($this->selDebitsCredits->Error());
						exit(1);
					}
					
					// Incorrect number of rows returned or an error
					$this->_rptBillingReport->AddMessageVariables(MSG_SERVICE_TITLE, Array('<FNN>' => $arrService['FNN']));
					$this->_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
					CliEcho("\n".__LINE__." >> Unable to Calculate Service Debit & Credit Totals Account #{$arrAccount['Id']}::{$arrService['FNN']}");
					exit(1);
				}
				else
				{
					$arrDebitsCredits = $this->selDebitsCredits->FetchAll();
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
					//$this->_rptBillingReport->AddMessage(MSG_OK);
				}
				
				
				// service total
				$fltServiceTotal	= $fltTotalCharge + $fltServiceDebits - $fltServiceCredits;
				
				// insert into ServiceTotal
				//$this->_rptBillingReport->AddMessage(MSG_SERVICE_TOTAL, FALSE);
				$arrServiceTotal = Array();
				$arrServiceTotal['FNN']					= $arrService['FNN'];
				$arrServiceTotal['AccountGroup']		= $arrAccount['AccountGroup'];
				$arrServiceTotal['Account']				= $arrAccount['Id'];
				$arrServiceTotal['Service']				= $arrService['Service'];
				$arrServiceTotal['InvoiceRun']			= $this->_strInvoiceRun;
				$arrServiceTotal['CappedCharge']		= $fltCappedCDRCharge;
				$arrServiceTotal['UncappedCharge']		= $fltUncappedCDRCharge;
				$arrServiceTotal['TotalCharge']			= $fltTotalCharge;
				$arrServiceTotal['Credit']				= $fltServiceCredits;
				$arrServiceTotal['Debit']				= $fltServiceDebits;
				$arrServiceTotal['RatePlan']			= $arrService['RatePlan'];
				$arrServiceTotal['CappedCost']			= $fltCappedCDRCost;
				$arrServiceTotal['UncappedCost']		= $fltUncappedCDRCost;
				$arrServiceTotal['PlanCharge']			= $arrService['MinMonthly'];
				$arrServiceTotal['service_rate_plan']	= $arrService['ServiceRatePlan'];
				
				if (!$this->insServiceTotal->Execute($arrServiceTotal))
				{
					Debug($this->insServiceTotal->Error());
					$this->_rptBillingReport->AddMessageVariables(MSG_SERVICE_TITLE, Array('<FNN>' => $arrService['FNN']));
					$this->_rptBillingReport->AddMessage(MSG_SERVICE_TOTAL, FALSE);
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
					CliEcho("\n".__LINE__." >> Unable to add Service Total for Account #{$arrAccount['Id']}::{$arrService['FNN']}");
					exit(1);
				}
				//$this->_rptBillingReport->AddMessage(MSG_OK);
				
				// add to invoice totals
				$fltTotalDebits		+= $fltServiceDebits + $fltTotalCharge;
				$fltTotalCredits	+= $fltServiceCredits;
				
				$arrAccountReturn['Services'][] = $arrServiceTotal;
				
				
				// DEBUG -- Do a quick check to see if the ServiceTotal has been correctly created
				$mixResult	= $this->_selServiceTotalCheck->Execute($arrServiceTotal);
				if ($mixResult === FALSE)
				{
					Debug($this->_selServiceTotalCheck->Error());
					exit(1);
				}
				elseif(!$mixResult)
				{
					CliEcho("\n".__LINE__." >> Unable to find Service Total for Account #{$arrAccount['Id']}::{$arrService['FNN']}");
					exit(1);
				}
				$intServicesComplete++;
			}
			
			// DEBUG -- Do a quick check to see if the ServiceTotals have been correctly created
			if ($intServicesComplete !== count($arrServices))
			{
				CliEcho("\n".__LINE__." >> Only $intServicesComplete of ".count($arrServices)." ServiceTotals were created for Account #{$arrAccount['Id']}");
				exit(1); 
			}
			/*else
			{
				CliEcho("Found all $intServicesComplete of ".count($arrServices)." ServiceTotals for Account #{$arrAccount['Id']}");
			}*/
			
			
			// Calculate Account Debit and Credit Totals
			//$this->_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
			$mixResult = $this->selAccountDebitsCredits->Execute(Array('Account' => $arrAccount['Id'], 'InvoiceRun' => $this->_strInvoiceRun));
			if($mixResult > 2 || $mixResult === FALSE)
			{
				if ($mixResult === FALSE)
				{

				}
				
				// Incorrect number of rows returned or an error
				$this->_rptBillingReport->AddMessageVariables(MSG_ACCOUNT_TITLE, Array('<AccountNo>' => $arrAccount['Id']));
				$this->_rptBillingReport->AddMessage(MSG_DEBITS_CREDITS, FALSE);
				$this->_rptBillingReport->AddMessage(MSG_FAILED);
				CliEcho("\n".__LINE__." >> Unable to Calculate Account Debits and Credits for Account #{$arrAccount['Id']}");
				exit(1);
			}
			else
			{
				$arrDebitsCredits = $this->selAccountDebitsCredits->FetchAll();
				foreach($arrDebitsCredits as $arrCharge)
				{
					if ($arrCharge['Nature'] == "DR")
					{
						$fltTotalDebits		+= $arrCharge['Amount'];
					}
					else
					{
						$fltTotalCredits	+= $arrCharge['Amount'];
					}
				}
				//$this->_rptBillingReport->AddMessage(MSG_OK);
				$arrAccountReturn['AccountAdjustments']	= $arrDebitsCredits;
			}
			
			//$this->_rptBillingReport->AddMessage(MSG_TEMP_INVOICE, FALSE);
			
			// calculate account balance from outstanding past invoices (this could give a negative value)
			$fltAccountBalance = 0.0;
			if(($fltAccountBalance = $this->Framework->GetAccountBalance($arrAccount['Id'])) === FALSE)
			{
				// Report and fail out
				$this->_rptBillingReport->AddMessageVariables(MSG_ACCOUNT_TITLE, Array('<AccountNo>' => $arrAccount['Id']));
				$this->_rptBillingReport->AddMessage(MSG_TEMP_INVOICE, FALSE);
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t\t-Reason: Cannot retrieve Account Balance");
				$this->intFailed++;
				CliEcho("\n".__LINE__." >> Unable to find Account Balance for Account #{$arrAccount['Id']}");
				exit(1);
			}
			
			// calculate initial invoice total and total owing
			$fltTotal		= ceil(($fltTotalDebits - $fltTotalCredits) * 100) / 100;
			$fltTax			= ceil(($fltTotal / TAX_RATE_GST) * 100) / 100;
			$fltBalance		= $fltTotal + $fltTax;
			$fltTotalOwing	= $fltBalance + $fltAccountBalance;
			
			// group invoice data
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
			$arrInvoiceData['TotalOwing']		= $fltTotalOwing;
			$arrInvoiceData['Balance']			= $fltBalance;
			$arrInvoiceData['Disputed']			= 0;
			$arrInvoiceData['AccountBalance']	= $fltAccountBalance;
			$arrInvoiceData['Status']			= INVOICE_TEMP;
			$arrInvoiceData['InvoiceRun']		= $this->_strInvoiceRun;
			
			$arrAccountReturn['InitialInvoiceData'] = $arrInvoiceData;
			
			// Add in Account modular charges
			if (!$bolRegenerate)
			{
				foreach ($this->_arrBillingChargeModules[$arrAccount['CustomerGroup']]['Billing_Charge_Account'] as $chgModule)
				{
					// Generate charge
					$mixResult = $chgModule->Generate($arrInvoiceData, $arrAccount);
					
					// Add to totals
					if (!is_bool($mixResult))
					{
						if ($mixResult < 0)
						{
							// Credit
							$fltTotalCredits	+= $mixResult;
						}
						else
						{
							// Debit
							$fltTotalDebits		+= $mixResult;
						}
						
						$arrAccountReturn['ChargeModules'][] = $mixResult;
					}
				}
			}
			
			// recalculate initial invoice total and total owing
			$fltTotal		= ceil(($fltTotalDebits - $fltTotalCredits) * 100) / 100;
			$fltTax			= ceil(($fltTotal / TAX_RATE_GST) * 100) / 100;
			$fltBalance		= $fltTotal + $fltTax;
			$fltTotalOwing	= $fltBalance + $fltAccountBalance;
			
			// Determine Delivery Method
			switch($arrAccount['BillingMethod'])
			{
				case BILLING_METHOD_EMAIL:
					if ($fltTotal+$fltTax != 0 || ($fltTotalOwing != 0 && $arrAccount['Status'] == ACCOUNT_STATUS_ACTIVE))
					{
						$intDeliveryMethod	= $arrAccount['BillingMethod'];
					}
					else
					{
						$intDeliveryMethod	= BILLING_METHOD_DO_NOT_SEND;
					}
					break;
					
				default:
					if ($fltTotal+$fltTax >= BILLING_MINIMUM_TOTAL || $fltTotalOwing >= BILLING_MINIMUM_TOTAL)
					{
						$intDeliveryMethod	= $arrAccount['BillingMethod'];
					}
					else
					{
						$intDeliveryMethod	= BILLING_METHOD_DO_NOT_SEND;
					}
					break;
			}
			
			/*if ($fltTotal+$fltTax >= BILLING_MINIMUM_TOTAL || $fltTotalOwing >= BILLING_MINIMUM_TOTAL || $arrAccount['BillingMethod'] == BILLING_METHOD_EMAIL)
			{
				$intDeliveryMethod	= $arrAccount['BillingMethod'];
			}
			else
			{
				$intDeliveryMethod	= BILLING_METHOD_DO_NOT_SEND;
			}*/
			
			// get new values, and write to temporary invoice table
			$arrInvoiceData['Credits']			= $fltTotalCredits;
			$arrInvoiceData['Debits']			= $fltTotalDebits;
			$arrInvoiceData['Total']			= $fltTotal;
			$arrInvoiceData['Tax']				= $fltTax;
			$arrInvoiceData['TotalOwing']		= $fltTotalOwing;
			$arrInvoiceData['Balance']			= $fltBalance;
			$arrInvoiceData['AccountBalance']	= $fltAccountBalance;
			$arrInvoiceData['DeliveryMethod']	= $intDeliveryMethod;
			
			$arrAccountReturn['FinalInvoiceData'] = $arrInvoiceData;
			
			// report error or success
			if(!$this->insTempInvoice->Execute($arrInvoiceData))
			{				
				// Report and fail out
				$this->_rptBillingReport->AddMessageVariables(MSG_ACCOUNT_TITLE, Array('<AccountNo>' => $arrAccount['Id']));
				$this->_rptBillingReport->AddMessage(MSG_TEMP_INVOICE, FALSE);
				$this->_rptBillingReport->AddMessage(MSG_FAILED."\n\t\t-Reason: Insert failed");
				$this->intFailed++;
				CliEcho("\n".__LINE__." >> Unable to add Temporary Invoice for Account #{$arrAccount['Id']}");
				exit(1);
			}
			
			$this->intPassed++;
			
			// Report and continue
			//$this->_rptBillingReport->AddMessage(MSG_OK."\n");
			
			$arrReturnData[$arrInvoiceData['Account']] = $arrAccountReturn;
		}
		
		// Return Data if debugging
		if ($bolReturnData)
		{
			return $arrReturnData;
		}
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
		$arrInvoiceRun	= $selGetInvoiceRun->Fetch();
		if (!$arrInvoiceRun['InvoiceRun'])
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED.MSG_FAILED_LINE, Array('<Reason>' => "There was no temporary invoice run"));
			return;
		}
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
		
		// change status of temp invoice CDRs
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_CDRS."\t", FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = CDR_INVOICED;
		$updCDRStatus = new StatementUpdate("CDR USE INDEX (Status)", "Status = ".CDR_TEMP_INVOICE, $arrUpdateData);
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
		
		// update Invoice Status to PRINT
		/*$this->_rptBillingReport->AddMessage(MSG_UPDATE_INVOICE_STATUS, FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = INVOICE_PRINT;
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
		}*/
		/*
		// BILLING OUTPUT (now in billing_deliver.php)
		foreach ($this->_arrBillOutput AS $strKey=>$objValue)
		{
			$this->_rptBillingReport->AddMessage(MSG_BUILD_SEND_OUTPUT, FALSE);
			// build billing output
			if (!$this->_arrBillOutput[$strKey]->BuildOutput())
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
		*/
		
		// Alert YBS that file is ready for upload
		//SendEmail('rich@voiptelsystems.com.au, msergeant@yellowbilling.com.au, turdminator@hotmail.com', 'Invoice Run Ready for Upload', 'The Invoice Run VBF file is ready for upload');
		
		
		// update Invoice Status to COMMITTED, or SETTLED if the invoice balance is zero
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_INVOICE_STATUS, FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status']	= new MySQLFunction("IF(Balance > 0, ".INVOICE_COMMITTED.", ".INVOICE_SETTLED.")");
		$arrUpdateData['SettledOn']	= new MySQLFunction("IF(Balance > 0, NULL, NOW())");
		$updInvoiceStatus = new StatementUpdate("Invoice", "InvoiceRun = '$strInvoiceRun' AND Status IN (".INVOICE_PRINT.", ".INVOICE_TEMP.")", $arrUpdateData);
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
		
		// Activate all Inactive ServiceRatePlans and ServiceRateGroups for Services that were Invoiced this month
		$arrCols	= Array();
		$arrCols['Active']	= 1;
		$updServiceRatePlan		= new StatementUpdate(	"ServiceRatePlan",
														"Active = 0 AND StartDatetime < NOW()",
														$arrCols);
		$qryServiceRatePlan		= new Query();
		
		$this->_rptBillingReport->AddMessage("Activating Inactive ServiceRatePlans for Invoiced Accounts...", FALSE);
		if ($qryServiceRatePlan->Execute("UPDATE ServiceRatePlan SET Active = 1 WHERE Active = 0 AND StartDatetime < NOW()") !== FALSE)
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		else
		{
			// Report and fail out
			Debug($qryServiceRatePlan->Error());
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		
		$updServiceRateGroup	= new StatementUpdate(	"ServiceRateGroup",
														"Active = 0 AND StartDatetime < NOW()",
														$arrCols);
		$qryServiceRateGroup	= new Query();
		
		$this->_rptBillingReport->AddMessage("Activating Inactive ServiceRateGroups for Invoiced Accounts...", FALSE);
		if ($qryServiceRateGroup->Execute("UPDATE ServiceRateGroup SET Active = 1 WHERE Active = 0 AND StartDatetime < NOW()") !== FALSE)
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		else
		{
			// Report and fail out
			Debug($qryServiceRateGroup->Error());
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		
		// Update Account.Sample field
		$arrCols	= Array();
		$arrCols['Sample']	= new MySQLFunction("Sample + 1");
		$updSampleAccounts	= new StatementUpdate("Account", "Sample < 0", $arrCols);
		$updSampleAccounts->Execute($arrCols, Array());
		
		// Update ServiceRatePlan.LastChargedOn field
		$qryUpdateLastChargedOn	= new Query();
		$selBillServices		= new StatementSelect("ServiceTotal", "Service", "InvoiceRun = <InvoiceRun>");
		$selBillServices->Execute($arrInvoiceRun);
		while ($arrService = $selBillServices->Fetch())
		{
			// Update ServiceRatePlan
			$strQuery	= "UPDATE ServiceRatePlan SET LastChargedOn = CURDATE() WHERE Service = {$arrService['Service']} AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER By CreatedOn DESC LIMIT 1";
			if ($qryUpdateLastChargedOn->Execute($strQuery) === FALSE)
			{
				$this->_rptBillingReport->AddMessage(MSG_FAILED);
				Debug($qryUpdateLastChargedOn->Error());
			}
		}
		
		// Update Service.discount_start_datetime to NULL
		$qryUpdateServiceDiscountDate	= new Query();
		$strQuery						= "UPDATE Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service SET discount_start_datetime = NULL, cdr_count = NULL, cdr_amount = NULL WHERE InvoiceRun = '$strInvoiceRun'";
		if ($qryUpdateServiceDiscountDate->Execute($strQuery) === FALSE)
		{
			Debug($qryUpdateServiceDiscountDate->Error());
		}
		
		// Generate InvoiceRun table entry
		$this->_rptBillingReport->AddMessage("Generating Profit Data...", FALSE);
		$arrResponse = $this->CalculateProfitData($strInvoiceRun, TRUE);
		if ($arrResponse['Id'])
		{
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
		}
		
		// empty temporary invoice table
		$this->_rptBillingReport->AddMessage("Truncating Temp Invoice table...", FALSE);
		$qryTruncate = new Query();
		if (!$qryTruncate->Execute("DELETE FROM InvoiceTemp "))
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
	}
	
	//------------------------------------------------------------------------//
	// MoveCDRs
	//------------------------------------------------------------------------//
	/**
	 * MoveCDRs()
	 *
	 * Moves Invoiced and >190 day old CDRs from the CDR Table to CDRInvoiced 
	 *
	 * Moves Invoiced and >190 day old CDRs from the CDR Table to CDRInvoiced 
	 * 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function MoveCDRs()
 	{
		// Get today's date
		$strDate	= date("Y-m-d");
		
		// Move Invoiced CDRS to CDRInvoiced table
		$this->_rptBillingReport->AddMessage("Moving Invoiced CDRs to CDRInvoiced...\t", FALSE);
		$qryCopyInvoicedCDRs	= new Query();
		//if($qryCopyInvoicedCDRs->Execute("INSERT INTO CDRInvoiced (SELECT * FROM CDR WHERE (Status = 199 OR StartDatetime < SUBDATE('$strDate', INTERVAL 190 DAY)))") === FALSE)
		if($qryCopyInvoicedCDRs->Execute("INSERT INTO CDRInvoiced (SELECT * FROM CDR WHERE Status = 199)") === FALSE)
		{			
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
			return;
		}
		
		$qryDeleteInvoicedCDRs	= new Query();
		if($qryDeleteInvoicedCDRs->Execute("DELETE FROM CDR WHERE Status = 199") === FALSE)
		//if($qryDeleteInvoicedCDRs->Execute("DELETE FROM CDR WHERE (Status = 199 OR StartDatetime < SUBDATE('$strDate', INTERVAL 190 DAY)") === FALSE)
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
		
		if ($strInvoiceRun)
		{
			// Remove Billing-time modular charges
			foreach ($this->_arrBillingChargeModules as $arrCustomerGroups)
			{
				foreach ($arrCustomerGroups as $arrModuleTypes)
				{
					foreach ($arrModuleTypes as $chgModule)
					{
						// Revoke Billing Charge Modules
						$mixResult = $chgModule->RevokeAll($strInvoiceRun);
					}
				}
			}
			
			// Remove Plan Charge Adjustments
			$this->_rptBillingReport->AddMessage("Removing Plan Charge Adjustments...\t\t\t", FALSE);
			$qryRemovePlanCharges	= new Query();
			$strChargeType			= "ChargeType LIKE 'PCA%' OR ChargeType LIKE 'PCP%'";
			$strQuery				= "DELETE FROM Charge WHERE InvoiceRun = '$strInvoiceRun' AND ($strChargeType)";
			if ($qryRemovePlanCharges->Execute($strQuery) === FALSE)
			{
				Debug($qryRemovePlanCharges);
				$this->_rptBillingReport->AddMessage(MSG_FAILED);
			}
			else
			{
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
			/*$selInvoicePayments = new StatementSelect("InvoicePayment", "*", "InvoiceRun = '$strInvoiceRun'");
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
				$arrPayment['Id']		= $arrInvoicePayment['Payment'];
				$ubiPayments->Execute($arrPayment);
				
				// remove InvoicePayment
				$qryDeletePayment->Execute("DELETE FROM InvoicePayment WHERE Id = ".$arrInvoicePayment['Id']);
			}*/
		}
		
		// Truncate the InvoiceOutput table
		$this->_rptBillingReport->AddMessage("Truncating InvoiceOutput table...\t\t\t", FALSE);
		$qryTruncateInvoiceOutput = new QueryTruncate();
		if ($qryTruncateInvoiceOutput->Execute("InvoiceOutput") === FALSE)
		{
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
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
	// GenerateBillAudit()
	//------------------------------------------------------------------------//
	/**
	 * GenerateBillAudit()
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
 	function GenerateBillAudit()
 	{
		Debug("Init Bill Audit...");
		
		// Initiate and Execute Invoice Summary Statement
		$arrInvoiceColumns['TotalInvoices']			= "COUNT(InvoiceTemp.Id)";
		$arrInvoiceColumns['TotalInvoicedExGST']	= "SUM(InvoiceTemp.Total)";
		$arrInvoiceColumns['TotalInvoicedIncGST']	= "SUM(InvoiceTemp.Total) + SUM(InvoiceTemp.Tax)";
		$arrInvoiceColumns['InvoiceRun']			= "InvoiceTemp.InvoiceRun";
		$selInvoiceSummary	= new StatementSelect(	"InvoiceTemp",
													$arrInvoiceColumns,
													"1",
													NULL,
													1,
													"InvoiceTemp.InvoiceRun");

		$arrCDRTotalsColumns['TotalCDRCost']		= "SUM(CDR.Cost)";
		$arrCDRTotalsColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrCDRTotalsColumns['TotalCDRs']			= "COUNT(CDR.Id)";
		$selCDRSummary		= new StatementSelect(	"CDR",
													$arrCDRTotalsColumns,
													"CDR.Credit = 0 AND CDR.Status = ".CDR_TEMP_INVOICE,
													NULL,
													1,
													"CDR.InvoiceRun");
		if (($intCount = $selInvoiceSummary->Execute()) === FALSE || ($intCDRCount = $selCDRSummary->Execute()) === FALSE)
		{
			// Error out
			Debug("Error on selInvoiceSummary or selCDRSummary");
			Debug($selInvoiceSummary->Error());
			Debug($selCDRSummary->Error());
			return FALSE;
		}
		if (!$intCDRCount || !$intCount)
		{
			// No data, return ERROR_NO_INVOICE_DATA
			Debug("No data returned from selInvoiceSummary or selCDRSummary");
			return ERROR_NO_INVOICE_DATA;
		}
		$arrInvoiceSummary = $selInvoiceSummary->Fetch();
		$arrInvoiceSummary = array_merge($arrInvoiceSummary, $selCDRSummary->Fetch());
		$this->_strInvoiceRun = $arrInvoiceSummary['InvoiceRun'];

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
		if (($intCount = $selCarrierSummary->Execute()) === FALSE)
		{
			// Error out
			Debug("Error on selCarrierSummary");
			Debug($selCarrierSummary->Error());
			return FALSE;
		}
		if (!$intCount)
		{
			// No data, return ERROR_NO_INVOICE_DATA
			Debug("No data returned from selCarrierSummary");
			return ERROR_NO_INVOICE_DATA;
		}
		$arrCarrierSummarys = $selCarrierSummary->FetchAll();
		
		// Initiate and Execute ServiceType Summary Statement
		$arrServiceTypeColumns['ServiceType']			= "CDR.ServiceType";
		$arrServiceTypeColumns['TotalCost']				= "SUM(CDR.Cost)";
		$arrServiceTypeColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrServiceTypeColumns['TotalCharged']			= "SUM(ServiceTotal.TotalCharge)";
		$arrServiceTypeColumns['TotalCDRs']				= "COUNT(DISTINCT CDR.Id)";
		$selServiceTypeSummary = new StatementSelect(	"CDR, Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service",
														$arrServiceTypeColumns,
														"CDR.Credit = 0 AND CDR.Status = ".CDR_TEMP_INVOICE." AND ServiceTotal.InvoiceRun = '{$this->_strInvoiceRun}'",
														"CDR.ServiceType",
														NULL,
														"CDR.ServiceType");
		if (($intCount = $selServiceTypeSummary->Execute()) === FALSE)
		{
			// Error out
			return FALSE;
		}
		if (!$intCount)
		{
			// No data, return ERROR_NO_INVOICE_DATA
			Debug("No data returned from selServiceTypeSummary");
			return ERROR_NO_INVOICE_DATA;
		}
		$arrServiceTypeSummarys = $selServiceTypeSummary->FetchAll();
		
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
		
		Debug("Init complete!  Generating Audit Report...");
		
		
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
		
		// Add Footer and Send off the audit report
		$this->_rptAuditReport->AddMessage(MSG_HORIZONTAL_RULE);
		$this->_rptAuditReport->Finish();
	}
	

	//------------------------------------------------------------------------//
	// RevertInvoiceRun
	//------------------------------------------------------------------------//
	/**
	 * RevertInvoiceRun()
	 *
	 * Reverts a specified Invoice Run after it has been commited.
	 *
	 * Reverts a specified Invoice Run.  Returns all CDRs to Rated status, and removes
	 * all traces of invoicing
	 * 
	 * @param	string		$strInvoiceRun		The invoice run to revert 
	 *
	 * @return	bool
	 *
	 * @method
	 */
 	function RevertInvoiceRun($strInvoiceRun)
 	{
		// Report Title
		$this->_rptBillingReport->AddMessage("[ REVERTING INVOICE ]"."\n");
		
		if (!$strInvoiceRun)
		{
			$this->_rptBillingReport->AddMessage("No InvoiceRun specified!");
			return FALSE;
		}
		
		// change status of CDR_INVOICED status CDRs to CDR_RATED
		$this->_rptBillingReport->AddMessage(MSG_REVERT_CDRS, FALSE);
		$arrColumns = Array();
		$arrColumns['Status']		= CDR_RATED;
		$arrColumns['InvoiceRun']	= NULL;
		$updCDRStatus = new StatementUpdate("CDR", "CDR.Credit = 0 AND InvoiceRun = '$strInvoiceRun' AND Status = ".CDR_INVOICED, $arrColumns);
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
		$updChargeStatus = new StatementUpdate("Charge", "InvoiceRun = '$strInvoiceRun' AND Status = ".CHARGE_INVOICED, $arrUpdateData);
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
		
		// Update Service Capped and Uncapped Charges
		$this->_rptBillingReport->AddMessage("Updating Service Charge Totals"."\t", FALSE);
		$arrData = Array();
		$arrData['Service.UncappedCharge']	= new MySQLFunction("Service.UncappedCharge + ServiceTotal.UncappedCharge");
		$arrData['Service.CappedCharge']	= new MySQLFunction("Service.CappedCharge + ServiceTotal.CappedCharge");
		$updServiceCharges = new StatementUpdate(	"Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service",
													"ServiceTotal.InvoiceRun = '$strInvoiceRun'",
													$arrData);
		if($updServiceCharges->Execute($arrData, Array()) === FALSE)
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
		
		// Remove Billing-time modular charges
			foreach ($this->_arrBillingChargeModules as $arrCustomerGroups)
			{
				foreach ($arrCustomerGroups as $arrModuleTypes)
				{
					foreach ($arrModuleTypes as $chgModule)
					{
						// Revoke Billing Charge Modules
						$mixResult = $chgModule->RevokeAll($strInvoiceRun);
					}
				}
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
			$arrPayment['Id']		= $arrInvoicePayment['Payment'];
			$ubiPayments->Execute($arrPayment);
			
			// remove InvoicePayment
			$qryDeletePayment->Execute("DELETE FROM InvoicePayment WHERE Id = ".$arrInvoicePayment['Id']);
		}
		
		// TODO!rich! - Update each Account's LastBilled field (later - we don't use this at the moment)
		
		// remove invoices
		$this->_rptBillingReport->AddMessage("Removing Invoices\t\t\t", FALSE);
		$qryDeleteInvoices = new Query();
		if(!$qryDeleteInvoices->Execute("DELETE FROM Invoice WHERE InvoiceRun = '$strInvoiceRun'"))
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
		
		return TRUE;
 	}
	
	//------------------------------------------------------------------------//
	// ExecuteAccount
	//------------------------------------------------------------------------//
	/**
	 * ExecuteAccounts()
	 *
	 * Execute Invoices for specified accounts
	 *
	 * Execute Invoices for specified accounts
	 *
	 * @param	array	$arrAccounts		Indexed array of accounts to execute
	 *		 	 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function ExecuteAccounts($arrAccounts)
 	{		
		$strAccounts = implode(', ', $arrAccounts);
		
		// fail if there is a temp invoice for this account
		$selFindTempInvoice = new StatementSelect("InvoiceTemp", "Id", "Account IN ($strAccounts)");
		if ($selFindTempInvoice->Execute())
		{
			Debug("Temporary Invoice found!  Aborting...");
			return;
		}
		
		// generate an InvoiceRun Id
		$strInvoiceRun = uniqid();
		$this->_strInvoiceRun = $strInvoiceRun;
		
		// Select Account Details
		$selAccountDetails	= new StatementSelect("Account", "*", "Id IN ($strAccounts)");
		if (!$selAccountDetails->Execute())
		{
			Debug("Error retrieving account data for $intAccount... : ".$selAccountDetails->Error());
		}
		
		// FetchAll will automatically put it in an indexed array for us
		$arrAccountDetails = $selAccountDetails->FetchAll();
		
		// Generate the invoice
		$this->GenerateInvoices($arrAccountDetails);
		
		// Finish off Billing Report
		$arrReportLines['<Total>']	= $this->intPassed + $this->intFailed;
		$arrReportLines['<Time>']	= $this->Framework->SplitWatch();
		$arrReportLines['<Pass>']	= $this->intPassed;
		$arrReportLines['<Fail>']	= $this->intFailed;
		$this->_rptBillingReport->AddMessageVariables(MSG_BUILD_REPORT, $arrReportLines);
	}
	
	//------------------------------------------------------------------------//
	// RevokeAccount
	//------------------------------------------------------------------------//
	/**
	 * RevokeAccount()
	 *
	 * Revoke a temporary invoice for a specified account
	 *
	 * Revoke a temporary invoice for a specified account.
	 * Once invoices have been commited they can not be revoked.
	 *
	 * @param	int		$intAccount		The Account to Revoke an Invoice for 	 	 
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function RevokeAccount($intAccount)
 	{
		// Single-Serving revoke
		
		// Report Title
		$this->_rptBillingReport->AddMessage("[ REVOKING SINGLE INVOICE ]"."\n");
		
		// Get Account Details
		$selAccount	= new StatementSelect("Account", "*", "Id = <Account>");
		if (!$selAccount->Execute(Array('Id' => $intAccount)))
		{
			if ($selAccount->Error())
			{
				throw new Exception("DB ERROR: ".$selAccount->Error());
			}
			else
			{
				// Bad Account Number
				throw new Exception("Account Number '$intAccount' does not exist!");
			}
		}
		$arrAccount	= $selAccount->Fetch();
		
		// Get InvoiceRun of the current Temporary Invoice Run
		$selGetInvoiceRun = new StatementSelect("InvoiceTemp", "InvoiceRun", "1", NULL, "1");
		if ($selGetInvoiceRun->Execute() === FALSE)
		{

		}
		$arrInvoiceRun = $selGetInvoiceRun->Fetch();
		$strInvoiceRun = $arrInvoiceRun['InvoiceRun'];
		
		// change status of CDR_TEMP_INVOICE status CDRs to CDR_RATED
		$this->_rptBillingReport->AddMessage(MSG_REVERT_CDRS, FALSE);
		$arrColumns = Array();
		$arrColumns['Status']		= CDR_RATED;
		$arrColumns['InvoiceRun']	= NULL;
		$updCDRStatus = new StatementUpdate("CDR USE INDEX (Account_2)", "Account = $intAccount AND CDR.Credit = 0 AND Status = ".CDR_TEMP_INVOICE, $arrColumns);
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
		$updChargeStatus = new StatementUpdate("Charge", "Account = $intAccount AND Status = ".CHARGE_TEMP_INVOICE, $arrUpdateData);
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
		
		if ($strInvoiceRun)
		{
			// Remove Billing-time modular charges
			foreach ($this->_arrBillingChargeModules[$arrAccount['CustomerGroup']] as $arrModuleTypes)
			{
				foreach ($arrModuleTypes as $chgModule)
				{
					// Revoke Billing Charge Modules
					$mixResult	= $chgModule->Revoke($strInvoiceRun, $intAccount);
				}
			}
			
			// clean up ServiceTotal table
			$this->_rptBillingReport->AddMessage("Removing ServiceTotal entries...\t\t\t\t", FALSE);
			$qryCleanServiceTotal = new Query();
			if($qryCleanServiceTotal->Execute("DELETE FROM ServiceTotal WHERE Account = $intAccount AND InvoiceRun = '$strInvoiceRun'") === FALSE)
			{
				Debug($qryCleanServiceTotal);
				
				$this->_rptBillingReport->AddMessage(MSG_FAILED);
			}
			else
			{
				$this->_rptBillingReport->AddMessage(MSG_OK);
			}
	
			// clean up ServiceTypeTotal table
			$this->_rptBillingReport->AddMessage("Removing ServiceTypeTotal entries...\t\t\t", FALSE);
			$qryCleanServiceTotal = new Query();
			if($qryCleanServiceTotal->Execute("DELETE FROM ServiceTypeTotal WHERE Account = $intAccount AND InvoiceRun = '$strInvoiceRun'") === FALSE)
			{
				$this->_rptBillingReport->AddMessage(MSG_FAILED);
			}
			else
			{
				$this->_rptBillingReport->AddMessage(MSG_OK);
			}
			
			// reverse payments
			$selInvoicePayments = new StatementSelect("InvoicePayment", "*", "Account = $intAccount AND InvoiceRun = '$strInvoiceRun'");
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
				$arrPayment['Id']		= $arrInvoicePayment['Payment'];
				$ubiPayments->Execute($arrPayment);
				
				// remove InvoicePayment
				$qryDeletePayment->Execute("DELETE FROM InvoicePayment WHERE Id = ".$arrInvoicePayment['Id']);
			}
 		}
		
		// remove temporary invoice
		$this->_rptBillingReport->AddMessage("Revoking invoice for $intAccount...\t\t\t", FALSE);
		$qryDeleteTempInvoice = new Query();
		if(!$qryDeleteTempInvoice->Execute("DELETE FROM InvoiceTemp WHERE Account = $intAccount"))
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
		
		// Remove from the InvoiceOutput table
		$this->_rptBillingReport->AddMessage("Removing InvoiceOutput entry...\t\t\t", FALSE);
		$qryDeleteInvoiceOutput = new Query();
		if ($qryDeleteInvoiceOutput->Execute("DELETE FROM InvoiceOutput WHERE Account = $intAccount") === FALSE)
		{
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		return TRUE;
	
	}
	
	//------------------------------------------------------------------------//
	// GenerateInvoiceOutput
	//------------------------------------------------------------------------//
	/**
	 * GenerateInvoiceOutput()
	 *
	 * Generates Invoice Output
	 *
	 * Generates Invoice Output and prints a sample run
	 *
	 * @param	integer	$intPrintTartget	optional Id of the Module to print from
	 *
	 * @return			bool
	 *
	 * @method
	 */
	 function GenerateInvoiceOutput($intPrintTartget = BILL_PRINT)
	 {
		switch ($intPrintTartget)
		{
			case BILL_PRINT:
				// Truncate the InvoiceOutput table
				$this->_rptBillingReport->AddMessage("Truncating InvoiceOutput table...\t\t\t", FALSE);
				$qryTruncateInvoiceOutput = new QueryTruncate();
				if ($qryTruncateInvoiceOutput->Execute("InvoiceOutput") === FALSE)
				{
					$this->_rptBillingReport->AddMessage(MSG_FAILED);
				}
				else
				{
					$this->_rptBillingReport->AddMessage(MSG_OK);
				}
				
				$arrUpdateData = Array();
				$arrUpdateData['Id']		= NULL;
				$arrUpdateData['Status']	= INVOICE_PRINT;
				$ubiInvoiceStatus = new StatementUpdateById("InvoiceTemp", $arrUpdateData);
				
				// limit to 100 non zero accounts
				//$selInvoices = new StatementSelect("InvoiceTemp", "*", "Total > 3", NULL, 100);
				
				// full run
				$selInvoices = new StatementSelect("InvoiceTemp", "*", "1");
				
				if ($selInvoices->Execute() === FALSE)
				{
					// ERROR
					Debug($selInvoices->Error());
					return FALSE;
				}
				
				$arrInvoices = $selInvoices->FetchAll();
				
				// for each invoice
				$intPassed = 0;
				echo "Invoices to generate: ".count($arrInvoices)."\n\n";
				foreach($arrInvoices as $arrInvoice)
				{			
					echo "Generating output for {$arrInvoice['Id']}...\t\t\t";
					ob_flush();
					
					// stick stuff in invoice output
					$this->_arrBillOutput[$intPrintTartget]->AddInvoice($arrInvoice);
					
					// update Invoice Status to PRINT
					$arrUpdateData['Id'] = $arrInvoice['Id'];
					if($ubiInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
					{
						Debug("Update status to PRINT failed! : ".$updInvoiceStatus->Error());
						return;
					}
				
					$intPassed++;
					echo "[   OK   ]\n";
				}
				
				// Report how many passed
				$intFailed = count($arrInvoices) - $intPassed;
				Debug("$intPassed Invoice Outputs generated ($intFailed failed)");
				if ($intPassed == 0)
				{
					return FALSE;
				}
				
				// build an output file
				if (!$this->_arrBillOutput[$intPrintTartget]->BuildOutput(BILL_SAMPLE))
				{
					Debug("Building Output FAILED!");
					return FALSE;
				}
				
				// send billing output
				if (!$this->_arrBillOutput[$intPrintTartget]->SendOutput(BILL_SAMPLE))
				{
					Debug("Sending Output FAILED!");
					return FALSE;
				}
				
				// update Invoice Status to INVOICE_TEMP
				$arrUpdateData = Array();
				$arrUpdateData['Status'] = INVOICE_TEMP;
				$updInvoiceStatus = new StatementUpdate("InvoiceTemp", "Status = ".INVOICE_PRINT, $arrUpdateData);
				if($updInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
				{
					Debug("Update status to INVOICE_TEMP failed! : ".$updInvoiceStatus->Error());
					return FALSE;
				}
				
				return TRUE;
				break;
			
			case BILL_FLEX_XML:
				// Get all of the Temporary Invoices
				$selInvoices = new StatementSelect("InvoiceTemp", "*", "1");
				
				if ($selInvoices->Execute() === FALSE)
				{
					Debug($selInvoices->Error());
					return FALSE;
				}
				
				// Generate XML Data for each Invoice
				$strInvoiceRun	= NULL;
				while ($arrInvoice = $selInvoices->Fetch())
				{
					$strInvoiceRun	= $arrInvoice['InvoiceRun'];
					CliEcho(" + Generating XML for Account #{$arrInvoice['Account']}...\t\t\t", FALSE);
					if (!$this->_arrBillOutput[$intPrintTartget]->AddInvoice($arrInvoice))
					{
						CliEcho("[ FAILED ]\n\t-- Could not write XML to file");
					}
					else
					{
						CliEcho("[   OK   ]");
					}
				}
				
				// If this is a Gold Invoice Run, make a Symlink from /YYYYMMDDHHIISS-gold/ to /YYYYMMDDHHIISS/
				$strFullDirectory	= INVOICE_XML_PATH.$strInvoiceRun;
				if (!stripos($strInvoiceRun, '-'))
				{
					@mkdir($strFullDirectory, 0777, TRUE);
					@symlink($strFullDirectory, $strFullDirectory.'-gold');
				}
				
				// Copy XML files to Frontend Server
				CliEcho("\n * Copying XML files to bne-feprod-01...\t\t\t\t", FALSE);
				$strCopyFiles	= $strFullDirectory.'/*.xml';
				$strRemoteDir	= str_replace('working.', '.', $strFullDirectory);
				$strWarning		= '';
				$resFEPROD		= ssh2_connect('10.50.50.131');
				if ($resFEPROD)
				{
					// Log in
					if (ssh2_auth_password($resFEPROD, 'rdavis', 'password'))
					{
						// Init SFTP
						$resSFTP	= ssh2_sftp($resFEPROD);
						if ($resSFTP)
						{
							// Create Directory & Symlink
							if (ssh2_sftp_mkdir($resSFTP, $strRemoteDir, 0777, TRUE) || ssh2_sftp_stat($resSFTP, $strRemoteDir))
							{
								// Copy XML files
								$arrFiles	= glob($strCopyFiles);
								foreach ($arrFiles as $strPath)
								{
									if (is_file($strPath))
									{
										// This is a file, copy it
										if (!ssh2_scp_send($resFEPROD, $strPath, $strRemoteDir.'/'.basename($strPath), 0777))
										{
											CliEcho("\n -- Unable to send file '".basename($strPath)."'");
										}
									}
								}
								
								CliEcho("[   OK   ]");
								if (!stripos($strInvoiceRun, '-'))
								{
									// Gold Run, so create a symlink
									if (!ssh2_sftp_symlink($resSFTP, $strRemoteDir, $strRemoteDir.'-gold'))
									{
										// Warning
										CliEcho("\t -- WARNING: Unable to create remote symlink '{$strRemoteDir}-gold'");
									}
								}
							}
							else
							{
								// Error
								CliEcho("[ FAILED ]");
								CliEcho("\t -- Unable to create remote directory '$strRemoteDir'");
							}
						}
						else
						{
							// Error
							CliEcho("[ FAILED ]");
							CliEcho("\t -- Unable to init SFTP protocol");
						}
					}
					else
					{
						// Error
						CliEcho("[ FAILED ]");
						CliEcho("\t -- Unable to connect with provided credentials");
					}
				}
				else
				{
					// Error
					CliEcho("[ FAILED ]");
					CliEcho("\t -- Unable to connect to SSH2 server");
				}
				
				return TRUE;
				break;
				
			default:
				CliEcho("'$intPrintTartget' is not a registered Billing Output Module!");
		}
	 }
	
	//------------------------------------------------------------------------//
	// Reprint
	//------------------------------------------------------------------------//
	/**
	 * Reprint()
	 *
	 * Reprint Specified Invoices
	 *
	 * Reprint Specified Invoices
	 *
	 * @param	array	$arrInvoices		The Invoices to Reprint
	 * @param	integer	$intPrintTartget	optional Id of the Module to print from
	 *
	 * @return			bool
	 *
	 * @method
	 */
	 function Reprint($arrInvoices, $intPrintTartget = BILL_PRINT)
	 {
		// Truncate the InvoiceOutput table
		$this->_rptBillingReport->AddMessage("Truncating InvoiceOutput table...\t\t\t", FALSE);
		$qryTruncateInvoiceOutput = new QueryTruncate();
		if ($qryTruncateInvoiceOutput->Execute("InvoiceOutput") === FALSE)
		{
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
		}
		else
		{
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		$arrUpdateData = Array();
		$arrUpdateData['Id']		= NULL;
		$arrUpdateData['Status']	= INVOICE_PRINT;
		$ubiInvoiceStatus = new StatementUpdateById("Invoice", $arrUpdateData);
		
		// for each invoice
		$intPassed = 0;
		$arrInvoicesDetails = Array();
		foreach($arrInvoices as $intInvoice)
		{
			// Get Invoice details
			$arrData['Id'] 	= $intInvoice;
			if ($this->selGetInvoice->Execute($arrData) === FALSE)
			{
				Debug("Invoice Retrieval FAILED! : ".$this->selGetInvoice->Error());
			}
			if (($arrInvoiceData = $this->selGetInvoice->Fetch()) === FALSE)
			{
				Debug("Invoice #$intAccount not found!");
				continue;
			}
			
			// Keep the old statuses
			$arrInvoiceStatuses[$intInvoice] = $arrInvoiceData['Status'];
			
			// stick stuff in invoice output
			$this->_arrBillOutput[$intPrintTartget]->AddInvoice($arrInvoiceData);
			
			// update Invoice Status to PRINT
			$arrUpdateData['Id'] = $arrData['Id'];
			if($ubiInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
			{
				Debug("Update status to PRINT failed! : ".$updInvoiceStatus->Error());
				return;
			}
		
			$intPassed++;
		}
		
		// Report how many passed
		$intFailed = count($arrInvoices) - $intPassed;
		Debug("$intPassed Invoice Outputs generated ($intFailed failed)");
		if ($intPassed == 0)
		{
			return FALSE;
		}
		
		// build an output file
		if (!$this->_arrBillOutput[$intPrintTartget]->BuildOutput(BILL_REPRINT))
		{
			Debug("Building Output FAILED!");
			return FALSE;
		}
		
		// send billing output
		if (!$this->_arrBillOutput[$intPrintTartget]->SendOutput(BILL_REPRINT))
		{
			Debug("Sending Output FAILED!");
			return FALSE;
		}
		
		// Revert to old Invoice Statuses
		foreach ($arrInvoiceStatuses as $intInvoice=>$intStatus)
		{
			$arrUpdateData['Id']		= $intInvoice;
			$arrUpdateData['Status']	= $intStatus;
			if($ubiInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
			{
				Debug("Reverting Invoice Status failed! (Invoice # $intInvoice) : ".$updInvoiceStatus->Error());
				return FALSE;
			}
		}
		
		return TRUE;
	 }
	
	//------------------------------------------------------------------------//
	// PrintSampleAccounts
	//------------------------------------------------------------------------//
	/**
	 * PrintSampleAccounts()
	 *
	 * Prints sample invoices for a list of accounts
	 *
	 * Prints sample invoices for a list of accounts
	 *
	 * @param	array	$arrAccounts		Indexed array of valid account numbers
	 * 										which have invoices in the InvoiceTemp table
	 * 
	 * @param	integer	$intPrintTartget	optional Id of the Module to print from
	 *
	 * @return			bool
	 *
	 * @method
	 */
	 function PrintSampleAccounts($arrAccounts, $intPrintTartget = BILL_PRINT)
	 {
		if (!is_array($arrAccounts))
		{
			echo "\$arrAccounts is not an array!\n\n";
			return FALSE;
		}
		
		echo " * Generating output for ".count($arrAccounts)." invoices...\t\t";
		
		// build an output file
		if (!$this->_arrBillOutput[$intPrintTartget]->BuildOutput(BILL_REPRINT_TEMP, $arrAccounts))
		{
			echo "[ FAILED ]\n\n";
			return FALSE;
		}
		else
		{
			echo "[   OK   ]\n";
		}
		
		echo " * Sending output for ".count($arrAccounts)." invoices...\t\t";
		
		// send billing output
		if (!$this->_arrBillOutput[$intPrintTartget]->SendOutput(BILL_REPRINT_TEMP))
		{
			echo "[ FAILED ]\n\n";
			return FALSE;
		}
		else
		{
			echo "[   OK   ]\n\n";
		}
		
		return TRUE;
	 }
	 
 	
  	//------------------------------------------------------------------------//
	// EmailInvoicePDFs()
	//------------------------------------------------------------------------//
	/**
	 * EmailInvoicePDFs()
	 *
	 * Sends invoices in emails from the specified directory
	 *
	 * Sends invoices in emails from the specified directory
	 *
	 * @return		string		$strPath			Full path for directory to send invoices from
	 *
	 * @method
	 */
 	function EmailInvoicePDFs($strPath)
 	{
 		$this->_rptBillingReport->AddMessage("[ EMAILING INVOICE PDFS ]\n");
 		
 		// Make sure our path is a string
 		if (!is_string($strPath))
 		{
 			$this->_rptBillingReport->AddMessage('$strPath is not a string!');
 			return FALSE;
 		}
 		
 		// Get $strBillingPeriod & InvoiceRun
 		$strBillingPeriod 	= date("F Y", strtotime("-1 month", time()));
 		$selInvoiceRun		= new StatementSelect("InvoiceRun", "*", "1", "BillingDate DESC", 1);
 		$selInvoiceRun->Execute();
 		$arrInvoiceRun		= $selInvoiceRun->Fetch();
 		
 		// add trailing slash if not already there
 		if (substr($strPath, 0, -1) != '/')
 		{
 			$strPath .= "/";
 		}
 		
 		// Get all PDF paths
 		$arrPDFPaths = glob($strPath."*.pdf");
 		
		
 		/*$selAccountEmail	= new StatementSelect(	"Account JOIN Contact ON Account.Id = Contact.Account",
 													"Account.Id AS Account, CustomerGroup, Email, FirstName",
 													"Account = <Account> AND Email != '' AND BillingMethod = ".BILLING_METHOD_EMAIL);*/
 		$selAccountEmail	= new StatementSelect(	"(Invoice JOIN Account ON Invoice.Account = Account.Id) JOIN Contact USING (Account)",
 													"Invoice.Account, CustomerGroup, Email, FirstName",
 													"Invoice.Account = <Account> AND Email != '' AND DeliveryMethod = 1 AND InvoiceRun = <InvoiceRun>");
		$updDeliveryMethod	= new StatementUpdate("Invoice", "InvoiceRun = <InvoiceRun> AND Account = <Account>", Array('DeliveryMethod' => NULL));
		
		$selCustomerGroup	= new StatementSelect("CustomerGroup", "*", "1");
		if ($selCustomerGroup->Execute() === FALSE)
		{
			// DB Error
			Debug($selCustomerGroup->Error());
			return FALSE;
		}
		$arrCustomerGroups	= Array();
		while ($arrCustomerGroup = $selCustomerGroup->Fetch())
		{
			$arrCustomerGroups[$arrCustomerGroup['Id']]	= $arrCustomerGroup;
		}
		
		
		
 		// Loop through each PDF
 		$intPassed	= 0;
 		$intIgnored	= 0;
 		foreach ($arrPDFPaths as $strPDFPath) 
 		{
 			// Get the account number from the filename, then find the account's email address
 			$arrSplit = explode('.', basename($strPDFPath));
 			
 			if ($selAccountEmail->Execute(Array('Account' => $arrSplit[0], 'InvoiceRun' => $arrInvoiceRun['InvoiceRun'])) === FALSE)
 			{
 				Debug($selAccountEmail->Error());
 				return FALSE;
 			}
 			if (!$arrDetails = $selAccountEmail->FetchAll())
 			{
 				// Bad Account Number or Non-Email Account
 				continue;
 				$intIgnored++;
 			}
 			
	 		$this->_rptBillingReport->AddMessage("\n\t+ Emailing Invoice(s) for Account #".$arrSplit[0]."...");
 			
 			// for each email-able contact
 			foreach ($arrDetails as $arrDetail)
 			{
	 			// Set email details based on Customer Group
	 			$arrHeaders = Array	(
	 									'From'		=> $arrCustomerGroups[$arrDetail['CustomerGroup']]['OutboundEmail'],
	 									'Subject'	=> "{$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']} Telephone Billing for $strBillingPeriod"
	 								);
				$strContent	=	"Please find attached your most recent Invoice from {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']}\n\n" .
								"Regards\n\n" .
								"The Team at {$arrCustomerGroups[$arrDetail['CustomerGroup']]['ExternalName']}";	 			
		 		
		 		// Does the customer have a first name?
		 		if (trim($arrDetail['FirstName']))
		 		{
		 			$strContent = "Dear ".$arrDetail['FirstName']."\n\n" . $strContent;
		 		}
		 		
	 			// Account for , separated email addresses
	 			$arrEmails = explode(',', $arrDetail['Email']);
	 			foreach ($arrEmails as $strEmail)
	 			{
		 			$strEmail = trim($strEmail);
		 			
		 			$this->_rptBillingReport->AddMessage(str_pad("\t\tAddress: '$strEmail'...", 70, " ", STR_PAD_RIGHT), FALSE);
		 			
		 			// Validate email address
		 			if (!preg_match('/^([[:alnum:]]([+-_.]?[[:alnum:]])*)@([[:alnum:]]([.]?[-[:alnum:]])*[[:alnum:]])\.([[:alpha:]]){2,25}$/', $strEmail))
		 			{
		 				$this->_rptBillingReport->AddMessage("[ FAILED ]\n\t\t\t-Reason: Email address is invalid");
		 				continue;
		 			}
		 			
		 			$mimMime = new Mail_mime("\n");
		 			$mimMime->setTXTBody($strContent);
		 			$mimMime->addAttachment(file_get_contents($strPDFPath), 'application/pdf', $arrSplit[0].'_'.str_replace(' ', '_', $strBillingPeriod).".pdf", FALSE);
					$strBody = $mimMime->get();
					$strHeaders = $mimMime->headers($arrHeaders);
		 			$emlMail =& Mail::factory('mail');
		 			
		 			// Uncomment this to Debug
		 			//$strEmail = 'rich@voiptelsystems.com.au';
		 			//$strEmail = 'turdminator@hotmail.com';
		 			
		 			// Send the email
		 			if (!$emlMail->send($strEmail, $strHeaders, $strBody))
		 			{
		 				$this->_rptBillingReport->AddMessage("[ FAILED ]\n\t\t\t-Reason: Mail send failed");
		 				continue;
		 			}
		 			//die;
					
					// Update DeliveryMethod
					$arrUpdateData	= Array();
					$arrWhere		= Array();
					$arrUpdateData['DeliveryMethod']	= BILLING_METHOD_EMAIL_SENT;
					$arrWhere['InvoiceRun']	= $arrInvoiceRun['InvoiceRun'];
					$arrWhere['Account']	= $arrDetail['Account'];
					if ($updDeliveryMethod->Execute($arrUpdateData, $arrWhere))
					{
						//Debug("Success!");
					}
					else
					{
						//Debug("Failure!");
					}
					//Debug($arrWhere);
					//die;*/
	 				
	 				$this->_rptBillingReport->AddMessage("[   OK   ]");
	 				$intPassed++;
	 				
	 				// Uncomment this to Debug
					//die;
	 			}
 			}
 		}
 		
 		$this->_rptBillingReport->AddMessage("\n\t* Found ".(int)count($arrPDFPaths)." PDFs ($intIgnored ignored), $intPassed emails sent.\n");
 	}
	 
 	
  	//------------------------------------------------------------------------//
	// PayNegativeBalances()
	//------------------------------------------------------------------------//
	/**
	 * PayNegativeBalances()
	 *
	 * "Pays off" invoices with negative Balances
	 *
	 * "Pays off" invoices with negative Balances
	 *
	 * @method
	 */
 	function PayNegativeBalances()
 	{
 		ob_start();
 		
 		$arrInvoiceColumns = Array();
 		$arrInvoiceColumns['Balance'] = NULL;
 		$selNegativeInvoices	= new StatementSelect("Invoice", "Id, Account, AccountGroup, Balance", "Status IN (101, 103) AND Balance < 0 AND CURDATE() >= ADDDATE(CreatedOn, INTERVAL 3 DAY)", "CreatedOn ASC");
 		$selPositiveInvoices	= new StatementSelect("Invoice", "Id, Balance, Status, InvoiceRun", "Status IN (101) AND Balance > 0 AND Account = <Account> AND CURDATE() >= ADDDATE(CreatedOn, INTERVAL 3 DAY)", "CreatedOn ASC");
 		$ubiInvoice				= new StatementUpdateById("Invoice", $arrInvoiceColumns);
 		$insInvoicePayment		= new StatementInsert("InvoicePayment");
 		
 		// Get all invoices with negative Balances and are more than 3 days old
 		$intCount = $selNegativeInvoices->Execute();
 		if ($intCount === FALSE)
 		{
 			Debug($selNegativeInvoices->Error());
 			return FALSE;
 		}
 		$arrNegativeInvoices = $selNegativeInvoices->FetchAll();
 		
 		echo " * Found $intCount Invoices with a negative Balance\n\n";
		
 		// For each of the -ve Invoices
 		$intZeroed = 0;
 		foreach ($arrNegativeInvoices as $arrNegativeInvoice)
 		{
			echo " * Paying Account {$arrNegativeInvoice['Account']} with Invoice #{$arrNegativeInvoice['Id']}...\t\t\t";
			
			// Get the abs balance for this invoice
			$fltNegativeBalance = abs((float)$arrNegativeInvoice['Balance']);
			
			// Get all of this Account's Outstanding Invoices
			if (($intCount = $selPositiveInvoices->Execute(Array('Account' => $arrNegativeInvoice['Account']))) === FALSE)
			{
				Debug($selPositiveInvoices->Error());
				return FALSE;
			}
			
			if (!$intCount)
			{
				echo "[  SKIP  ]\n";
				continue;
			}
			echo "\n\t * Paying off $intCount Invoices...\t\t\n";
			//echo "\t * Opening Balance is \$$fltNegativeBalance\t\t\tOutstanding\tPayment\t\tBalance\t\tCredits Remaining\n";
			
			// For each of the +ve Invoices
			$fltTotalPaid			= 0;
			$fltTotalOutstanding	= 0;
			$fltTotalRemaining		= 0;
			while ($arrPositiveInvoice = $selPositiveInvoices->Fetch())
			{
				if ($fltNegativeBalance == 0 || $fltNegativeBalance == -0)
				{
					//echo "\t - Insufficient Funds for Invoice #{$arrPositiveInvoice['Id']}\t\${$arrPositiveInvoice['Balance']}\t\$0\t\t\${$arrPositiveInvoice['Balance']}\t\$0\n";
					continue;
				}
				
				//echo "\t - Paying off Invoice #{$arrPositiveInvoice['Id']}...\t\t";
				
				// Pay this Invoice off
				$fltPositiveBalance		= $arrPositiveInvoice['Balance'];
				$fltTotalOutstanding	+= $fltPositiveBalance;
				$fltPositiveBalanceNew	= max(0, $fltPositiveBalance - $fltNegativeBalance);
				$fltTotalRemaining		+= $fltPositiveBalanceNew;
				$fltPayment				= $fltPositiveBalance - $fltPositiveBalanceNew;
				$fltTotalPaid			+= $fltPayment;
				$fltNegativeBalance		= RoundCurrency($fltNegativeBalance - $fltPayment);
				
				//echo "\$$fltPositiveBalance\t\$$fltPayment\t\t\$$fltPositiveBalanceNew\t\t\$$fltNegativeBalance\n";
				
				// Add Credit Payment to InvoicePayment
				$arrInvoicePayment = Array();
				$arrInvoicePayment['InvoiceRun']	= $arrPositiveInvoice['InvoiceRun'];
				$arrInvoicePayment['Account']		= $arrNegativeInvoice['Account'];
				$arrInvoicePayment['AccountGroup']	= $arrNegativeInvoice['AccountGroup'];
				$arrInvoicePayment['Payment']		= $arrNegativeInvoice['Id'];
				$arrInvoicePayment['Amount']		= $fltPayment;
				$insInvoicePayment->Execute($arrInvoicePayment);
				
				// Update the +ve Invoice
				$arrInvoiceColumns['Id']		= $arrPositiveInvoice['Id'];
				$arrInvoiceColumns['Balance']	= $fltPositiveBalanceNew;
				$ubiInvoice->Execute($arrInvoiceColumns);
				
				
				// Update the -ve Invoice
				$arrInvoiceColumns['Id']		= $arrNegativeInvoice['Id'];
				$arrInvoiceColumns['Balance']	= 0 - $fltNegativeBalance;
				$ubiInvoice->Execute($arrInvoiceColumns);
				
				ob_flush();
			}
			
			if ($fltTotalPaid)
			{
				//echo "\t\t\t\t\tTotals:\t\t\$$fltTotalOutstanding\t\t\$$fltTotalPaid\n";
			}
			echo "\t * Closing Balance is \$$fltNegativeBalance\n";
			
			if (!$fltNegativeBalance)
			{
				$intZeroed++;
			}
			
			ob_flush();
 		}
 		
 		$intTotal = count($arrNegativeInvoices);
 		echo "\n * $intZeroed of $intTotal Negative Invoices completely paid off";
 	}
 		 
 	
  	//------------------------------------------------------------------------//
	// GenerateProfitReport()
	//------------------------------------------------------------------------//
	/**
	 * GenerateProfitReport()
	 *
	 * Generates a Profit Report in XLS format
	 *
	 * Generates a Profit Report in XLS format
	 * 
	 * @param	string	$strFileName		Path to write the XLS document to
	 * @param	string	$strInvoiceRun		optional InvoiceRun of the committed invoice.  If blank, then uses InvoiceTemp
	 *
	 * @method
	 */
	 function GenerateProfitReport($strFileName, $strInvoiceRun = NULL)
	 {
	 	// Get CLI Interface & Console window
	 	$itfInterface	= new CLIInterface("viXen Billing Application v7.05");
	 	$itfInterface->InitConsole("Generating Profit Report", TRUE);
	 	
	 	// InvoiceRun details
	 	if ($strInvoiceRun)
	 	{
	 		// Committed Invoice
	 		$itfInterface->ConsoleAddLine("Getting Details for InvoiceRun '$strInvoiceRun'...");
	 		$selInvoices = new StatementSelect("Invoice", "*", "InvoiceRun = '$strInvoiceRun'", NULL, NULL); //FIXME (optimise)
	 	}
	 	else
	 	{
	 		// Temporary Invoice
	 		$itfInterface->ConsoleAddLine("Getting Details for Temporary Invoice Run...");
	 		$selInvoices = new StatementSelect("InvoiceTemp", "*", NULL, NULL, NULL); //FIXME (optimise)
	 	}
	 	
	 	// Select all Invoices from selected invoice run
	 	$intCount = $selInvoices->Execute();
	 	$arrInvoices = $selInvoices->FetchAll();
	 	
	 	// Set InvoiceRun
	 	$strInvoiceRun = $arrInvoices[0]['InvoiceRun'];
	 	
	 	// Statements
	 	$strNLDTypes = "2, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 18, 19, 20, 27, 28, 33, 35, 36, 38";
	 	$selAccountDetails = new StatementSelect("Account", "BusinessName, CustomerGroup", "Id = <Account>");
	 	$arrCDRColumns = Array();
	 	$arrCDRColumns['CostNLD']		=	"SUM( CASE\n" .
	 										"WHEN RecordType IN ($strNLDTypes) THEN Cost\n" .
	 										"ELSE 0\n" .
	 										"END )";
	 	$arrCDRColumns['ChargeNLD']		=	"SUM( CASE\n" .
	 										"WHEN RecordType IN ($strNLDTypes) THEN Charge\n" .
	 										"ELSE 0\n" .
	 										"END )";
	 	$arrCDRColumns['BillCost']		=	"SUM(Cost)";
	 	$arrCDRColumns['BillCharge']	=	"SUM(Charge)";
	 	$selCDRTotals = new StatementSelect("CDR", $arrCDRColumns, "InvoiceRun = '$strInvoiceRun' AND Account = <Account>");
	 	
	 	// Init XLS file
	 	$itfInterface->ConsoleAddLine("Initiating Excel Document '$strFileName'...");
	 	$strPeriod = date("M y", strtotime("-1 month", strtotime($arrInvoices[0]['DueOn'])));
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strFileName);
		$wksProfitReport =& $wkbWorkbook->addWorksheet("$strPeriod Profit Report");
		
		// Init XLS Formats
		$fmtTitle		= $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		
		$fmtTotal		= $wkbWorkbook->addFormat();
		$fmtTotal->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$fmtTotal->setBold();
		
		$fmtCurrency	= $wkbWorkbook->addFormat();
		$fmtCurrency->setNumFormat('$#,##0.00;$#,##0.00 CR');
		
		$fmtPercentage	= $wkbWorkbook->addFormat();
		$fmtPercentage->setNumFormat('0.00%;-0.00%');
		
		$fmtPCTotal		= $wkbWorkbook->addFormat();
		$fmtPCTotal->setNumFormat('0.00%;-0.00%');
		$fmtPCTotal->setBold();
		
		// Add XLS Title Row
		$wksProfitReport->writeString(0, 0, "Account No."	, $fmtTitle);
		$wksProfitReport->writeString(0, 1, "Customer Group", $fmtTitle);
		$wksProfitReport->writeString(0, 2, "Customer Name"	, $fmtTitle);
		$wksProfitReport->writeString(0, 3, "Cost NLD"		, $fmtTitle);
		$wksProfitReport->writeString(0, 4, "Charge NLD"	, $fmtTitle);
		$wksProfitReport->writeString(0, 5, "Bill Cost"		, $fmtTitle);
		$wksProfitReport->writeString(0, 6, "Bill Charge"	, $fmtTitle);
		$wksProfitReport->writeString(0, 7, "Margin"		, $fmtTitle);
	 	
	 	
	 	$itfInterface->ConsoleAddLine("\n[ ACCOUNT BREAKDOWN ]\n");
	 	// foreach Invoice
	 	$intRow = 1;
	 	$intAccountsDone = 0;
	 	foreach ($arrInvoices as $arrInvoice)
	 	{
	 		// Calculate percentage done
	 		$fltPercentDone = ($intAccountsDone / count($arrInvoices)) * 100;
	 		$strConsoleText = "\t+ Account No {$arrInvoice['Account']}\t:";
	 	
	 		// Get Customer Details
	 		$itfInterface->ConsoleAddLine("$strConsoleText Getting Customer Details...", $fltPercentDone);
	 		$selAccountDetails->Execute(Array('Account' => $arrInvoice['Account']));
	 		$arrDetails = $selAccountDetails->Fetch();
	 		
	 		// Get CDR Total Charge and Cost
	 		$itfInterface->ConsoleRedrawLine("$strConsoleText Getting CDR Totals...");
	 		$selCDRTotals->Execute(Array('Account' => $arrInvoice['Account']));
	 		$arrCDRTotals = $selCDRTotals->Fetch();
	 		
	 		// Calculate Margin
	 		$fltCharge	= (float)$arrInvoice['Total'];
	 		$fltCost	= (float)$arrCDRTotals['BillCost'];
	 		if ($fltCharge == 0 && $fltCost == 0)
	 		{
	 			$fltMargin = 0.0;
	 		}
	 		else
	 		{
	 			$fltMargin	= round((($fltCharge - $fltCost) / $fltCharge), 4);
	 		}
	 		
	 		// Output to XLS file
	 		$itfInterface->ConsoleRedrawLine("$strConsoleText Writing to XLS Document...");
			$wksProfitReport->writeNumber($intRow, 0, $arrInvoice['Account']);
			$wksProfitReport->writeString($intRow, 1, GetConstantDescription($arrDetails['CustomerGroup'], 'CustomerGroup'));
			$wksProfitReport->writeString($intRow, 2, $arrDetails['BusinessName']);
			$wksProfitReport->writeNumber($intRow, 3, $arrCDRTotals['CostNLD']				, $fmtCurrency);
			$wksProfitReport->writeNumber($intRow, 4, $arrCDRTotals['ChargeNLD']			, $fmtCurrency);
			$wksProfitReport->writeNumber($intRow, 5, $arrCDRTotals['BillCost']				, $fmtCurrency);
			$wksProfitReport->writeNumber($intRow, 6, $arrInvoice['Total']					, $fmtCurrency);
			//$wksProfitReport->writeNumber($intRow, 7, $fltMargin							, $fmtPercentage);
			$wksProfitReport->writeFormula($intRow, 7, "=(G".($intRow+1)." - F".($intRow+1).") / ABS(G".($intRow+1).")"	, $fmtPercentage);
			
			// DONE
	 		$itfInterface->ConsoleRedrawLine("$strConsoleText [   OK   ]");
	 		$intAccountsDone++;
	 		$intRow++;
	 	}
	 	
	 	// Add in Gross Totals
	 	$itfInterface->ConsoleAddLine("\nAdding Gross Totals to XLS...");
	 	$wksProfitReport->writeString($intRow + 1, 2, "Gross Totals: ", $fmtTitle);
	 	$wksProfitReport->writeFormula($intRow + 1, 3, "=SUM(D2:D".($intRow).")", $fmtTotal);
	 	$wksProfitReport->writeFormula($intRow + 1, 4, "=SUM(E2:E".($intRow).")", $fmtTotal);
	 	$wksProfitReport->writeFormula($intRow + 1, 5, "=SUM(F2:F".($intRow).")", $fmtTotal);
	 	$wksProfitReport->writeFormula($intRow + 1, 6, "=SUM(G2:G".($intRow).")", $fmtTotal);
	 	$wksProfitReport->writeFormula($intRow + 1, 7, "=(G".($intRow + 2)." - F".($intRow + 2).") / ABS(G".($intRow + 2).")", $fmtPCTotal);
	 	
	 	// Close XLS File
	 	$itfInterface->ConsoleAddLine("Saving XLS Document...");
		$wkbWorkbook->close();
	 	
	 	$strDate = date("Ymd_His");
	 	$strAutoName = FILES_BASE_PATH."log/billing/profit/$strDate.log";
	 	$arrItems = Array();
	 	$arrItems[TRUE]		["Name"] = "Save to '$strAutoName'";
	 	$arrItems[FALSE]	["Name"] = "Do not save log";
	 	$mixResults = $itfInterface->DrawMenu($arrItems, "Save Log to file?");
	 	switch ($mixResults)
	 	{
	 		case TRUE:
	 			// Write to default path
	 			return file_put_contents($strAutoName, implode("\n", $itfInterface->ConsoleGetContents()));
	 			break;
	 			
	 		case FALSE;
	 			return TRUE;
	 			break;
	 	}
	 }
	 
	 
	 
  	//------------------------------------------------------------------------//
	// CalculateProfitData()
	//------------------------------------------------------------------------//
	/**
	 * CalculateProfitData()
	 *
	 * Calculates Profit Data for an Invoice Run
	 *
	 * Calculates Profit Data for an Invoice Run.  
	 * 
	 * @param	string	$strInvoiceRun	optional	InvoiceRun of the committed invoice.  If blank, then uses InvoiceTemp
	 * @param	boolean	$bolInsert		optional	TRUE: Add to InvoiceRun table; FALSE: Don't Insert
	 * 												NOTE: Will not insert at all if $strInvoiceRun is NULL (Temp Invoices)
	 * 
	 * @return	array								DB Array of InvoiceRun table insert data
	 *
	 * @method
	 */
	 function CalculateProfitData($strInvoiceRun = NULL, $bolInsert = FALSE)
	 {
		$selCDRTotals		= new StatementSelect("CDR", "SUM(Cost) AS BillCost, SUM(Charge) AS BillRated", "InvoiceRun = <InvoiceRun>");
		$selChargeTotals	= new StatementSelect("Charge", "SUM(CASE WHEN Nature = 'CR' THEN (0 - Amount) ELSE Amount END) AS Total", "InvoiceRun = <InvoiceRun>");
		$insInvoiceRun		= new StatementInsert("InvoiceRun");
		
		// Get InvoiceRun data
		if ($strInvoiceRun)
		{
			// Committed Invoice
			$strTable		= "Invoice";
			$selInvoiceData	= new StatementSelect("Invoice", "InvoiceRun, CreatedOn AS BillingDate, SUM(Total) AS BillInvoiced, SUM(Tax) AS BillTax, COUNT(Id) AS InvoiceCount", "InvoiceRun = <InvoiceRun>", "CreatedOn", NULL, "InvoiceRun");
		}
		else
		{
			// Temp Invoice
			$strTable		= "InvoiceTemp";
			$selInvoiceData	= new StatementSelect("InvoiceTemp", "InvoiceRun, CreatedOn AS BillingDate, SUM(Total) AS BillInvoiced, SUM(Tax) AS BillTax, COUNT(Id) AS InvoiceCount", "1", "CreatedOn", NULL, "InvoiceRun");
		}
		if (!$selInvoiceData->Execute(Array('InvoiceRun' => $strInvoiceRun)))
		{
			return FALSE;
		}
		$arrInvoiceRun = $selInvoiceData->Fetch();
		
		// Get additional Details
		$selCDRTotals->Execute($arrInvoiceRun);
		$arrInvoiceRun = array_merge($arrInvoiceRun, $selCDRTotals->Fetch());
		$selChargeTotals->Execute($arrInvoiceRun);
		$arrChargeTotals = $selChargeTotals->Fetch();
		$arrInvoiceRun['BillRated'] += $arrChargeTotals['Total'];
		
		// Handle Etech Invoices
		if (!$arrInvoiceRun['BillCost'])
		{
			$arrInvoiceRun['BillCost']		= 0;
			$arrInvoiceRun['BillRated']		= 0;
		}
		
		// Date hack for invoice run on the 30th
		$strCreatedOn = $arrInvoiceRun['BillingDate'];
		$arrDate = explode('-', $arrInvoiceRun['BillingDate']);
		if ((int)$arrDate[2] >= 28)
		{
			$arrInvoiceRun['BillingDate'] = date("Y-m-d", strtotime("+1 month", strtotime("{$arrDate[0]}-$arrDate[1]-01")));
		}
		
		// Outstanding Totals
		$selThisOutstanding		= new StatementSelect($strTable, "SUM(Balance) AS Balance", "SettledOn IS NULL AND InvoiceRun = <InvoiceRun>");
		$selLastOutstanding		= new StatementSelect("Invoice", "SUM(Balance) AS Balance", "SettledOn IS NULL AND CreatedOn < <CreatedOn>", "CreatedOn DESC", 1, "InvoiceRun");
		$selTotalOutstanding	= new StatementSelect("Invoice", "SUM(Balance) AS Balance", "SettledOn IS NULL AND CreatedOn < <CreatedOn>");
		$selThisOutstanding->Execute($arrInvoiceRun);
		$selLastOutstanding->Execute(Array('CreatedOn' => $strCreatedOn));
		$selTotalOutstanding->Execute(Array('CreatedOn' => $strCreatedOn));
		$arrThisOutstanding		= $selThisOutstanding->Fetch();
		$arrLastOutstanding		= $selLastOutstanding->Fetch();
		$arrTotalOutstanding	= $selTotalOutstanding->Fetch();
		$arrBalanceData['TotalBalance']		= $arrThisOutstanding['Balance'];
		$arrBalanceData['TotalOutstanding']	= $arrTotalOutstanding['Balance'];
		$arrBalanceData['PreviousBalance']	= $arrLastOutstanding['Balance'];
		
		$arrInvoiceRun['BalanceData'] = serialize($arrBalanceData);
		
		// Insert data to DB if flag is set & using committed invoices
		if ($bolInsert && $strInvoiceRun)
		{
			// Try to delete an older version of this entry
			$qryDelete = new Query();
			$qryDelete->Execute("DELETE FROM InvoiceRun WHERE InvoiceRun = '$strInvoiceRun'");
			
			// Insert new data
			$arrInvoiceRun['Id'] = $insInvoiceRun->Execute($arrInvoiceRun);
		}
		
		$arrInvoiceRun['GrossProfit']	= $arrInvoiceRun['BillInvoiced'] - $arrInvoiceRun['BillCost'];
		$arrInvoiceRun['ProfitMargin']	= round((($arrInvoiceRun['BillInvoiced'] - $arrInvoiceRun['BillCost']) / abs($arrInvoiceRun['BillInvoiced'])) * 100, 2)."%";
		return $arrInvoiceRun;
	 }
	
	
	//------------------------------------------------------------------------//
	// RegenerateAccounts
	//------------------------------------------------------------------------//
	/**
	 * RegenerateAccounts()
	 *
	 * Regenerates Invoice Data for a set of Accounts
	 *
	 * Regenerates Invoice Data for a set of Accounts
	 *
	 * @param	array	$arrAccounts				Indexed array of account id's to execute
	 * @param	boolean	$bolReprint		[optional]	Reprint Invoice Output Data (default = FALSE)
	 *
	 * @return	integer	$intInvoices				Number of Invoices generated
	 *
	 * @method
	 */
 	function RegenerateAccounts($arrAccounts, $bolReprint = FALSE)
 	{		
		if (!is_array($arrAccounts))
		{
			return FALSE;
		}
		
		// get temp invoice run id
		$selFindTempInvoice = new StatementSelect("InvoiceTemp", "DISTINCT InvoiceRun", 1);
		if (!$selFindTempInvoice->Execute())
		{
			Debug("No Temporary Invoice found!  Aborting...");
			return;
		}
		$arrInvoiceRun			= $selFindTempInvoice->Fetch();
		$strInvoiceRun			= $arrInvoiceRun['InvoiceRun'];
		$this->_strInvoiceRun	= $strInvoiceRun;
		
		$qryDelete = new Query();
		
		// for each account
		foreach ($arrAccounts as $intAccount)
		{
			if ((int)$intAccount > 1000000000)
			{
				//----------------------------------------------------------------//
				// REMOVE DATA
				//----------------------------------------------------------------//
				$qryDelete->Execute("DELETE FROM InvoiceTemp		WHERE Account = $intAccount");
				$qryDelete->Execute("DELETE FROM ServiceTypeTotal	WHERE Account = $intAccount AND InvoiceRun = '$strInvoiceRun'");
				$qryDelete->Execute("DELETE FROM ServiceTotal		WHERE Account = $intAccount AND InvoiceRun = '$strInvoiceRun'");
				$qryDelete->Execute("DELETE FROM InvoiceOutput		WHERE Account = $intAccount");
			}
		}
		
		//----------------------------------------------------------------//
		// REGENERATE DATA
		//----------------------------------------------------------------//
		// Select Account Details
		$strAccounts = implode(', ', $arrAccounts);
		$selAccountDetails	= new StatementSelect("Account", "*", "Id IN ($strAccounts)");
		if (!$selAccountDetails->Execute())
		{
			Debug("Error retrieving account data for $intAccount... : ".$selAccountDetails->Error());
		}
		
		// FetchAll will automatically put it in an indexed array for us
		$arrAccountDetails = $selAccountDetails->FetchAll();
		
		// Generate the invoice
		$arrInfo = $this->GenerateInvoices($arrAccountDetails, FALSE, TRUE);
		
		
		//----------------------------------------------------------------//
		// REGENERATE OUTPUT DATA
		//----------------------------------------------------------------//
		$selInvoice = new StatementSelect("InvoiceTemp", "*", "Account = <Account>");
		if ($bolReprint)
		{
			foreach ($arrAccounts as $intAccount)
			{
				$selInvoice->Execute(Array('Account' => $intAccount));
				if ($arrInvoice = $selInvoice->Fetch())
				{
					CliEcho(" + Generating Output for $intAccount...");
					$this->_arrBillOutput[BILL_PRINT]->AddInvoice($arrInvoice);
				}
			}
		}
	}
}
?>

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
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 echo "<pre>";

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Use GET variables to select which action to take
switch ($_GET['action'])
{
	case "commit":
		$appBilling->Commit();
		break;
	case "revoke":
		$appBilling->Revoke();
		break;
	case "execute":
	default:
		// By default, run Execute()
		$appBilling->Execute();
		break;
}

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();



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
		$this->_rptBillingReport 	= new Report("Billing Report for ".date("Y-m-d H:i:s"), "flame@telcoblue.com.au", FALSE);
		$this->_rptAuditReport		= new Report("Bill Audit Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au");
		
		// Report headers
		$this->_rptBillingReport->AddMessage(MSG_HORIZONTAL_RULE);
		$this->_rptAuditReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		// Construct the Bill Output objects
		$this->_arrBillOutput[BILL_PRINT]	= new BillingModulePrint(&$this->db);
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
			return;		// TODO: FIXME - Should we return if this fails??
		}
		
		// Init Select Statements
		$selServices					= new StatementSelect("Service", "*", "Account = <Account>");
		$selAccounts					= new StatementSelect("Account", "*", "Id = 1000158426");	// TODO: Should have a WHERE clause in final version
		
		// Init Update Statements
		$arrCDRCols = Array();
		$arrCDRCols['Status']			= CDR_TEMP_INVOICE;
		$updCDRs						= new StatementUpdate("CDR", "Account = <Account> AND Status = ".CDR_RATED, $arrCDRCols);
		
		// Init Insert Statements
		$arrInvoiceData 				= Array();
		$arrInvoiceData['CreatedOn']	= new MySQLFunction("NOW()");
		$arrInvoiceData['DueOn']		= new MySQLFunction("DATE_ADD(NOW(), INTERVAL <Days> DAY");
		$insTempInvoice					= new StatementInsert("InvoiceTemp", $arrInvoiceData);
		$insServiceTotal				= new StatementInsert("ServiceTotal");
		
		$intPassed = 0;
		$intFailed = 0;
		
		// get a list of all accounts that require billing today
		// TODO: FIXME - Faking for now...
		$selAccounts->Execute(Array("Archived" => FALSE));
		$arrAccounts = $selAccounts->FetchAll();

		// Report Title
		$this->_rptBillingReport->AddMessage("\n".MSG_BILLING_TITLE."\n");
		
		// generate an InvoiceRun Id
		$strInvoiceRun = uniqid();
		
		// prepare (clean) billing files
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			$this->_arrBillOutput[$strKey]->clean();
		}
		
		foreach ($arrAccounts as $arrAccount)
		{
			$this->_rptBillingReport->AddMessageVariables(MSG_LINE, Array('<AccountNo>' => $arrAccount['Id']), FALSE);
			
			// Set status of CDR_RATED CDRs for this account to CDR_TEMP_INVOICE
			if(!$updCDRs->Execute($arrCDRCols, Array('Account' => $arrAccount['Id'])))
			{
				// Report and warn
				$this->_rptBillingReport->AddMessageVariables(MSG_LINE_FAILED, Array('<Reason>' => "WARNING: Cannot link CDRs"), FALSE);
			}
			
			// calculate totals
			$fltDebits = 0;
			$fltTotalCharge = 0;

			// Retrieve list of services for this account
			$selServices->Execute(Array('Account' => $arrAccount['Id']));
			$arrServices = $selServices->FetchAll();

			// for each service belonging to this account
			foreach ($arrServices as $arrService)
			{
				if ($arrService['ChargeCap'] > 0)
				{
					// DEBUG
					Debug("There is a charge cap");
					
					// If we have a charge cap, apply it
					$fltTotalCharge = floatval (min ($arrService['CappedCharge'], $arrService['ChargeCap'] + $arrService['UnCappedCharge']));
					
					if ($arrService['UsageCap'] > 0 && $arrService['UsageCap'] < $arrService['CappedCharge'])
					{
						// DEBUG
						Debug("Gone over cap");
						$fltTotalCharge += floatval ($arrService['UncappedCharge'] - $arrService['UsageCap']);
					}
				}
				else
				{
					$fltTotalCharge = floatval ($arrService['CappedCharge'] + $arrService['UncappedCharge']);
				}

				// If there is a minimum monthly charge, apply it
				if ($arrService['MinMonthly'] > 0)
				{
					// DEBUG
					Debug("There is a minimum monthly");
					$fltTotalCharge = floatval(max($arrService['MinMonthly'], $fltTotalCharge));
				}
				
				// service totals
				$fltServiceCredits	= 0.0; 						//TODO!!!! - IGNORE FOR NOW
				$fltServiceDebits	= $fltTotalCharge;
				$fltServiceTotal	= $fltTotalCharge - $fltServiceCredits;
				
				// insert into ServiceTotal
				$arrServiceTotal = Array();
				$arrServiceTotal['InvoiceRun']		= $strInvoiceRun;
				$arrServiceTotal['FNN']				= $arrService['FNN'];
				$arrServiceTotal['AccountGroup']	= $arrService['AccountGroup'];
				$arrServiceTotal['Account']			= $arrService['Account'];
				$arrServiceTotal['Service']			= $arrService['Id'];
				$arrServiceTotal['RecordType']		= $arrService['RecordType'];
				$arrServiceTotal['CappedCharge']	= $arrService['CappedCharge'];
				$arrServiceTotal['UncappedCharge']	= $arrService['UncappedCharge'];
				$arrServiceTotal['TotalCharge']		= $fltServiceTotal;
				$insServiceTotal->Execute($arrServiceTotal);
				
				// add to invoice totals
				$fltTotalDebits		+= $fltServiceDebits;
				$fltTotalCredits	+= $fltServiceCredits;
			}
			
			// calculate invoice total
			$fltTotal	= $fltTotalDebits - $fltTotalCredits;
			$fltBalance	= $fltTotal; 				//TODO!!!! - FAKE FOR NOW
			
			// write to temporary invoice table
			$arrInvoiceData = Array();
			$arrInvoiceData['AccountGroup']	= $arrAccount['AccountGroup'];
			$arrInvoiceData['Account']		= $arrAccount['Id'];
			//$arrInvoiceData['CreatedOn']	= new MySQLFunction("NOW()");
			$arrInvoiceData['CreatedOn']	= date("Y-m-d H:i:s");
			//$arrInvoiceData['DueOn']		= new MySQLFunction("DATE_ADD(NOW(), INTERVAL <Days> DAY", Array("Days"=>$arrAccount['PaymentTerms']));
			$arrInvoiceData['DueOn']		= date("Y-m-d H:i:s", strtotime("+ ". $arrAccount['PaymentTerms'] ." days"));
			$arrInvoiceData['Credits']		= $fltTotalCredits;
			$arrInvoiceData['Debits']		= $fltTotalDebits;
			$arrInvoiceData['Total']		= $fltTotal;
			$arrInvoiceData['Tax']			= $fltTotal / TAX_RATE_GST;
			$arrInvoiceData['Balance']		= $fltBalance;
			$arrInvoiceData['Status']		= INVOICE_TEMP;
			$arrInvoiceData['InvoiceRun']	= $strInvoiceRun;
			
			// report error or success
			if(!$insTempInvoice->Execute($arrInvoiceData))
			{
				// Report and fail out
				$this->_rptBillingReport->AddMessageVariables(MSG_FAILED.MSG_LINE_FAILED, Array('<Reason>' => "Unable to create temporary invoice"));
				$intFailed++;
				continue;
			}
			
			// work out the bill printing target
			// TODO - LATER : fake it for now
			$intPrintTarget = BILL_PRINT;
			
			// build billing output for this invoice
			$this->_arrBillOutput[$intPrintTarget]->AddInvoice($arrInvoiceData);
			
			$intPassed++;
			
			// Report and continue
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
		$strQuery .= " GROUP BY Service, RecordType";
		
		// run query
		$qryServiceTypeTotal = new Query();
		$qryServiceTypeTotal->Execute($strQuery);
		
		// BILLING VALIDATION
		// Make sure all of our totals add up
		//TODO!!!!
		
		// BILLING OUTPUT
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			// build billing output sample
			$this->_arrBillOutput[$strKey]->BuildSample();
			
			// send billig output sample
			$this->_arrBillOutput[$strKey]->SendSample();
			
			// REPORTING
			//TODO!!!!
		}
		
		// Finish off Billing Report
		$arrReportLines['<Total>']	= $intPassed + $intFailed;
		$arrReportLines['<Time>']	= $this->Framework->SplitWatch();
		$arrReportLines['<Pass>']	= $intPassed;
		$arrReportLines['<Fail>']	= $intFailed;
		$this->_rptBillingReport->AddMessageVariables(MSG_BUILD_REPORT, $arrReportLines);	
		
		// Retrieve data for use in the audit report
		$arrInvoiceColumns['TotalInvoices']			= "COUNT(DISTINCT InvoiceTemp.Id)";
		$arrInvoiceColumns['TotalInvoicedExGST']	= "SUM(InvoiceTemp.Total)";
		$arrInvoiceColumns['TotalInvoicedIncGST']	= "SUM(InvoiceTemp.Total) + SUM(InvoiceTemp.Tax)";
		$arrInvoiceColumns['TotalCDRCost']			= "SUM(CDR.Cost)";
		$arrInvoiceColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrInvoiceColumns['TotalCDRs']				= "COUNT(DISTINCT CDR.Id)";
		$selInvoiceSummary	= new StatementSelect(	"InvoiceTemp, CDR",
													$arrInvoiceColumns,
													/*"CDR.Status = ".CDR_TEMP_INVOICE*/ NULL,
													NULL,
													NULL,
													"InvoiceTemp.InvoiceRun");
		$selInvoiceSummary->Execute();
		if (!$arrInvoiceSummary = $selInvoiceSummary->Fetch())
		{
			// TODO: Error
		}
		else
		{
			// TODO: Append to report
			//Debug($arrInvoiceSummary);
		}
		
		$arrCarrierColumns['CarrierId']				= "CDR.Carrier";
		$arrCarrierColumns['TotalCost']				= "SUM(CDR.Cost)";
		$arrCarrierColumns['TotalRated']			= "SUM(CDR.Charge)";
		$arrCarrierColumns['TotalCDRs']				= "COUNT(CDR.Id)";
		$selCarrierSummary = new StatementSelect(	"CDR",
													$arrCarrierColumns,
													/*"CDR.Status = ".CDR_TEMP_INVOICE*/NULL,
													"CDR.Carrier",
													NULL,
													"CDR.Carrier");
		$selCarrierSummary->Execute();
		if (!$arrCarrierSummarys = $selCarrierSummary->FetchAll())
		{
			// TODO: Error
		}
		else
		{
			//Debug($arrCarrierSummarys);	
		}
		
		$arrServiceTypeColumns['ServiceType']			= "ServiceType";
		$arrServiceTypeColumns['TotalCost']				= "SUM(Cost)";
		$arrServiceTypeColumns['TotalRated']			= "SUM(Charge)";
		$arrServiceTypeColumns['TotalCDRs']				= "COUNT(Id)";
		$selServiceTypeSummary = new StatementSelect(	"CDR",
														$arrServiceTypeColumns,
														/*"CDR.Status = ".CDR_TEMP_INVOICE*/NULL,
														"ServiceType",
														NULL,
														"ServiceType");
		$selServiceTypeSummary->Execute();
		if (!$arrServiceTypeSummarys = $selServiceTypeSummary->FetchAll())
		{
			// TODO: Error
		}
		else
		{
			//Debug($arrServiceTypeSummarys);	
		}
		
		$arrCarrierRecordTypeColumns['RecordType']		= "RecordType.Name";
		$arrCarrierRecordTypeColumns['TotalCost']		= "SUM(CDR.Cost)";
		$arrCarrierRecordTypeColumns['TotalRated']		= "SUM(CDR.Charge)";
		$arrCarrierRecordTypeColumns['TotalCDRs']		= "COUNT(CDR.Id)";
		$selRecordTypes 			= new StatementSelect("CDR JOIN RecordType ON CDR.RecordType = RecordType.Id",
														  $arrCarrierRecordTypeColumns,
														  "CDR.Carrier = <Carrier> OR CDR.ServiceType = <ServiceType>",
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
			$selRecordTypes->Execute(Array('Carrier' => $arrCarrierSummary['CarrierId'], 'ServiceType' => DONKEY));
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
			$arrServiceTypeSummaryVars['<TotalCDRs>']		= number_format((int)$arrServiceTypeSummary['TotalCDRs']);
			
			// Generate Carrier's Record Type Breakdowns
			$strRecordTypes = "";
			$selRecordTypes->Execute(Array('Carrier' => DONKEY, 'ServiceType' => $arrServiceTypeSummary['ServiceType']));
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
		$selCheckTempInvoices->Execute();
		if($selCheckTempInvoices->Fetch() !== FALSE)
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED.MSG_FAILED_LINE, Array('<Reason>' => ""));
			return;
		}
		else
		{
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
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
		
		// apply invoice no. to all CDRs for this invoice
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_CDRS, FALSE);
		$strQuery  = "UPDATE CDR INNER JOIN Invoice using (Account)";
		$strQuery .= " SET CDR.Invoice = Invoice.Id, CDR.Status = ".CDR_INVOICED;
		$strQuery .= " WHERE CDR.Status = ".CDR_TEMP_INVOICE." AND Invoice.Status = ".INVOICE_TEMP;
		$qryCDRInvoice = new Query();
		if(!$qryCDRInvoice->Execute($strQuery))
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
		
		// update invoice status
		$this->_rptBillingReport->AddMessage(MSG_UPDATE_INVOICE_STATUS, FALSE);
		$arrUpdateData = Array();
		$arrUpdateData['Status'] = INVOICE_COMMITTED;
		$updInvoiceStatus = new StatementUpdate("Invoice", "Status = ".INVOICE_TEMP, $arrUpdateData);
		if($updInvoiceStatus->Execute($arrUpdateData, Array()) === FALSE)
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
		
		foreach ($this->_arrBillOutput AS $strKey=>$strValue)
		{
			// build billing output
			$this->_arrBillOutput[$strKey]->BuildOutput();
			
			// send billig output
			$this->_arrBillOutput[$strKey]->SendOutput();
			
			// REPORTING
			//TODO!!!!
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
		$updCDRStatus = new StatementUpdate("CDR", "Status = ".CDR_TEMP_INVOICE, Array('Status' => CDR_RATED));
		if($updCDRStatus->Execute(Array('Status' => CDR_RATED), Array()) === FALSE)
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
		
		//TODO!!!! - Future: clean up Service total & Service type total table 
		
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
 }


?>

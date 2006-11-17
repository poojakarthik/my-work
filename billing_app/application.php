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

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

$appBilling->Execute();
//$appBilling->Commit();
//$appBilling->Revoke();

$appBilling->FinaliseReport();

// finished
echo("\n-- End of Billing --\n");
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
		
		$this->_rptBillingReport = new Report("Billing Report for ".date("Y-m-d H:i:s"), "flame@telcoblue.com.au");
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
		
		// Report header
		$this->_rptBillingReport->AddMessage("\n".MSG_HORIZONTAL_RULE);
		
		// Empty the temporary invoice table
		// This is safe, because there should be no CDRs with CDR_TEMP_INVOICE status anyway
		if (!$this->Revoke())
		{
			return;		// TODO: FIXME - Should we return if this fails??
		}
				
		// Init Select Statements
		$selServices					= new StatementSelect("Service", "*", "Account = <Account>");
		$selAccounts					= new StatementSelect("Account", "*");	// TODO: Should have a WHERE clause in final version
		
		// Init Update Statements
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
		$selAccounts->Execute();
		$arrAccounts = $selAccounts->FetchAll();

		// Report
		$this->_rptBillingReport->AddMessage(MSG_BUILD_TEMP_INVOICES."\n");
		
		foreach ($arrAccounts as $arrAccount)
		{
			// Set status of CDR_RATED CDRs for this account to CDR_TEMP_INVOICE
			if(!$updCDRs->Execute($arrCDRCols, Array('Account' => $arrAccount['Id'])))
			{
				// Report and fail out
				$this->_rptBillingReport->AddMessageVariables(MSG_FAILED.MSG_LINE_FAILED, Array('Reason' => "Cannot link CDRs"));
				$intFailed++;
				continue;
			}
			
			// generate an InvoiceRun Id
			$strInvoiceRun = uniqid();
			
			
			// calculate totals
			$fltDebits = 0;
			//TODO!!!
			// this comes from adding up the service totals

			// for each service belonging to this account
				// if ChargeCap
					// if CappedCharge > UsageCap
						// captotal = CappedCharge - UsageCap
					// else
						// captotal = min(ChargeCap, CappedCharge)
				// else
					// captotal = CappedCharge
					
				// servicetotal = captotal + UncappedCharge
				
				// if MinMonthly
					// servicetotal = max(servicetotal, MinMonthly)
				// $fltDebits += servicetotal

			// Retrieve list of services for this account
			$selServices->Execute(Array('Account' => $arrAccount['Id']));
			$arrServices = $selServices->FetchAll();

			// for each service belonging to this account
			foreach ($arrServices as $arrService)
			{
				if ($arrService['ChargeCap'] > 0)
				{
					// If we have a charge cap, apply it
					$fltTotalCharge = floatval (min ($arrService['CappedCharge'], $arrService['ChargeCap'] + $arrService['UnCappedCharge']));
					
					if ($arrService['UsageCap'] > 0 && $arrService['UsageCap'] < $arrService['CappedCharge'])
					{
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
					$fltTotalCharge = floatval(max($arrService['MinMonthly'], $fltTotalCharge));
				}
				
				// service totals
				$fltServiceCredits	= 0.0; 						//TODO!!!! - IGNORE FOR NOW
				$fltServiceDebits	= $fltTotalCharge;
				$fltServiceTotal	= $fltTotalCharge - $fltServiceCredits;
				
				// TODO!!!! - insert into servicetotal & service type total
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
				// save this
				$insServiceTotal->Execute($arrServiceTotal);
				
				// add to invoice totals
				$fltTotalDebits		+= $fltServiceDebits;
				$fltTotalCredits	+= $fltServiceCredits;
			}
			
			// calculate invoice total
			$fltTotal	= $fltServiceDebits - $fltTotalCredits;
			$fltBalance	= $fltTotal; 				//TODO!!!! - FAKE FOR NOW
			
			// write to temporary invoice table
			$arrInvoiceData = Array();
			$arrInvoiceData['AccountGroup']	= $arrAccount['AccountGroup'];
			$arrInvoiceData['Account']		= $arrAccount['Id'];
			//$arrInvoiceData['CreatedOn']	= new MySQLFunction("NOW()");
			//$arrInvoiceData['DueOn']		= new MySQLFunction("DATE_ADD(NOW(), INTERVAL <Days> DAY", Array("Days"=>$arrAccount['PaymentTerms']));
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
				$this->_rptBillingReport->AddMessageVariables(MSG_FAILED.MSG_LINE_FAILED, Array('Reason' => "Unable to create temporary invoice"));
				$intFailed++;
				continue;
			}
			
			// build output
			//TODO!!! - LATER
			
			// write to billing file
			//TODO!!! - LATER
			
			$intPassed++;
			
			// Report and continue
			$this->_rptBillingReport->AddMessage(MSG_OK);
		}
		
		$arrReportLines['<Total>']	= $intPassed + $intFailed;
		$arrReportLines['<Time>']	= $this->Framework->SplitWatch();
		$arrReportLines['<Pass>']	= $intPassed;
		$arrReportLines['<Fail>']	= $intFailed;
		$this->_rptBillingReport->AddMessageVariables(MSG_BUILD_REPORT, $arrReportLines);		
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
		// copy temporary invoices to invoice table
		$this->_rptBillingReport->AddMessage(MSG_COMMIT_TEMP_INVOICES, FALSE);
		$siqInvoice = new QuerySelectInto();
		if(!$siqInvoice->Execute('Invoice', 'InvoiceTemp'))
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
		$strQuery .= " SET CDR.Invoice = Invoice.Id, CDR.Status = {CDR_INVOICED}";
		$strQuery .= " WHERE CDR.Status = {CDR_TEMP_INVOICE} AND Invoice.Status = {INVOICE_TEMP}";
		$qryCDRInvoice = new Query();
		if(!$qryCDRInvoice->Execute($strQuery))
		{
			// Report and fail out
			$this->_rptBillingReport->AddMessage(MSG_FAILED);
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
		$this->_rptBillingReport->AddMessageVariables("\n".MSG_HORIZONTAL_RULE."\n".MSG_BILLING_FOOTER, Array('<Time>' => $this->Framework->SplitWatch()));
		
		// Send off the report
		return $this->_rptBillingReport->Finish();
	}
 }


?>

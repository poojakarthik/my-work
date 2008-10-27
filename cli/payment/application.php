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
 * @package		Payment_application
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationPayment
//----------------------------------------------------------------------------//
/**
 * ApplicationPayment
 *
 * Payment Module
 *
 * Payment Module
 *
 *
 * @prefix		app
 *
 * @package		Payment_application
 * @class		ApplicationPayment
 */
 class ApplicationPayment extends ApplicationBaseClass
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
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		$this->_arrPaymentModules = Array();
		
		$this->_rptPaymentReport = new Report("Payments Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au");
		$this->_rptPaymentReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		$arrColumns = Array();
		$arrColumns['Status']		= NULL;
		$this->_ubiPaymentFile		= new StatementUpdateById("FileImport", $arrColumns);
		
		$arrColumns = Array();
		$arrColumns['carrier']		= NULL;
		$arrColumns['Payment']		= NULL;
		$arrColumns['SequenceNo']	= NULL;
		$arrColumns['File']			= NULL;
		$arrColumns['Status']		= NULL;
		$this->_insPayment			= new StatementInsert("Payment", $arrColumns);

		$arrColumns = Array();
		$arrColumns['Id']			= "Payment.Id";
		$arrColumns['carrier']		= "Payment.carrier";
		$arrColumns['Payment']		= "Payment.Payment";
		$arrColumns['FileType']		= "FileImport.FileType";
		$arrColumns['File']			= "FileImport.Id";
		$arrColumns['SequenceNo']	= "Payment.SequenceNo";
		$this->_selGetImportedPayments	= new StatementSelect("Payment JOIN FileImport ON Payment.File = FileImport.Id", $arrColumns, "Payment.Status = ".PAYMENT_IMPORTED, NULL, "1000");
		
		$arrColumns = Array();
		$arrColumns['Id']		= NULL;
		$arrColumns['Status']	= NULL;
		$this->_ubiSavePaymentStatus	= new StatementUpdateById("Payment", $arrColumns);
		
		$this->_selGetNormalisedPayments	= new StatementSelect("Payment", "*", "Status = ".PAYMENT_WAITING." OR Status = ".PAYMENT_PAYING);
		
		$this->_selAccountInvoices			= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account", "Invoice.*", "Account.Archived != ".ACCOUNT_STATUS_ARCHIVED." AND Invoice.Account = <Account> AND Invoice.Balance > 0 AND (Invoice.Status = ".INVOICE_COMMITTED." OR Invoice.Status = ".INVOICE_DISPUTED.")", "Invoice.DueOn ASC");
		
		$this->_selAccountGroupInvoices		= new StatementSelect("Invoice JOIN Account ON Account.Id = Invoice.Account", "Invoice.*", "Account.Archived != ".ACCOUNT_STATUS_ARCHIVED." AND Invoice.AccountGroup = <AccountGroup> AND Invoice.Balance > 0 AND (Invoice.Status = ".INVOICE_COMMITTED." OR Invoice.Status = ".INVOICE_DISPUTED.")", "Invoice.DueOn ASC");
		
		$this->_selCreditInvoices			= new StatementSelect("Invoice", "*", "Account = <Account> AND Balance < 0 AND (Status = ".INVOICE_COMMITTED." OR Status = ".INVOICE_DISPUTED.")");
		
		$this->_ubiPayment					= new StatementUpdateById("Payment");
		
		//TODO!rich! make this update only status & balance
		$this->_ubiInvoice					= new StatementUpdateById("Invoice");
		
		$this->_ubiSaveNormalisedPayment	= new StatementUpdateById("Payment");
		
		$this->_insInvoicePayment			= new StatementInsert("InvoicePayment");
		
 		// Load Payment Normalisation CarrierModules
 		CliEcho(" * PAYMENT MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_NORMALISATION_PAYMENT));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$this->_arrPaymentModules[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
 			CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'Carrier')." : ".$this->_arrPaymentModules[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
 		}
		
 		// Load Direct Debit CarrierModules
 		CliEcho(" * DIRECT DEBIT MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_PAYMENT_DIRECT_DEBIT));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$modModule	= new $arrModule['Module']($arrModule['Carrier'], $arrModule['customer_group']);
 			$this->_arrDirectDebitModules[$arrModule['customer_group']][$modModule->intBillingType]	= $modModule;
 			
 			CliEcho("\t + ".GetConstantDescription($arrModule['customer_group'], 'CustomerGroup')." : ".GetConstantDescription($arrModule['Carrier'], 'Carrier')." : ".GetConstantDescription($modModule->intBillingType, 'BillingType'));	
 		}
 		
 		CliEcho();
	}
	
	//------------------------------------------------------------------------//
	// Execute
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Execute the application
	 *
	 * Execute the application
	 *
	 * @return			VOID
	 *
	 * @method
	 */
 	function Execute()
 	{
		// IMPORT PAYMENTS
		$this->Import();
		
		// NORMALISE PAYMENTS
		$this->_rptPaymentReport->AddMessage(MSG_NORMALISE_TITLE);
		while($intCount = $this->Normalise())
		{
			$arrReportLine['<Total>']	= $intCount;
			$arrReportLine['<Time>']	= $this->Framework->LapWatch();
			$this->_rptPaymentReport->AddMessageVariables("\n".MSG_NORMALISE_SUBTOTALS."\n", $arrReportLine);
		}
		// Report normalisation results
		$arrReportLine['<Total>']	= $this->_intNormalisationCount;
		$arrReportLine['<Time>']	= $this->Framework->LapWatch();
		$arrReportLine['<Pass>']	= $this->_intNormalisationPassed;
		$arrReportLine['<Fail>']	= $this->_intNormalisationCount - $this->_intNormalisationPassed - $this->_intNormalisationIgnored;
		$arrReportLine['<Ignore>']	= $this->_intNormalisationIgnored;
		$this->_rptPaymentReport->AddMessageVariables(MSG_NORMALISE_FOOTER, $arrReportLine);
		
		//die;
		
		// PROCESS PAYMENTS
		$this->_rptPaymentReport->AddMessage(MSG_PROCESS_TITLE);
		$intCount = $this->Process();

		// Report normalisation results
		$arrReportLine['<Total>']	= $this->_intProcessCount;
		$arrReportLine['<Time>']	= $this->Framework->LapWatch();
		$arrReportLine['<Pass>']	= $this->_intProcessPassed;
		$arrReportLine['<Fail>']	= $this->_intProcessCount - $this->_intProcessPassed;
		$this->_rptPaymentReport->AddMessageVariables(MSG_PROCESS_FOOTER, $arrReportLine);
		
		/*// Email to Payments Officer
		$this->_rptPaymentReport->AddMessage("\nEmailing Confirmation to Payments Officer...\t\t\t", FALSE);
		$mimMimeEmail = new Mail_Mime("\n");
		$mimMimeEmail->setTXTBody("Payments Processing was completed at ".date("d/m/Y H:i:s", time())."\n\n - Pablo");
	 	$emlMail =& Mail::factory('mail');
		$arrExtraHeaders = Array(
									'From'		=> "payments@voiptel.com.au",
									'Subject'	=> "Payments Completed @ ".date("d/m/Y H:i:s", time())
								);
		$strContent = $mimMimeEmail->get();
		$arrHeaders = $mimMimeEmail->headers($arrExtraHeaders);
		if ($emlMail->send(EMAIL_CREDIT_MANAGER, $arrHeaders, $strContent))
		{
			// Success
			$this->_rptPaymentReport->AddMessage("[   OK   ]\n");
		}
		else
		{
			// Failure
			$this->_rptPaymentReport->AddMessage("[ FAILED ]\n");
		}*/
		
		$this->_rptPaymentReport->AddMessage(MSG_HORIZONTAL_RULE);
		$this->_rptPaymentReport->Finish(FILES_BASE_PATH."log/payment/".date("Ymd_His").".log");
	}
	
	//------------------------------------------------------------------------//
	// Import
	//------------------------------------------------------------------------//
	/**
	 * Import()
	 *
	 * Imports Records from Payment Files
	 *
	 * Imports Records from Payment Files
	 *
	 * @return		void
	 *
	 * @method
	 */
	 function Import()
	 {
		$this->_rptPaymentReport->AddMessage(MSG_IMPORT_TITLE);
		$this->Framework->StartWatch();
		
		// Retrieve list of Payment Files marked as either ready to process, or failed process
		$arrFileTypes	= Array();
		foreach ($this->_arrPaymentModules as $intCarrier=>$arrCarrierFileTypes)
		{
			foreach (array_keys($arrCarrierFileTypes) as $intFileType)
			{
				$arrFileTypes[]	= $intFileType;
			}
		}
		
		// Do we have any FileTypes to import?
		if (!count($arrFileTypes))
		{
			return FALSE;
		}
		
		$strWhere					= "FileType IN (".implode(', ', $arrFileTypes).") AND Status IN (".FILE_COLLECTED.", ".FILE_REIMPORT.")";
		$this->_selGetPaymentFiles	= new StatementSelect("FileImport JOIN compression_algorithm ON FileImport.compression_algorithm_id = compression_algorithm.id", "FileImport.*, compression_algorithm.file_extension, compression_algorithm.php_stream_wrapper", $strWhere);
		
		// Loop through the Payment File entries
		$intCount	= 0;
		$intPassed	= 0;
		if ($this->_selGetPaymentFiles->Execute() === FALSE)
		{

		}
		while ($arrFile = $this->_selGetPaymentFiles->Fetch())
		{
			$intCount++;
			
			// Make sure the file exists
			if (!file_exists($arrFile['Location']))
			{
				// Report the error, and UPDATE the database with a new status, then move to the next file
				$arrColumns['Id']		= $arrFile['Id'];
				$arrColumns['Status']	= FILE_IMPORT_FAILED;
				if ($this->_ubiPaymentFile->Execute($arrColumns) === FALSE)
				{
					
				}
				
				// Add to the Normalisation report
				$this->_rptPaymentReport->AddMessageVariables(MSG_IMPORT_LINE.MSG_FAIL.MSG_REASON."Cannot locate file '".$arrFile['Location']."'", Array('<Id>' => TruncateName($arrFile['FileName'], 30)));
				continue;
			}
			
			// update file status
			$arrColumns = Array();
			$arrColumns['Id']		= $arrFile['Id'];
			$arrColumns['Status']	= FILE_IMPORTING;
			if ($this->_ubiPaymentFile->Execute($arrColumns) === FALSE)
			{
				continue;
			}
			
			// Import
			$ptrFile		= fopen($arrFile['php_stream_wrapper'].$arrFile['Location'], "r");
			$intSequence	= 1;
			while (!feof($ptrFile))
			{
				// Read line
				$arrData['carrier']		= $arrFile['Carrier'];
				$arrData['Payment']		= fgets($ptrFile);
				$arrData['SequenceNo']	= $intSequence;
				$arrData['File']		= $arrFile['Id'];
				$arrData['Status']		= PAYMENT_IMPORTED;
				if ($this->_insPayment->Execute($arrData) === FALSE)
				{
					echo "Error Inserting Payment! -> ".Debug($this->_insPayment);
				}
				
				// Increment sequence number
				$intSequence++;
			}
			$intPassed++;
			$this->_rptPaymentReport->AddMessageVariables(MSG_IMPORT_LINE.MSG_OK, Array('<Id>' => TruncateName($arrFile['FileName'], 30)), TRUE, FALSE);
			
			// update file status
			$arrColumns = Array();
			$arrColumns['Id']		= $arrFile['Id'];
			$arrColumns['Status']	= FILE_IMPORTED;
			if ($this->_ubiPaymentFile->Execute($arrColumns) === FALSE)
			{
			
			}
		}
		
		// Report totals
		$arrReportLine['<Total>']		= $intCount;
		$arrReportLine['<Time>']		= $this->Framework->LapWatch();
		$arrReportLine['<Passed>']		= $intPassed;
		$arrReportLine['<Failed>']		= $intCount - $intPassed;
		$this->_rptPaymentReport->AddMessageVariables(MSG_IMPORT_FOOTER, $arrReportLine);
	 }
	
	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalise next 1000 Payment Records
	 *
	 * Normalise next 1000 Payment Records
	 *
	 * @return	bool	returns true until all Payments have been normalised
	 *
	 * @method
	 */
	function Normalise()
	{
		// get next 1000 payments
		$intCount = $this->_selGetImportedPayments->Execute();
		if ($intCount == 0)
		{
			// No payments left, so return false
			return FALSE;
		}
		$arrPayments = $this->_selGetImportedPayments->FetchAll();
		
		$intPassed = 0;
		foreach($arrPayments as $arrPayment)
		{
			// use payment module to decode the payment
			$arrNormalised = $this->_arrPaymentModules[$arrPayment['carrier']][$arrPayment['FileType']]->Normalise($arrPayment['Payment']);
			if($arrNormalised['Status'] || !is_array($arrNormalised))
			{
				$this->_rptPaymentReport->AddMessageVariables(MSG_NORMALISE_LINE, Array('<Id>' => $arrPayment['Id']), FALSE);
				
				if (is_array($arrNormalised))
				{
					$intStatus		= $arrNormalised['Status'];
				}
				else
				{
					$intStatus		= $arrNormalised;
					$arrNormalised	= $arrPayment;
				}
				
				// An error has occurred
				switch($intStatus)
				{
					case PAYMENT_CANT_NORMALISE_HEADER:
						$this->_rptPaymentReport->AddMessage(MSG_IGNORE.MSG_REASON."Header Record");
						$this->_intNormalisationIgnored++;
						break;
					case PAYMENT_CANT_NORMALISE_FOOTER:
						$this->_rptPaymentReport->AddMessage(MSG_IGNORE.MSG_REASON."Footer Record");
						$this->_intNormalisationIgnored++;
						break;
					case PAYMENT_CANT_NORMALISE_INVALID:
						$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Not a vaild Payment Record");
						break;
					case PAYMENT_BAD_OWNER:
						$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Cannot match to an account");
						break;
					default:
						$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."An unknown error occurred with code ".(int)$arrNormalised." in module ".GetConstantDescription($arrPayment['FileType'], 'PaymentType').".");
						$intStatus = PAYMENT_BAD_NORMALISE;
				}
				
				$arrNormalised = array_merge($arrPayment, $arrNormalised);
				$arrNormalised['Status']	= $intStatus;
				if ($this->_ubiPayment->Execute($arrNormalised) === FALSE)
				{
					$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Unable to modify Payment record");
				}
				continue;
			}
			
			// save the payment to DB
			$arrNormalised = array_merge($arrPayment, $arrNormalised);
			$arrNormalised['Status']	= PAYMENT_WAITING;
			$intResult = $this->_ubiPayment->Execute($arrNormalised);
			if($intResult === FALSE)
			{
				$this->_ubiPayment->Error();
			}
			elseif(!$intResult)
			{
				$this->_rptPaymentReport->AddMessageVariables(MSG_NORMALISE_LINE, Array('<Id>' => $arrPayment['Id']), FALSE);
				$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Unable to modify Payment record");
			}
			$intPassed++;
			
			// Add Credit Card Surcharge
			if ($arrNormalised['OriginType'] == PAYMENT_TYPE_CREDIT_CARD && (int)$arrNormalised['OriginId'])
			{
				AddCreditCardSurcharge($arrNormalised['Id']);
			}
			
			$this->_rptPaymentReport->AddMessageVariables(MSG_NORMALISE_LINE.MSG_OK, Array('<Id>' => $arrPayment['Id']), TRUE, FALSE);
		}
		
		$this->_intNormalisationPassed	+= $intPassed;
		$this->_intNormalisationCount	+= $intCount;
		return $intCount;
	}
		
	//------------------------------------------------------------------------//
	// Process
	//------------------------------------------------------------------------//
	/**
	 * Process()
	 *
	 * Process all outstanding Payment Records
	 *
	 * Process all outstanding Payment Records
	 *
	 * @return	bool
	 *
	 * @method
	 */
	 function Process()
	 {
		// Check to see if we're in the middle of a Billing Run
		if (isInvoicing())
		{
			// Don't process payments, there is a Temp Invoice Run
			$this->_rptPaymentReport->AddMessage("WARNING: There is a Billing Run occurring.  No payments will be processed until this is complete.");
			return FALSE;
		}
		
		// get all payments
		$intCount = $this->_selGetNormalisedPayments->Execute();
		$this->_intProcessCount = $intCount;
		if (!$intCount)
		{
			// No payments left, so return false
			return FALSE;
		}
		$arrPayments = $this->_selGetNormalisedPayments->FetchAll();
		
		foreach($arrPayments as $arrPayment)
		{
			$this->_rptPaymentReport->AddMessageVariables(MSG_PROCESS_LINE, Array('<Id>' => $arrPayment['Id']), TRUE, FALSE);
			
			// set current payment
			$this->_arrCurrentPayment = $arrPayment;
			
			// get a list of outstanding invoices for this account group
			//		(and account if we have one in $arrPayment) sorted oldest invoice first
			$arrWhere = Array();
			$arrWhere['AccountGroup'] 	= $arrPayment['AccountGroup'];
			$arrWhere['Account'] 		= $arrPayment['Account'];
			if ($arrWhere['Account'])
			{
				$selOutstandingInvoices = $this->_selAccountInvoices;
			}
			else
			{
				$selOutstandingInvoices = $this->_selAccountGroupInvoices;
			}
			
			if (($intCount2 = $selOutstandingInvoices->Execute($arrWhere)) === FALSE)
			{
				Debug($selOutstandingInvoices->Error());
				continue;
			}
			
			// set default status
			$this->_arrCurrentPayment['Status'] = PAYMENT_PAYING;
			
			// while we have some payment left and an invoice to pay it against
			while ($this->_arrCurrentPayment['Balance'] > 0.0 && ($arrInvoice = $selOutstandingInvoices->Fetch()))
			{				
				// set current invoice
				$this->_arrCurrentInvoice = $arrInvoice;
				
				// apply payment against the invoice
				$fltBalance = $this->_PayInvoice();
				if ($fltBalance === FALSE)
				{
					// something went wrong
					$this->_rptPaymentReport->AddMessageVariables(MSG_INVOICE_LINE.MSG_FAIL.MSG_REASON, Array('<Id>' => $arrInvoice['Id']));
					
					// set status
					$this->_arrCurrentPayment['Status'] = PAYMENT_BAD_PROCESS;
					
					// don't try any more invoices					
					break;
				}
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrCurrentPayment) === FALSE)
				{
					Debug($this->_ubiPayment->Error());
					continue;
				}
				
				$this->_rptPaymentReport->AddMessageVariables(MSG_INVOICE_LINE.MSG_OK, Array('<Id>' => $arrInvoice['Id']), TRUE, FALSE);
			}
			
			// check if we have spent all our money
			if ($this->_arrCurrentPayment['Balance'] == 0)
			{
				$this->_arrCurrentPayment['Status'] = PAYMENT_FINISHED;
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrCurrentPayment) === FALSE)
				{
					Debug($this->_ubiPayment->Error());
					continue;
				}
			}
			
			$this->_intProcessPassed++;
		}
		return $intCount;
	 }
	 
	//------------------------------------------------------------------------//
	// PayInvoice
	//------------------------------------------------------------------------//
	/**
	 * PayInvoice()
	 *
	 * Apply Payment to an invoice
	 *
	 * Apply Payment to an invoice
	 *
	 * @return	mixed	float	Balance
	 *					FALSE	if something went wrong
	 *
	 * @method
	 */
	 function _PayInvoice()
	 {
	 	// work out the payment amount
		$fltPayment = Min($this->_arrCurrentPayment['Balance'], $this->_arrCurrentInvoice['Balance']);
		
		// work out the payment balance
		$fltBalance = $this->_arrCurrentPayment['Balance'] - $fltPayment;
		
		// work out invoice balance
		$this->_arrCurrentInvoice['Balance'] -= $fltPayment;
		
		// work out if this invoice has been paid in full
		if ($this->_arrCurrentInvoice['Balance'] < 0.01)
		{
			// set status
			if ($this->_arrCurrentInvoice['Status'] == INVOICE_COMMITTED)
			{
				// normal invoice
				$this->_arrCurrentInvoice['Status'] = INVOICE_SETTLED;
			}
			elseif ($this->_arrCurrentInvoice['Status'] == INVOICE_DISPUTED)
			{
				// disputed invoice
				$this->_arrCurrentInvoice['Status'] = INVOICE_DISPUTED_SETTLED;
			}
			
			// force balance to 0
			$this->_arrCurrentInvoice['Balance'] = 0;
		}
		
		// add an invoice payment record
		$arrInvoicePayment['invoice_run_id']	= $this->_arrCurrentInvoice['invoice_run_id'];
		$arrInvoicePayment['Account']			= $this->_arrCurrentPayment['Account'];
		$arrInvoicePayment['AccountGroup']		= $this->_arrCurrentPayment['AccountGroup'];
		$arrInvoicePayment['Payment']			= $this->_arrCurrentPayment['Id'];
		$arrInvoicePayment['Amount']			= $fltPayment;
		if ($this->_insInvoicePayment->Execute($arrInvoicePayment) === FALSE)
		{
			Debug($this->_insInvoicePayment->Error());
		}
		
		// update the invoice
		if ($this->_ubiInvoice->Execute($this->_arrCurrentInvoice) === FALSE)
		{
			Debug($this->_ubiInvoice->Error());
		}
		
		// save the balance
		$this->_arrCurrentPayment['Balance'] = $fltBalance;

		// return the balance
		return $fltBalance;
		
	 }
	 
	 
	//------------------------------------------------------------------------//
	// ProcessCredit
	//------------------------------------------------------------------------//
	/**
	 * ProcessCredit()
	 *
	 * Process all outstanding Credit Invoices for an account
	 *
	 * Process all outstanding Credit Invoices for an account
	 *
	 * @return
	 *
	 * @method
	 */
	 function ProcessCredit($intAccountGroup)
	 {
	 	/*
	 	$intAccountGroup = (int)$intAccountGroup;
		
		// get all credit invoices
		$intCount = $this->_selCreditInvoices->execute(Array('AccountGroup' => $intAccountGroup));
		if (!$intCount)
		{
			// No credits left, so return false
			return FALSE;
		}
		
		while($arrCredit = $this->_selCreditInvoices->Fetch())
		{
			// set current payment
			
			
			// get a list of outstanding invoices for this account
			$arrWhere = Array();
			$arrWhere['AccountGroup'] 	= $arrPayment['AccountGroup'];
			$arrWhere['Account'] 		= $arrPayment['Account'];
			if ($arrWhere['Account'])
			{
				$selOutstandingInvoices = $this->_selAccountInvoices;
			}
			else
			{
				$selOutstandingInvoices = $this->_selAccountGroupInvoices;
			}
			
			if ($selOutstandingInvoices->Execute($arrWhere) === FALSE)
			{

			}
			
			// set default status
			$this->_arrCurrentPayment['Status'] = PAYMENT_PAYING; 
			
			// while we have some payment left and an invoice to pay it against
			while ($this->_arrCurrentPayment['Balance'] > 0 && $arrInvoice = $selOutstandingInvoices->Fetch())
			{
				$this->_rptPaymentReport->AddMessageVariables(MSG_INVOICE_LINE, Array('<Id>' => $arrInvoice['Id']));
				
				// set current invoice
				$this->_arrCurrentInvoice = $arrInvoice;
				
				// apply payment against the invoice
				$fltBalance = $this->_PayInvoice();
				if ($fltBalance === FALSE)
				{
					// something went wrong
					$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON);
					
					// set status
					$this->_arrCurrentPayment['Status'] = PAYMENT_BAD_PROCESS;
					
					// don't try any more invoices					
					break;
				}
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrCurrentPayment) === FALSE)
				{

				}
				
				$this->_rptPaymentReport->AddMessage(MSG_OK);
			}
			
			// check if we have spent all our money
			if ($this->_arrCurrentPayment['Balance'] == 0)
			{
				$this->_arrCurrentPayment['Status'] = PAYMENT_FINISHED;
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrCurrentPayment) === FALSE)
				{

				}
			}
			
			// Process successful
			$this->_rptPaymentReport->AddMessage(MSG_OK);
		}
		*/
	 }
	 
	 
	 
	//------------------------------------------------------------------------//
	// ReversePayment
	//------------------------------------------------------------------------//
	/**
	 * ReversePayment()
	 *
	 * Reverses a specified Payment
	 *
	 * Reverses a specified Payment
	 * 
	 * @param	integer	$intPayment		the Id of the Payment to reverse
	 * @param	integer	$intEmployee	optional Id of the Employee who reversed
	 *
	 * @return	boolean					whether the removal was successful or not
	 *
	 * @method
	 */
	 function ReversePayment($intPayment, $intReversedBy = NULL)
	 {
	 	return $this->Framework->ReversePayment($intPayment, $intReversedBy);
	 }
	 
	 
	 
	//------------------------------------------------------------------------//
	// ConsolidateInvoicePayments
	//------------------------------------------------------------------------//
	/**
	 * ConsolidateInvoicePayments()
	 *
	 * Merges InvoicePayments where the Payment and Invoice are the same
	 *
	 * Merges InvoicePayments where the Payment and Invoice are the same
	 *
	 * @return	boolean					whether the removal was successful or not
	 *
	 * @method
	 */
	 function ConsolidateInvoicePayments()
	 {
	 	$selDuplicates		= new StatementSelect("InvoicePayment", "Payment, invoice_run_id", "invoice_run_id != 'Etech'", NULL, NULL, "Payment, Invoice HAVING COUNT(Id) > 1");
	 	$selInvoicePayments = new StatementSelect("InvoicePayment", "*", "Payment = <Payment> AND invoice_run_id = <invoice_run_id> AND Id != <Id>");
	 	$ubiInvoicePayment	= new StatementUpdateById("InvoicePayment", Array('Amount' => NULL));
	 	$qryDelete			= new Query();
	 	
	 	// Find duplicated entries
	 	$selDuplicates->Execute();
	 	while ($arrDuplicate = $selDuplicates->Fetch())
	 	{
	 		// Find entries to consolidate with
	 		$selInvoicePayments->Execute($arrDuplicate);
	 		
	 		// We will consolidate to this IP entry
	 		$arrBaseIP = $selInvoicePayments->Fetch();
	 		
	 		// Merge and delete duplicates
	 		while ($arrIP = $selInvoicePayments->Fetch())
	 		{
	 			$arrBaseIP['Amount'] += $arrIP['Amount'];
	 			$qryDelete->Execute("DELETE FROM InvoicePayment WHERE Id = {$arrIP['Id']}");
	 		}
	 		
	 		// Save base IP entry
	 		$ubiInvoicePayment->Execute($arrBaseIP);
	 	}
	 }
	
	//------------------------------------------------------------------------//
	// RunDirectDebits
	//------------------------------------------------------------------------//
	/**
	 * RunDirectDebits()
	 *
	 * Performs Direct Debits Payments
	 *
	 * Performs Direct Debits Payments, generating any files or performing any
	 * other (eg SOAP) operations necessary
	 * 
	 * @param	boolean	$bolForce			[optional]	TRUE: Direct Debits will run no matter what day it is in the Billing Cycle (not recommended)
	 *
	 * @return	array									['Success']		: TRUE on success, FALSE on error
	 * 													['Description']	: Error Message
	 *
	 * @method
	 */
	 function RunDirectDebits($bolForce = FALSE)
	 {
	 	CliEcho("\n[ PERFORMING DIRECT DEBITS ]\n");
	 	
	 	$intRunDate	= time();
	 	
	 	// Are the Direct Debits due?
	 	if ($bolForce !== TRUE)
	 	{
	 		// Retrieve Direct Debit Scheduling Details, and determine if today is the Invoice Due Date
	 		$selSchedule	= new StatementSelect(	"InvoiceRun LEFT JOIN automatic_invoice_run_event ON InvoiceRun.Id = automatic_invoice_run_event.invoice_run_id",
														"InvoiceRun.Id AS Id, InvoiceRun.Id AS invoice_run_id, InvoiceRun.BillingDate, automatic_invoice_run_event.scheduled_datetime, automatic_invoice_run_event.actioned_datetime, automatic_invoice_run_event.id AS id",
														"scheduled_datetime IS NOT NULL AND actioned_datetime IS NULL AND automatic_invoice_action_id = ". AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT,
														"InvoiceRun.BillingDate DESC, automatic_invoice_run_event.id DESC",
														"1");
			
			$arrUpdateCols	= Array();
			$arrUpdateCols['actioned_datetime']	= NULL;
			$ubiSchedule	= new StatementUpdateById("automatic_invoice_run_event", $arrUpdateCols);
			
	 		if (!$selSchedule->Execute())
	 		{
	 			// No Event Scheduled
	 			if ($selSchedule->Error())
	 			{
	 				return Array('Success' => FALSE, 'Description' => "ERROR: DB Error in \$selSchedule: ".$selSchedule->Error());
	 			}
	 			else
	 			{
	 				return Array('Success' => FALSE, 'Description' => "ERROR: Direct Debits have not been scheduled!");
	 			}
	 		}
	 		$arrSchedule	= $selSchedule->Fetch();
	 		
	 		// Determine Direct Debit Payment Date
	 		if (strtotime($arrSchedule['scheduled_datetime']) > $intRunDate)
	 		{
	 			// We haven't reached the Direct Debit date yet
	 			return Array('Success' => TRUE, 'Description' => "Direct Debits are not due yet, expected on {$arrSchedule['scheduled_datetime']}");
	 		}
	 		elseif ($arrSchedule['actioned_datetime'] !== NULL)
	 		{
	 			// Direct Debits have already been run for this InvoiceRun
	 			return Array('Success' => TRUE, 'Description' => "Direct Debits have already run for InvoiceRun {$arrSchedule['invoice_run_id']}, on {$arrSchedule['actioned_datetime']}");
	 		}
	 	}
	 	
		// Retrieve Direct Debit Minimum
		// ... not needed now that we can join onto the payment_terms table for the customer group

	 	$selAccountDebts	= new StatementSelect("(Invoice JOIN Account ON Account.Id = Invoice.Account) JOIN payment_terms ON payment_terms.customer_group_id = Account.CustomerGroup", "Account, SUM(Invoice.Balance) AS Charge, direct_debit_minimum", "CustomerGroup = <CustomerGroup> AND Account.BillingType = <BillingType> AND Account.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.") AND payment_terms.id IN (SELECT MAX(id) FROM payment_terms WHERE customer_group_id = <CustomerGroup>)", "Account.Id", NULL, "Account.Id HAVING Charge >= payment_terms.direct_debit_minimum");
		
	 	// Process Direct Debits for each Billing Type
	 	$intAccountsCharged	= 0;
	 	foreach ($this->_arrDirectDebitModules as $intCustomerGroup=>&$arrBillingTypes)
	 	{
		 	CliEcho(" * ".GetConstantDescription($intCustomerGroup, 'CustomerGroup'));
		 	
		 	foreach ($arrBillingTypes as $intBillingType=>&$modModule)
		 	{
		 		// HACKHACKHACK: Ensure we're only trying to Direct Debit people who should be
		 		if ($intBillingType === BILLING_TYPE_ACCOUNT)
		 		{
		 			// Account Billing is non-Direct Debit, so ignore
		 			continue;
		 		}
		 		
		 		CliEcho("\t * ".GetConstantDescription($intBillingType, 'BillingType'));
		 		
			 	// Get list of AccountGroups and debts to settle
			 	if ($selAccountDebts->Execute(Array('CustomerGroup' => $intCustomerGroup, 'BillingType' => $intBillingType)))
			 	{
			 		while ($arrAccount	= $selAccountDebts->Fetch())
			 		{
				 		CliEcho("\t\t + Debiting Account #{$arrAccount['Account']}...\t\t\t", FALSE);
				 		
				 		// Run the Module
				 		$arrRunResult	= $modModule->Output($arrAccount);
				 		if ($arrRunResult['Success'] === TRUE || $arrRunResult['Pass'] === TRUE || $arrRunResult === TRUE)
				 		{
				 			// Success
				 			CliEcho("[   OK   ]");
				 		}
				 		else
				 		{
				 			// An Error Occurred
				 			CliEcho("[ FAILED ]");
				 			CliEcho("\t\t\t -- {$arrRunResult['Message']}{$arrRunResult['Description']}");
				 		}
			 		}
			 		
			 		// Export/Send the module
		 			$arrExportResult	= $modModule->Export();
			 		if ($arrExportResult['Pass'] === TRUE)
			 		{
				 		if (is_int($arrExportResult['AccountsCharged']))
				 		{
				 			// Success, Charges Sent
				 			$intAccountsCharged	+= $arrExportResult['AccountsCharged'];
				 		}
				 		else
				 		{
				 			// Success, no Charges Sent (aka Failed for a sane reason)
				 			// TODO -- should this ever happen?
				 		}
			 		}
			 		else
			 		{
			 			return Array('Success' => FALSE, 'Description' => $arrExportResult['Description'], 'arrExportResult' => $arrExportResult);
			 		}
			 	}
			 	elseif ($selAccountDebts->Error())
			 	{
			 		// An Error Occurred
				 	return Array('Success' => FALSE, 'Description' => "ERROR: \$selAccountDebts failed: ".$selAccountDebts->Error());
			 	}
			 	else
			 	{
			 		// No Matches
			 		CliEcho("\t\t -- There were no Accounts to debit.");
			 	}
		 	}
	 	}
	 	CliEcho();
		
		// Update automatic_invoice_action_id Entry (only if not forced)
		if (!$bolForce)
		{
			CliEcho("* Saving Direct Debit State back to DB...");
			$arrSchedule['actioned_datetime']	= date("Y-m-d H:i:s", $intRunDate);
			if ($ubiSchedule->Execute($arrSchedule) === FALSE)
			{
				// Error
				return Array('Success' => FALSE, 'Description' => "ERROR: \$ubiSchedule failed: ".$ubiSchedule->Error());
			}
		}
	 	
	 	// Everything appears to have run fine
	 	return Array('Success' => TRUE, 'AccountsCharged' => $intAccountsCharged);
	 }
 }


?>
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
 
//echo "<pre>";

// Application entry point - create an instance of the application object
$appPayment = new ApplicationPayment($arrConfig);

// Execute the application
$appPayment->Execute();

// finished
echo("\n-- End of Payment --\n");
//echo "</pre>";
die();



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
		
		$this->_rptPaymentReport = new Report("Payments Report for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au");
		$this->_rptPaymentReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		$this->_selGetPaymentFiles	= new StatementSelect("FileImport", "*", "Carrier = ".CARRIER_PAYMENT." AND Status = ".CDRFILE_WAITING);
		
		//$arrColumns['Id']			= NULL;
		$arrColumns['Status']		= NULL;
		$this->_ubiPaymentFile		= new StatementUpdateById("FileImport", $arrColumns);
		
		$arrColumns = Array();
		$arrColumns['Payment']		= NULL;
		$arrColumns['SequenceNo']	= NULL;
		$arrColumns['File']			= NULL;
		$arrColumns['Status']		= NULL;
		$this->_insPayment			= new StatementInsert("Payment", $arrColumns);

		$arrColumns = Array();
		$arrColumns['Id']			= "Payment.Id";
		$arrColumns['Payment']		= "Payment.Payment";
		$arrColumns['FileType']		= "FileImport.FileType";
		$arrColumns['File']			= "FileImport.Id";
		$arrColumns['SequenceNo']	= "Payment.SequenceNo";
		$this->_selGetImportedPayments	= new StatementSelect("Payment JOIN FileImport ON Payment.File = FileImport.Id", $arrColumns, "Payment.Status = ".PAYMENT_IMPORTED, NULL, "1000");
		
		$arrColumns = Array();
		$arrColumns['Id']		= NULL;
		$arrColumns['Status']	= NULL;
		$this->_ubiSavePaymentStatus	= new StatementUpdateById("Payment", $arrColumns);
		
		$this->_selGetNormalisedPayments	= new StatementSelect("Payment", "*", "Status = ".PAYMENT_WAITING);
		
		$this->_selAccountInvoices			= new StatementSelect("Invoice", "*", "Account = <Account> AND Balance > 0 AND (Status = ".INVOICE_COMMITTED." OR Status = ".INVOICE_DISPUTED.")", "DueOn ASC");
		
		$this->_selAccountGroupInvoices		= new StatementSelect("Invoice", "*", "AccountGroup = <AccountGroup> AND Balance > 0 AND (Status = ".INVOICE_COMMITTED." OR Status = ".INVOICE_DISPUTED.")", "DueOn ASC");
		
		$this->_selCreditInvoices			= new StatementSelect("Invoice", "*", "Account = <Account> AND Balance < 0 AND (Status = ".INVOICE_COMMITTED." OR Status = ".INVOICE_DISPUTED.")");
		
		$this->_ubiPayment					= new StatementUpdateById("Payment");
		
		$this->_ubiInvoice					= new StatementUpdateById("Invoice");
		
		$this->_ubiSaveNormalisedPayment	= new StatementUpdateById("Payment");
		
		// Payment modules
		$this->_arrPaymentModules[PAYMENT_TYPE_BILLEXPRESS]	= new PaymentModuleBillExpress();
		$this->_arrPaymentModules[PAYMENT_TYPE_BPAY]		= new PaymentModuleBPay();
		$this->_arrPaymentModules[PAYMENT_TYPE_SECUREPAY]	= new PaymentModuleSecurePay();
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
		
		die;
		
		// PROCESS PAYMENTS
		$this->_rptPaymentReport->AddMessage(MSG_PROCESS_TITLE);
		while($intCount = $this->Process())
		{
			$arrReportLine['<Total>']	= $intCount;
			$arrReportLine['<Time>']	= $this->Framework->LapWatch();
			$this->_rptPaymentReport->AddMessageVariables(MSG_PROCESS_SUBTOTALS, $arrReportLine);
		}
		// Report normalisation results
		$arrReportLine['<Total>']	= $this->_intProcessCount;
		$arrReportLine['<Time>']	= $this->Framework->LapWatch();
		$arrReportLine['<Pass>']	= $this->_intProcessPassed;
		$arrReportLine['<Fail>']	= $this->_intProcessCount - $this->_intProcessPassed;
		$this->_rptPaymentReport->AddMessageVariables(MSG_PROCESS_FOOTER, $arrReportLine);
		$this->_rptPaymentReport->AddMessage(MSG_HORIZONTAL_RULE);
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
	
		// Loop through the Payment File entries
		$intCount	= 0;
		$intPassed	= 0;
		if ($this->_selGetPaymentFiles->Execute() === FALSE)
		{

		}
		while ($arrFile = $this->_selGetPaymentFiles->Fetch())
		{
			$intCount++;
			$this->_rptPaymentReport->AddMessageVariables(MSG_IMPORT_LINE, Array('<Id>' => TruncateName($arrFile['FileName'], 30)), FALSE);
			
			// Make sure the file exists
			if (!file_exists($arrFile['Location']))
			{
				// Report the error, and UPDATE the database with a new status, then move to the next file
				$arrColumns['Id']		= $arrFile['Id'];
				$arrColumns['Status']	= CDRFILE_IMPORT_FAILED;
				if ($this->_ubiPaymentFile->Execute($arrColumns) === FALSE)
				{

				}
				
				// Add to the Normalisation report
				$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Cannot locate file '".$arrFile['Location']."'");
				continue;
			}
			
			// update file status
			$arrColumns = Array();
			$arrColumns['Id']		= $arrFile['Id'];
			$arrColumns['Status']	= CDRFILE_IMPORTING;
			if ($this->_ubiPaymentFile->Execute($arrColumns) === FALSE)
			{
				continue;
			}

			// Import
			$ptrFile		= fopen($arrFile['Location'], "r");
			$intSequence	= 1;
			while (!feof($ptrFile))
			{
				// Read line
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
			$this->_rptPaymentReport->AddMessage(MSG_OK);
			
			// update file status
			$arrColumns = Array();
			$arrColumns['Id']		= $arrFile['Id'];
			$arrColumns['Status']	= CDRFILE_IMPORTED;
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
			// <DEBUG>
			/*if ($arrPayment['FileType'] != PAYMENT_TYPE_BILLEXPRESS)
			{
				continue;
			}*/
			// </DEBUG>
			
			$this->_rptPaymentReport->AddMessageVariables(MSG_NORMALISE_LINE, Array('<Id>' => $arrPayment['Id']));
			// use payment module to decode the payment
			$arrNormalised = $this->_arrPaymentModules[$arrPayment['FileType']]->Normalise($arrPayment['Payment']);
			if($arrNormalised['Status'] !== $arrPayment['Status'] || !is_array($arrNormalised))
			{
				// An error has occurred
				switch($arrNormalised)
				{
					case PAYMENT_CANT_NORMALISE_HEADER:
						$this->_rptPaymentReport->AddMessage(MSG_IGNORE.MSG_REASON."Header Record");
						$intStatus = $arrNormalised;
						$this->_intNormalisationIgnored++;
						break;
					case PAYMENT_CANT_NORMALISE_FOOTER:
						$this->_rptPaymentReport->AddMessage(MSG_IGNORE.MSG_REASON."Footer Record");
						$intStatus = $arrNormalised;
						$this->_intNormalisationIgnored++;
						break;
					case PAYMENT_CANT_NORMALISE_INVALID:
						$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Not a vaild Payment Record");
						$intStatus = $arrNormalised;
						break;
					default:
						$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."An unknown error occurred with code ".(int)$arrNormalised." in module ".GetConstantDescription($arrPayment['FileType'], 'PaymentType').".");
						$intStatus = PAYMENT_BAD_NORMALISE;
				}
				$arrNormalised	= Array();
				$arrNormalised['Status']	= $intStatus;
				$arrNormalised['Id']		= $arrPayment['Id'];
				if ($this->_ubiSavePaymentStatus->Execute($arrNormalised) === FALSE)
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
				$this->_rptPaymentReport->AddMessage(MSG_FAIL.MSG_REASON."Unable to modify Payment record");
			}
			$intPassed++;
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
	 * @return	bool	returns true until all Payments have been processed
	 *
	 * @method
	 */
	 function Process()
	 {
		// get all payments
		$intCount = $this->_selGetNormalisedPayments->Execute();
		if (!$intCount)
		{
			// No payments left, so return false
			return FALSE;
		}
		$arrPayments = $this->_selGetNormalisedPayments->FetchAll();
		
		foreach($arrPayments as $arrPayment)
		{
			$this->_rptPaymentReport->AddMessageVariables(MSG_PROCESS_LINE, Array('<Id>' => $arrPayment['Id']), FALSE);
			
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
			
			if ($selOutstandingInvoices->Execute($arrWhere) === FALSE)
			{

			}
			
			// set default status
			$this->_arrPayment['Status'] = PAYMENT_PAYING; 
			
			// while we have some payment left and an invoice to pay it against
			while ($this->_arrPayment['Balance'] > 0 && $arrInvoice = $selOutstandingInvoices->Fetch())
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
					$this->_arrPayment['Status'] = PAYMENT_BAD_PROCESS;
					
					// don't try any more invoices					
					break;
				}
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrPayment) === FALSE)
				{

				}
				
				$this->_rptPaymentReport->AddMessage(MSG_OK);
			}
			
			// check if we have spent all our money
			if ($this->_arrPayment['Balance'] == 0)
			{
				$this->_arrPayment['Status'] = PAYMENT_FINISHED;
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrPayment) === FALSE)
				{

				}
			}
			
			// Process successful
			$this->_rptPaymentReport->AddMessage(MSG_OK);
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
		
		// work out the balance
		$fltBalance = $this->_arrCurrentPayment['Balance'] - $fltPayment;
		
		// work out if this invoice has been paid in full
		if ($fltPayment == $this->_arrCurrentInvoice['Balance'])
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
		}
		
		// add an invoice payment record
		$arrInvoicePayment['Invoice']	= $this->_arrCurrentInvoice['Id'];
		$arrInvoicePayment['Payment']	= $this->_arrCurrentPayment['Id'];
		$arrInvoicePayment['Amount']	= $fltPayment;
		if ($this->_insInvoicePayment->Execute($arrInvoicePayment) === FALSE)
		{

		}
		
		// update the invoice
		if ($this->_ubiInvoice->Execute($this->_arrCurrentInvoice) === FALSE)
		{

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
			$this->_arrPayment['Status'] = PAYMENT_PAYING; 
			
			// while we have some payment left and an invoice to pay it against
			while ($this->_arrPayment['Balance'] > 0 && $arrInvoice = $selOutstandingInvoices->Fetch())
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
					$this->_arrPayment['Status'] = PAYMENT_BAD_PROCESS;
					
					// don't try any more invoices					
					break;
				}
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrPayment) === FALSE)
				{

				}
				
				$this->_rptPaymentReport->AddMessage(MSG_OK);
			}
			
			// check if we have spent all our money
			if ($this->_arrPayment['Balance'] == 0)
			{
				$this->_arrPayment['Status'] = PAYMENT_FINISHED;
				
				// update payment table
				if ($this->_ubiPayment->Execute($this->_arrPayment) === FALSE)
				{

				}
			}
			
			// Process successful
			$this->_rptPaymentReport->AddMessage(MSG_OK);
		}
		*/
	 }
 }


?>

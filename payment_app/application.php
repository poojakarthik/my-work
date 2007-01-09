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
 * @package		skeleton_application
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
echo "<pre>";

// Application entry point - create an instance of the application object
$appPayment = new ApplicationPayment($arrConfig);

// Execute the application
$appPayment->Execute();

// finished
echo("\n-- End of Payment --\n");
echo "</pre>";
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
		
		$this->_selGetPaymentFiles	= new StatementSelect("FileImport", "*", "Status = ".PAYMENT_WAITING);
		
		$arrColumns['Id']			= NULL;
		$arrColumns['Status']		= NULL;
		$this->_ubiPaymentFile		= new StatementUpdateById("FileImport", $arrColumns);
		
		$arrColumns = Array();
		$arrColumns['Payment']		= NULL;
		$arrColumns['SequenceNo']	= NULL;
		$arrColumns['File']			= NULL;
		$arrColumns['Status']		= NULL;
		$this->_insPayment			= new StatementInsert("Payment", $arrColumns);

		$arrColumns = Array();
		$arrColumns['Id']		= "Payment.Id";
		$arrColumns['Payment']	= "Payment.Payment";
		$arrColumns['FileType']	= "FileImport.FileType";
		$this->_selGetImportedPayments	= new StatementSelect("Payment JOIN FileImport ON Payment.File = FileImport.Id", $arrColumns, "Status = ".PAYMENT_IMPORTED, NULL, "1000");
		
		$arrColumns = Array();
		$arrColumns['Id']		= NULL;
		$arrColumns['Status']	= NULL;
		$this->_ubiSavePaymentStatus	= new StatementUpdateById("Payment");
		
		$this->_selGetNormalisedPayments	= new StatementSelect("Payment", "*", "Status = ".PAYMENT_WAITING, NULL, "1000");
		
		$this->_selOutstandingInvoices		= new StatementSelect("Invoice", "*", "Status = ".INVOICE_COMMITTED." OR Status = ".INVOICE_DISPUTED, "DueOn ASC", "1000");
		
		$this->_ubiPayment					= new StatementUpdateById("Payment");
		
		$this->_ubiInvoice					= new StatementUpdateById("Invoice");
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
		// import payments
		$this->Import();
		
		// normalise payments
		while($this->Normalise())
		{
		
		}
		
		// process payments
		while($this->Process())
		{
		
		}
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
		$this->_rptPaymentReport->AddMessage(MSG_IMPORTING_TITLE);
		$this->Framework->StartWatch();
	
		// Loop through the CDR File entries
		$intCount	= 0;
		$intPassed	= 0;
		$this->_selGetPaymentFiles->Execute();
		while ($arrFile = $selSelectCDRFiles->Fetch())
		{
			$intCount++;
			// Make sure the file exists
			if (!file_exists($arrFile['Location']))
			{
				// Report the error, and UPDATE the database with a new status, then move to the next file
				$arrColumns['Id']		= $arrFile['Id'];
				$arrColumns['Status']	= PAYMENT_BAD_IMPORT;
				$this->_ubiPaymentFile->Execute($arrFile);
				
				// Add to the Normalisation report
				$this->_rptPaymentReport->AddMessageVariables(MSG_FAIL_FILE_MISSING, Array('<Path>' => $arrFile['Location']));
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
				$this->_insPayment->Execute($arrData);
				
				// Increment sequence number
				$intSequence++;
			}
			$intPassed++;
		}
		
		// Report totals
		$arrReportLine['<Total>']		= $intCount;
		$arrReportLine['<Time>']		= $this->Framework->LapWatch();
		$arrReportLine['<Pass>']		= $intPassed;
		$arrReportLine['<Fail>']		= $intCount - $intPassed;
		$this->AddToNormalisationReport(MSG_IMPORT_FOOTER, $arrReportLine);
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
	 * @return	bool	returns true untill all Payments have been normalised
	 *
	 * @method
	 */
	function Normalise()
	{
		// get next 1000 payments
		$this->_selGetImportedPayments->Execute();
		$arrPayments = $this->_selGetImportedPayments->FetchAll();
		if (count($arrPayments) == 0)
		{
			// No payments left, so return false
			return FALSE;
		}
		
		foreach($arrPayments as $arrPayment)
		{
			// use payment module to decode the payment
			$arrNormalised = $this->_arrPaymentModules[$arrPayment['FileType']]->Normalise($arrPayment);
			if(!is_array($arrNormalised))
			{
				// An error has occurred
				switch($arrNormalised)
				{
					// TODO: Handle different errors
					default:
						Debug("An unknown error occurred with code ".(int)$arrNormalised.".");
						$intStatus = PAYMENT_BAD_NORMALISE;
				}
				$arrNormalised	= Array();
				$arrNormalised['Status']	= $intStatus;
				$arrNormalised['Id']		= $arrPayment['Id'];
				if (!$this->_ubiSavePaymentStatus->Execute($arrNormalised))
				{
					// TODO: Error
				}
				continue;
			}
			
			// save the payment to DB
			$arrNormalised['Status'] = PAYMENT_WAITING;
			if(!$this->_ubiSaveNormalisedPayment->Execute($arrNormalised))
			{
				// TODO: An error occurred
			}
		}
	}
		
	//------------------------------------------------------------------------//
	// Process
	//------------------------------------------------------------------------//
	/**
	 * Process()
	 *
	 * Process next 1000 Payment Records
	 *
	 * Process new CDRs
	 *
	 * @return	bool	returns true untill all Payments have been processed
	 *
	 * @method
	 */
	 function Process()
	 {
		// get next 1000 payments
		$this->_selGetNormalisedPayments->Execute();
		$arrPayments = $this->_selGetNormalisedPayments->FetchAll();
		
		foreach($arrPayments as $arrPayment)
		{
			// set current payment
			$this->_arrCurrentPayment = $arrPayment;
			
			// get a list of outstanding invoices for this account group
			//		(and account if we have one in $arrPayment) sorted oldest invoice first
			$arrWhere['AccountGroup'] = $arrPayment['AccountGroup'];
			$this->_selOutstandingInvoices->Execute($arrWhere);
			
			// set default status
			$this->_arrPayment['Status'] = PAYMENT_PAYING; 
			
			// while we have some payment left and an invoice to pay it against
			while ($this->_arrPayment['Balance'] && $arrInvoice = $this->_selOutstandingInvoices->Fetch())
			{
				// set current invoice
				$this->_arrCurrentInvoice = $arrInvoice;
				
				// apply payment against the invoice
				$fltBalance = $this->_PayInvoice();
				if ($fltBalance === FALSE)
				{
					// something went wrong
					//TODO!!!! - report it
					
					// set status
					$this->_arrPayment['Status'] = PAYMENT_BAD_PROCESS;
					
					// don't try any more invoices					
					break;
				}
				
				// update payment table
				$this->_ubiPayment->Execute($this->_arrPayment);
			}
			
			// check if we have spent all our money
			if ($this->_arrPayment['Balance'] == 0)
			{
				$this->_arrPayment['Status'] = PAYMENT_FINISHED;
				
				// update payment table
				$this->_ubiPayment->Execute($this->_arrPayment);
			}
		}
		
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
		$this->_insInvoicePayment->Execute($arrInvoicePayment);
		
		// update the invoice
		$this->_ubiInvoice->Execute($this->_arrCurrentInvoice);
		
		// save the balance
		$this->_arrCurrentPayment['Balance'] = $fltBalance;

		// return the balance
		return $fltBalance;
		
	 }
 }


?>

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
		While($this->Normalise())
		{
		
		}
		
		// process payments
		While($this->Process())
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
	 	//TODO!!!!
	 	// Make this work just like the Normalise->Import Method
		// Payment.Status = PAYMENT_IMPORTED
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
	 	//TODO!!!!
		// see Payment table description in table_descriptions.odt
		//
		// each payment type will need a module (similar to normalisation modules)
		// 	we have sample files for each format
		//
		// in payment type module
		//		match payment to an account group
		//		match payment to an account (optional, but we should always try)
		//		Balance = Amount
		//		Status = PAYMENT_WAITING || PAYMENT_BAD_IMPORT
		
		// get next 1000 payments
		//$arrPayments =
		//TODO!!!!
		
		foreach($arrPayments as $arrPayment)
		{
			// use payment module to decode the payment
			// TODO!!!!
			
			// save the payment to DB
			// TODO!!!!
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
		// $arrPayments =
		//TODO!!!!
		
		foreach($arrPayments as $arrPayment)
		{
			// set current payment
			$this->_arrCurrentPayment = $arrPayment;
			
			// get a list of outstanding invoices for this account group
			//		(and account if we have one in $arrPayment) sorted oldest invoice first
			// $selInvoices =
			//TODO!!!!
			
			// set default status
			$this->_arrPayment['Status'] = PAYMENT_PAYING; 
			
			// while we have some payment left and an invoice to pay it against
			while ($this->_arrPayment['Balance'] && $arrInvoice = $selInvoices->Fetch())
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
				// TODO!!!!
			}
			
			// check if we have spent all our money
			if ($this->_arrPayment['Balance'] == 0)
			{
				$this->_arrPayment['Status'] = PAYMENT_FINISHED;
				
				// update payment table
				// TODO!!!!
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
		//TODO!!!!
		
		// update the invoice
		//TODO!!!!
		
		// save the balance
		$this->_arrCurrentPayment['Balance'] = $fltBalance;

		// return the balance
		return $fltBalance;
		
	 }
 }


?>

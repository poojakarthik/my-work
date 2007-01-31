<?php
	
	//----------------------------------------------------------------------------//
	// Invoice.php
	//----------------------------------------------------------------------------//
	/**
	 * Invoice.php
	 *
	 * File containing Invoice Class
	 *
	 * File containing Invoice Class
	 *
	 * @file		Invoice.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Invoice
	//----------------------------------------------------------------------------//
	/**
	 * Invoice
	 *
	 * An Invoice in the Database
	 *
	 * An Invoice in the Database
	 *
	 *
	 * @prefix		inv
	 *
	 * @package		intranet_app
	 * @class		Invoice
	 * @extends		dataObject
	 */
	
	class Invoice extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Invoice
		 *
		 * Constructor for a new Invoice
		 *
		 * @param	Integer		$intId		The Id of the Invoice being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Invoice information and Store it ...
			$selInvoice = new StatementSelect ('Invoice', '*', 'Id = <Id>', null, 1);
			$selInvoice->useObLib (TRUE);
			$selInvoice->Execute (Array ('Id' => $intId));
			
			if ($selInvoice->Count () <> 1)
			{
				throw new Exception ('Invoice does not exist.');
			}
			
			$selInvoice->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Invoice', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Account
		//------------------------------------------------------------------------//
		/**
		 * Account()
		 *
		 * Get the Account the Invoice was Charged to
		 *
		 * Get the Account the Invoice was Charged to
		 *
		 * @return	Account
		 *
		 * @method
		 */
		
		function Account ()
		{
			return new Account ($this->Pull ('Account')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Charges
		//------------------------------------------------------------------------//
		/**
		 * Charges()
		 *
		 * Get the Charges the Invoice has
		 *
		 * Get the Charges the Invoice has
		 *
		 * @return	Charges
		 *
		 * @method
		 */
		
		public function Charges ()
		{
			$cgsCharges = new Charges ();
			$cgsCharges->Constrain ('Account'		, '=', $this->Pull ('Account')->getValue ());
			$cgsCharges->Constrain ('InvoiceRun'	, '=', $this->Pull ('InvoiceRun')->getValue ());
			return $cgsCharges;
		}
		
		//------------------------------------------------------------------------//
		// ServiceTotals
		//------------------------------------------------------------------------//
		/**
		 * ServiceTotals()
		 *
		 * Get a list of Service Totals on this Invoice
		 *
		 * Get a list of Service Totals on this Invoice
		 *
		 * @return	ServiceTotals
		 *
		 * @method
		 */
		
		public function ServiceTotals ()
		{
			$stlServiceTotals = new ServiceTotals;
			$stlServiceTotals->Constrain ('InvoiceRun',	'=',	$this->Pull ('InvoiceRun')->getValue ());
			$stlServiceTotals->Constrain ('Account',	'=',	$this->Pull ('Account')->getValue ());
			
			return $stlServiceTotals;
		}
		
		//------------------------------------------------------------------------//
		// ServiceTotal
		//------------------------------------------------------------------------//
		/**
		 * ServiceTotal()
		 *
		 * Get a Service Total
		 *
		 * Get a Service Totals on this Invoice
		 *
		 * @return	ServiceTotal
		 *
		 * @method
		 */
		
		public function ServiceTotal ($intServiceTotal)
		{
			$selServiceTotal = new StatementSelect (
				'ServiceTotal', 
				'Id', 
				'Id = <Id> AND InvoiceRun = <InvoiceRun>', 
				null, 
				1
			);
			
			$selServiceTotal->Execute (Array ('Id' => $intServiceTotal, 'InvoiceRun' => $this->Pull ('InvoiceRun')->getValue ()));
			
			if ($selServiceTotal->Count () <> 1)
			{
				throw new Exception ('Invoice does not exist.');
			}
			
			return new ServiceTotal ($intServiceTotal);
		}
		
		//------------------------------------------------------------------------//
		// CDRs
		//------------------------------------------------------------------------//
		/**
		 * CDRs()
		 *
		 * Get the CDRs the Invoice has
		 *
		 * Get the CDRs the Invoice has
		 *
		 * @return	CDRs_Invoiced
		 *
		 * @method
		 */
		
		public function CDRs ()
		{
			return new CDRs_Invoiced ($this);
		}
		
		//------------------------------------------------------------------------//
		// Dispute
		//------------------------------------------------------------------------//
		/**
		 * Dispute()
		 *
		 * Apply a Dispute against this Invoice
		 *
		 * Apply a Dispute against this Invoice
		 *
		 * @param	Float			$fltDisputed			The amount to set the Dispute as (inc. GST)
		 * @return	void
		 *
		 * @method
		 */
		
		public function Dispute ($fltDisputed)
		{
			$fltDisputed = str_replace ("$", "", $fltDisputed);
			
			$arrDispute = Array (
				"Disputed"		=> $fltDisputed,
				"Status"		=> INVOICE_DISPUTED
			);
			
			$updDispute = new StatementUpdate ('Invoice', 'Id = <Id>', $arrDispute);
			$updDispute->Execute ($arrDispute, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Resolve
		//------------------------------------------------------------------------//
		/**
		 * Resolve()
		 *
		 * Resolve a Dispute
		 *
		 * Resolve a Dispute
		 *
		 * @param	Integer		$intResolveMethod		(CONSTANT) The method in which this dispute will be resolved
		 * @param	Float		$fltAmount				The amount which will be changed to the Account, if customer to Pay $X.XX
		 * @return	void
		 *
		 * @method
		 */
		
		public function Resolve ($intResolveMethod, $fltAmount)
		{
			switch ($intResolveMethod)
			{
				case DISPUTE_RESOLVE_FULL_PAYMENT:
					$arrInvoice = Array (
					);
					
					break;
					
				case DISPUTE_RESOLVE_PARTIAL_PAYMENT:
					$arrInvoice = Array (
					);
					
					break;
					
				case DISPUTE_RESOLVE_NO_PAYMENT:
//					$arrInvoice = Array (
//						"Status"	=> INVOICE_COMMMITTED
//					);
					
//					$updDispute = new StatementUpdate ('Invoice', 'Id = <Id>', $arrInvoice);
//					$updDispute->Execute ($arrInvoice);
//						Invoice.Balance += Invoice.Disputed, Invoice.Status = INVOICE_COMMITTED
					break;
			}
			
			//	TODO!bash! URGENT need the following options (radio buttons)...
			//		Text === "Customer to pay full amount" => Invoice.Balance += Invoice.Disputed, Invoice.Status = INVOICE_COMMITTED
			//		Text === "Customer to pay $" [Input.Amount]
			//				Invoice.Balance += Input.Amount, Invoice.Disputed -= Input.Amount, Invoice.Status = ?????
			//
			//	TODO!flame! URGENT : Status ???? -->
			//		Text === "Payment NOT required" : Invoice.Status = ????
			//
			//	TODO!bash! auto add an account note (by employee) to say how this was resolved, use "Disputed Invoice Resolved : " same text as options
			//	TODO!bash! auto add an account note (by employee) when an invoice is disputed "Invoice Disputed $".DiputedAmount
			
			$arrResolve = Array (
				"Status"		=> INVOICE_DISPUTED_SETTLED
			);
			
			$updDispute = new StatementUpdate ('Invoice', 'Id = <Id> AND Status = ' . INVOICE_DISPUTED, $arrResolve);
			$updDispute->Execute ($arrResolve, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>

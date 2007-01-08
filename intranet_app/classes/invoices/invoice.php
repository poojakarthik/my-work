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
	 * @prefix	act
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
	}
	
?>

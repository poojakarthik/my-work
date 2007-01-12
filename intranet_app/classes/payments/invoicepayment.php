<?php
	
	//----------------------------------------------------------------------------//
	// invoicepayment.php
	//----------------------------------------------------------------------------//
	/**
	 * invoicepayment.php
	 *
	 * File containing InvoicePayment Class
	 *
	 * File containing InvoicePayment Class
	 *
	 * @file		invoicepayment.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// InvoicePayment
	//----------------------------------------------------------------------------//
	/**
	 * InvoicePayment
	 *
	 * An InvoicePayment in the Database
	 *
	 * An InvoicePayment in the Database
	 *
	 *
	 * @prefix	ivp
	 *
	 * @package		intranet_app
	 * @class		InvoicePayment
	 * @extends		dataObject
	 */
	
	class InvoicePayment extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new InvoicePayment
		 *
		 * Constructor for a new InvoicePayment
		 *
		 * @param	Integer		$intId		The Id of the InvoicePayment being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the InvoicePayment information and Store it ...
			$selInvoicePayment = new StatementSelect ('InvoicePayment', '*', 'Id = <Id>', null, 1);
			$selInvoicePayment->useObLib (TRUE);
			$selInvoicePayment->Execute (Array ('Id' => $intId));
			
			if ($selInvoicePayment->Count () <> 1)
			{
				throw new Exception ('InvoicePayment does not exist.');
			}
			
			$selInvoicePayment->Fetch ($this);
			
			// Construct the object
			parent::__construct ('InvoicePayment', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Payment
		//------------------------------------------------------------------------//
		/**
		 * Payment()
		 *
		 * Pulls the Payment object and returns its Information
		 *
		 * Pulls the Payment object and returns its Information
		 *
		 * @return	Payment
		 *
		 * @method
		 */
		
		public function Payment ()
		{
			return new Payment ($this->Pull ('Payment')->getValue ());
		}
	}
	
?>

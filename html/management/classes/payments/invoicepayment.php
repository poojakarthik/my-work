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
			$arrColumns = Array();
			$arrColumns['Id'] 		= "InvoicePayment.Id";
			$arrColumns['Invoice']	= "Invoice.Id";
			$arrColumns['Applied'] 	= "Payment.Amount - Payment.Balance";
			$arrColumns['InvBalance']	= "Invoice.Balance";
			$arrColumns['PaidOn']	= "DATE_FORMAT(Payment.PaidOn, '%e/%m/%Y')";
			$arrColumns['Type'] 	= "Payment.PaymentType";
			$arrColumns['TXN']		= "Payment.TXNReference";
			$arrColumns['Amount'] 	= "Payment.Amount";
			$arrColumns['PayBalance']	= "Payment.Balance";			
			$arrColumns['Status']		= "Payment.Status";
			
			//Pull information and store it
			$selSelect = new StatementSelect("InvoicePayment LEFT OUTER JOIN Invoice USING (invoice_run_id, Account), Payment",
							$arrColumns,
						"InvoicePayment.Id = <Id> AND Payment.Id = InvoicePayment.Payment", '', 1);
			$arrWhere = Array('Id'=>$intId);
			$intCount = $selSelect->Execute ($arrWhere);
			$arrResult = $selSelect->Fetch($this);
			
			if ($selSelect->Count () <> 1)
			{
				throw new Exception ('InvoicePayment does not exist.');
			}
			
			// fixing reversed payments
				if($arrResult['Status'] == 250)
				{
					$arrResult['Applied'] = 0;
				}
				
			$arrResult['TypeName'] = GetConstantDescription($arrResult['Type'], 'payment_type');
			
			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResult, 'PaymentDetails');
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

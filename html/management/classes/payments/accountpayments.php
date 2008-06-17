<?php
	
	//----------------------------------------------------------------------------//
	// AccountPayments.php
	//----------------------------------------------------------------------------//
	/**
	 * AccountPayments.php
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * @file		AccountPayments.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// AccountPayments
	//----------------------------------------------------------------------------//
	/**
	 * AccountPayments
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 *
	 * @prefix		acp
	 *
	 * @package		intranet_app
	 * @class		AccountPayments
	 * @extends		Search
	 */
	
	//class AccountPayments extends Search
	class AccountPayments extends dataObject
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Gets payment information
		 *
		 * Gets the payment information using a StatementSelect and outputs 
		 * to the page using the bypass method.
		 *
		 * @param 	Integer		$actAccount		The account for which the payments
		 *										are displayed
		 *
		 * @method
		 */
		
		function __construct ($actAccount)
		{
			//Create the array of columns required for the query
			$arrColumns = Array();
			$arrColumns['Id'] 		= "InvoicePayment.Id";
			$arrColumns['Invoice']	= "Invoice.Id";
			$arrColumns['PaidOn']	= "DATE_FORMAT(Payment.PaidOn, '%e/%m/%Y')";
			$arrColumns['Amount']	= "Payment.Amount";			
		
			//Pull information and store it
			$selSelect = new StatementSelect("InvoicePayment LEFT OUTER JOIN Invoice USING (InvoiceRun, Account), Payment",
							$arrColumns,
						"InvoicePayment.Account = <Id> AND Payment.Id = InvoicePayment.Payment", 'Payment.PaidOn DESC');
			$arrWhere = Array('Id' => $actAccount->Pull ('Id')->getValue());
			$intCount = $selSelect->Execute ($arrWhere);
			$arrResults = $selSelect->FetchAll ($this);
			
			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResults, 'AccountPayments');

		}
		

		

	}
	
?>

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
		 * Constructs an AccountPayment Searching Routine
		 *
		 * Constructs an AccountPayment Searching Routine
		 *
		 * @param	Account			$actAccount			The account we are viewing Payments for
		 *
		 * @method
		 */
		
		function __construct ($actAccount)
		{
			/* ORIGINAL 
			//Bash's "code" commented out
			// Construct the collation with the number of CDRs that are unbilled
			//parent::__construct ('AccountPayments', 'InvoicePayment', 'InvoicePayment');
			
			//$this->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());

			//return;
			*/
			
			//TODO!nate! Your re-written code for Payments for the accounts, is not working. !!!!  DONE
			//Grab AccountPayments
			//This is based on the SelectStatement-style constructor for InvoicePayment			
			$arrColumns = Array();
			$arrColumns['Id'] 		= "InvoicePayment.Id";
			$arrColumns['Invoice']	= "Invoice.Id";
			$arrColumns['PaidOn']	= "DATE_FORMAT(Payment.PaidOn, '%e/%m/%Y')";
			$arrColumns['Amount']	= "Payment.Amount";
			
		
			$selSelect = new StatementSelect("InvoicePayment LEFT OUTER JOIN Invoice USING (InvoiceRun, Account), Payment",
							$arrColumns,
						"InvoicePayment.Account = <Id> AND Payment.Id = InvoicePayment.Payment", 'Payment.PaidOn DESC');
							
			$selSelect->useObLib (TRUE);
			$arrWhere = Array('Id' => $actAccount->Pull ('Id')->getValue());
			$intCount = $selSelect->Execute ($arrWhere);
			
			//dont make a dataArray/dataObject (not sure which), but use a normal array instead
			
			$arrResults = $selSelect->FetchAll ($this);
			
			$selCount = $selSelect->Count();
			$GLOBALS['Style']->Paginate($arrResults, $selCount, 1, 20, 'AccountPayments');

			// Take the array of data from the query and push it onto the response
			$GLOBALS['Style']->InsertDOM($arrResults, 'AccountPayments');

		}
		

		

	}
	
?>

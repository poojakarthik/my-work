<?php
	
	//----------------------------------------------------------------------------//
	// Payments_new.php
	//----------------------------------------------------------------------------//
	/**
	 * Payments_new.php
	 *
	 * Contains the class that gets information about payments
	 *
	 * Contains the class that gets information about payments
	 *
	 * @file		Payments_new.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Nathan 'nate' Abussi 
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Payments_new
	//----------------------------------------------------------------------------//
	/**
	 * Payments_new
	 *
	 * Contains the class that gets information about payments
	 *
	 * Contains the class that gets information about payments
	 *
	 *
	 * @prefix		acp
	 *
	 * @package		intranet_app
	 * @class		Payments_new
	 * @extends		dataObject
	 */
	
	class Payments_new extends dataObject
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
			$arrColumns['Id']			= "Id";
			$arrColumns['PaidOn'] 		= "DATE_FORMAT(PaidOn, '%e/%m/%Y')";
			$arrColumns['Type']			= "PaymentType";
			$arrColumns['Amount']		= "Amount";
			$arrColumns['Applied']		= "Amount-Balance";
			$arrColumns['Balance'] 		= "Balance";
			$arrColumns['Status']		= "Status";			
		
			//Pull information and store it
			$selSelect = new StatementSelect("Payment",	$arrColumns,"Account = <Id>", 'Payment.PaidOn DESC');
			$arrWhere = Array('Id' => $actAccount->Pull ('Id')->getValue());
			$intCount = $selSelect->Execute ($arrWhere);
			$arrResults = $selSelect->FetchAll ($this);
			
			foreach ($arrResults as $intKey=>$arrResult)
			{
				$arrResults[$intKey]['TypeName'] = GetConstantDescription($arrResults[$intKey]['Type'], 'payment_type');
				// fixing reversed payments
				if($arrResults[$intKey]['Status'] == 250)
				{
					$arrResults[$intKey]['Applied'] = 0;
					$arrResults[$intKey]['StatusName'] = GetConstantDescription($arrResults[$intKey]['Status'], 'payment_status');
				}
			}
			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResults, 'Payments');

		}
		

		

	}
	
?>

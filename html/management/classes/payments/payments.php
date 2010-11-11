<?php

	//----------------------------------------------------------------------------//
	// Payments.php
	//----------------------------------------------------------------------------//
	/**
	 * Payments.php
	 *
	 * Contains the Class that Controls Payment Searching
	 *
	 * Contains the Class that Controls Payment Searching
	 *
	 * @file		Payments.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Payments
	//----------------------------------------------------------------------------//
	/**
	 * Payments
	 *
	 * Controls Searching for an existing Payment
	 *
	 * Controls Searching for an existing Payment
	 *
	 *
	 * @prefix		pay
	 *
	 * @package		intranet_app
	 * @class		Payments
	 * @extends		Search
	 */
	
	class Payments extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Payment Searching Routine
		 *
		 * Constructs an Payment Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Payments', 'Payment', 'Payment');
			$this->Order ('PaidOn', FALSE);
		}
		
		//------------------------------------------------------------------------//
		// Pay
		//------------------------------------------------------------------------//
		/**
		 * Pay()
		 *
		 * Adds new payment information to the database
		 *
		 * Adds new payment information to the database
		 *
		 * @param	Array		$arrDetails			Associative Array of payment information
		 * @return	Integer
		 *
		 * @method
		 */
		
		public function Pay ($arrDetails)
		{
			$arrData = Array (
				"AccountGroup"			=> $arrDetails ['AccountGroup'],
				"Account"				=> $arrDetails ['Account'],
				"PaidOn"				=> new MySQLFunction ('NOW()'),
				"PaymentType"			=> $arrDetails ['PaymentType'],
				"Amount"				=> $arrDetails ['Amount'],
				"TXNReference"			=> $arrDetails ['TXNReference'],
				"EnteredBy"				=> $arrDetails ['EnteredBy'],
				"Payment"				=> "",
				"Balance"				=> $arrDetails ['Amount'],
				"Status"				=> PAYMENT_WAITING,
				"created_datetime"		=> date('Y-m-d H:i:s')
			);
			
			$insService = new StatementInsert ('Payment', $arrData);
			return $insService->Execute ($arrData);
		}
	}
	
?>

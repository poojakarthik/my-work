<?php

	//----------------------------------------------------------------------------//
	// accounts.php
	//----------------------------------------------------------------------------//
	/**
	 * accounts.php
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * Contains the Class that Controls Account Searching
	 *
	 * @file		accounts.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Accounts
	//----------------------------------------------------------------------------//
	/**
	 * Accounts
	 *
	 * Controls Searching for an existing account
	 *
	 * Controls Searching for an existing account
	 *
	 *
	 * @prefix		acs
	 *
	 * @package		intranet_app
	 * @class		Accounts
	 * @extends		dataObject
	 */
	
	class Accounts extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Account Searching Routine
		 *
		 * Constructs an Account Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Accounts', 'Account', 'Account');
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Creates a new Account
		 *
		 * Creates a new Account
		 *
		 *
		 * @param	AccountGroup	$acgAccountGroup	[optional] The AccountGroup Object which this Account will be added to.
		 *												If this is null, then it is assumed a new account group will be created
		 * @param	Array			$arrDetails			The details of the new account (Tainted)
		 * @return	Account
		 *
		 * @method
		 */
		
		public function Add ($acgAccountGroup, $arrDetails)
		{
			$abnABN = new ABN ('ABN', '');
			if (!$abnABN->setValue ($arrDetails ['ABN']))
			{
				throw new Exception ('ABN');
			}
			
			$acnACN = new ACN ('ACN', '');
			if (!$acnACN->setValue ($arrDetails ['ACN']))
			{
				throw new Exception ('ACN');
			}
			
			$bmeBillingMethods = new BillingMethods ();
			if (!$bmeBillingMethods->setValue ($arrDetails ['BillingMethod']))
			{
				throw new Exception ('BillingMethod');
			}
			
			$arrData = Array (
				"BusinessName"		=> $arrDetails ['BusinessName'],
				"TradingName"		=> $arrDetails ['TradingName'],
				"ABN"				=> $abnABN->getValue (),
				"ACN"				=> $acnACN->getValue (),
				"Address1"			=> $arrDetails ['Address1'],
				"Address2"			=> $arrDetails ['Address2'],
				"Suburb"			=> $arrDetails ['Suburb'],
				"Postcode"			=> $arrDetails ['Postcode'],
				"State"				=> $arrDetails ['State'],
				"Country"			=> "AU",

				
				
				"BillingType"		=> "",
				"PrimaryContact"	=> "",
				"CustomerGroup"	=> "",
				"CreditCard"		=> "", 
				"AccountGroup"		=> "",
				"LastBilled"		=> null,
				"BillingDate"		=> "", 
				"BillingFreq"		=> "",
				"BillingFreqType"	=> "",
				"BillingMethod"		=> $arrDetails ['BillingMethod'],
				"PaymentTerms"		=> PAYMENT_TERMS_DEFAULT,
				"Archived"			=> 0
			);
			
			debug ($arrData);
			exit;
		}
	}
	
?>

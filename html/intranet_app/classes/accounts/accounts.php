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
		// ABN
		//------------------------------------------------------------------------//
		/**
		 * ABN()
		 *
		 * Get an Unarchived Account by ABN
		 *
		 * Get an Unarchived Account by ABN
		 *
		 * @param	ABN		$abnABN			The ABN# that we're wishing to view
		 * @method
		 */
		
		public function ABN (ABN $abnABN)
		{
			// Get the ABN's Value
			$strABN = $abnABN->getValue ();
			
			// Make sure the ABN is not Blank
			if ($strABN == '')
			{
				throw new Exception ('ABN Invalid');
			}
			
			// Get the Id of the Account
			$selAccount = new StatementSelect ('Account', 'Id', 'ABN = <ABN> AND Archived = 0', null, 1);
			$selAccount->Execute (Array ('ABN' => $strABN));
			
			if ($selAccount->Count () <> 1)
			{
				throw new Exception ('ABN not found');
			}
			
			$arrAccount = $selAccount->Fetch ();
			
			return new Account ($arrAccount ['Id']);
		}
		
		//------------------------------------------------------------------------//
		// ACN
		//------------------------------------------------------------------------//
		/**
		 * ACN()
		 *
		 * Get an Unarchived Account by ACN
		 *
		 * Get an Unarchived Account by ACN
		 *
		 * @param	ACN		$acnACN			The ACN# that we're wishing to view
		 * @method
		 */
		
		public function ACN (ACN $acnACN)
		{
			// Get the ACN's Value
			$strACN = $acnACN->getValue ();
			
			// Make sure the ACN is not Blank
			if ($strACN == '')
			{
				throw new Exception ('ACN Invalid');
			}
			
			// Get the Id of the Account
			$selAccount = new StatementSelect ('Account', 'Id', 'ACN = <ACN> AND Archived = 0', null, 1);
			$selAccount->Execute (Array ('ACN' => $strACN));
			
			if ($selAccount->Count () <> 1)
			{
				throw new Exception ('ACN not found');
			}
			
			$arrAccount = $selAccount->Fetch ();
			
			return new Account ($arrAccount ['Id']);
		}
		
		//------------------------------------------------------------------------//
		/**
		 * NameSearch()
		 *
		 * Get a list of Accounts which partially match a Business or Trading Name
		 *
		 * Get a list of Accounts which partially match a Business or Trading Name
		 *
		 * @param	String		$strName			The name searching for in the Businesss or Trading Name
		 * @method
		 */
		
		public function NameSearch ($strName)
		{
			// Make sure the Name is not Blank
			if ($strName == '')
			{
				throw new Exception ('Name Invalid');
			}
			
			// Get the matching Accounts
			$selAccounts = new StatementSelect ('Account', 'Id', 'BusinessName LIKE <BusinessName> OR TradingName LIKE <TradingName>');
			$selAccounts->Execute (
				Array (
					'BusinessName'	=> '%' . $strName . '%',
					'TradingName'	=> '%' . $strName . '%'
				)
			);
			
			// If there are more than 15 records, we cannot perform a search
			if ($selAccounts->Count () > 15)
			{
				throw new Exception ('Accounts Refine');
			}
			
			
			// Start the results array
			$oblarrAccounts = new dataArray ('Accounts');
			
			// Push the results
			foreach ($selAccounts->FetchAll () AS $arrAccount)
			{
				$oblarrAccounts->Push (new Account ($arrAccount ['Id']));
			}
			
			return $oblarrAccounts;
		}
	}
	
?>

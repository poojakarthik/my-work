<?php
	
	//----------------------------------------------------------------------------//
	// account.php
	//----------------------------------------------------------------------------//
	/**
	 * account.php
	 *
	 * File containing Account Class
	 *
	 * File containing Account Class
	 *
	 * @file		account.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Account
	//----------------------------------------------------------------------------//
	/**
	 * Account
	 *
	 * An account in the Database
	 *
	 * An account in the Database
	 *
	 *
	 * @prefix	act
	 *
	 * @package		intranet_app
	 * @class		Account
	 * @extends		dataObject
	 */
	
	class Account extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Account
		 *
		 * Constructor for a new Account
		 *
		 * @param	Integer		$intId		The Id of the Account being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccount = new StatementSelect ('Account', '*', 'Id = <Id>', null, 1);
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			
			if ($selAccount->Count () <> 1)
			{
				throw new Exception ('Account does not exist.');
			}
			
			$selAccount->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Account', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Update Account Information
		 *
		 * Update Information relating to an Account
		 *
		 * @param	Array		$arrAccount		An associative array representing new Account Information
		 * @return	Void
		 *
		 * @method
		 */
		 
		public function Update ($arrAccount)
		{
			$arrDetails = Array (
				"BusinessName"	=>	(($arrAccount ['BusinessName'])	? $arrAccount ['BusinessName']	: ''),
				"TradingName"	=>	(($arrAccount ['TradingName'])		? $arrAccount ['TradingName']	: ''),
				"ABN"			=>	(($arrAccount ['ABN'])				? $arrAccount ['ABN']			: ''),
				"ACN"			=>	(($arrAccount ['ACN'])				? $arrAccount ['ACN']			: ''),
				"Address1"		=>	(($arrAccount ['Address1'])			? $arrAccount ['Address1']		: ''),
				"Address2"		=>	(($arrAccount ['Address2'])			? $arrAccount ['Address2']		: ''),
				"Suburb"		=>	(($arrAccount ['Suburb'])			? $arrAccount ['Suburb']		: ''),
				"Postcode"		=>	(($arrAccount ['Postcode'])			? $arrAccount ['Postcode']		: ''),
				"State"			=>	(($arrAccount ['State'])			? $arrAccount ['State']			: '')
			);
			
			$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrDetails, 1);
			$updAccount->Execute ($arrDetails, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Contacts
		//------------------------------------------------------------------------//
		/**
		 * Contacts()
		 *
		 * Retrieves Contact List
		 *
		 * Retrieves Contact List
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		 
		public function Contacts ()
		{
			$oblarrContacts = new dataArray ('Contacts', 'Contact');
			
			$selContacts = new StatementSelect (
				'Contact', 
				'Id', 
				'(AccountGroup = <AccountGroup> AND CustomerContact = 1)' .
				'OR (Account = <Account> AND CustomerContact = 0)'
			);
			
			$selContacts->Execute (
				Array (
					'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
					'Account'		=> $this->Pull ('Id')->getValue ()
				)
			);
			
			foreach ($selContacts->FetchAll () as $arrContact)
			{
				$oblarrContacts->Push (new Contact ($arrContact ['Id']));
			}
			
			return $oblarrContacts;
		}
		
		//------------------------------------------------------------------------//
		// ArchiveStatus
		//------------------------------------------------------------------------//
		/**
		 * ArchiveStatus()
		 *
		 * Update Account Archive Status Information
		 *
		 * Update Account Archive Status Information (Cascade Against Service and Specific Contacts)
		 *
		 * @param	Array		$bolStatus		Boolean representing whether to Archive (true) or unarchive (false)
		 * @return	Void
		 *
		 * @method
		 */
		 
		public function ArchiveStatus ($bolStatus)
		{
			// If we want to Archive the Account
			if ($bolStatus == 1)
			{
				// Set up an Archive SET clause
				$arrArchive = Array (
					"Archived"	=>	1
				);
				

				// Cascade down to include the Services
				$updAccount = new StatementUpdate ('Service', 'Account = <Account>', $arrArchive);
				$updAccount->Execute ($arrArchive, Array ('Account' => $this->Pull ('Id')->getValue ()));
				
				
				// Now we want to include the Contacts
				
				// Here are the preconditions for changing a contact to archived
				// 1.	If the Contact is not a CustomerContact
				// 2.	If the Contact is a Customer Contact
					//	2.1		AND there are no other Unarchived Accounts in the Account Group
				
				$updAccount = new StatementUpdate ('Contact', 'Account = <Account> AND CustomerContact = 0', $arrArchive);
				$updAccount->Execute ($arrArchive, Array ('Account' => $this->Pull ('Id')->getValue ()));
				
				// Count the number of Unarchived Accounts in the Account Group
				$selNumAccounts = new StatementSelect (
					'Account',
					'count(*) AS length',
					'AccountGroup = <AccountGroup> AND Id != <Id> AND Archived = 0'
				);
				
				$selNumAccounts->Execute (
					Array (
						'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
						'Id'			=> $this->Pull ('Id')->getValue ()
					)
				);
				
				$arrLength = $selNumAccounts->Fetch ();
				
				// If there are no "other" Accounts that are unarchived, we want to archive the Customer Contacts
				if ($arrLength ['length'] == 0)
				{
					$updAccount = new StatementUpdate ('Contact', 'Account = <Account> AND CustomerContact = 1', $arrArchive);
					$updAccount->Execute ($arrArchive, Array ('Account' => $this->Pull ('Id')->getValue ()));
				}
				
				
				// Finally - Alter the Account itself
				$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrArchive, 1);
				$updAccount->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
			}
			else
			{
				// If we reach this block, then we want to unarchive the account.
				// But, this is not a Cascadable action. Any services or contacts
				// that need to be unarchived need to be done manually.
				// This is set to avoid conflicts in the system.
				
				// Set up an Archive SET clause
				$arrArchive = Array (
					"Archived"	=>	0
				);
				
				// Alter the Account itself
				$updAccount = new StatementUpdate ('Account', 'Id = <Id>', $arrArchive, 1);
				$updAccount->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
			}
		}
	}
	
?>

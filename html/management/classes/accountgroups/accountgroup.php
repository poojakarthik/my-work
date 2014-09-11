<?php
	
//----------------------------------------------------------------------------//
// accountgroup.php
//----------------------------------------------------------------------------//
/**
 * accountgroup.php
 *
 * File containing Account Group Class
 *
 * File containing Account Group Class
 *
 * @file		accountgroup.php
 * @language	PHP
 * @package		intranet_app
 * @author		Bashkim 'bash' Isai
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// AccountGroup
	//----------------------------------------------------------------------------//
	/**
	 * AccountGroup
	 *
	 * An Account Group in the Database
	 *
	 * An Account Group in the Database
	 *
	 *
	 * @prefix	agr
	 *
	 * @package		intranet_app
	 * @class		AccountGroup
	 * @extends		dataObject
	 */
	
	class AccountGroup extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Account Group Object
		 *
		 * Constructor for a new Account Group Object
		 *
		 * @param	Integer		$intId		The Id of the Account Group being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccountGroup = new StatementSelect ('AccountGroup', '*', 'Id = <Id>', null, '1');
			$selAccountGroup->useObLib (TRUE);
			$selAccountGroup->Execute (Array ('Id' => $intId));
			
			if ($selAccountGroup->Count () != 1)
			{
				throw new Exception ('No such Account Group');
			}
			
			$selAccountGroup->Fetch ($this);
			
			// Construct the object
			parent::__construct ('AccountGroup', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// getAccount
		//------------------------------------------------------------------------//
		/**
		 * getAccount()
		 *
		 * Get an Account
		 *
		 * Get an Account if it is Located in this Account Group
		 *
		 * @param	Integer		$intId		The Id of the Account being Retrieved
		 * @return	Account
		 *
		 * @method
		 */
		
		public function getAccount ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccount = new StatementSelect ('Account', 'Id', 'AccountGroup = <AccountGroup> AND Id = <Id>');
			$selAccount->Execute (Array ('AccountGroup' => $this->Pull ('Id')->getValue (), 'Id' => $intId));
			
			if ($arrAccount = $selAccount->Fetch ())
			{
				return new Account ($arrAccount ['Id']);
			}
			
			throw new Exception ('Account is not in this Account Group');
		}
		
		//------------------------------------------------------------------------//
		// getAccounts
		//------------------------------------------------------------------------//
		/**
		 * getAccounts()
		 *
		 * Get all associated Accounts
		 *
		 * Get all the Accounts associated with this account group
		 *
		 * @return	Accounts
		 *
		 * @method
		 */
		
		public function getAccounts ()
		{
			$acsAccounts = new Accounts;
			$acsAccounts->Constrain ('AccountGroup', '=', $this->Pull ('Id')->getValue ());
			
			return $acsAccounts;
		}
		
		//------------------------------------------------------------------------//
		// getContacts
		//------------------------------------------------------------------------//
		/**
		 * getContacts()
		 *
		 * Get all associated Contacts
		 *
		 * Get all the contacts associated with this account group
		 *
		 * @param	$bolOnlyFullAccessContacts	optional, Set to TRUE if you only want to retrieve the 
		 * 										contacts who can view all accounts belonging to the AccountGroup
		 * 										Defaults to FALSE
		 * @return	dataArray
		 *
		 * @method
		 */

		public function getContacts($bolOnlyFullAccessContacts = FALSE)
		{
			// Start the array
			$oblarrContacts = new dataArray('Contacts', 'Contact');
			
			if ($bolOnlyFullAccessContacts)
			{
				$strOnlyFullAccess = "AND CustomerContact = 1";
			}
			
			// Pull all the active contacts ...
			$selContacts = new StatementSelect('Contact', 'Id', "AccountGroup = <AccountGroup> AND Archived = 0 $strOnlyFullAccess");
			$selContacts->Execute (Array ('AccountGroup' => $this->Pull('Id')->getValue ()));
			
			foreach ($selContacts->FetchAll() as $arrContact)
			{
				$oblarrContacts->Push(new Contact($arrContact ['Id']));
			}
			
			return $oblarrContacts;
		}
		
		//------------------------------------------------------------------------//
		// getDirectDebits
		//------------------------------------------------------------------------//
		/**
		 * getDirectDebits()
		 *
		 * Get all associated Direct Debit Details
		 *
		 * Get all associated Direct Debit Details. Direct Debit Details
		 * is not stored in a Search object for security reasons. Therefore, 
		 * a manual search must be done.
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function getDirectDebits ()
		{
			// Start the array
			$oblarrDDRs = new dataArray ('DirectDebits', 'DirectDebit');
			
			// Pull all the active contacts ...
			$selDDR = new StatementSelect ('DirectDebit', 'Id', 'AccountGroup = <AccountGroup> AND Archived = 0');
			$selDDR->Execute (Array ('AccountGroup' => $this->Pull ('Id')->getValue ()));
			
			foreach ($selDDR->FetchAll () as $arrDDR)
			{
				$oblarrDDRs->Push (new DirectDebit ($arrDDR ['Id']));
			}
			
			return $oblarrDDRs;
		}
		
		//------------------------------------------------------------------------//
		// getDirectDebit
		//------------------------------------------------------------------------//
		/**
		 * getDirectDebit()
		 *
		 * Get a Direct Debit Bank Account if it belongs to an Account Group
		 *
		 * Get a Direct Debit Bank Account if it belongs to an Account Group
		 *
		 * @return	DirectDebit
		 *
		 * @method
		 */
		
		public function getDirectDebit ($intId)
		{
			// Pull all the active contacts ...
			$selDDR = new StatementSelect ('DirectDebit', 'Id', 'Id = <Id> AND AccountGroup = <AccountGroup>', null, 1);
			$selDDR->Execute (
				Array (
					'AccountGroup'	=> $this->Pull ('Id')->getValue (),
					'Id'			=> $intId
				)
			);
			
			if ($selDDR->Count () <> 1)
			{
				throw new Exception ('DDR Not Found');
			}
			
			return new DirectDebit ($intId);
		}
		
		//------------------------------------------------------------------------//
		// AddDirectDebit
		//------------------------------------------------------------------------//
		/**
		 * AddDirectDebit()
		 *
		 * Add a new Direct Debit Account to this Account Group
		 *
		 * Add a new Direct Debit Account to this Account Group
		 *
		 * @param	Array			$arrData		Associative array of Direct Debit Information
		 * @return	DirectDebit
		 *
		 * @method
		 */
		
		public function AddDirectDebit ($arrData)
		{
			$arrDirectDebit = Array (
				'AccountGroup'	=> $this->Pull('Id')->getValue (),
				'BankName'		=> $arrData['BankName'],
				'BSB'			=> $arrData['BSB'],
				'AccountNumber' => $arrData['AccountNumber'],
				'AccountName'	=> $arrData['AccountName'],
				'Archived'		=> 0,
				'created_on'	=> GetCurrentISODateTime(),
				'employee_id'	=> $arrData['employee_id']
			);
			
			$insDirectDebit = new StatementInsert ('DirectDebit');
			$intDirectDebit = $insDirectDebit->Execute ($arrDirectDebit);
			
			return new DirectDebit ($intDirectDebit);
		}
		
		//------------------------------------------------------------------------//
		// getCreditCards
		//------------------------------------------------------------------------//
		/**
		 * getCreditCards()
		 *
		 * Get all associated Credit Card Details
		 *
		 * Get all associated Credit Card Details. Credit Card Details
		 * is not stored in a Search object for security reasons. Therefore, 
		 * a manual search must be done.
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function getCreditCards ()
		{
			// Start the array
			$oblarrCCs = new dataArray ('CreditCards', 'CreditCard');
			
			// Pull all the active contacts ...
			$selCC = new StatementSelect ('CreditCard', 'Id', 'AccountGroup = <AccountGroup> AND Archived = 0');
			$selCC->Execute (Array ('AccountGroup' => $this->Pull ('Id')->getValue ()));
			
			foreach ($selCC->FetchAll () as $arrCC)
			{
				$oblarrCCs->Push (new CreditCard ($arrCC ['Id']));
			}
			
			return $oblarrCCs;
		}
		
		//------------------------------------------------------------------------//
		// getCreditCard
		//------------------------------------------------------------------------//
		/**
		 * getCreditCard()
		 *
		 * Get a Credit Card if it belongs to an Account Group
		 *
		 * Get a Credit Card if it belongs to an Account Group
		 *
		 * @return	CreditCard
		 *
		 * @method
		 */
		
		public function getCreditCard ($intId)
		{
			// Pull all the active contacts ...
			$selCC = new StatementSelect ('CreditCard', 'Id', 'Id = <Id> AND AccountGroup = <AccountGroup>', null, 1);
			$selCC->Execute (
				Array (
					'AccountGroup'	=> $this->Pull ('Id')->getValue (),
					'Id'			=> $intId
				)
			);
			
			if ($selCC->Count () <> 1)
			{
				throw new Exception ('CC Not Found');
			}
			
			return new CreditCard ($intId);
		}
		
		//------------------------------------------------------------------------//
		// AddCreditCard
		//------------------------------------------------------------------------//
		/**
		 * AddCreditCard()
		 *
		 * Add a new Credit Card to this Account Group
		 *
		 * Add a new Credit Card to this Account Group
		 *
		 * @param	Array			$arrData		Associative array of Credit Card Information
		 * @return	CreditCard
		 *
		 * @method
		 */
		
		public function AddCreditCard ($arrData)
		{
			if (!CheckCC ($arrData ['CardNumber'], $arrData ['CardType']))
			{
				throw new Exception ('Card Invalid');
			}
			
			$arrCreditCard = Array (
				'AccountGroup'	=> $this->Pull('Id')->getValue (),
				'CardType'		=> $arrData['CardType'],
				'Name'			=> $arrData['Name'],
				'CardNumber'	=> Encrypt($arrData['CardNumber']),
				'ExpMonth'		=> $arrData['ExpMonth'],
				'ExpYear'		=> $arrData['ExpYear'],
				'CVV'			=> Encrypt($arrData['CVV']),
				'Archived'		=> 0,
				'created_on'	=> GetCurrentISODateTime(),
				'employee_id'	=> $arrData['employee_id']
			);
			
			$insCreditCard = new StatementInsert ('CreditCard');
			$intCreditCard = $insCreditCard->Execute ($arrCreditCard);
			
			return new CreditCard ($intCreditCard);
		}
	}
	
?>

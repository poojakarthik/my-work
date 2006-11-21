<?php
	
//----------------------------------------------------------------------------//
// authenticatedcontact.php
//----------------------------------------------------------------------------//
/**
 * authenticatedcontact.php
 *
 * Provides an outlet for Authenticated Contacts
 *
 * Provides an outlet for Authenticated Contacts
 *
 * @file	authenticatedcontact.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// AuthenticatedContact
	//----------------------------------------------------------------------------//
	/**
	 * AuthenticatedContact
	 *
	 * Outlet for someone who is logged into the system
	 *
	 * Outlet for someone who is logged into the system
	 *
	 *
	 * @prefix	atc
	 *
	 * @package	client_app
	 * @extends	dataObject
	 */
	
	class AuthenticatedContact extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// AuthenticatedContact
		//------------------------------------------------------------------------//
		/**
		 * AuthenticatedContact()
		 *
		 * A person who is logged in
		 *
		 * A class representing a person who is logged in
		 *
		 * @method
		 */
		
		function __construct ()
		{
			// Contruct the ObLib object
			
			parent::__construct ("AuthenticatedContact");
			
			// Check their session is valid ...
			$selAuthenticated = new StatementSelect (
				"Contact", "*", 
				"Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW()"
			);
			$selAuthenticated->useObLib (TRUE);
			$selAuthenticated->Execute(Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
			
			// If the session is invalid - then throw an exception
			if ($selAuthenticated->Count () <> 1)
			{
				throw new Exception ("Class AuthenticatedContact could not instantiate because Session could not be Authenticated");
			}
			
			$selAuthenticated->Fetch ($this);
		}
		
		//------------------------------------------------------------------------//
		// checkPassword
		//------------------------------------------------------------------------//
		/**
		 * checkPassword()
		 *
		 * Check if the password is of this person
		 *
		 * Check if the password in the first parameter is of the contact
		 *
		 * @param	String	$strPassWord	The password we want to check is correct
		 *
		 * @return	Boolean					TRUE:	If the PassWord is correct
		 *									FALSE:	If the PassWord is incorrect
		 *
		 * @method
		 */
		
		public function checkPassword ($strPassWord)
		{
			// Check the password against the Id field in the database
			$selPasswordMatch = new StatementSelect (
				"Contact", "count(*) AS Matches", "Id = <Id> AND PassWord = SHA1(<PassWord>)"
			);
			
			$selPasswordMatch->Execute (
				Array (
					"Id" => 		$this->Pull ("Id")->getValue (),
					"PassWord" =>	$strPassWord
				)
			);
			
			$arrLength = $selPasswordMatch->Fetch ();
			
			// If there is at least 1 match, we're good
			return $arrLength ['Matches'] == 1;
		}
		
		//------------------------------------------------------------------------//
		// isCustomerContact
		//------------------------------------------------------------------------//
		/**
		 * isCustomerContact()
		 *
		 * Check if the contact is a customer contact
		 *
		 * Checks to see whether or not the AuthenticatedContact is a CustomerContact or
		 * a regular contact
		 *
		 * @return	Boolean					TRUE:	Contact is CustomerContact
		 *									FALSE:	Contact is not CustomerContact
		 *
		 * @method
		 */
		
		public function isCustomerContact ()
		{
			return $this->Pull ("CustomerContact")->getValue () == 1;
		}
		
		//------------------------------------------------------------------------//
		// getAccounts
		//------------------------------------------------------------------------//
		/**
		 * getAccounts()
		 *
		 * List of accessible accounts
		 *
		 * Gets a list of accounts that this person has access to. If this person is 
		 * not a customer contact, this method will throw an exception.
		 *
		 * @return	dataArray 0..* Account
		 *
		 * @method
		 */
		
		public function getAccounts ()
		{
			// If we're not a customer contact, we don't have multiple accounts - so throw an exception
			if (!$this->isCustomerContact ())
			{
				throw new Exception ("You cannot list accounts because you only have 1");
			}
			
			// Get a list of accounts for this person ...
			$selAccounts = new StatementSelect ("Account", "Id", "AccountGroup = <AccountGroup>");
			$selAccounts->Execute(Array("AccountGroup" => $this->Pull ("AccountGroup")->getValue ()));
			
			// Start an ObLib Array named Accounts containing Account Objects
			$oblarrAccounts = new dataArray ("Accounts", "Account");
			
			// Add each account to the Array
			while ($AccountId = $selAccounts->Fetch ())
			{
				$oblarrAccounts->Push (new Account ($this, $AccountId ['Id']));
			}
			
			// Return the ObLib array
			return $oblarrAccounts;
		}
		
		//------------------------------------------------------------------------//
		// getAccount
		//------------------------------------------------------------------------//
		/**
		 * getAccount()
		 *
		 * Get an account
		 *
		 * Get an account (identified by Id if specified, otherwise the Primary Account).
		 *
		 * @param	Integer	$intId			[Optional]	The ID of the account we wish to retrieve, otherwise Primary Account
		 *
		 * @return	Account
		 *
		 * @method
		 */
		
		public function getAccount ($Id=null)
		{
			// If the Contact is an Account Group Contact, then we want to validate against the Account Group rather than the Account
			// Otherwise - we want to authenticate against the Account in the Contact Profile
			
			if ($this->isCustomerContact ())
			{
				$selAccount = new StatementSelect ("Account", "Id", "Id = <Id> AND AccountGroup = <AccountGroup>");
				$selAccount->Execute
				(
					Array
					(
						// If we have an Id, use that - otherwise use the Primary Account
						"Id" => (($Id !== null) ? $Id : $this->Pull ("Account")->getValue ()),
						"AccountGroup" => $this->Pull ("AccountGroup")->getValue ()
					)
				);
			}
			else
			{
				$selAccount = new StatementSelect ("Account", "Id", "Id = <Id> AND Id = <Account>");
				$selAccount->Execute
				(
					Array
					(
						// If we have an Id, use that - otherwise use the Primary Account
						"Id" 		=> ($Id === null) ? $this->Pull ("Account")->getValue () : $Id,
						"Account"	=> $this->Pull ("Account")->getValue ()
					)
				);
			}
			
			// If we did not find an account
			// Or we don't have access to the account we want
			// Throw an Exception
			if ($selAccount->Count () == 0)
			{
				throw new Exception ("The account you requested could not be found");
			}
			
			// We've made it up to here - we're good
			
			// Return the Fields in an Array
			$arrAccount = $selAccount->Fetch ();
			
			// Return the Account we want
			return new Account ($this, $arrAccount ['Id']);
		}
		
		//------------------------------------------------------------------------//
		// getInvoice
		//------------------------------------------------------------------------//
		/**
		 * getInvoice()
		 *
		 * Retrieves an invoice.
		 *
		 * Returns an Invoice Object of a particular Invoice.
		 *
		 * @param	String	$intInvoice		The number of the Invoice wishing to be viewed
		 *
		 * @return	Invoice					The invoice wishing to be viewed (Authentication
		 * 									is handed in the Invoice Object.
		 *
		 * @method
		 */
		
		public function getInvoice ($intInvoice)
		{
			return new Invoice ($this, $intInvoice);
		}
		
		//------------------------------------------------------------------------//
		// getContacts
		//------------------------------------------------------------------------//
		/**
		 * getContacts()
		 *
		 * Retrieves Contacts.
		 *
		 * Returns a list of Contacts in the System if the User is a Customer Contact.
		 *
		 * @return	mixed					IF: The the AuthenticatedContact is Not a Customer Contact, Return False
		 *									ELSE: Return the Contacts Object
		 *
		 * @method
		 */
		
		public function getContacts ()
		{
			// If the person is not a Customer Contact
			if (!$this->isCustomerContact ())
			{
				// Do not return the Contacts object
				return false;
			}
			
			// We are authenticated - so return the Contacts object
			return new Contacts ($this);
		}
		
		//------------------------------------------------------------------------//
		// getContact
		//------------------------------------------------------------------------//
		/**
		 * getContact()
		 *
		 * Retrieves Contacts.
		 *
		 * Returns a list of Contacts in the System if the User is a Customer Contact.
		 *
		 * @param	Integer	$intInvoice		The number of the Invoice wishing to be viewed
		 *
		 * @return	mixed					IF: The the AuthenticatedContact is Not a Customer Contact, Return False
		 *									ELSE: Return the Contacts Object
		 *
		 * @method
		 */
		
		public function getContact ($intId)
		{	
			// If the person is not a customer contact 
			// And they are wishing to view a customer which is not themselves
			if (!$this->isCustomerContact () && $intId <> $this->Pull ("Id")->getValue ())
			{
				// Return false because they do not have access to their profile
				return false;
			}
			
			// Otherwise Return the Contact wishing to be viewed
			return new Contact ($this, $intId);
		}
	}
	
?>

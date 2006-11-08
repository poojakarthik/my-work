<?php
	
	class AuthenticatedContact extends dataObject
	{
		
		function __construct ()
		{
			parent::__construct ("AuthenticatedContact");
			
			// Check their session is valid ...
			$selAuthenticated = new StatementSelect (
				"Contact", "*", 
				"Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW()"
			);
			$selAuthenticated->useObLib (TRUE);
			$selAuthenticated->Execute(Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
			
			if ($selAuthenticated->Count () <> 1)
			{
				throw new Exception ("Class AuthenticatedContact could not instantiate because Session could not be Authenticated");
			}
			
			$rowContact = $selAuthenticated->Fetch ($this);
		}
		
		public function isCustomerContact ()
		{
			return $this->Pull ("CustomerContact")->getValue () == 1;
		}
		
		public function getAccounts ()
		{
			// If we're not a customer contact, we don't have multiple accounts - so die
			if (!$this->isCustomerContact ())
			{
				throw new Exception ("You cannot list accounts because you only have 1");
			}
			
			// Get a list of accounts for this person ...
			$selAccounts = new StatementSelect ("Account", "Id", "AccountGroup = <AccountGroup>");
			$selAccounts->Execute(Array("AccountGroup" => $this->Pull ("AccountGroup")->getValue ()));
			
			$oblarrAccounts = new dataArray ("Accounts", "Account");
			
			while ($AccountId = $selAccounts->Fetch ())
			{
				$oblarrAccounts->Push (new Account ($this, $AccountId ['Id']));
			}
			
			return $oblarrAccounts;
		}
		
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
						"Id" 		=> ($Id === null) ? $this->Pull ("Account")->getValue () : $Id,
						"Account"	=> $this->Pull ("Account")->getValue ()
					)
				);
			}
			
			if ($selAccount->Count () == 0)
			{
				throw new Exception ("The account you requested could not be found");
			}
			
			$arrAccount = $selAccount->Fetch ();
			
			return new Account ($this, $arrAccount ['Id']);
		}
		
		public function getInvoice ($intInvoice)
		{
			return new Invoice ($this, $intInvoice);
		}
		
		public function getContacts ()
		{
			if (!$this->isCustomerContact ())
			{
				return false;
			}
			
			return new Contacts ($this);
		}
		
		public function getContact ($Id)
		{
			if (!$this->isCustomerContact () && $Id <> $this->Pull ("Id")->getValue ())
			{
				throw new Exception ("No access to Contact");
			}
			
			return new Contact ($this, $Id);
		}
	}
	
?>

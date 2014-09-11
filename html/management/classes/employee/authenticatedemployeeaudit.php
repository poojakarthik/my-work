<?php
	
	//----------------------------------------------------------------------------//
	// authenticatedemployeeaudit.php
	//----------------------------------------------------------------------------//
	/**
	 * authenticatedemployeeaudit.php
	 *
	 * Authenticated Employee Audit Class
	 *
	 * Authenticated Employee Audit Class
	 *
	 * @file		authenticatedemployeeaudit.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.12
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */

	//----------------------------------------------------------------------------//
	// AuthenticatedEmployeeAudit
	//----------------------------------------------------------------------------//
	/**
	 * AuthenticatedEmployeeAudit
	 *
	 * Authenticated Employee Audit Information
	 *
	 * Contains information about the Accounts and Contacts that the Authenticated
	 * Employee has Access to
	 *
	 *
	 * @prefix		aea
	 *
	 * @package		intranet_app
	 * @class		AuthenticatedEmployeeAudit
	 * @extends		dataArray
	 */
	
	class AuthenticatedEmployeeAudit extends dataArray
	{
		
		//------------------------------------------------------------------------//
		// _oblintEmployee
		//------------------------------------------------------------------------//
		/**
		 * _oblintEmployee
		 *
		 * Current Employee Id
		 *
		 * Current Employee Id
		 *
		 * @type	AuthenticatedEmployeeSession
		 *
		 * @property
		 */
		
		private $_oblintEmployee;
		
		//------------------------------------------------------------------------//
		// _oblarrAccounts
		//------------------------------------------------------------------------//
		/**
		 * _oblarrAccounts
		 *
		 * The Audit Trail for Accounts
		 *
		 * The Audit Trail for Accounts
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		
		private $_oblarrAccounts;
		
		//------------------------------------------------------------------------//
		// _oblarrContacts
		//------------------------------------------------------------------------//
		/**
		// _oblarrContacts
		 *
		 * The Audit Trail for Contacts
		 *
		 * The Audit Trail for Contacts
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		
		private $_oblarrContacts;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Object for Personal Audit Trails
		 *
		 * Firstly, cleans up the Audit Trail to remove expired Audits, then
		 * pulls a list of the Accounts and Contacts in the audit trail that
		 * the AuthenticatedEmployee has access to
		 *
		 * @method
		 */
		
		function __construct ($intEmployee)
		{
			parent::__construct ('AuditList');
			
			// Store the Authenticated Employee
			$this->_oblintEmployee = $this->Push (new dataInteger ('Employee', $intEmployee));
			
			$this->_oblarrAccounts = $this->Push (new dataArray ('Accounts'));
			$this->_oblarrContacts = $this->Push (new dataArray ('Contacts'));
		}
		
		//------------------------------------------------------------------------//
		// RecordAccount
		//------------------------------------------------------------------------//
		/**
		 * RecordAccount()
		 *
		 * Record Account Request
		 *
		 * Record a Request to Access an Account. This is not used as much as
		 * RecordContact
		 *
		 * @param	Account			$actAccount		The Account being Accessed
		 * @return	Void
		 *
		 * @method
		 */
		
		public function RecordAccount (Account $actAccount)
		{
			//TODO!  This function doesn't seem to be working
			
			// Insert the Audit
			$arrAudit = Array (
				'employee_id'		=> $this->_oblintEmployee->getValue(),
				'account_id'		=> $actAccount->Pull('Id')->getValue(),
				'viewed_on'			=> new MySQLFunction("NOW()")
			);
			
			$insAudit = new StatementInsert ('employee_account_log', $arrAudit);
			$insAudit->Execute ($arrAudit);
			
			/* Declaring $oblstrAccount as a reference:	foreach ($this->_oblarrAccounts as &$oblstrAccount)
			 * does not seem to work in php 5.2.3
			 * which is what is being used on the flexdemo fe server.  I think it assumes it to be a reference
			 * if the object you are iterating through implements the iterator interface.
			 * At any rate, this functionality doesn't work anyway.  It does not add the Account to the session information
			 * and therefore the account is not shown in the "Recent Customers" popup of the old system
			 */
			foreach ($this->_oblarrAccounts as $oblstrAccount)
			{
				if ($oblstrAccount->getValue () == $actAccount->Pull ('Id')->getValue ())
				{
					$this->_oblarrAccounts->Pop ($oblstrAccount);
				}
			}

			$this->_oblarrAccounts->Push (new dataString ('Account', $actAccount->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// RecordContact
		//------------------------------------------------------------------------//
		/**
		 * RecordContact()
		 *
		 * Record Contact Audit
		 *
		 * Record a Request to Access a Contact
		 *
		 * @param	Contact			$cntContact		The Contact being Accessed
		 * @return	Void
		 *
		 * @method
		 */
		
		public function RecordContact (Contact $cntContact)
		{
			// Insert the Audit
			$arrAudit = Array (
				'employee_id'		=> $this->_oblintEmployee->getValue(),
				'account_id'		=> $cntContact->Pull('Account')->getValue(),
				'contact_id'		=> $cntContact->Pull('Id')->getValue(),
				'viewed_on		'	=> new MySQLFunction("NOW()")
			);
			
			$insAudit = new StatementInsert ('employee_account_log', $arrAudit);
			$insAudit->Execute ($arrAudit);
			
			// Make the list only 19 Contacts long (deleting items from the top first)
			$intLastToDelete = $this->_oblarrContacts->Length () - 20;
			
			$intI = 0;
			
			foreach ($this->_oblarrContacts as $oblstrContact)
			{
				++$intI;
				
				if ($intI <= $intLastToDelete)
				{
					$this->_oblarrContacts->Pop ($oblstrContact);
				}
			}
			
			// Make sure the item isn't already in the list
			foreach ($this->_oblarrContacts as $oblstrContact)
			{
				if ($oblstrContact->getValue () == $cntContact->Pull ('Id')->getValue ())
				{
					return;
				}
			}
			
			// Add the item to the list
			$this->_oblarrContacts->Push (new dataString ('Contact', $cntContact->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Output
		//------------------------------------------------------------------------//
		/**
		 * Output()
		 *
		 * Outputs the Data for XSLT
		 *
		 * Outputs the Data for XSLT
		 *
		 * @return	DOMDocument
		 *
		 * @method
		 */
		
		public function Output ()
		{
			$oblarrBase = new dataArray ('AuditList');
			
			$oblarrAccounts = $oblarrBase->Push (new dataArray ('Accounts', 'Account'));
			$oblarrContacts = $oblarrBase->Push (new dataArray ('Contacts', 'Contact'));
			
			foreach ($this->_oblarrAccounts as $oblstrAccount)
			{
				$oblarrAccounts->Push (new Account ($oblstrAccount->getValue ()));
			}
			
			foreach ($this->_oblarrContacts as $oblstrContact)
			{
				$cntContact [$oblstrContact->getValue ()] = new Contact ($oblstrContact->getValue ());
				//$cntContact [$oblstrContact->getValue ()]->PrimaryAccount ();
				$oblarrContacts->Push ($cntContact [$oblstrContact->getValue ()]);
			}
			
			return $oblarrBase->Output ();
		}
		
		//------------------------------------------------------------------------//
		// __sleep
		//------------------------------------------------------------------------//
		/**
		 * __sleep()
		 *
		 * Specific function for sleeping the Audit
		 *
		 * Specific function for sleeping the Audit
		 *
		 * @return	Array
		 *
		 * @method
		 */
		
		public function __sleep ()
		{
			$this->_sleepEmployee = $this->_oblintEmployee->getValue ();
			$this->_sleepAccounts = Array ();
			$this->_sleepContacts = Array ();
			
			foreach ($this->_oblarrAccounts as $oblstrAccount)
			{
				$this->_sleepAccounts [] = $oblstrAccount->getValue ();
			}
			
			foreach ($this->_oblarrContacts as $oblstrContact)
			{
				$this->_sleepContacts [] = $oblstrContact->getValue ();
			}
			
			return Array (
				"_sleepEmployee", 
				"_sleepAccounts",
				"_sleepContacts"
			);
		}
		
		//------------------------------------------------------------------------//
		// __wakeup
		//------------------------------------------------------------------------//
		/**
		 * __wakeup()
		 *
		 * Specific function for restarting the Audit
		 *
		 * Specific function for restarting the Audit
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function __wakeup ()
		{
			$this->__construct ($this->_sleepEmployee);
			
			foreach ($this->_sleepAccounts as $strAccount)
			{
				$this->_oblarrAccounts->Push (new dataString ('Account', $strAccount));
			}
			
			foreach ($this->_sleepContacts as $strContact)
			{
				$this->_oblarrContacts->Push (new dataString ('Contact', $strContact));
			}
			
			unset ($this->_sleepEmployee);
			unset ($this->_sleepAccounts);
			unset ($this->_sleepContacts);
		}
	}
	
?>

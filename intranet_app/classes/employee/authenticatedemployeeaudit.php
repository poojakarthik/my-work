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
			// Insert the Audit
			$arrAudit = Array (
				'Employee'		=> $this->_oblintEmployee->getValue (),
				'Account'		=> $actAccount->Pull ('Id')->getValue (), 
				'RequestedOn'	=> date ('Y-m-d H:i:s', mktime ())
			);
			
			$insAudit = new StatementInsert ('EmployeeAccountAudit', $arrAudit);
			$insAudit->Execute ($arrAudit);
			
			foreach ($this->_oblarrAccounts as &$oblstrAccount)
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
				'Employee'		=> $this->_oblintEmployee->getValue (),
				'Account'		=> $cntContact->Pull ('Account')->getValue (), 
				'Contact'		=> $cntContact->Pull ('Id')->getValue (), 
				'RequestedOn'	=> date ('Y-m-d H:i:s', mktime ())
			);
			
			$insAudit = new StatementInsert ('EmployeeAccountAudit');
			$insAudit->Execute ($arrAudit);
			
			foreach ($this->_oblarrContacts as $oblstrContact)
			{
				if ($oblstrContact->getValue () == $cntContact->Pull ('Id')->getValue ())
				{
					$this->_oblarrContacts->Pop ($oblstrContact);
				}
			}
			
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
				$oblarrContacts->Push (new Contact ($oblstrContact->getValue ()));
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

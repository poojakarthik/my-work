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
	 * @extends		dataCollection
	 */
	
	class AuthenticatedEmployeeAudit extends dataCollection
	{
		
		//------------------------------------------------------------------------//
		// _aemAuthenticatedEmployee
		//------------------------------------------------------------------------//
		/**
		 * _aemAuthenticatedEmployee
		 *
		 * Current Authenticated Employee
		 *
		 * The current Authenticated Employee that the Audit belongs to
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		
		private $_aemAuthenticatedEmployee;
		
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
		
		function __construct (AuthenticatedEmployee &$aemAuthenticatedEmployee)
		{
			// Store the Authenticated Employee
			$this->_aemAuthenticatedEmployee =& $aemAuthenticatedEmployee;
			
			parent::__construct ('AuditList');
			
			// Get the values of the Auditing Trail ...
			$selAuditTrail = new StatementSelect (
				"EmployeeAccountAudit",
				"MAX(RequestedOn) AS Latest, Account, Contact, RequestedOn", 
				"Employee = <Employee> GROUP BY Account",
				"Latest DESC",
				5
			);
			
			$selAuditTrail->Execute (
				Array (
					"Employee" => $this->_aemAuthenticatedEmployee->Pull ('Id')->getValue ()
				)
			);
			
			// Loop through each item
			foreach ($selAuditTrail->FetchAll () AS $arrAudit)
			{
				// Make an item in the array
				$oblarrAuditItem = $this->Push (
					new AccountContactAudit (
						new Account ($arrAudit ['Account']),
						($arrAudit ['Contact']) ? new Contact ($arrAudit ['Contact']) : null
					)
				);
			}
		}
		
		//------------------------------------------------------------------------//
		// RecordAccount
		//------------------------------------------------------------------------//
		/**
		 * RecordAccount()
		 *
		 * Record Account Request
		 *
		 * Record a Request to Access an Account
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
				'Employee'		=> $this->_aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'Account'		=> $actAccount->Pull ('Id')->getValue (), 
				'RequestedOn'	=> date ('Y-m-d H:i:s', mktime ())
			);
			
			$insAudit = new StatementInsert ("EmployeeAccountAudit");
			$insAudit->Execute ($arrAudit);
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
				'Employee'		=> $this->_aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'Account'		=> $cntContact->Pull ('Account')->getValue (), 
				'Contact'		=> $cntContact->Pull ('Id')->getValue (), 
				'RequestedOn'	=> date ('Y-m-d H:i:s', mktime ())
			);
			
			$insAudit = new StatementInsert ("EmployeeAccountAudit");
			$insAudit->Execute ($arrAudit);
		}
	}
	
?>

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
	 * Contains information about the most recently viewed Accounts and Contacts
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
		 * Pulls a list of the last 5 Accounts and Contacts from the Auditing Trail
		 * for the AuthenticatedEmployee
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
				"MAX(RequestedOn) AS Latest, Account, RequestedOn", 
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
		 * @param	Integer			$intId		The Id of the Account being Accessed
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
	}
	
?>

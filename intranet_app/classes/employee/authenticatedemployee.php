<?php

	//----------------------------------------------------------------------------//
	// authenticatedemployee.php
	//----------------------------------------------------------------------------//
	/**
	 * authenticatedemployee.php
	 *
	 * File for the AuthenticatedEmployee Class
	 *
	 * A file which contains the PHP code for an AuthenticatedEmployee
	 *
	 * @file	authenticatedemployee.php
	 * @language	PHP
	 * @package	intranet_app
	 * @author	Bashkim 'bash' Isai
	 * @version	6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license	NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
 
	//----------------------------------------------------------------------------//
	// AuthenticatedEmployee
	//----------------------------------------------------------------------------//
	/**
	 * AuthenticatedEmployee
	 *
	 * Manages an Employee logged into the System
	 *
	 * Manages the profile for an Employee who is currently logged into the System
	 *
	 *
	 * @prefix	aem
	 *
	 * @package	intranet_app
	 * @class	AuthenticatedEmployee
	 * @extends	dataObject
	 */
	
	class AuthenticatedEmployee extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _aeaAudit
		//------------------------------------------------------------------------//
		/**
		 * _aeaAudit
		 *
		 * Audit Trail
		 *
		 * An object which controls that Auditing of Information within the System
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		 
		private $_aeaAudit;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Created a new AuthenticatedEmployee
		 *
		 * Creates a new AuthenticatedEmployee based on the Cookie Variables
		 * (to help prevent against possible hacking)
		 *
		 * @method
		 */
		
		function __construct ()
		{
			// Contruct the ObLib object
			parent::__construct ("AuthenticatedEmployee");
			
			// Check their session is valid ...
			$selAuthenticated = new StatementSelect (
				"Employee",
				"*", 
				"Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW()",
				null,
				"1"
			);
			$selAuthenticated->useObLib (TRUE);
			$selAuthenticated->Execute(Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
			
			// If the session is invalid - then throw an exception
			if ($selAuthenticated->Count () <> 1)
			{
				throw new Exception ("Class AuthenticatedEmployee could not instantiate because Session could not be Authenticated");
			}
			
			$selAuthenticated->Fetch ($this);
			
			// Push an Audit Trail onto the Object
			$this->_aeaAudit =& $this->Push (new AuthenticatedEmployeeAudit ($this));
		}
		
		public function Audit ()
		{
			return $this->_aeaAudit;
		}
	}
	
?>

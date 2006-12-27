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
		 * An object which controls the Auditing of Information within the System
		 *
		 * @type	AuthenticatedEmployeeAudit
		 *
		 * @property
		 */
		 
		private $_aeaAudit;
		
		//------------------------------------------------------------------------//
		// _aepPriviledges
		//------------------------------------------------------------------------//
		/**
		 * _aepPriviledges
		 *
		 * Priviledge Management
		 *
		 * An object which controls the Priviledge Management in the System
		 *
		 * @type	AuthenticatedEmployeePriviledges
		 *
		 * @property
		 */
		 
		private $_aepPriviledges;
		
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
			parent::__construct ('AuthenticatedEmployee');
			
			// Check their session is valid ...
			$selAuthenticated = new StatementSelect ('Employee', '*', 'Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW()', null, '1');
			$selAuthenticated->useObLib (TRUE);
			$selAuthenticated->Execute(Array('Id' => $_COOKIE ['Id'], 'SessionId' => $_COOKIE ['SessionId']));
			
			// If the session is invalid - then throw an exception
			if ($selAuthenticated->Count () <> 1)
			{
				throw new Exception ('Class AuthenticatedEmployee could not instantiate because Session could not be Authenticated');
			}
			
			$selAuthenticated->Fetch ($this);
			
			
			
			
			// Get the Serialized session and pop it from the
			// Object so we can reconsititute it
			$strSession = $this->Pop ('Session')->getValue ();
			
			// If the string is empty, then we don't have anything to reconstitute
			// So we can just start an empty array. Otherwise if the string is filled, 
			// then we have to reconstitute it
			if ($strSession == "")
			{
				$this->Push (new dataArray ('Session'));
			}
			else
			{
				// Reconsititute the Session (Unserialize)
				$oblobjSession = unserialize ($strSession);
				
				// If the base Tag Name is not 'Session', then something is Wrong
				if ($oblobjSession->tagName () <> 'Session')
				{
					throw new Exception ('Possible hacking attempt');
				}
				
				// Save the Session Information to the Object
				$this->Push ($oblobjSession);
			}
			
			// Push an Audit Trail onto the Object
			$this->_aeaAudit =& $this->Push (new AuthenticatedEmployeeAudit ($this));
			
			
			// Start hte Priviledges System
			$this->_aepPriviledges = $this->Push (new AuthenticatedEmployeePriviledges ($this));
			
			// If Karma ...
			if ($this->Pull ('Karma')->getValue () <> 0)
			{
				sleep ($this->Pull ('Karma')->getValue ());
			}
		}
		
		//------------------------------------------------------------------------//
		// Audit
		//------------------------------------------------------------------------//
		/**
		 * Audit()
		 *
		 * Return the Audit Object
		 *
		 * Return the Audit Object
		 *
		 * @return	AuthenticatedEmployeeAudit
		 *
		 * @method
		 */
		
		public function Audit ()
		{
			return $this->_aeaAudit;
		}
		
		//------------------------------------------------------------------------//
		// Priviledges
		//------------------------------------------------------------------------//
		/**
		 * Priviledges()
		 *
		 * Return the Priviledges Object
		 *
		 * Return the Priviledges Object
		 *
		 * @return	AuthenticatedEmployeePriviledges
		 *
		 * @method
		 */
		
		public function Priviledges ()
		{
			return $this->_aepPriviledges;
		}
		
		//------------------------------------------------------------------------//
		// Save
		//------------------------------------------------------------------------//
		/**
		 * Save()
		 *
		 * Saves the Session Information to the Database
		 *
		 * Saves the Session Information to the Database
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Save ()
		{
			// Create an Array of the Fields we want to update ...
			// Which is only the Session Information in the Employee Table
			$arrEmployeeSession = Array (
				'Session'	=> serialize ($this->Pull ('Session'))
			);
			
			// Now that we can update the employees profile to include this information
			$updSession = new StatementUpdate ('Employee', 'Id = <Id>', $arrEmployeeSession);
			$updSession->Execute ($arrEmployeeSession, Array ('Id' => 1));
		}
	}
	
?>

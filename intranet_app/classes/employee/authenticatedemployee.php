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
		// _oblarrSession
		//------------------------------------------------------------------------//
		/**
		 * _oblarrSession
		 *
		 * Session Information
		 *
		 * Session Information
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		 
		private $_oblarrSession;
		
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
			$selEmployee = new StatementSelect (
				'Employee', 
				'*', 
				'Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW() AND Archived = 0', 
				null, 
				'1'
			);
			
			$selEmployee->useObLib (TRUE);
			$selEmployee->Execute(Array('Id' => $_COOKIE ['Id'], 'SessionId' => $_COOKIE ['SessionId']));
			
			// If the session is invalid - then throw an exception
			if ($selEmployee->Count () <> 1)
			{
				throw new Exception ('Class AuthenticatedEmployee could not instantiate because Session could not be Authenticated');
			}
			
			$selEmployee->Fetch ($this);
			
			
			
			
			// Get the Serialized session and pop it from the
			// Object so we can reconsititute it
			$strSession = $this->Pop ('Session')->getValue ();
			
			// If the string is empty, then we don't have anything to reconstitute
			// So we can just start an empty array. Otherwise if the string is filled, 
			// then we have to reconstitute it
			
			if ($strSession == "")
			{
				$this->_oblarrSession = $this->Push (new dataArray ('Session'));
			}
			else
			{
				// Reconsititute the Session (Unserialize)
				$oblobjSession = unserialize ($strSession);
				
				// If we are not using an Authenticated Employee session - die
				if (!($oblobjSession instanceOf dataArray) && !$oblobjSession != null)
				{
					throw new Exception ('Possible hacking attempt');
				}
				
				// Save the Session Information to the Object
				$this->_oblarrSession = $this->Push ($oblobjSession);
				
				foreach ($this->_oblarrSession as &$mixSessionItem)
				{
					if ($mixSessionItem instanceOf AuthenticatedEmployeeAudit)
					{
						$this->_aeaAudit =& $mixSessionItem;
					}
				}
			}
			
			if ($this->_aeaAudit == null)
			{
				$this->_aeaAudit = $this->_oblarrSession->Push (new AuthenticatedEmployeeAudit ($this->Pull ('Id')->getValue ()));
			}
			
			// Start the Priviledges System
			$this->_aepPriviledges = $this->Push (new AuthenticatedEmployeePriviledges ($this));
			
			// If Karma ...
			if ($this->Pull ('Karma')->getValue () < 0)
			{
				sleep (abs ($this->Pull ('Karma')->getValue ()));
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
				'Session'	=> serialize ($this->_oblarrSession)
			);
			
			// Now that we can update the employees profile to include this information
			$updSession = new StatementUpdate ('Employee', 'Id = <Id>', $arrEmployeeSession);
			$updSession->Execute ($arrEmployeeSession, Array ('Id' => 1));
		}
	}
	
?>

<?php

	//----------------------------------------------------------------------------//
	// AuthenticatedEmployeePrivileges.php
	//----------------------------------------------------------------------------//
	/**
	 * AuthenticatedEmployeePrivileges.php
	 *
	 * File for the AuthenticatedEmployeePrivileges Class
	 *
	 * File for the AuthenticatedEmployeePrivileges Class
	 *
	 * @file	AuthenticatedEmployeePrivileges.php
	 * @language	PHP
	 * @package	intranet_app
	 * @author	Bashkim 'bash' Isai
	 * @version	6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license	NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
 
	//----------------------------------------------------------------------------//
	// AuthenticatedEmployeePrivileges
	//----------------------------------------------------------------------------//
	/**
	 * AuthenticatedEmployeePrivileges
	 *
	 * Manages Access Privileges
	 *
	 * Manages Access Privileges to certain Sections of the System
	 *
	 *
	 * @prefix	aep
	 *
	 * @package	intranet_app
	 * @class	AuthenticatedEmployeePrivileges
	 * @extends	dataObject
	 */
	
	class AuthenticatedEmployeePrivileges extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _aemAuthenticatedEmployee
		//------------------------------------------------------------------------//
		/**
		 * aemAuthenticatedEmployee
		 *
		 * The AuthenticatedEmployee to run tests on
		 *
		 * The AuthenticatedEmployee to run tests on
		 *
		 * @type	AuthenticatedEmployee
		 *
		 * @property
		 */
		
		private $_aemAuthenticatedEmployee;
		
		//------------------------------------------------------------------------//
		// _oblarrPrivileges
		//------------------------------------------------------------------------//
		/**
		 * _oblarrPrivileges
		 *
		 * Priviledge Definition Array
		 *
		 * An Array of Permissions
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		
		private $_oblarrPrivileges;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Created a new AuthenticatedEmployeePrivileges
		 *
		 * Creates a new AuthenticatedEmployeePrivileges Object which controls access
		 * to particular parts of the System
		 *
		 * @method
		 */
		
		function __construct (AuthenticatedEmployee &$aemAuthenticatedEmployee)
		{
			// Contruct the ObLib object
			parent::__construct ('AuthenticatedEmployeePrivileges');
			
			$this->_aemAuthenticatedEmployee =& $aemAuthenticatedEmployee;
			
			$this->_oblarrPrivileges = $this->Push (new dataArray ('Permissions', 'Permission'));
			
			// Test each Permission
			foreach ($GLOBALS['Permissions'] AS $intKey => $intValue)
			{
				if (HasPermission ($this->_aemAuthenticatedEmployee->Pull ('Privileges')->getValue (), $intKey))
				{
					$this->_oblarrPrivileges->Push (new Permission ($intKey));
				}
			}
			
			return $this->_oblarrPrivileges;
		}
		
		//------------------------------------------------------------------------//
		// Validate
		//------------------------------------------------------------------------//
		/**
		 * Validate()
		 *
		 * Checks to see if they have access to a particular section
		 *
		 * Runs through validation to ensure that an AuthenticatedEmployee has permission
		 * to view a particular section of the web site
		 *
		 *
		 * @param	Integer		$intGuardName		The Constant Representation that we're testing against. 
		 *
		 * @return	Boolean							(TRUE/FALSE) Depending on whether or not permission was granted
		 *
		 * @method
		 */
		
		public function Validate ($intGuardValue)
		{
			return HasPermission ($this->_aemAuthenticatedEmployee->Pull ('Privileges')->getValue (), $intGuardValue);
		}
	}
	
?>

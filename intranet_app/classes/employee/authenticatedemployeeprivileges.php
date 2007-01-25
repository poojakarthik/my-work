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
		// _arrPrivileges
		//------------------------------------------------------------------------//
		/**
		 * _arrPrivileges
		 *
		 * Priviledge Definition Array
		 *
		 * An associative array of Boolean values to represent what areas a user has access to
		 *
		 * @type	Array [Associative => Boolean]
		 *
		 * @property
		 */
		
		private $_arrPrivileges;
		
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
			
			$this->_arrPrivileges = Array ();
			
			// Start the Antecedent at the Value that we have in the system
			$antecedent = intval ($this->_aemAuthenticatedEmployee->Pull ('Privileges')->getValue ());
			
			// Consequential Position
			$concequent = 1;
			
			// Work out what we have access to ...
			while ($antecedent > 0)
			{
				$this->_arrPrivileges [$concequent] = ($antecedent % 2 == 1) ? true : false;
				
				$antecedent = intval ($antecedent / 2);
				$concequent = $concequent + $concequent;
			}
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
		 *	WARNING :
		 *	Be aware that the value inside $intGuardValue is ALWAYS represented in Binary, whereas the key in
		 *  the array $this->_arrPrivileges is represented in Decimal. $intGuardValue is converted from 
		 *	Binary to Decimal during this comparison stage.
		 *	
		 *	This is done because when editing constant values, it's easier to keep track of Binary values
		 *	than it is to keep track of plain text values
		 *
		 *
		 * @return	Boolean							(TRUE/FALSE) Depending on whether or not permission was granted
		 *
		 * @method
		 */
		
		public function Validate ($intGuardValue)
		{
			// BIN -> DEC
			$intGuardValue = base_convert ($intGuardValue, 2, 10);
			
			// Check if it exists
			if (!isset ($this->_arrPrivileges [$intGuardValue]))
			{
				return false;
			}
			
			return $this->_arrPrivileges [$intGuardValue] == 1;
		}
	}
	
?>

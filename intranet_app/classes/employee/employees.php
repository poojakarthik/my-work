<?php

	//----------------------------------------------------------------------------//
	// employees.php
	//----------------------------------------------------------------------------//
	/**
	 * employees.php
	 *
	 * Contains the Class that Controls Employee Searching
	 *
	 * Contains the Class that Controls Employee Searching
	 *
	 * @file		employees.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Employees
	//----------------------------------------------------------------------------//
	/**
	 * Employees
	 *
	 * Controls Searching for an existing Employee
	 *
	 * Controls Searching for an existing Employee
	 *
	 *
	 * @prefix		ems
	 *
	 * @package		intranet_app
	 * @class		Employees
	 * @extends		dataObject
	 */
	
	class Employees extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Employee Searching Routine
		 *
		 * Constructs an Employee Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Employees', 'Employee', 'Employee');
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Creates a new Employee
		 *
		 * Creates a new Employee
		 *
		 * @param	AuthenticatedEmployee		$aemAuthenticatedEmployee			The currently logged in user
		 * @param	Array						$arrData							Raw Employee Data
		 * @return	Employee
		 * @method
		 */
		 
		public function Add (AuthenticatedEmployee $aemAuthenticatedEmployee, $arrData)
		{
			// Check the Username is not in use
			$selEmployee = new StatementSelect ('Employee', 'count(*) AS length', 'UserName = <UserName> AND Archived = 0');
			$selEmployee->Execute (Array ('UserName' => $arrData ['UserName']));
			$arrUserNames = $selEmployee->Fetch ();
			
			if ($arrUserNames ['length'] <> 0)
			{
				throw new Exception ('UserName Obtained Elsewhere');
			}
			
			$arrEmployee = Array (
				"FirstName"			=> $arrData ['FirstName'],
				"LastName"			=> $arrData ['LastName'],
				"Email"				=> $arrData ['Email'],
				"Extension"			=> $arrData ['Extension'],
				"Phone"				=> $arrData ['Phone'],
				"Mobile"			=> $arrData ['Mobile'],
				"UserName"			=> $arrData ['UserName'],
				"PassWord"			=> sha1 ($arrData ['PassWord']),
				"DOB"				=> date ("Y-m-d", mktime (0, 0, 0, $arrData ['DOB-month'], $arrData ['DOB-day'], $arrData ['DOB-year'])),
				"SessionId"			=> "",
				"SessionExpire"		=> date ("Y-m-d H:i:S"),
				"Session"			=> "",
				"Karma"				=> 0,
				"PabloSays"			=> PABLO_TIP_POLITE,
				"Privileges"		=> 0,
				"Archived"			=> 0
			);
			
			$insEmployee = new StatementInsert ('Employee');
			$intEmployee = $insEmployee->Execute ($arrEmployee);
			
			return new Employee ($intEmployee);
		}
	}
	
?>

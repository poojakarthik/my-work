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
	
	//class Employees extends Search
	class Employees extends dataObject
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Gets employee information
		 *
		 * Gets the employee information using a StatementSelect and outputs 
		 * to the page using the bypass method.
		 *
		 * @param 	Integer		$currPage		The current page to be displayed
		 * @param 	Integer		$rangeLength	The number of records displayed
		 *										at one time
		 *
		 * @method
		 */
		 
		function __construct ($currPage, $rangeLength)
		{
			
			//Get the results of the query (this includes LIMIT [for pagination
			//purposes])
			$rangeStart = ($currPage - 1) * $rangeLength;
			$selInvoice = new StatementSelect ('Employee', '*', 'Archived = <Archived>', NULL, $rangeStart . ', ' . $rangeLength );
			$selInvoice->Execute (Array ('Archived' => 0));
			$arrResults = $selInvoice->FetchAll ($this);

			//Find number of rows in the total query (not paginated)
			$selCount = new StatementSelect ('Employee', 'Count(*)', 'Archived = <Archived>');
	
			//Extract values for pagination
			$GLOBALS['Style']->Paginate($arrResults, $selCount, $currPage, $rangeLength, 'Employees');

			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResults, 'Employees');
		}
		
		function Sample ($intPage, $intLength)
		{
			return Search::Sample($intPage, $intLength);
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
				"DOB"				=> sprintf ("%04d", $arrData ['DOB-year']) . "-" .
									   sprintf ("%02d", $arrData ['DOB-month']) . "-" .  
									   sprintf ("%02d", $arrData ['DOB-day']),
				"SessionId"			=> "",
				"SessionExpire"		=> new MySQLFunction ("NOW()"),
				"Session"			=> "",
				"Karma"				=> 0,
				"PabloSays"			=> PABLO_TIP_POLITE,
				"Privileges"		=> 0,
				"Archived"			=> 0
			);
			
			$insEmployee = new StatementInsert ('Employee', $arrEmployee);
			$intEmployee = $insEmployee->Execute ($arrEmployee);
			
			return new Employee ($intEmployee);
		}
	}
	
?>

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
	}
	
?>

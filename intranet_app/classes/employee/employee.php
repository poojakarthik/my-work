<?php
	
	//----------------------------------------------------------------------------//
	// employee.php
	//----------------------------------------------------------------------------//
	/**
	 * employee.php
	 *
	 * File containing Employee Class
	 *
	 * File containing Employee Class
	 *
	 * @file		employee.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Employee
	//----------------------------------------------------------------------------//
	/**
	 * Employee
	 *
	 * An Employee in the Database
	 *
	 * An Employee in the Database
	 *
	 *
	 * @prefix	emp
	 *
	 * @package		intranet_app
	 * @class		Employee
	 * @extends		dataObject
	 */
	
	class Employee extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Employee
		 *
		 * Constructor for a new Employee
		 *
		 * @param	Integer		$intId		The Id of the Employee being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Employee information and Store it ...
			$selEmployee = new StatementSelect ('Employee', '*', 'Id = <Id>', null, 1);
			$selEmployee->useObLib (TRUE);
			$selEmployee->Execute (Array ('Id' => $intId));
			
			if ($selEmployee->Count () <> 1)
			{
				throw new Exception ('Employee does not exist.');
			}
			
			$selEmployee->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Employee', $this->Pull ('Id')->getValue ());
		}
	}
	
?>

<?php

	//----------------------------------------------------------------------------//
	// bugs.php
	//----------------------------------------------------------------------------//
	/**
	 * bugs.php
	 *
	 * Contains the Class that Controls Bug Searching
	 *
	 * Contains the Class that Controls Bug Searching
	 *
	 * @file		bugs.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Bugs
	//----------------------------------------------------------------------------//
	/**
	 * Bugs
	 *
	 * Controls Searching for an existing bug
	 *
	 * Controls Searching for an existing bug
	 *
	 *
	 * @prefix		acs
	 *
	 * @package		intranet_app
	 * @class		Bugs
	 * @extends		Search
	 */
	
	class Bugs extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Bug Searching Routine
		 *
		 * Constructs an Bug Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Bugs', 'Bug', 'Bug');
		}
		
		//------------------------------------------------------------------------//
		// Report
		//------------------------------------------------------------------------//
		/**
		 * Report()
		 *
		 * Report a new Bug
		 *
		 * Report a new Bug
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee		The Employee logged into the system
		 * @param	String					$strPageDetails					The HTML value of the Page
		 * @param	String					$strComment						The actual information about a bug
		 * @return	void
		 *
		 * @method
		 */
		
		public function Report (AuthenticatedEmployee $aemAuthenticatedEmployee, $strPageDetails, $strComment, $strSerialisedGET, $strSerialisedPOST)
		{
			$arrBug = Array (
				"CreatedBy"			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				"CreatedOn"			=> new MySQLFunction ("NOW()"),
				"PageName"			=> $_SERVER ['HTTP_REFERER'],
				"PageDetails"		=> $strPageDetails,
				"Comment"			=> $strComment,
				"SerialisedGET"		=> $strSerialisedGET, 
				"SerialisedPOST"	=> $strSerialisedPOST,
				"Status"			=> BUG_UNREAD
			);
			
			$insBug = new StatementInsert ('BugReport', $arrBug);
			$intBug = $insBug->Execute ($arrBug);
		}
	}
		
	
	//----------------------------------------------------------------------------//
	// Bug_list
	//----------------------------------------------------------------------------//
	/**
	 * Bug_list
	 *
	 * Contains the class that gets information about payments
	 *
	 * Contains the class that gets information about payments
	 *
	 *
	 * @prefix		bgl
	 *
	 * @package		intranet_app
	 * @class		Bug_list
	 * @extends		dataObject
	 */
	
	class Bug_list extends dataObject
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Gets bug list information
		 *
		 * Gets the bug information using a StatementSelect and outputs 
		 * to the page using the bypass method.
		 *
		 * @param 	Object		$objWhere		
		 *										
		 *
		 * @method
		 */
		
		function __construct ($objWhere, $intStart, $intLength)
		{
			//Create the array of columns required for the query
			$arrColumns = Array();
			$arrColumns['Id']				= "BugReport.Id";
			$arrColumns['CreatedOn'] 		= "DATE_FORMAT(BugReport.CreatedOn, '%e/%m/%Y')";
			$arrColumns['CreatedBy']		= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
			$arrColumns['AssignedTo']		= "CONCAT(Employee2.FirstName, ' ', Employee2.LastName)";
			$arrColumns['Comment']			= "BugReport.Comment";
			$arrColumns['Status']			= "BugReport.Status";
			$arrColumns['PageName'] 		= "BugReport.PageName";			
		
			$strTables = '(BugReport LEFT JOIN Employee ON (BugReport.CreatedBy = Employee.Id)) LEFT JOIN Employee AS Employee2 ON (BugReport.AssignedTo = Employee2.Id)';
			
			if($objWhere->Table('BugReportComment'))
			{
				$strTables = "($strTables) LEFT JOIN BugReportComment on (BugReport.Id = BugReportComment.BugReport)";
			}
			
			//Pull information and store it
			$selSelect = new StatementSelect($strTables, $arrColumns, $objWhere->WhereString());
			$intCount = $selSelect->Execute ($objWhere->WhereArray);
			$arrResults = $selSelect->FetchAll ($this);

			foreach ($arrResults as $intKey=>$arrResult)
			{
				$arrResults[$intKey]['Status'] = GetConstantDescription($arrResults[$intKey]['Status'], 'BugStatus');
				$arrPageName = explode('?', BaseName($arrResults[$intKey]['PageName']), 2);
				//TODO!Sean! Comment out this line to see the Oblib '&' symbol error
				$arrResults[$intKey]['PageName'] = $arrPageName[0];
			}
	

			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResults, 'Bugs');

		}
		

		

	}
	
?>

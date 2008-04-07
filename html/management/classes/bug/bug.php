<?php
	
	//----------------------------------------------------------------------------//
	// bug.php
	//----------------------------------------------------------------------------//
	/**
	 * bug.php
	 *
	 * File containing Bug Class
	 *
	 * File containing Bug Class
	 *
	 * @file		bug.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Bug
	//----------------------------------------------------------------------------//
	/**
	 * Bug
	 *
	 * An bug in the Database
	 *
	 * An bug in the Database
	 *
	 *
	 * @prefix	act
	 *
	 * @package		intranet_app
	 * @class		Bug
	 * @extends		dataObject
	 */
	
	class Bug extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Bug
		 *
		 * Constructor for a new Bug
		 *
		 * @param	Integer		$intId		The Id of the Bug being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
		
			$arrColumns = Array();
			$arrColumns['Id']				= "BugReport.Id";
			$arrColumns['CreatedOn'] 		= "DATE_FORMAT(BugReport.CreatedOn, '%e/%m/%Y')";
			$arrColumns['CreatedBy']		= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
			$arrColumns['CreatedById']		= "BugReport.CreatedBy";
			$arrColumns['Comment']			= "BugReport.Comment";
			$arrColumns['Status']			= "BugReport.Status";
			$arrColumns['PageName'] 		= "BugReport.PageName";
			$arrColumns['ClosedOn']			= "DATE_FORMAT(BugReport.ClosedOn,  '%e/%m/%Y')";
			$arrColumns['Resolution'] 		= "BugReport.Resolution";
			$arrColumns['AssignedTo'] 		= "CONCAT(Employee2.FirstName, ' ', Employee2.LastName)";
			
			$strTables = '(BugReport LEFT JOIN Employee ON (BugReport.CreatedBy = Employee.Id)) LEFT JOIN Employee AS Employee2 ON (BugReport.AssignedTo = Employee2.Id)';
			
			// Pull all the bug information and Store it ...
			/* $selBug = new StatementSelect ('BugReport', '*', 'BugReport.Id = <Id>', null, 1);
			$selBug->useObLib (TRUE);
			$selBug->Execute (Array ('Id' => $intId));
			
			if ($selBug->Count () <> 1)
			{
				throw new Exception ('Bug does not exist.');
			}
			
			$selBug->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Bug', $this->Pull ('Id')->getValue ());
			*/ 
			
			//Pull information and store it
			$selBug = new StatementSelect($strTables, $arrColumns, 'BugReport.Id = <Id>', null, 1);
			$intCount = $selBug->Execute (Array ('Id' => $intId));
			if ($selBug->Count () <> 1)
			{
				throw new Exception ('Bug does not exist.');
			}
			$arrResults = $selBug->Fetch ($this);


				$arrResults['Status'] = GetConstantDescription($arrResults['Status'], 'BugStatus');
				$arrPageName = explode('?', BaseName($arrResults['PageName']), 2);
				//TODO!Sean! Comment out this line to see the Oblib '&' symbol error
				$arrResults['PageName'] = $arrPageName[0];
				$arrResults['Comment'] = nl2br($arrResults['Comment']);
				$arrResults['Resolution'] = nl2br($arrResults['Resolution']);
	

			//Insert into the DOM Document
			$GLOBALS['Style']->InsertDOM($arrResults, 'Bug');
			

		}
	}
	
?>

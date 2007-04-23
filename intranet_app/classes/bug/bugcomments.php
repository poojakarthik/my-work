<?php
	
	//----------------------------------------------------------------------------//
	// bugcomments.php
	//----------------------------------------------------------------------------//
	/**
	 * bugcomments.php
	 *
	 * File containing Bug Comment Class
	 *
	 * File containing Bug Comment Class
	 *
	 * @file		bug.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Andrew White
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Bug Comments
	//----------------------------------------------------------------------------//
	/**
	 * Bug Comments
	 *
	 * Returns the comments for a particular bug
	 *
	 * Returns the comments for a particular bug
	 *
	 *
	 * @prefix	bco
	 *
	 * @package		intranet_app
	 * @class		Bug
	 * @extends		dataObject
	 */
	
	class BugComments extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for the bug comments
		 *
		 * Constructor for the bug comments
		 *
		 * @param	Integer		$intId		The Id of the bug that comments are being retrieved for
		 *
		 * @method
		 */
		
		function __construct ($intId=NULL)
		{
			if($intId)
			{
				$arrColumns = Array();
				$arrColumns['CreatedOn'] 		= "DATE_FORMAT(BugReportComment.CreatedOn, '%e/%m/%Y')";
				$arrColumns['CreatedBy']		= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
				$arrColumns['Comment']			= "BugReportComment.Comment";
				$arrColumns['BugReport']		= "BugReportComment.BugReport";
				
				$strTables = '(BugReportComment LEFT JOIN Employee ON (BugReportComment.CreatedBy = Employee.Id))';
				
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
				$selBugComments = new StatementSelect($strTables, $arrColumns, 'BugReportComment.BugReport = <Id>', null);
				$intCount = $selBugComments->Execute (Array ('Id' => $intId));
				$arrResults = $selBugComments->FetchAll ($this);
				
				//TODO!Tyson! Make this work
				//foreach($arrResults AS $intKey => $arrResult)
				//{
				//	$arrResults[$intkey]['Comment'] = nl2br($arrResults[$intkey]['Comment']);
				//}
				
				//Insert into the DOM Document
				$GLOBALS['Style']->InsertDOM($arrResults, 'BugComments');
			}
		}
		
		//------------------------------------------------------------------------//
		// AddComment
		//------------------------------------------------------------------------//
		/**
		 * Add Comment()
		 *
		 * Add a comment to a bug
		 *
		 * Add a comment to a bug
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee		The Employee logged into the system
		 * @param	Integer					$intBugReport					The Id of the bugreport to add comment to
		 * @param	String					$strComment						The actual information about a bug
		 * @return	void
		 *
		 * @method
		 */
		
		public function AddComment(AuthenticatedEmployee $aemAuthenticatedEmployee, $intBugReport, $strComment)
		{
			$arrBug = Array (
				"CreatedBy"			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				"CreatedOn"			=> new MySQLFunction ("NOW()"),
				"Comment"			=> $strComment,
				"BugReport"		=> $intBugReport
			);
			
			$insBug = new StatementInsert ('BugReportComment', $arrBug);
			$intBug = $insBug->Execute ($arrBug);
		}
	}
	
?>

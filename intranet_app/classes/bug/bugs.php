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
				"CreatedOn"			=> date ('Y-m-d H:i:s'),
				"PageName"			=> $_SERVER ['HTTP_REFERER'],
				"PageDetails"		=> $strPageDetails,
				"Comment"			=> $strComment,
				"SerialisedGET"		=> $strSerialisedGET, 
				"SerialisedPOST"	=> $strSerialisedPOST,
				"Status"			=> BUG_UNREAD
			);
			
			$insBug = new StatementInsert ('BugReport');
			$intBug = $insBug->Execute ($arrBug);
		}
	}
	
?>

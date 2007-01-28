<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		monitor_application
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// Create an Instance of the Monitor App
$appMonitor = new ApplicationMonitor($arrConfig);





//----------------------------------------------------------------------------//
// ApplicationMonitor
//----------------------------------------------------------------------------//
/**
 * ApplicationMonitor
 *
 * System Monitor Module
 *
 * System Monitor Module
 *
 *
 * @prefix		app
 *
 * @package		monitor_application
 * @class		ApplicationSkel
 */
 class ApplicationMonitor extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		
		$this->sqlQuery 				= new Query();
	}
	
	// return an array of status counts
 	function GetStatusCountCDR()
	{
		$arrOutput = Array();
		$strQuery = "SELECT Status, COUNT(Id) AS CountCDR FROM CDR GROUP BY Status";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Status']] = $arrRow['CountCDR'];
		}
		return $arrOutput;
	}
	
	// return an array of invalid FNNs
	function GetInvalidFNN()
	{
		/*
		SELECT * FROM `Service`
		WHERE FNN NOT LIKE '__________'
		AND FNN NOT LIKE '__________i'
		AND ISNULL(ClosedOn)
		AND ServiceType = 0
		*/
	
	}
	
	
	function GetBadDestination()
	{
		// clean output array
		$arrOutput = Array();
		
		$strQuery = "SELECT Status, COUNT(Id) AS CountCDR FROM CDR GROUP BY Status";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Status']] = $arrRow['CountCDR'];
		}
		return $arrOutput;
	}
	
	
	
 }


?>

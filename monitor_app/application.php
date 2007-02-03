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
 * @class		ApplicationMonitor
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
		
		$this->_selCDR = new StatementSelect("CDR JOIN FileImport ON CDR.File = FileImport.Id", "CDR.*, FileImport.FileType AS FileType", "CDR.Id = <Id>");
		$this->sqlQuery 				= new Query();
		
		// get record types
		$this->arrRecordType = Array();
		$selFindRecordType = new StatementSelect("RecordType", "*");
		$selFindRecordType->Execute();
		$arrRecordTypes = $selFindRecordType->FetchAll();
		foreach($arrRecordTypes AS $arrRecordType)
		{
			$this->arrRecordType[$arrRecordType['Id']] = $arrRecordType;
		}
	}
	
	// return an array of status counts
 	function CountCDRStatus($intStatus=FALSE)
	{
		if ($intStatus !== FALSE)
		{
			$strWhere = "WHERE Status = ".(int)$intStatus;
		}
		else
		{
			$strWhere = "";
		}
		
		$arrOutput = Array();
		$strQuery = "SELECT Status, COUNT(Id) AS CountCDR FROM CDR $strWhere GROUP BY Status";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Status']] = $arrRow['CountCDR'];
		}
		if ($intStatus !== FALSE)
		{
			return $arrOutput[(int)$intStatus];
		}
		else
		{
			return $arrOutput;
		}
	}
	
	// return an array of Status, RecordType counts
 	function CountCDRStatusRecordType($intStatus=FALSE)
	{
		if ($intStatus !== FALSE)
		{
			$strWhere = "WHERE Status = ".(int)$intStatus;
		}
		else
		{
			$strWhere = "";
		}
		
		$arrOutput = Array();
		$strQuery = "SELECT Status, RecordType, COUNT(Id) AS CountCDR FROM CDR $strWhere GROUP BY Status, RecordType";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Status']][$arrRow['RecordType']] = $arrRow['CountCDR'];
		}
		if ($intStatus !== FALSE)
		{
			return $arrOutput[(int)$intStatus];
		}
		else
		{
			return $arrOutput;
		}
	}
	
	// return a list of CDRs
	function ListCDR($arrWhere, $intStart, $intLimit)
	{
		// return 1 - 1000 CDRs
		$intLimit = (int)$intLimit;
		$intLimit = max(1, $intLimit);
		$intLimit = min(1000, $intLimit);
		
		$intStart = (int)$intStart;
		if ($intStart)
		{
			$strWhere = "Id > $intStart";
			foreach($arrWhere AS $strKey=>$strValue)
			{
				$strWhere .= " AND $strKey = <$strKey>";
			}
			$arrWhere[Id] = $intStart;
			$selCDR = new StatementSelect('CDR', '*', $strWhere, '', $intLimit);
		}
		else
		{
			$selCDR = new StatementSelect('CDR', '*', $arrWhere, '', $intLimit);
		}
		$selCDR->Execute($arrWhere);
		return $selCDR->FetchAll();
	}
	
	// return a single CDR
	function GetCDR($intId)
	{
		// get CDR
		if (!$this->_selCDR->Execute(Array('Id' => $intId)))
		{
			return FALSE;
		}
		else
		{
			return $this->_selCDR->Fetch();
		}
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
 }


?>

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
		$strQuery = "SELECT Status, COUNT(Id) AS `Count`, SUM(Cost) AS `Cost`, SUM(Charge) AS `Charge` FROM CDR $strWhere GROUP BY Status";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Status']] = $arrRow;
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
		$strQuery = "SELECT Status, RecordType, COUNT(Id) AS `Count`,  SUM(Cost) AS `Cost`, SUM(Charge) AS `Charge` FROM CDR $strWhere GROUP BY Status, RecordType";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Status']][$arrRow['RecordType']] = $arrRow;
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
	
	// return a list of rate groups for a service
	function ListServiceRateGroup($intService)
	{
		$intService = (int)$intService;
		if (!$intService)
		{
			return FALSE;
		}
		$strQuery  = "SELECT RateGroup.*, ServiceRateGroup.StartDateTime AS StartDateTime, ServiceRateGroup.EndDateTime AS EndDateTime\n";
		$strQuery .= " FROM ServiceRateGroup, RateGroup\n";
		$strQuery .= " WHERE RateGroup.Id = ServiceRateGroup.RateGroup\n";
		$strQuery .= " AND ServiceRateGroup.Service = $intService\n";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		$arrOutput = Array();
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[] = $arrRow;
		}
		return $arrOutput;
		
	}
	
	// return a list of rates for a service
	function ListServiceRate($intService)
	{
		$intService = (int)$intService;
		if (!$intService)
		{
			return FALSE;
		}
		
		$strQuery = "SELECT Rate.* FROM ServiceRateGroup, RateGroup, Rate, RateGroupRate\n";
		$strQuery .= " WHERE RateGroup.Id = RateGroupRate.RateGroup\n";
		$strQuery .= " AND Rate.Id = RateGroupRate.Rate\n";
		$strQuery .= " AND RateGroup.Id = ServiceRateGroup.RateGroup\n";
		$strQuery .= " AND ServiceRateGroup.Service = $intService\n";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		$arrOutput = Array();
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[] = $arrRow;
		}
		return $arrOutput;
	}
	
	// return a list of rate plans for a service
	function ListServiceRatePlan($intService)
	{
		$intService = (int)$intService;
		if (!$intService)
		{
			return FALSE;
		}
		
		$strQuery  = "SELECT RatePlan.*, ServiceRatePlan.StartDateTime AS StartDateTime, ServiceRatePlan.EndDateTime AS EndDateTime\n";
		$strQuery .= " FROM ServiceRatePlan, RatePlan\n";
		$strQuery .= " WHERE RatePlan.Id = ServiceRatePlan.RatePlan\n";
		$strQuery .= " AND ServiceRatePlan.Service = $intService\n";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		$arrOutput = Array();
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[] = $arrRow;
		}
		return $arrOutput;
	}
	
	// return a list of etech invoices
	function ListEtechInvoice()
	{
		$this->selEtechInvoices = new StatementSelect(	"CDREtech",
														"InvoiceRun AS BillingPeriod",
														"1",
														"InvoiceRun DESC",
														NULL,
														"InvoiceRun");
		
		$this->selEtechInvoices->Execute();
		return $this->selEtechInvoices->FetchAll();
	}
	
	// return a viXen/Etech invoice comparison
	function GetEtechInvoice($intEtechInvoice)
	{
		// TODO
	}
	
	// return a viXen/Etech CDR comparison
	function GetEtechCDR($intEtechCDR)
	{
		// TODO
	}
 }


?>

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
		
		$this->_selCDR 		= new StatementSelect("CDR JOIN FileImport ON CDR.File = FileImport.Id", "CDR.*, FileImport.FileType AS FileType", "CDR.Id = <Id>");
		$this->_selRate 	= new StatementSelect("Rate", "*", "Id = <Id>");
		$this->sqlQuery 	= new Query();
		
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
	
	// return an array of rate counts
 	function CountCDRRate($intRate=FALSE)
	{
		if ($intRate !== FALSE)
		{
			$strWhere = "WHERE Rate = ".(int)$intRate;
		}
		else
		{
			$strWhere = "";
		}
		
		$arrOutput = Array();
		$strQuery = "SELECT Rate.Id AS Rate, Rate.Name AS Name, Rate.Description AS Description, COUNT(CDR.Id) AS `Count`, SUM(CDR.Cost) AS `Cost`, SUM(CDR.Charge) AS `Charge` FROM CDR JOIN Rate ON (CDR.Rate = Rate.Id) $strWhere GROUP BY CDR.Rate";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Rate']] = $arrRow;
		}
		if ($intRate !== FALSE)
		{
			return $arrOutput[(int)$intRate];
		}
		else
		{
			return $arrOutput;
		}
	}
	
	// return an array of rate counts
 	function CountCompareCDRRate($strCompare, $intRate=FALSE)
	{
		if ($intRate !== FALSE)
		{
			$strWhere = "WHERE CDR.Rate = ".(int)$intRate;
		}
		else
		{
			$strWhere = "";
		}
		
		$arrOutput = Array();
		$strQuery = "SELECT Rate.Id AS Rate, Rate.Name AS Name, Rate.Description AS Description, COUNT(CDR.Id) AS `Count`, SUM(CDR.Cost) AS `Cost`, SUM(CDR.Charge) AS `Charge`, COUNT(CDR{$strCompare}.Id) AS `CompareCount`, SUM(CDR{$strCompare}.Charge) AS `CompareCharge` FROM CDR LEFT JOIN Rate ON (CDR.Rate = Rate.Id) LEFT JOIN CDR{$strCompare} ON (CDR{$strCompare}.VixenCDR = CDR.Id) $strWhere GROUP BY CDR.Rate";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		Debug($this->sqlQuery->Error());
		
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[$arrRow['Rate']] = $arrRow;
		}
		if ($intRate !== FALSE)
		{
			return $arrOutput[(int)$intRate];
		}
		else
		{
			return $arrOutput;
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
	
	// return a Comparison list of CDRs
	function ListCompareCDR($arrWhere, $strCompare, $intStart, $intLimit)
	{
		if (!$strCompare)
		{
			return FALSE;
		}
		
		// return 1 - 1000 CDRs
		$intLimit = (int)$intLimit;
		$intLimit = max(1, $intLimit);
		$intLimit = min(1000, $intLimit);
		
		$strTable = "CDR JOIN CDR$strCompare ON (CDR.Id = CDR{$strCompare}.VixenCDR)";
		
		$strData  = "CDR.*, CDR{$strCompare}.Id AS CompareId";
		$strData .= ", CDR{$strCompare}.RecordType AS CompareRecordType";
		$strData .= ", CDR{$strCompare}.Charge AS CompareCharge";
		$strData .= ", CDR{$strCompare}.SequenceNo AS CompareSequenceNo";
		$strData .= ", CDR{$strCompare}.Description AS CompareDescription";
		
		$intStart = (int)$intStart;
		$strWhere = "CDR.Id > $intStart";
		foreach($arrWhere AS $strKey=>$strValue)
		{
			$strFullKey = $strKey;
			if (strpos($strKey, '.') === FALSE)
			{
				$strFullKey = "CDR.$strKey";
			}
			$strWhere .= " AND $strFullKey = <$strKey>";
		}
		$arrWhere[Id] = $intStart;

		$selCDR = new StatementSelect($strTable, $strData, $strWhere, '', $intLimit);
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
	
	// return a single Rate
	function GetRate($intId)
	{
		// get Rate
		if (!$this->_selRate->Execute(Array('Id' => $intId)))
		{
			return FALSE;
		}
		else
		{
			return $this->_selRate->Fetch();
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
														"InvoiceRun",
														"1",
														"InvoiceRun DESC",
														NULL,
														"InvoiceRun");
		
		$this->selEtechInvoices->Execute();
		return $this->selEtechInvoices->FetchAll();
	}
	
	// return a viXen/Etech invoice comparison
	function ListEtechCDRsByRate($strEtechInvoice, $intRate, $intStart, $intLimit)
	{
		// return 1 - 1000 CDRs
		$intLimit = (int)$intLimit;
		$intLimit = max(1, $intLimit);
		$intLimit = min(1000, $intLimit);
		
		$intRate = (int)$intRate;
		$intStart = (int)$intStart;
		if ($intStart)
		{
			$strWhere = "CDREtech.Id > $intStart AND ";
		}
		else
		{
			$strWhere = "";
		}
		
		$strWhere .= "CDREtech.InvoiceRun = '$strEtechInvoice'";
		
		// only show rated CDRs
		$strWhere .= " AND (CDR.Status = 150 OR CDR.Status = 198 OR CDR.Status = 199)";
		
		// only show CDRs for the rate
		$strWhere .= " AND CDR.Rate = $intRate";
		
		$this->selEtechCDRs = new StatementSelect(	"CDREtech LEFT OUTER JOIN CDR ON (CDREtech.VixenCDR = CDR.Id) LEFT OUTER JOIN RecordType ON (CDREtech.RecordType = RecordType.Id) LEFT OUTER JOIN Rate ON (CDR.Rate = Rate.Id)",
													"CDREtech.*, (CDREtech.Charge - CDR.Charge) AS Difference, CDR.Cost AS CDRCost, CDR.Charge AS CDRCharge, RecordType.Name AS RecordTypeName, Rate.Name AS RateName",
													$strWhere,
													NULL,
													$intLimit);
		

		$this->selEtechCDRs->Execute();
		$arrCDRs = $this->selEtechCDRs->FetchAll();
		return $arrCDRs;
	}
	
	// return a viXen/Etech invoice comparison
	function ListEtechCDRs($strEtechInvoice, $intStart, $intLimit)
	{
		// return 1 - 1000 CDRs
		$intLimit = (int)$intLimit;
		$intLimit = max(1, $intLimit);
		$intLimit = min(1000, $intLimit);
		
		$intStart = (int)$intStart;
		if ($intStart)
		{
			$strWhere = "CDREtech.Id > $intStart AND ";
		}
		else
		{
			$strWhere = "";
		}
		
		$strWhere .= "CDREtech.InvoiceRun = '$strEtechInvoice'";
		
		// only show rated CDRs
		$strWhere .= " AND (CDR.Status = 150 OR CDR.Status = 198 OR CDR.Status = 199)";
		
		// destination mismatch -- WTF??? look at this later
		//$strWhere .= " AND CDR.Destination != CDREtech.Destination";
		$strWhere .= " AND CDR.Destination = CDREtech.Destination";
		
		// starting point
		$strWhere .= " AND CDREtech.Id > 100000";
		
		// IGNORE
		
		// calls  with charge within 1c
		$strWhere .= " AND ((CDREtech.Charge - CDR.Charge) > 0.01 OR (CDREtech.Charge - CDR.Charge) < -0.01)";
		
		// calls to 101
		$strWhere .= " AND CDREtech.Destination != '101'";
		
		// rated zero by etech
		$strWhere .= " AND CDREtech.Charge > 0";
		
		// National-08c-06f-01s-00m:70c10m was missing exs rate
		//$strWhere .= " AND CDR.Rate != 72";
		
		// 1900 rate (etech does not have a 28c flagfall)
		//$strWhere .= " AND CDR.Rate != 151";
		
		// other cost LOOK AT THIS LATER
		$strWhere .= " AND CDR.Rate != 153";
		$strWhere .= " AND CDR.Rate != 47";
		
		// All IDD Rates LOOK AT THIS LATER
		$strWhere .= " AND CDR.RecordType != 28 AND CDR.RecordType != 27";
		
		// NZ Mobile
		//$strWhere .= " AND CDR.Rate != 1636";
		
		// SMS20 - we are 20c x etech is 18c x -> Shared500 plan. published rate is 20c x
		$strWhere .= " AND CDR.Rate != 38";
		
		// SMS22 - we are 23c x etech is 20c x ->
		$strWhere .= " AND CDR.Rate != 39";
		
		// National-08c-00f-01s-00m - was 7.5cpm
		$strWhere .= " AND CDR.Rate != 69";
		
		// T3 ld NZ - we charge about 19c less per call ?? don't know why
		$strWhere .= " AND CDR.Rate != 7378";
		
		// virt voip nz - we charge less per call
		$strWhere .= " AND CDR.Rate != 2782";
		
		// National-08c-06f-01s-00m:70c10m			-> we rate 16c lower per call, or randomly more 1c - 6c
		//											-> this is probably a rate mismatch
		$strWhere .= " AND CDR.Rate != 72";
		
		// National-30c-18f-30s-00m					-> we rate lower per call
		//											-> 
		$strWhere .= " AND CDR.Rate != 19";
		
		//Mobile-30c-18f-30s-00m					-> we rate lower per call
		//											-> 
		$strWhere .= " AND CDR.Rate != 4";
		
		// National VOIP BH
		// we are 10c etech is 16c
		$strWhere .= " AND CDR.Rate != 91";
		
		// Mobile-30c-00f-01s-30m					-> etech not charrging the 30c minimum
		$strWhere .= " AND CDR.Rate != 115";
		
		
		// FLEET RATES
		//TODO!flame! I think rating is broken for this type of rate
		// etech have some calls < 180sec rated > 0
		// we have some calls > 180ser rated at 0
		
		//FleetNational-30c-00f-30s-00m:00c03m
		$strWhere .= " AND CDR.Rate != 14";
		
		//FleetMobile-30c-00f-30s-00m:00c03m
		$strWhere .= " AND CDR.Rate != 12";
		
		//FleetMobile-25c-00f-01s-00m:00c03m
		$strWhere .= " AND CDR.Rate != 96";
		
		//FleetMobile-40c-00f-30s-00m:00c03m
		$strWhere .= " AND CDR.Rate != 13";
		
		//FleetNational-40c-00f-30s-00m:00c03m
		$strWhere .= " AND CDR.Rate != 15";
		
		
		//PINNACLE RATES
		// etech charge 50c any time of day
		// we charge a different rate out of business hours
		$strWhere .= " AND CDR.Rate != 10";
		$strWhere .= " AND CDR.Rate != 26";
		$strWhere .= " AND CDR.Rate != 24";
		$strWhere .= " AND CDR.Rate != 25";
		$strWhere .= " AND CDR.Rate != 11";
		$strWhere .= " AND CDR.Rate != 9";
		$strWhere .= " AND CDR.Rate != 121";
		$strWhere .= " AND CDR.Rate != 89";
		$strWhere .= " AND CDR.Rate != 122";
		
		
		// National 14 is rating at 15C !!!!!!!!!!!!!!!!!!!!!!
		
		//Roaming-35
		// etech are rating at 17.69% markup on cost
		$strWhere .= " AND CDR.Rate != 40";
		
		// AAPT BandStep 4
		$strWhere .= " AND !(CDR.Carrier = 3 AND (CDR.CDR LIKE '%0004%'))";
		
		// INBOUND - don't show any
		$strWhere .= " AND CDR.ServiceType != 103";
		// Local-08c-00f-01s-00m:00c20m		-> charging at 0
		// National-11c-00f-01s-00m			-> we charge about 0.00185 more per second
		// Local-08c-00f-01s-04m			-> we are charging less but our rating is correct... is the rate wrong?  only one call on this atm
		
		
		
		$this->selEtechCDRs = new StatementSelect(	"CDREtech LEFT OUTER JOIN CDR ON (CDREtech.VixenCDR = CDR.Id) LEFT OUTER JOIN RecordType ON (CDREtech.RecordType = RecordType.Id) LEFT OUTER JOIN Rate ON (CDR.Rate = Rate.Id)",
													"CDREtech.*, (CDREtech.Charge - CDR.Charge) AS Difference, CDR.Cost AS CDRCost, CDR.Charge AS CDRCharge, RecordType.Name AS RecordTypeName, Rate.Name AS RateName",
													$strWhere,
													NULL,
													$intLimit);
		

		$this->selEtechCDRs->Execute();
		$arrCDRs = $this->selEtechCDRs->FetchAll();
		return $arrCDRs;
	}
	
	// return a viXen/Etech CDR comparison
	function GetEtechCDR($intEtechCDR)
	{
		$this->selEtechCDR = new StatementSelect(	"CDREtech",
													"*",
													"Id = <Id>");
		
		$arrColumns = Array();
		$arrColumns['Id']				= "CDR.Id";
		$arrColumns['FNN']				= "CDR.FNN";
		$arrColumns['Destination']		= "CDR.Destination";
		$arrColumns['StartDatetime']	= "CDR.StartDatetime";
		$arrColumns['EndDatetime']		= "CDR.EndDatetime";
		$arrColumns['Units']			= "CDR.Units";
		$arrColumns['AccountGroup']		= "CDR.AccountGroup";
		$arrColumns['Account']			= "CDR.Account";
		$arrColumns['Account']			= "CDR.Account";
		$this->selVixenCDR = new StatementSelect(	"CDR",
													"*",
													"Id = <Id>");
		
		$this->selEtechCDR->Execute(Array('Id' => $intEtechCDR));
		$arrResults = Array();
		$arrResults[]	= $this->selEtechCDR->Fetch();
		
		if ($arrResults[0]['VixenCDR'])
		{
			$this->selVixenCDR->Execute(Array('Id' => $arrResults[0]['VixenCDR']));
			$arrResults[]	= $this->selVixenCDR->Fetch();
		}
		return $arrResults;
	}
	
	// return a list of invoice coparisons CDR
	function GetInvoiceCompare()
	{
		$arrOutput = Array();
		$strQuery  = "SELECT InvoiceTemp.Account AS Account, ";
		$strQuery .= "InvoiceTemp.InvoiceRun AS InvoiceRun, ";
		$strQuery .= "(InvoiceTemp.Total + InvoiceTemp.Tax) AS VixenTotal, ";
		$strQuery .= "InvoiceEtech.Total AS EtechTotal, ";
		$strQuery .= "(InvoiceTemp.Total + InvoiceTemp.Tax) - InvoiceEtech.Total AS Dif ";
		$strQuery .= "FROM InvoiceTemp, InvoiceEtech ";
		$strQuery .= "WHERE InvoiceTemp.Account = InvoiceEtech.Account ";
		$strQuery .= "ORDER BY Dif ";
		
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		if (!$sqlResult)
		{
			Debug($this->sqlQuery->Error());
		}
		while ($arrRow = $sqlResult->fetch_assoc())
		{
			$arrOutput[] = $arrRow;
		}
		
		return $arrOutput;
	}
	
	function GetAccountServiceTotals($intAccount, $strInvoiceRun)
	{
		$selServiceTotals	= new StatementSelect(	"ServiceTotal",
													"*",
													"Account = <Account> AND " .
													"InvoiceRun = <InvoiceRun>"
													);
		
		$selServiceTotals->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun));
		$arrTotals = $selServiceTotals->FetchAll();
		return $arrTotals;
	}
	
	function GetAccountCDRTotals($intAccount, $strInvoiceRun)
	{
		$arrOutput = Array();
		$selCDRTotals		= new StatementSelect(	"CDR JOIN Rate ON (CDR.Rate = Rate.Id)",
													"Rate.Uncapped AS Uncapped, SUM(CDR.Charge) AS Charge, SUM(CDR.Cost) AS Cost, COUNT(CDR.Id) AS Count",
													"CDR.Account = <Account> AND " .
													"CDR.Credit = 0".
													" AND CDR.InvoiceRun = <InvoiceRun>" ,
													NULL,
													NULL,
													"Rate.Uncapped");
		
		$selCDRTotals->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun));
		$arrCDRTotals = $selCDRTotals->FetchAll();

		foreach($arrCDRTotals as $arrCDRTotal)
		{
			if ($arrCDRTotal['Uncapped'])
			{
				$arrOutput['Uncapped']	= $arrCDRTotal;
			}
			else
			{
				$arrOutput['Capped']	= $arrCDRTotal;
			}
		}
		
		return $arrOutput;
	}
	
	function GetAccountChargeTotals($intAccount, $strInvoiceRun)
	{
		$arrOutput = Array();
		$selCharges		= new StatementSelect(	"Charge",
													"SUM(Amount) AS Amount, COUNT(Id) AS Count, Nature",
													"Account = <Account> " .
													" AND InvoiceRun = <InvoiceRun>" ,
													NULL,
													NULL,
													"Nature");
		
		$selCharges->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun));
		$arrCharges = $selCharges->FetchAll();

		foreach($arrCharges as $arrCharge)
		{
			if ($arrCharge['Nature'] == 'DR')
			{
				$arrOutput['Debit']		= $arrCharge;
			}
			else
			{
				$arrOutput['Credit']	= $arrCharge;
			}
		}
		
		return $arrOutput;
	}
	
	function GetAccountCharges($intAccount, $strInvoiceRun)
	{
		$selCharges	= new StatementSelect(	"Charge",
													"*",
													"Account = <Account> AND " .
													"InvoiceRun = <InvoiceRun>"
													);
		
		$selCharges->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun));
		return $selCharges->FetchAll();
	}
	
	function GetTempInvoice($intAccount, $strInvoiceRun)
	{
		$selTempInvoice	= new StatementSelect(	"InvoiceTemp",
													"*",
													"Account = <Account> AND " .
													"InvoiceRun = <InvoiceRun>"
													);
		
		$selTempInvoice->Execute(Array('Account' => $intAccount, 'InvoiceRun' => $strInvoiceRun));
		return $selTempInvoice->FetchAll();
	}
	
	
 }


?>

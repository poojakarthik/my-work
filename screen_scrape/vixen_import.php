<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// vixen_import
//----------------------------------------------------------------------------//
/**
 * vixen_import
 *
 * Contains classes for importing a new Telco into vixen
 *
 * Contains classes for importing a new Telco into vixen
 *
 * @file		vixen_import.php
 * @language	PHP
 * @package		vixen_import
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenImport
//----------------------------------------------------------------------------//
/**
 * VixenImport
 *
 * Vixen import Module
 *
 * Vixen import Module
 *
 *
 * @prefix		obj
 *
 * @package		vixen_import
 * @class		VixenImport
 */
class VixenImport extends ApplicationBaseClass
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Importer
	 *
	 * Constructor for the Importer
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
		
		// config
		$this->arrConfig = $arrConfig;
		
		// db access
		$this->insRateGroupRate 		= new StatementInsert("RateGroupRate");
		$this->insRatePlanRateGroup 	= new StatementInsert("RatePlanRateGroup");
		$this->insServiceRateGroup		= new StatementInsert("ServiceRateGroup");
		$this->insServiceRatePlan		= new StatementInsert("ServiceRatePlan");
		$this->sqlQuery 				= new Query();
		$this->selServicesByType		= new StatementSelect(	"Service",
														"Id, FNN",
														"Account = <Account> AND ServiceType = <ServiceType>");
	}
	
	// ------------------------------------//
	// Insert Records
	// ------------------------------------//
	
	// insert a single RateGroupRate record 
	function InsertRateGroupRate($intRateGroup, $intRate)
	{
		$arrRateGroupRate['RateGroup']	= $intRateGroup;
		$arrRateGroupRate['Rate']		= $intRate;
		return $this->insRateGroupRate->Execute($arrRateGroupRate);
	}
	
	// insert a single RatePlanRateGroup record 
	function InsertRatePlanRateGroup($intRatePlan, $intRateGroup)
	{
		// Link RateGroup to RatePlan
		$arrRatePlanRateGroup['RateGroup']	= $intRateGroup;
		$arrRatePlanRateGroup['RatePlan']	= $intRatePlan;
		return $this->insRatePlanRateGroup->Execute($arrRatePlanRateGroup);
	}
	
	// insert a single RatePlanRateGroup record 
	function InsertServiceRateGroup($intService, $intRateGroup)
	{
		// insert into ServiceRateGroup
		$arrData['Service']			= $intService;
		$arrData['RateGroup']		= $intRateGroup;
		$arrData['CreatedBy']		= 22;	// Rich ;)
		$arrData['CreatedOn']		= date("Y-m-d");
		$arrData['StartDatetime']	= "2006-01-01 11:57:40";
		$arrData['EndDatetime']		= "2030-11-30 11:57:45";
		$this->insServiceRateGroup->Execute($arrData);
	}
	
	// insert a single ServiceRatePlan record
	function InsertServiceRatePlan($intService, $intRatePlan)
	{
		// insert into ServiceRatePlan
		$arrData['Service']			= $intService;
		$arrData['RatePlan']		= $intRatePlan;
		$arrData['CreatedBy']		= 22;	// Rich ;)
		$arrData['CreatedOn']		= date("Y-m-d");
		$arrData['StartDatetime']	= "2006-01-01 11:57:40";
		$arrData['EndDatetime']		= "2030-11-30 11:57:45";
		$this->insServiceRatePlan->Execute($arrData);
	}
	
	// ------------------------------------//
	// Create Links
	// ------------------------------------//
	
	// Create all RateGroupRate records
	function CreateRateGroupRate()
	{
		// get list of all rate groups
		$strQuery = "SELECT * FROM RateGroup";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		While ($arrRateGroup = $sqlResult->fetch_assoc())
		{
			if ($arrRateGroup['RecordType'] != 27 && $arrRateGroup['RecordType'] != 27)
			{
				// NON-IDD Rates
				// look for a matching rate
				$strQuery = "SELECT Id FROM Rate WHERE RecordType = {$arrRateGroup['RecordType']} AND Name LIKE '{$arrRateGroup['Name']}' LIMIT 1";
				$sqlRate = $this->sqlQuery->Execute($strQuery);
				$arrRate = $sqlRate->fetch_assoc();
				if ($arrRate['Id'])
				{
					// save the link
					$this->InsertRateGroupRate($arrRateGroup['Id'], $arrRate['Id']);
				}
				else
				{
					// single rate not found, look for a group of matching rates
					$strQuery = "SELECT Id FROM Rate WHERE RecordType = {$arrRateGroup['RecordType']} AND Name LIKE '{$arrRateGroup['Name']} - %'";
					$sqlRate = $this->sqlQuery->Execute($strQuery);
					While ($arrRate = $sqlRate->fetch_assoc())
					{
						// save the links
						$this->InsertRateGroupRate($arrRateGroup['Id'], $arrRate['Id']);
					}
				}
			}
			else
			{
				// IDD Rates
				// look for a group of matching rates
				$strQuery = "SELECT Id FROM Rate WHERE RecordType = {$arrRateGroup['RecordType']} AND Name LIKE '{$arrRateGroup['Name']} : %'";
				$sqlRate = $this->sqlQuery->Execute($strQuery);
				While ($arrRate = $sqlRate->fetch_assoc())
				{
					// save the links
					$this->InsertRateGroupRate($arrRateGroup['Id'], $arrRate['Id']);
				}
			}
		}
	}
	
	// create all RatePlanRateGroup records
	function CreateRatePlanRateGroup()
	{
		// for each defined RatePlan
		if (is_array($this->arrConfig['RatePlan']))
		{
			foreach ($this->arrConfig['RatePlan'] as $intRatePlan=>$arrRatePlan)
			{
				// get the RatePlan Id
				if ((int)$intRatePlan != $intRatePlan)
				{
					$strQuery = "SELECT Id FROM RatePlan WHERE Name = '$intRatePlan' LIMIT 1";
					$sqlRatePlan = $this->sqlQuery->Execute($strQuery);
					$arrPlan = $sqlRatePlan->fetch_assoc();
					$intRatePlan = $arrPlan['Id'];
					if (!(int)$intRatePlan)
					{
						//error
						$this->Error("RatePlan $intRatePlan not found");
						continue;
					}
				}
					
				// for each RecordType within the RatePlan
				foreach ($arrRatePlan as $intRecordType=>$intRateGroup)
				{
					// get RecordType Id
					if ((int)$intRecordType != $intRecordType)
					{
						$strQuery = "SELECT Code FROM RecordType WHERE Name = '$intRecordType' LIMIT 1";
						$sqlRecordType = $this->sqlQuery->Execute($strQuery);
						$arrRecordType = $sqlRecordType->fetch_assoc();
						$intRecordType = $arrRecordType['Code'];
						if (!(int)$intRecordType)
						{
							//error
							$this->Error("RecordType $intRecordType not found");
							continue;
						}
					}
					
					// get the RateGroup Id
					if ((int)$intRateGroup != $intRateGroup)
					{
						$strQuery = "SELECT Id FROM RateGroup WHERE RecordType = $intRecordType AND Name = '$intRateGroup' LIMIT 1";
						$sqlRateGroup = $this->sqlQuery->Execute($strQuery);
						$arrRateGroup = $sqlRateGroup->fetch_assoc();
						$intRateGroup = $arrRateGroup['Id'];
						if (!(int)$intRateGroup)
						{
							//error
							$this->Error("RateGroup $intRateGroup not found");
							continue;
						}
					}
					
					// link RatePlan to RateGroup
					$this->InsertRatePlanRateGroup($intRatePlan, $intRateGroup);
				}
			}
		}
		else
		{
			$this->Error("No Rate Plans Found");
			return FALSE;
		}
		
		return TRUE;
	}
	
	// ------------------------------------//
	// Errors
	// ------------------------------------//
	
	// report an error
	function Error($strError)
	{
		$this->strLastError = "$strError \n";
		$this->strErrorLog += "$strError \n";
	}
	
	// return the error log
	function ErrorLog()
	{
		return $this->strErrorLog;
	}
	
	// return the last error
	function LastError()
	{
		return $this->strLastError;
	}
	
	// ------------------------------------//
	// ADD
	// ------------------------------------//
	
	function AddCustomer($arrCustomer)
	{
	
	}
	
	function AddAccountGroup($arrAccountGroup)
	{
	
	}
	
	function AddAccount($arrAccount)
	{
	
	}
	
	function AddContact()
	{
	
	}
	
	// ------------------------------------//
	// IMPORT
	// ------------------------------------//
	
	function ImportRate()
	{
	
	}
	
	function ImportRateGroup()
	{
	
	}
	
	function ImportRatePlan()
	{
	
	}
	
	
	
	
	
	// ------------------------------------//
	// Match 
	// ------------------------------------//
	
	function MatchAccounts_RateGroups($start)
	{
		echo "Checking ".($start * 100)." - ".($start * 100 + 100)."\n";
		// Get acount details from the scrape
		$sql = "SELECT CustomerId, DataSerialized FROM ScrapeAccount ";
		$sql .= "LIMIT " . ($start * 100) . ", 100";
		$query = mysql_query ($sql);
		while ($row = mysql_fetch_assoc ($query))
		{
			$arrScrapeAccount = unserialize($row['DataSerialized']);
			$arrScrapeAccount['AccountId'] = (int)$row['CustomerId'];
			Decode($arrScrapeAccount);
		}
	}
	
	
	// CreateServiceRatePlan
	function CreateServiceRateGroup()
	{
	
	}
	
	// CreateServiceRateGroup
	function Decode($arrScrapeAccount)
	{
		//echo "Decoding\n";
		if (!is_array($arrScrapeAccount))
		{
			return FALSE;
		}
		
		$arrRates = Array();
				
		$insServiceRateGroup	= $GLOBALS['insServiceRateGroup'];
		$selServicesByType		= $GLOBALS['selServicesByType'];
		
		// for each RecordType
		foreach ($GLOBALS['arrRecordTypes'] AS $strName=>$intServiceType )
		{
			//echo $intServiceType."\n";
			
			// if we have a rate for this RecordType
			if ($arrScrapeAccount[$strName])
			{
				//if we have a conversion name for this rate
				if ($GLOBALS['arrRates'][$strName][$arrScrapeAccount[$strName]])
				{
					// add to rate report
					$GLOBALS['arrRateReport'][$strName][$arrScrapeAccount[$strName]] = $intRateGroup;
				
					if (!is_array($GLOBALS['arrRates'][$strName][$arrScrapeAccount[$strName]]))
					{
						$arrRateGroup = Array($GLOBALS['arrRates'][$strName][$arrScrapeAccount[$strName]]);
					}
					else
					{
						$arrRateGroup = $GLOBALS['arrRates'][$strName][$arrScrapeAccount[$strName]];
					}
					
					foreach($arrRateGroup as $intRateGroup)
					{
						//echo $intRateGroup."\n";
						// insert record
						
						$selServicesByType->Execute(Array('ServiceType' => $intServiceType, 'Account' => $arrScrapeAccount['AccountId']));
						$arrServices = $selServicesByType->FetchAll();
						// for each service of $intServiceType
						foreach($arrServices as $arrService)
						{
							// insert into ServiceRateGroup
							$arrData['Service']			= $arrService['Id'];
							$arrData['RateGroup']		= $intRateGroup;
							$arrData['CreatedBy']		= 22;	// Rich ;)
							$arrData['CreatedOn']		= date("Y-m-d");
							$arrData['StartDatetime']	= "2006-01-01 11:57:40";
							$arrData['EndDatetime']		= "2030-11-30 11:57:45";
							$insServiceRateGroup->Execute($arrData);
							//echo "{$arrService['Id']} - {$arrService['FNN']}\n";
						}
						//echo $arrScrapeAccount['AccountId']."\n";
					}
				}
				else
				{
					//error
					echo "No new rate found for : $intServiceType \t: {$arrScrapeAccount[$strName]}\n";
					
					// add to rate report
					$GLOBALS['arrRateReport'][$strName][$arrScrapeAccount[$strName]] = 0;
				}
			}
		}
		
		return TRUE;
	}
}


?>

#!/usr/bin/php
<?php

// outputs a CSV of IDD rates from the Etech scrape data

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");
	
	// run the script
	Run();
	
	//finish
	Die ();
	
	
	//#########################################################################
	
	function Run()
	{
		// display the header row
		ShowHeader();
		
		// Get acount details from the scrape
		$sqlQuery = new Query();
		$strQuery = "SELECT DataSerialised, AxisM FROM ScrapeRates";
		$sqlResult = $sqlQuery->Execute($strQuery);
		while ($row = $sqlResult->fetch_assoc())
		{
			$arrScrapeAccount = unserialize($row['DataSerialised']);
			$arrScrapeAccount['Carrier'] = $row['AxisM'];
			Decode($arrScrapeAccount);
		}
	}
	
	// display header row
	function ShowHeader()
	{
		echo "Id;";
		foreach($GLOBALS['arrDatabaseTableDefine']['Rate']['Column'] AS $strKey => $arrValue)
		{
			echo "$strKey;";
		}
		echo "\n";
	}
	
	// display rows for a single rate group
	function Decode($arrScrapeRate)
	{
		// setup db object
		$sqlQuery = new Query();
		
		// return on error
		if (!is_array($arrScrapeRate))
		{
			return FALSE;
		}
		
		// set RateGroup wide values
		$fltStdFlagfall		= $arrScrapeRate['StdFlag'];
		$fltExsFlagfall		= $arrScrapeRate['PostFlag'];
		$arrTitle 			= explode(': ',$arrScrapeRate['Title']);
		$strName 			= trim($arrTitle[1]);
		switch ($arrScrapeRate['Carrier'])
		{
			case 19:
				$strCarrier = 'Unitel';
				$intCarrier = 1;
				break;
			case 24:
				$strCarrier = 'Optus';
				$intCarrier = 2;
				break;
			case 26:
				$strCarrier = 'AAPT';
				$intCarrier = 3;
				break;
			default:
				$strCarrier = 'Unknown';
		}
		
		// display each record
		foreach ($arrScrapeRate['Rates'] as $arrRate)
		{
			// clean output array
			$arrOutput = Array();
			
			// get rate specific values
			$strDestination 	= $arrRate['Destination'];
			$intCapSet 			= Max($arrScrapeRate['SetCap'],  $arrRate['CapSet']);
			
			// set output array values
			$arrOutput['Name'] 				= "$strName : $strCarrier : $strDestination";
			$arrOutput['Description'] 		= "Calls to $strDestination via the $strCarrier network on the $strName plan";
			$arrOutput['StdRatePerUnit'] 	= number_format($arrRate['StdRate'] / 60, 8, '.', '');
			$arrOutput['ExsRatePerUnit'] 	= number_format($arrRate['PostCredit'] / 60, 8, '.', '');
			if ($intCapSet)
			{
				$arrOutput['CapUsage'] 		= Max($arrScrapeRate['CapTime'], $arrRate['CapSeconds']);
				$arrOutput['CapCost']		= Max($arrScrapeRate['MaxCost'], $arrRate['CapCost']);
			}
			$arrOutput['StdFlagfall'] 		= $fltStdFlagfall;
			$arrOutput['ExsFlagfall'] 		= $fltExsFlagfall;
			$arrOutput['ServiceType'] 		= 102;
			$arrOutput['RecordType'] 		= 28;
			$arrOutput['StdUnits'] 			= 1;
			$arrOutput['StartTime'] 		= '00:00:00';
			$arrOutput['EndTime'] 			= '23:59:59';
			$arrOutput['Monday'] 			= 1;
			$arrOutput['Tuesday'] 			= 1;
			$arrOutput['Wednesday'] 		= 1;
			$arrOutput['Thursday'] 			= 1;
			$arrOutput['Friday'] 			= 1;
			$arrOutput['Saturday'] 			= 1;
			$arrOutput['Sunday'] 			= 1;
			
			// try to find the destination code
			$strQuery = "SELECT Code FROM DestinationCode WHERE CarrierDescription LIKE '$strDestination' AND Carrier = $intCarrier LIMIT 1";
			$sqlResult = $sqlQuery->Execute($strQuery);
			$row = $sqlResult->fetch_assoc();
			if ($row['Code'])
			{
				$arrOutput['Destination'] 			= $row['Code'];
			}
			
			// display output row
			echo ";"; // Id
			foreach($GLOBALS['arrDatabaseTableDefine']['Rate']['Column'] AS $strKey => $arrValue)
			{
				echo $arrOutput[$strKey].";";
			}
			echo "\n";
		}
		
		
		return TRUE;
	}
	
?>

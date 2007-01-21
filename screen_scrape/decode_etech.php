<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// decode_etech
//----------------------------------------------------------------------------//
/**
 * decode_etech
 *
 * Contains all classes for decoding etech scrape data
 *
 * Contains all classes for decoding etech scrape data
 *
 * @file		decode_etech.php
 * @language	PHP
 * @package		vixen_import
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDecode
//----------------------------------------------------------------------------//
/**
 * VixenDecode
 *
 * etech decode Module
 *
 * etech decode Module
 *
 *
 * @prefix		obj
 *
 * @package		vixen_import
 * @class		VixenDecode
 */
 class VixenDecode extends ApplicationBaseClass
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
		
		// local config
		$this->arrConfig = $arrConfig;
		
		// setup db object
		$this->sqlQuery = new Query();
	}
	
	// ------------------------------------//
	// Decode Constants
	// ------------------------------------//
	
	// carrier code	
	function DecodeCarrier($intCarrierCode)
	{
		switch ($intCarrierCode)
		{
			case 19:
				$intCarrier = CARRIER_UNITEL;
				break;
			case 24:
				$intCarrier = CARRIER_OPTUS;
				break;
			case 26:
				$intCarrier = CARRIER_AAPT;
				break;
			default:
				$intCarrier = 0;
		}
		
		return $intCarrier;
	}
	
	// ------------------------------------//
	// Decode Records
	// ------------------------------------//
	
	// decode a group of IDD rate records
	function DecodeIDDGroupRate($arrScrapeRate)
	{
		// return on error
		if (!is_array($arrScrapeRate))
		{
			return FALSE;
		}
		
		// clean output array
		$arrFullOutput = Array();
		
		// set RateGroup wide values
		$fltStdFlagfall		= $arrScrapeRate['StdFlag'];
		$fltExsFlagfall		= $arrScrapeRate['PostFlag'];
		$arrTitle 			= explode(': ',$arrScrapeRate['Title']);
		$strName 			= trim($arrTitle[1]);
		
		// get carrier code
		$intCarrier			= $this->DecodeCarrier($arrScrapeRate['Carrier']);	
		
		// get carrier name
		if ($intCarrier)
		{
			$strCarrier 	= GetCarrierName($intCarrier);
		}
		else
		{
			$strCarrier 	= 'Unknown';
		}
		
		// for each record
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
			$sqlResult = $this->sqlQuery->Execute($strQuery);
			$row = $sqlResult->fetch_assoc();
			if ($row['Code'])
			{
				$arrOutput['Destination'] 			= $row['Code'];
			}
			
			$arrFullOutput[] = $arrOutput;
		}
		
		return $arrFullOutput;
	}
 }


?>

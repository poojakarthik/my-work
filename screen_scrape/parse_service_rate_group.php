#!/usr/bin/php
<?=system ("clear");?>

	=====================================================================================================
	WELCOME TO THE ETECH DATA PARSER (version 1.0)
	=====================================================================================================
	
<?php
	
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

	
	
	// set up global defs
	
	// rate report
	$GLOBALS['arrRateReport'] = Array();
	
	// Record Types
	$GLOBALS['arrRecordTypes'] = Array
	(
		//TODO!!!!
		"localrate"				=> 102,
		"natrate"				=> 102,
		"mobrate"				=> 102,
		"intrate"				=> 102,
		"service_equip_rate"	=> 102,
		
		"mobileunitel"			=> 101,
		"mobiletelstra"			=> 101,
		"mobileother"			=> 101,
		"mobilenational"		=> 101,
		"mobile1800"			=> 101,
		"mobilevoicemail"		=> 101,
		"mobilediverted"		=> 101,
		"mobilesms"				=> 101,
		"mobilemms"				=> 101,
		"mobiledata"			=> 101,
		"mobileinternational"	=> 101
	);
	
	// Rates
$arrRates['localrate']['Local 14/13c (T3CC)'] = intRateId;
$arrRates['localrate']['Local 14/11c (tb fleet, t3 local)'] = intRateId;
$arrRates['localrate']['Local 10c (VV)'] = intRateId;
$arrRates['localrate']['Local 13 (B39c)'] = intRateId;
$arrRates['localrate']['Local 16/14 (VT, BSC,T3CTM)'] = intRateId;
$arrRates['localrate']['Local 12c (B15ctm)'] = intRateId;
$arrRates['localrate']['VoiceTalk'] = intRateId;
$arrRates['localrate']['Tier 3 corporate Local Saver'] = intRateId;
$arrRates['localrate']['Local 17c (nat16)'] = intRateId;
$arrRates['localrate']['Local 11/11c'] = intRateId;
$arrRates['localrate']['Local 16.36 (res 18c gst)'] = intRateId;
$arrRates['localrate']['Tier 3 corporate Long Distance'] = intRateId;
$arrRates['localrate']['Local 18c'] = intRateId;
$arrRates['natrate']['Tier 3 corporate capped (6ff,8cpm)'] = intRateId;
$arrRates['natrate']['Tier 3 corporate Local (6ff,8cpm)'] = intRateId;
$arrRates['natrate']['Blue V.VOIP (0ff,10cpcall)'] = intRateId;
$arrRates['natrate']['39cent cap (6ff,8cpm)'] = intRateId;
$arrRates['natrate']['Bus. Saver Capped (7ff,9cpm)'] = intRateId;
$arrRates['natrate']['Blue 15c CTM  (6ff,6cpm)'] = intRateId;
$arrRates['natrate']['VoiceTalk (10 ff/12cpm) cap'] = intRateId;
$arrRates['natrate']['National 16'] = intRateId;
$arrRates['natrate']['Tier 3 corporate Mobile Saver (6.5ff,9cpm)'] = intRateId;
$arrRates['natrate']['Tier 3 corp. L D (0 ff,10cpm)'] = intRateId;
$arrRates['natrate']['7.5cpm no flag'] = intRateId;
$arrRates['natrate']['True Blue Fleet (6ff,9cpm)'] = intRateId;
$arrRates['natrate']['National 8c no ff'] = intRateId;
$arrRates['natrate']['5.5cpm no flag'] = intRateId;
$arrRates['natrate']['Residential (20ff,18cpm)'] = intRateId;
$arrRates['natrate']['Pinnacle (13c per call)'] = intRateId;
$arrRates['natrate']['7cpm 0 flag'] = intRateId;
$arrRates['natrate']['6 cpm 0 flag'] = intRateId;
$arrRates['mobrate']['Tier 3 corporate capped(27cpm, 20c min)'] = intRateId;
$arrRates['mobrate']['Tier 3 corporate Local Saver (9ff, 26cpm)'] = intRateId;
$arrRates['mobrate']['Virtual VOIP (30cpm, 30c min)'] = intRateId;
$arrRates['mobrate']['39cent cap (20min 27cpm)'] = intRateId;
$arrRates['mobrate']['Business Saver Capped (10ff, 27cpm)'] = intRateId;
$arrRates['mobrate']['Blue 15c CTM (15ff,15cpm)'] = intRateId;
$arrRates['mobrate']['VoiceTalk standard (10ff, 27cpm)'] = intRateId;
$arrRates['mobrate']['National 16 (30cpm, 20c min)'] = intRateId;
$arrRates['mobrate']['Tier 3 corporate Mobile Saver (6ff, 26cpm)'] = intRateId;
$arrRates['mobrate']['tier 3 corporate Long distance (5ff, 26cpm)'] = intRateId;
$arrRates['mobrate']['Pinnacle (50cper call)'] = intRateId;
$arrRates['mobrate']['CTM 26c no flag'] = intRateId;
$arrRates['mobrate']['True Blue Fleet (6ff, 25cpm)'] = intRateId;
$arrRates['mobrate']['Voicetalk Feb06 cap'] = intRateId;
$arrRates['mobrate']['CTM 22 no flag'] = intRateId;
$arrRates['mobrate']['CTM 30 no ff'] = intRateId;
$arrRates['mobrate']['CTM 23cpm 0ff'] = intRateId;
$arrRates['mobrate']['Residential (20ff, 27.27cpm)'] = intRateId;
$arrRates['mobrate']['CTM 25c 0 flag'] = intRateId;
$arrRates['mobrate']['CTM 24c 0 flag'] = intRateId;
$arrRates['mobrate']['CTM 25pm 8ff'] = intRateId;
$arrRates['intrate']['Blue 15c CTM'] = intRateId;
$arrRates['intrate']['Tier 3 corporate capped'] = intRateId;
$arrRates['intrate']['Blue Virtual VOIP'] = intRateId;
$arrRates['intrate']['39c Cap Intl'] = intRateId;
$arrRates['intrate']['VoiceTalk'] = intRateId;
$arrRates['intrate']['National 16'] = intRateId;
$arrRates['intrate']['Tier 3 corporate Long Distance'] = intRateId;
$arrRates['intrate']['Mobile Zero Plan'] = intRateId;
$arrRates['intrate']['Tier 3 corporate Mobile Saver'] = intRateId;
$arrRates['intrate']['Residential'] = intRateId;
$arrRates['service_equip_rate']['Tier 3 Corporate Capped'] = intRateId;
$arrRates['service_equip_rate']['Tier 3 Corporate Local Saver'] = intRateId;
$arrRates['service_equip_rate']['Blue Virtual VOIP'] = intRateId;
$arrRates['service_equip_rate']['Business Saver Capped'] = intRateId;
$arrRates['service_equip_rate']['VoiceTalk'] = intRateId;
$arrRates['service_equip_rate']['39cent cap'] = intRateId;
$arrRates['service_equip_rate']['National 16'] = intRateId;
$arrRates['service_equip_rate']['Blue 15c CTM'] = intRateId;
$arrRates['service_equip_rate']['Tier 3 Corporate Mobile Saver'] = intRateId;
$arrRates['service_equip_rate']['True Blue Fleet'] = intRateId;
$arrRates['service_equip_rate']['Residential'] = intRateId;
$arrRates['service_equip_rate']['Pinnacle ($33.00)'] = intRateId;
$arrRates['mobileunitel']['Mobile Zero Plan'] = intRateId;
$arrRates['mobileunitel']['Fleet Mobile 60'] = intRateId;
$arrRates['mobileunitel']['Pinnacle'] = intRateId;
$arrRates['mobileunitel']['Fleet Mobile Peter K Special'] = intRateId;
$arrRates['mobileunitel']['Fleet Mobile 30'] = intRateId;
$arrRates['mobileunitel']['Blue Shared 500'] = intRateId;
$arrRates['mobiletelstra']['Mobile Zero Plan'] = intRateId;
$arrRates['mobiletelstra']['Fleet Mobile 60'] = intRateId;
$arrRates['mobiletelstra']['Pinnacle'] = intRateId;
$arrRates['mobiletelstra']['Fleet Mobile 30'] = intRateId;
$arrRates['mobiletelstra']['Blue Shared 500'] = intRateId;
$arrRates['mobileother']['Mobile Zero Plan'] = intRateId;
$arrRates['mobileother']['Fleet Mobile 60'] = intRateId;
$arrRates['mobileother']['Pinnacle'] = intRateId;
$arrRates['mobileother']['Fleet Mobile 30'] = intRateId;
$arrRates['mobileother']['Blue Shared 500'] = intRateId;
$arrRates['mobileother']['Fleet Mobile Peter K Special'] = intRateId;
$arrRates['mobilenational']['Mobile Zero Plan'] = intRateId;
$arrRates['mobilenational']['Fleet Mobile 60'] = intRateId;
$arrRates['mobilenational']['Pinnacle'] = intRateId;
$arrRates['mobilenational']['Fleet Mobile 30'] = intRateId;
$arrRates['mobilenational']['Blue Shared 500'] = intRateId;
$arrRates['mobilenational']['Fleet Mobile Peter K special'] = intRateId;
$arrRates['mobile1800']['Mobile Zero Plan'] = intRateId;
$arrRates['mobile1800']['Fleet Mobile 60'] = intRateId;
$arrRates['mobile1800']['Pinnacle'] = intRateId;
$arrRates['mobile1800']['Fleet Mobile 30'] = intRateId;
$arrRates['mobile1800']['Blue Shared 500'] = intRateId;
$arrRates['mobilevoicemail']['Voicemail Retrievals'] = intRateId;
$arrRates['mobilevoicemail']['Pinnacle'] = intRateId;
$arrRates['mobilediverted']['DiversionAll'] = intRateId;
$arrRates['mobilediverted']['Pinnacle'] = intRateId;
$arrRates['mobilesms']['25c inc SMS'] = intRateId;
$arrRates['mobilesms']['Pinnacle 18c ex'] = intRateId;
$arrRates['mobilesms']['22c inc SMS'] = intRateId;
$arrRates['mobilemms']['MMS 75c inc (68.2 ex)'] = intRateId;
$arrRates['mobiledata']['GPRS 2c ex (BS100,Fleet 30, Zero)'] = intRateId;
$arrRates['mobiledata']['GPRS 1.5c ex (BS500, Fleet 60)'] = intRateId;
$arrRates['mobiledata']['GPRS 1.8c ex (BS250)'] = intRateId;
$arrRates['mobileinternational']['Mobile Zero Plan'] = intRateId;


	
	
	$GLOBALS['arrRates'] = $arrRates;
	
	
	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
	// connect
	mysql_connect ("10.11.12.13", "bash", "bash");
	mysql_select_db ("bash");
	
	// How many accounts do we have
	$sql = "SELECT count(*) AS records FROM ScrapeAccount ";
	$query = mysql_query ($sql);
	
	$row = mysql_fetch_assoc ($query);
	$records = $row ['records'];
	
	//$records = 1000;
	
	echo "Checking $records records\n";
	
	// Loop through each Scrape
	for ($start=0; $start < ceil ($records / 100); ++$start)
	{
		Run($start);
	}
	
	// output report
	echo "\n\n";
	foreach($GLOBALS['arrRateReport'] AS $strRecordType=>$arrValue)
	{
		foreach($arrValue AS $strRateName=>$intValue)
		{
			echo "\$arrRates['$strRecordType']['$strRateName'] = intRateId;\n";
		}
	}
	
	//finish
	Die ();
	
	
	//#########################################################################
	
	function Run($start)
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
	
	function Decode($arrScrapeAccount)
	{
		//echo "Decoding\n";
		if (!is_array($arrScrapeAccount))
		{
			return FALSE;
		}
		
		$arrRates = Array();
				
		$insServiceRateGroup	= new StatementInsert("ServiceRateGroup");
		$selServicesByType		= new StatementInsert(	"Service",
														"Id",
														"Account = {$arrScrapeAccount['AccountId']} AND ServiceType = <ServiceType>");
		
		// for each RecordType
		foreach ($GLOBALS['arrRecordTypes'] AS $strName=>$intServiceType )
		{
			// if we have a rate for this RecordType
			if ($arrScrapeAccount[$strName])
			{
				//if we have a conversion name for this rate
				if ($GLOBALS['arrRates'][$arrScrapeAccount[$strName]])
				{
					// add to rate report
					$GLOBALS['arrRateReport'][$strName][$arrScrapeAccount[$strName]] = $intRateGroup;
				
					$intRateGroup = $GLOBALS['arrRates'][$arrScrapeAccount[$strName]];
					
					// insert record
					$selServicesByType->Execute(Array('ServiceType' => $intServiceType));
					$arrServices = $selServicesByType->FetchAll();
					// for each service of $intServiceType
					foreach($arrServices as $arrService)
					{
						// insert into ServiceRateGroup
						$arrData['Service']			= "";
						$arrData['RateGroup']		= $intRateGroup;
						$arrData['CreatedBy']		= 22;	// Rich ;)
						$arrData['CreatedOn']		= date("Y-m-d");
						$arrData['StartDatetime']	= "2006-01-01 11:57:40";
						$arrData['EndDatetime']		= "2030-11-30 11:57:45";
						$insServiceRateGroup->Execute($arrData);
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
	
?>

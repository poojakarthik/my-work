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
	$arrRates['localrate']	['Local 14/13c (T3CC)']					[17]		= 67;			// Local
	$arrRates['localrate']	['Local 14/13c (T3CC)']					[18]		= 70;			// Programmed Local
	$arrRates['localrate']	['Local 14/11c (tb fleet, t3 local)']	[17]		= 66;
	$arrRates['localrate']	['Local 14/11c (tb fleet, t3 local)']	[18]		= 74;
	$arrRates['localrate']	['Local 10c (VV)']						[17]		= 65;
	$arrRates['localrate']	['Local 10c (VV)']						[18]		= 72;
	$arrRates['localrate']	['Local 13 (B39c)']						[17]		= 63;
	$arrRates['localrate']	['Local 13 (B39c)']						[18]		= 70;
	$arrRates['localrate']	['Local 16/14 (VT, BSC,T3CTM)']			[17]		= 66;
	$arrRates['localrate']	['Local 16/14 (VT, BSC,T3CTM)']			[18]		= 73;
	$arrRates['localrate']	['Local 12c (B15ctm)']					[17]		= 64;
	$arrRates['localrate']	['Local 12c (B15ctm)']					[18]		= 71;
	$arrRates['localrate']	['VoiceTalk']							[17]		= 66;
	$arrRates['localrate']	['VoiceTalk']							[18]		= 73;
	$arrRates['localrate']	['Tier 3 corporate Local Saver']		[17]		= 67;
	$arrRates['localrate']	['Tier 3 corporate Local Saver']		[18]		= 74;
	$arrRates['localrate']	['Local 17c (nat16)']					[17]		= 68;
	$arrRates['localrate']	['Local 17c (nat16)']					[18]		= 75;
	$arrRates['localrate']	['Local 11/11c']						[17]		= /* NO MATCH*/null;
	$arrRates['localrate']	['Local 11/11c']						[18]		= 74;
	$arrRates['localrate']	['Local 16.36 (res 18c gst)']			[17]		= 69;
	$arrRates['localrate']	['Local 16.36 (res 18c gst)']			[18]		= 76;
	$arrRates['localrate']	['Tier 3 corporate Long Distance']		[17]		= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['localrate']	['Tier 3 corporate Long Distance']		[18]		= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['localrate']	['Local 18c']							[17]		= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['localrate']	['Local 18c']							[18]		= /* PLAN DOESN'T EXIST ANYMORE */null;
	
	$arrRates['natrate']	['Tier 3 corporate capped (6ff,8cpm)']				= 81;
	$arrRates['natrate']	['Tier 3 corporate Local (6ff,8cpm)']				= 86;
	$arrRates['natrate']	['Blue V.VOIP (0ff,10cpcall)']						= 79;
	$arrRates['natrate']	['39cent cap (6ff,8cpm)']							= 77;
	$arrRates['natrate']	['Bus. Saver Capped (7ff,9cpm)']					= 80;
	$arrRates['natrate']	['Blue 15c CTM  (6ff,6cpm)']						= 78;
	$arrRates['natrate']	['VoiceTalk (10 ff/12cpm) cap']						= 84;
	$arrRates['natrate']	['National 16']										= 83;
	$arrRates['natrate']	['Tier 3 corporate Mobile Saver (6.5ff,9cpm)']		= 87;
	$arrRates['natrate']	['Tier 3 corp. L D (0 ff,10cpm)']					= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['natrate']	['7.5cpm no flag']									= /* NO MATCH*/null;
	$arrRates['natrate']	['True Blue Fleet (6ff,9cpm)']						= 82;
	$arrRates['natrate']	['National 8c no ff']								= /* NO MATCH*/null;
	$arrRates['natrate']	['5.5cpm no flag']									= /* NO MATCH*/null;
	$arrRates['natrate']	['Residential (20ff,18cpm)']						= /* PLAN FOUND, INCORRECT RATE */null;
	$arrRates['natrate']	['Pinnacle (13c per call)']							= 88;
	$arrRates['natrate']	['7cpm 0 flag']										= /* NO MATCH*/null;
	$arrRates['natrate']	['6 cpm 0 flag']									= /* NO MATCH*/null;
	
	$arrRates['mobrate']	['Tier 3 corporate capped(27cpm, 20c min)']			= 94;
	$arrRates['mobrate']	['Tier 3 corporate Local Saver (9ff, 26cpm)']		= 99;
	$arrRates['mobrate']	['Virtual VOIP (30cpm, 30c min)']					= 92;
	$arrRates['mobrate']	['39cent cap (20min 27cpm)']						= 90;
	$arrRates['mobrate']	['Business Saver Capped (10ff, 27cpm)']				= 124;
	$arrRates['mobrate']	['Blue 15c CTM (15ff,15cpm)']						= 91;
	$arrRates['mobrate']	['VoiceTalk standard (10ff, 27cpm)']				= 97;
	$arrRates['mobrate']	['National 16 (30cpm, 20c min)']					= 96;
	$arrRates['mobrate']	['Tier 3 corporate Mobile Saver (6ff, 26cpm)']		= 100;
	$arrRates['mobrate']	['tier 3 corporate Long distance (5ff, 26cpm)']		= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['mobrate']	['Pinnacle (50cper call)']							= 101;
	$arrRates['mobrate']	['CTM 26c no flag']									= /* NO MATCH*/null;
	$arrRates['mobrate']	['True Blue Fleet (6ff, 25cpm)']					= 95;
	$arrRates['mobrate']	['Voicetalk Feb06 cap']								= /* NO MATCH*/null;
	$arrRates['mobrate']	['CTM 22 no flag']									= /* NO MATCH*/null;
	$arrRates['mobrate']	['CTM 30 no ff']									= /* NO MATCH*/null;
	$arrRates['mobrate']	['CTM 23cpm 0ff']									= /* NO MATCH*/null;
	$arrRates['mobrate']	['Residential (20ff, 27.27cpm)'] = intRateId;
	$arrRates['mobrate']	['CTM 25c 0 flag']									= /* NO MATCH*/null;
	$arrRates['mobrate']	['CTM 24c 0 flag']									= /* NO MATCH*/null;
	$arrRates['mobrate']	['CTM 25pm 8ff']									= /* NO MATCH*/null;
	
	$arrRates['intrate']	['Blue 15c CTM'] 									= 192;
	$arrRates['intrate']	['Tier 3 corporate capped'] 						= 197;
	$arrRates['intrate']	['Blue Virtual VOIP'] 								= 193;
	$arrRates['intrate']	['39c Cap Intl'] 									= 191;
	$arrRates['intrate']	['VoiceTalk'] 										= 201;
	$arrRates['intrate']	['National 16'] 									= 195;
	$arrRates['intrate']	['Tier 3 corporate Long Distance'] 					= 198;
	$arrRates['intrate']	['Mobile Zero Plan'] 								= 194;
	$arrRates['intrate']	['Tier 3 corporate Mobile Saver'] 					= 199;
	$arrRates['intrate']	['Residential'] 									= 196;
	
	$arrRates['service_equip_rate']	['Tier 3 Corporate Capped']			[21]	= 103;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Capped']			[22]	= 107;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Capped']			[23]	= 113;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Local Saver']	[21]	= 103;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Local Saver']	[22]	= 107;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Local Saver']	[23]	= 113;
	$arrRates['service_equip_rate']	['Blue Virtual VOIP']				[21]	= 104;
	$arrRates['service_equip_rate']	['Blue Virtual VOIP']				[22]	= 108;
	$arrRates['service_equip_rate']	['Blue Virtual VOIP']				[23]	= 112;
	$arrRates['service_equip_rate']	['Business Saver Capped']			[21]	= 103;
	$arrRates['service_equip_rate']	['Business Saver Capped']			[22]	= 107;
	$arrRates['service_equip_rate']	['Business Saver Capped']			[23]	= 113;
	$arrRates['service_equip_rate']	['VoiceTalk']						[21]	= 103;
	$arrRates['service_equip_rate']	['VoiceTalk']						[22]	= 107;
	$arrRates['service_equip_rate']	['VoiceTalk']						[23]	= 116;
	$arrRates['service_equip_rate']	['39cent cap']						[21]	= 103;
	$arrRates['service_equip_rate']	['39cent cap']						[22]	= 107;
	$arrRates['service_equip_rate']	['39cent cap']						[23]	= 111;
	$arrRates['service_equip_rate']	['National 16']						[21]	= 105;
	$arrRates['service_equip_rate']	['National 16']						[22]	= 109;
	$arrRates['service_equip_rate']	['National 16']						[23]	= 115;
	$arrRates['service_equip_rate']	['Blue 15c CTM']					[21]	= 104;
	$arrRates['service_equip_rate']	['Blue 15c CTM']					[22]	= 108;
	$arrRates['service_equip_rate']	['Blue 15c CTM']					[23]	= 112;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Mobile Saver']	[21]	= 103;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Mobile Saver']	[22]	= 107;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Mobile Saver']	[23]	= 113;
	$arrRates['service_equip_rate']	['True Blue Fleet']					[21]	= 103;
	$arrRates['service_equip_rate']	['True Blue Fleet']					[22]	= 107;
	$arrRates['service_equip_rate']	['True Blue Fleet']					[23]	= 114;
	$arrRates['service_equip_rate']	['Residential']						[21]	= 103;
	$arrRates['service_equip_rate']	['Residential']						[22]	= 107;
	$arrRates['service_equip_rate']	['Residential']						[23]	= 117;
	$arrRates['service_equip_rate']	['Pinnacle ($33.00)']				[21]	= 106;
	$arrRates['service_equip_rate']	['Pinnacle ($33.00)']				[22]	= 110;
	$arrRates['service_equip_rate']	['Pinnacle ($33.00)']				[23]	= 118;
	
	$arrRates['mobileunitel']	['Mobile Zero Plan']							= 11;
	$arrRates['mobileunitel']	['Fleet Mobile 60']								= 16;
	$arrRates['mobileunitel']	['Pinnacle']									= 18;
	$arrRates['mobileunitel']	['Fleet Mobile Peter K Special']				= 15;
	$arrRates['mobileunitel']	['Fleet Mobile 30']								= 17;
	$arrRates['mobileunitel']	['Blue Shared 500']								= 14;
	
	$arrRates['mobiletelstra']	['Mobile Zero Plan']							= 19;
	$arrRates['mobiletelstra']	['Fleet Mobile 60']								= 24;
	$arrRates['mobiletelstra']	['Pinnacle']									= 26;
	$arrRates['mobiletelstra']	['Fleet Mobile 30']								= 25;
	$arrRates['mobiletelstra']	['Blue Shared 500']								= 22;
	
	$arrRates['mobileother']	['Mobile Zero Plan']							= 27;
	$arrRates['mobileother']	['Fleet Mobile 60']								= 32;
	$arrRates['mobileother']	['Pinnacle']									= 34;
	$arrRates['mobileother']	['Fleet Mobile 30']								= 33;
	$arrRates['mobileother']	['Blue Shared 500']								= 30;
	$arrRates['mobileother']	['Fleet Mobile Peter K Special']				= 31;
	
	$arrRates['mobilenational']	['Mobile Zero Plan']							= 35;
	$arrRates['mobilenational']	['Fleet Mobile 60']								= 32;
	$arrRates['mobilenational']	['Pinnacle']									= 34;
	$arrRates['mobilenational']	['Fleet Mobile 30']								= 41;
	$arrRates['mobilenational']	['Blue Shared 500']								= 38;
	$arrRates['mobilenational']	['Fleet Mobile Peter K special']				= 39;
	
	$arrRates['mobile1800']		['Mobile Zero Plan']							= 43;
	$arrRates['mobile1800']		['Fleet Mobile 60']								= 48;
	$arrRates['mobile1800']		['Pinnacle']									= 50;
	$arrRates['mobile1800']		['Fleet Mobile 30']								= 49;
	$arrRates['mobile1800']		['Blue Shared 500']								= 46;
	
	$arrRates['mobilevoicemail']	['Voicemail Retrievals']					= 54;
	$arrRates['mobilevoicemail']	['Pinnacle']								= 55;
	
	$arrRates['mobilediverted']	['DiversionAll']								= 56;
	$arrRates['mobilediverted']	['Pinnacle']									= 56;
	
	$arrRates['mobilesms']		['25c inc SMS']									= 51;
	$arrRates['mobilesms']		['Pinnacle 18c ex']								= 53;
	$arrRates['mobilesms']		['22c inc SMS']									= 52;
	
	$arrRates['mobilemms']		['MMS 75c inc (68.2 ex)']						= 62;
	
	$arrRates['mobiledata']		['GPRS 2c ex (BS100,Fleet 30, Zero)']			= 59;
	$arrRates['mobiledata']		['GPRS 1.5c ex (BS500, Fleet 60)']				= 60;
	$arrRates['mobiledata']		['GPRS 1.8c ex (BS250)']						= 58;
	
	$arrRates['mobileinternational']	['Mobile Zero Plan']				= intRateId;

	// set global rate array
	$GLOBALS['arrRates'] = $arrRates;
	
	// database things
	$GLOBALS['insServiceRateGroup']	= new StatementInsert("ServiceRateGroup");
	$GLOBALS['selServicesByType']		= new StatementSelect(	"Service",
														"Id, FNN",
														"Account = <Account> AND ServiceType = <ServiceType>");
	
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
	/*echo "\n\n";
	foreach($GLOBALS['arrRateReport'] AS $strRecordType=>$arrValue)
	{
		foreach($arrValue AS $strRateName=>$intValue)
		{
			echo "\$arrRates['$strRecordType']['$strRateName'] = intRateId;\n";
		}
	}*/
	
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
	
?>

#!/usr/bin/php
<?=system ("clear");?>

	=====================================================================================================
	viXen : TelcoBlue Import (version 1.0)
	=====================================================================================================
	
<?php
	
	
	
	
	// Define New RatePlans
	//TODO!!!!
	$arrConfig['RatePlan']['PlanName'][17] 		= 'Local-14';
	$arrConfig['RatePlan']['PlanName'][18]		= 'ProgramLocal-13';
	
	// Record Types
	$GLOBALS['arrRecordTypes'] = Array
	(
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
	
	// Old Rates => New RateGroups
	$arrRates['localrate']	['Local 14/13c (T3CC)']					[17]		= 'Local-14';			// Local
	$arrRates['localrate']	['Local 14/13c (T3CC)']					[18]		= 'ProgramLocal-13';	// Programmed Local
	$arrRates['localrate']	['Local 14/11c (tb fleet, t3 local)']	[17]		= 'Local-14';
	$arrRates['localrate']	['Local 14/11c (tb fleet, t3 local)']	[18]		= 'ProgramLocal-11';
	$arrRates['localrate']	['Local 10c (VV)']						[17]		= 'Local-10';
	$arrRates['localrate']	['Local 10c (VV)']						[18]		= 'ProgramLocal-10';
	$arrRates['localrate']	['Local 13 (B39c)']						[17]		= 'Local-13';
	$arrRates['localrate']	['Local 13 (B39c)']						[18]		= 'ProgramLocal-13';
	$arrRates['localrate']	['Local 16/14 (VT, BSC,T3CTM)']			[17]		= 'Local-16';
	$arrRates['localrate']	['Local 16/14 (VT, BSC,T3CTM)']			[18]		= 'ProgramLocal-14';
	$arrRates['localrate']	['Local 12c (B15ctm)']					[17]		= 'Local-12';
	$arrRates['localrate']	['Local 12c (B15ctm)']					[18]		= 'ProgramLocal-12';
	$arrRates['localrate']	['VoiceTalk']							[17]		= 'Local-16';
	$arrRates['localrate']	['VoiceTalk']							[18]		= 'ProgramLocal-14';
	$arrRates['localrate']	['Tier 3 corporate Local Saver']		[17]		= 'Local-14';
	$arrRates['localrate']	['Tier 3 corporate Local Saver']		[18]		= 'ProgramLocal-11';
	$arrRates['localrate']	['Local 17c (nat16)']					[17]		= 'Local-17';
	$arrRates['localrate']	['Local 17c (nat16)']					[18]		= /* NO MATCH*/null;
	$arrRates['localrate']	['Local 11/11c']						[17]		= /* NO MATCH*/null;
	$arrRates['localrate']	['Local 11/11c']						[18]		= 'ProgramLocal-11';
	$arrRates['localrate']	['Local 16.36 (res 18c gst)']			[17]		= 'Local-1636';
	$arrRates['localrate']	['Local 16.36 (res 18c gst)']			[18]		= 'ProgramLocal-1636';
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
	$arrRates['service_equip_rate']	['Tier 3 Corporate Local Saver']	[21]	= 103;
	$arrRates['service_equip_rate']	['Blue Virtual VOIP']				[21]	= 104;
	$arrRates['service_equip_rate']	['Business Saver Capped']			[21]	= 103;
	$arrRates['service_equip_rate']	['VoiceTalk']						[21]	= 103;
	$arrRates['service_equip_rate']	['39cent cap']						[21]	= 103;
	$arrRates['service_equip_rate']	['National 16']						[21]	= 105;
	$arrRates['service_equip_rate']	['Blue 15c CTM']					[21]	= 104;
	$arrRates['service_equip_rate']	['Tier 3 Corporate Mobile Saver']	[21]	= 103;
	$arrRates['service_equip_rate']	['True Blue Fleet']					[21]	= 103;
	$arrRates['service_equip_rate']	['Residential']						[21]	= 103;
	$arrRates['service_equip_rate']	['Pinnacle ($33.00)']				[21]	= 106;
	
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
	
	$arrRates['mobile']	['Mobile Zero Plan']									= 27;
	$arrRates['mobile']	['Fleet Mobile 60']										= 32;
	$arrRates['mobile']	['Pinnacle']											= 34;
	$arrRates['mobile']	['Fleet Mobile 30']										= 33;
	$arrRates['mobile']	['Blue Shared 500']										= 30;
	$arrRates['mobile']	['Fleet Mobile Peter K Special']						= 31;
	
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

	// set config rate array
	$arrConfig['Rate'] = $arrRates;
	
// ---------------------------------------------------------------------------//
// SCRIPT
// ---------------------------------------------------------------------------//

	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
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
	
	// setup a db query object
	$sqlQuery = new Query();
	
	// instanciate the etech decoder
	require_once('decode_etech.php');
	$objDecoder = new VixenDecode($arrConfig);
	
	// instanciate the import object
	require_once('vixen_import.php');
	$objImport = new VixenImport($arrConfig);
	
	// Import Rates
	$objImport->ImportRate();
	
	// Import RateGroups
	$objImport->ImportRateGroup();
	
	// Import RatePlans
	$objImport->ImportRatePlan();
	
	// Match RateGroups to Rates
	//$objImport->CreateRateGroupRate();
	
	// Match RatePlans to RateGroups
	//$objImport->CreateRatePlanRateGroup();
	
	// Add Customers
	while ($arrRow = $objDecoder->FetchCustomer())
	{	
		// get the etech customer details
		$arrScrape = unserialize($arrRow['DataSerialised']);
		$arrScrape['CustomerId'] = $arrRow['CustomerId'];

		// decode the customer
		echo "Decoding Customer  : {$arrScrape['CustomerId']}\n";
		$arrCustomer = $objDecoder->DecodeCustomer($arrScrape);
		
		// add the customer
		echo "Importing Customer : {$arrScrape['CustomerId']}\n";
		$objImport->AddCustomerWithId($arrCustomer);
	}
	
	//finish
	Die ();

// ---------------------------------------------------------------------------//
// IMPORT CLASS
// ---------------------------------------------------------------------------//	
	

?>

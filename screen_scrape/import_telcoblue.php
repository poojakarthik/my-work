#!/usr/bin/php
<?=system ("clear");?>

	=====================================================================================================
	viXen : TelcoBlue Import (version 1.0)
	=====================================================================================================
	
<?php

// ---------------------------------------------------------------------------//
// CRAP THAT NEEDS TO GO AT THE TOP !
// ---------------------------------------------------------------------------//

	set_time_limit (0);
	
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
	
	Define('USER_NAME', 'Import');
	

// ---------------------------------------------------------------------------//
// CONFIG
// ---------------------------------------------------------------------------//

/*
"IDD : 39c Cap Intl"
"IDD : Blue 15c CTM"
"IDD : Blue Virtual VOIP"
"IDD : Mobile Zero Plan"
"IDD : National 16"
"IDD : Residential"
"IDD : Tier 3 corporate capped"
"IDD : Tier 3 corporate Long Distance"
"IDD : Tier 3 corporate Mobile Saver"
"IDD : True Blue Fleet"
"IDD : VoiceTalk"
*/
	// Define New RateGroups
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2820';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-Blue39cCap'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3635';
	$arrGroup[]				= 'Faxstream-3635';
	$arrGroup[]				= 'ResidentialLine-3635';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-Blue15CTM'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3635';
	$arrGroup[]				= 'Faxstream-3635';
	$arrGroup[]				= 'ResidentialLine-3635';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-VOIP'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2979';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-BusSaverCapped'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2979';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-TierThreeCorporateCapped'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-3499';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-TrueBlueFleet'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3595';
	$arrGroup[]				= 'Faxstream-3595';
	$arrGroup[]				= 'ResidentialLine-3595';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-National16'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2698';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-VoicetalkCapped'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2995';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-Residential'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2979';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-TierThreeLocalSaver'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2979';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-TierThreeMobileSaver'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3300';
	$arrGroup[]				= 'Faxstream-3300';
	$arrGroup[]				= 'ResidentialLine-3300';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-Pinnacle'] = $arrGroup;
	
	$arrGroup = Array();
	$arrGroup[]				= 'BusinessLine-3499';
	$arrGroup[]				= 'Faxstream-3499';
	$arrGroup[]				= 'ResidentialLine-2820';
	$arrGroup[]				= 'S&E-Other';
	$arrConfig['Import']['RateGroup'][SERVICE_TYPE_LAND_LINE]['S&E']['S&E-PeterKGroupSpecial'] = $arrGroup;

	
	
	
	// Define New RatePlans
	
	// RateGroups common to all Mobile Plans	
	$arrPlan = Array();
	$arrPlan['Roaming']				= 'Roaming-35';
	$arrPlan['MMS']					= 'MMS-68';
	$arrPlan['Other']				= 'Other-Cost';
	$arrPlan['OSNetworkAirtime']	= 'OSNetworkAirtime-Cost';
	
	// Plan Zero
	$arrPlan['GPRS']				= 'GPRS-18-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-30-10-30';
	$arrPlan['Mobile']				= 'Mobile-30-10-01';
	$arrPlan['National']			= 'National-30-10-01';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Plan Zero'] = $arrPlan;
	
	// $35 Cap
	$arrPlan['GPRS']				= 'GPRS-20-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-70-25-30';
	$arrPlan['Mobile']				= 'Mobile-70-25-30';
	$arrPlan['National']			= 'National-70-25-30';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['$35 Cap'] = $arrPlan;
	
	// Plan Ten
	$arrPlan['GPRS']				= 'GPRS-18-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-30-10-30';
	$arrPlan['Mobile']				= 'Mobile-30-10-01';
	$arrPlan['National']			= 'National-30-10-01';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Plan Ten'] = $arrPlan;
	
	// Blue Shared 100
	$arrPlan['GPRS']				= 'GPRS-20-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-40-14-30';
	$arrPlan['Mobile']				= 'Mobile-40-14-01';
	$arrPlan['National']			= 'National-40-14-01';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Blue Shared 100'] = $arrPlan;
	
	// Blue Shared 250
	$arrPlan['GPRS']				= 'GPRS-18-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-30-10-30';
	$arrPlan['Mobile']				= 'Mobile-30-10-01';
	$arrPlan['National']			= 'National-30-10-01';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Blue Shared 250'] = $arrPlan;
	
	// Blue Shared 500
	$arrPlan['GPRS']				= 'GPRS-15-00-01';
	$arrPlan['SMS']					= 'SMS-20';
	$arrPlan['Freecall']			= 'Freecall-26-09-30';
	$arrPlan['Mobile']				= 'Mobile-26-09-01';
	$arrPlan['National']			= 'National-26-09-01';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Blue Shared 500'] = $arrPlan;
	
	// Fleet Special Peter K
	$arrPlan['GPRS']				= 'GPRS-20-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-30-15-30';
	$arrPlan['Mobile']				= 'Mobile-30-15-30';
	$arrPlan['National']			= 'National-30-15-30';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Fleet Special Peter K'] = $arrPlan;
	
	// Fleet 60
	$arrPlan['GPRS']				= 'GPRS-15-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-30-18-30';
	$arrPlan['Mobile']				= 'Mobile-30-18-30';
	$arrPlan['National']			= 'National-30-18-30';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Fleet 60'] = $arrPlan;
	
	// Fleet 30
	$arrPlan['GPRS']				= 'GPRS-20-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-40-18-30';
	$arrPlan['Mobile']				= 'Mobile-40-18-30';
	$arrPlan['National']			= 'National-40-18-30';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Fleet 30'] = $arrPlan;
	
	// Fleet 20
	$arrPlan['GPRS']				= 'GPRS-20-00-01';
	$arrPlan['SMS']					= 'SMS-22';
	$arrPlan['Freecall']			= 'Freecall-40-18-30';
	$arrPlan['Mobile']				= 'Mobile-40-18-30';
	$arrPlan['National']			= 'National-40-18-30';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Fleet 20'] = $arrPlan;
	
	// Pinnacle (Mobile)
	$arrPlan['GPRS']				= 'GPRS-18-00-01';
	$arrPlan['SMS']					= 'SMS-18';
	$arrPlan['Freecall']			= 'Freecall-30-10-30';
	$arrPlan['Mobile']				= 'Mobile-Pinnacle';
	$arrPlan['National']			= 'National-Pinnacle';
	$arrPlan['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrPlan['VoiceMailRetrieval']	= 'VoiceMailRetrieval-Pinnacle';
	$arrPlan['IDD']					= ''; //TODO!!!!
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_MOBILE]['Pinnacle'] = $arrPlan;
	
	// RateGroups common to all Land Line Plans
	$arrPlan = Array();
	$arrPlan['OneThree']			= 'OneThree-Cost';
	$arrPlan['Other']				= 'Other-Cost';
	$arrPlan['SexHotline']			= '1900-28';
	
	// Blue 39c Cap
	$arrPlan['Mobile']				= 'Mobile-27-00-01-89';
	$arrPlan['Local']				= 'Local-13';
	$arrPlan['National']			= 'National-08-06-01-39';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-13';
	$arrPlan['S&E']					= 'S&E-Blue39cCap';
	$arrPlan['IDD']					= '39c Cap Intl';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Blue 39c Cap'] = $arrPlan;
	
	// Blue 15 CTM
	$arrPlan['Mobile']				= 'Mobile-15-15-01';
	$arrPlan['Local']				= 'Local-12';
	$arrPlan['National']			= 'National-06-06-01';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-12';
	$arrPlan['S&E']					= 'S&E-Blue15CTM';
	$arrPlan['IDD']					= 'Blue 15c CTM';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Blue 15 CTM'] = $arrPlan;
	
	// Virtual VOIP
	$arrPlan['Mobile']				= 'Mobile-30-00-01';
	$arrPlan['Local']				= 'Local-10';
	$arrPlan['National']			= 'National-VOIP';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-10';
	$arrPlan['S&E']					= 'S&E-VOIP';
	$arrPlan['IDD']					= 'Blue Virtual VOIP';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Virtual VOIP'] = $arrPlan;
	
	// Bus Saver Capped
	$arrPlan['Mobile']				= 'Mobile-27-10-01-150';
	$arrPlan['Local']				= 'Local-16';
	$arrPlan['National']			= 'National-09-07-01-90';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-14';
	$arrPlan['S&E']					= 'S&E-BusSaverCapped';
	$arrPlan['IDD']					= 'National 16'; //TODO!!!! - CHECK THIS
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Bus Saver Capped'] = $arrPlan;
	
	// Tier Three Corporate Capped
	$arrPlan['Mobile']				= 'Mobile-27-00-01-130';
	$arrPlan['Local']				= 'Local-14';
	$arrPlan['National']			= 'National-08-06-01-70';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-13';
	$arrPlan['S&E']					= 'S&E-TierThreeCorporateCapped';
	$arrPlan['IDD']					= 'Tier 3 corporate capped';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Tier Three Corporate Capped'] = $arrPlan;
	
	// True Blue Fleet
	$arrPlan['Mobile']				= 'Mobile-25-06-01';
	$arrPlan['Local']				= 'Local-14';
	$arrPlan['National']			= 'National-09-06-01';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-11';
	$arrPlan['S&E']					= 'S&E-TrueBlueFleet';
	$arrPlan['IDD']					= 'True Blue Fleet';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['True Blue Fleet'] = $arrPlan;
	
	// National 16
	$arrPlan['Mobile']				= 'Mobile-30-00-01';
	$arrPlan['Local']				= 'Local-17';
	$arrPlan['National']			= 'National-Nat16';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-16';
	$arrPlan['S&E']					= 'S&E-National16';
	$arrPlan['IDD']					= 'National 16';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['National 16'] = $arrPlan;
	
	// Voicetalk Capped
	$arrPlan['Mobile']				= 'Mobile-27-10-01-99';
	$arrPlan['Local']				= 'Local-16';
	$arrPlan['National']			= 'National-12-10-99';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-14';
	$arrPlan['S&E']					= 'S&E-VoicetalkCapped';
	$arrPlan['IDD']					= 'VoiceTalk';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Voicetalk Capped'] = $arrPlan;
	
	// Residential
	$arrPlan['Mobile']				= 'Mobile-30-20-165';
	$arrPlan['Local']				= 'Local-1636';
	$arrPlan['National']			= 'National-20-20-165';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-1636';
	$arrPlan['S&E']					= 'S&E-Residential';
	$arrPlan['IDD']					= 'Residential';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Residential'] = $arrPlan;
	
	// Tier Three Mobile Saver
	$arrPlan['Mobile']				= 'Mobile-26-06-01';
	$arrPlan['Local']				= 'Local-16';
	$arrPlan['National']			= 'National-09-06-01';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-14';
	$arrPlan['S&E']					= 'S&E-TierThreeMobileSaver';
	$arrPlan['IDD']					= 'Tier 3 corporate Mobile Saver';
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Tier Three Mobile Saver'] = $arrPlan;
	
	// Tier Three Local Saver
	$arrPlan['Mobile']				= 'Mobile-26-09-01';
	$arrPlan['Local']				= 'Local-14';
	$arrPlan['National']			= 'National-08-06';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-11';
	$arrPlan['S&E']					= 'S&E-TierThreeLocalSaver';
	$arrPlan['IDD']					= 'Tier 3 corporate Mobile Saver'; //TODO!!!! - CHECK THIS
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Tier Three Local Saver'] = $arrPlan;
	
	// Pinnacle
	$arrPlan['Mobile']				= 'Mobile-Pinnacle';
	$arrPlan['Local']				= 'Local-13';
	$arrPlan['National']			= 'National-Pinnacle';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-13';
	$arrPlan['S&E']					= 'S&E-Pinnacle';
	$arrPlan['IDD']					= 'National 16'; //TODO!!!! - CHECK THIS
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Pinnacle'] = $arrPlan;
	
	// Peter K Group Special
	$arrPlan['Mobile']				= 'Mobile-PeterK';
	$arrPlan['Local']				= 'Local-13';
	$arrPlan['National']			= 'National-PeterK';
	$arrPlan['ProgramLocal']		= 'ProgramLocal-13';
	$arrPlan['S&E']					= 'S&E-PeterKGroupSpecial';
	$arrPlan['IDD']					= 'National 16'; //TODO!!!! - CHECK THIS
	$arrConfig['Import']['RatePlan'][SERVICE_TYPE_LAND_LINE]['Peter K Group Special'] = $arrPlan;
	
	// Record Types
	$arrConfig['Decode']['RecordType'] = Array
	(
		"localrate"				=> SERVICE_TYPE_LAND_LINE,
		"natrate"				=> SERVICE_TYPE_LAND_LINE,
		"mobrate"				=> SERVICE_TYPE_LAND_LINE,
		"intrate"				=> SERVICE_TYPE_LAND_LINE,
		"service_equip_rate"	=> SERVICE_TYPE_LAND_LINE,
		
		"mobileunitel"			=> SERVICE_TYPE_MOBILE,
		"mobiletelstra"			=> SERVICE_TYPE_MOBILE,
		"mobileother"			=> SERVICE_TYPE_MOBILE,
		"mobilenational"		=> SERVICE_TYPE_MOBILE,
		"mobile1800"			=> SERVICE_TYPE_MOBILE,
		"mobilevoicemail"		=> SERVICE_TYPE_MOBILE,
		"mobilediverted"		=> SERVICE_TYPE_MOBILE,
		"mobilesms"				=> SERVICE_TYPE_MOBILE,
		"mobilemms"				=> SERVICE_TYPE_MOBILE,
		"mobiledata"			=> SERVICE_TYPE_MOBILE,
		"mobileinternational"	=> SERVICE_TYPE_MOBILE
	);
	
	// Old Rates => New RateGroups
	// $arrRates[strRecordType][strRateName][strNewRecordType] = strNewRateGroup
	
	//RecordType specified by Name not Id, ie. 'Local' NOT 17
	//and they ALL need to have the RecordType Name specfied
	//also, RatePlan must be specified by name and not Id 
	
			// Etech RecordType			// Etech RateGroup								// TB RecordType		// TB RateGroup
	$arrRates['localrate']				['Local 14/13c (T3CC)']							['Local']				= 'Local-14';
	$arrRates['localrate']				['Local 14/13c (T3CC)']							['ProgramLocal']		= 'ProgramLocal-13';
	$arrRates['localrate']				['Local 14/11c (tb fleet, t3 local)']			['Local']				= 'Local-14';
	$arrRates['localrate']				['Local 14/11c (tb fleet, t3 local)']			['ProgramLocal']		= 'ProgramLocal-11';
	$arrRates['localrate']				['Local 10c (VV)']								['Local']				= 'Local-10';
	$arrRates['localrate']				['Local 10c (VV)']								['ProgramLocal']		= 'ProgramLocal-10';
	$arrRates['localrate']				['Local 13 (B39c)']								['Local']				= 'Local-13';
	$arrRates['localrate']				['Local 13 (B39c)']								['ProgramLocal']		= 'ProgramLocal-13';
	$arrRates['localrate']				['Local 16/14 (VT, BSC,T3CTM)']					['Local']				= 'Local-16';
	$arrRates['localrate']				['Local 16/14 (VT, BSC,T3CTM)']					['ProgramLocal']		= 'ProgramLocal-14';
	$arrRates['localrate']				['Local 12c (B15ctm)']							['Local']				= 'Local-12';
	$arrRates['localrate']				['Local 12c (B15ctm)']							['ProgramLocal']		= 'ProgramLocal-12';
	$arrRates['localrate']				['VoiceTalk']									['Local']				= 'Local-16';
	$arrRates['localrate']				['VoiceTalk']									['ProgramLocal']		= 'ProgramLocal-14';
	$arrRates['localrate']				['Tier 3 corporate Local Saver']				['Local']				= 'Local-14';
	$arrRates['localrate']				['Tier 3 corporate Local Saver']				['ProgramLocal']		= 'ProgramLocal-11';
	$arrRates['localrate']				['Local 17c (nat16)']							['Local']				= 'Local-17';
	$arrRates['localrate']				['Local 17c (nat16)']							['ProgramLocal']		= /* NO MATCH*/null;
	$arrRates['localrate']				['Local 11/11c']								['Local']				= /* NO MATCH*/null;
	$arrRates['localrate']				['Local 11/11c']								['ProgramLocal']		= 'ProgramLocal-11';
	$arrRates['localrate']				['Local 16.36 (res 18c gst)']					['Local']				= 'Local-1636';
	$arrRates['localrate']				['Local 16.36 (res 18c gst)']					['ProgramLocal']		= 'ProgramLocal-1636';
	$arrRates['localrate']				['Tier 3 corporate Long Distance']				['Local']				= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['localrate']				['Tier 3 corporate Long Distance']				['ProgramLocal']		= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['localrate']				['Local 18c']									['Local']				= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['localrate']				['Local 18c']									['ProgramLocal']		= /* PLAN DOESN'T EXIST ANYMORE */null;
	
	$arrRates['natrate']				['Tier 3 corporate capped (6ff,8cpm)']			['National']			= 'National-08-06-01-70';
	$arrRates['natrate']				['Tier 3 corporate Local (6ff,8cpm)']			['National']			= 'National-08-06';
	$arrRates['natrate']				['Blue V.VOIP (0ff,10cpcall)']					['National']			= 'National-VOIP';
	$arrRates['natrate']				['39cent cap (6ff,8cpm)']						['National']			= 'National-08-06-01-39';
	$arrRates['natrate']				['Bus. Saver Capped (7ff,9cpm)']				['National']			= 'National-09-07-01-90';
	$arrRates['natrate']				['Blue 15c CTM  (6ff,6cpm)']					['National']			= 'National-06-06-01';
	$arrRates['natrate']				['VoiceTalk (10 ff/12cpm) cap']					['National']			= 'National-12-10-99';
	$arrRates['natrate']				['National 16']									['National']			= 'National-Nat16';
	$arrRates['natrate']				['Tier 3 corporate Mobile Saver (6.5ff,9cpm)']	['National']			= 'National-09-065';
	$arrRates['natrate']				['Tier 3 corp. L D (0 ff,10cpm)']				['National']			= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['natrate']				['7.5cpm no flag']								['National']			= /* NO MATCH*/null;
	$arrRates['natrate']				['True Blue Fleet (6ff,9cpm)']					['National']			= 'National-09-06-01';
	$arrRates['natrate']				['National 8c no ff']							['National']			= /* NO MATCH*/null;
	$arrRates['natrate']				['5.5cpm no flag']								['National']			= /* NO MATCH*/null;
	$arrRates['natrate']				['Residential (20ff,18cpm)']					['National']			= /* PLAN FOUND, INCORRECT RATE */null;
	$arrRates['natrate']				['Pinnacle (13c per call)']						['National']			= 'National-LL-Pinnacle';
	$arrRates['natrate']				['7cpm 0 flag']									['National']			= /* NO MATCH*/null;
	$arrRates['natrate']				['6 cpm 0 flag']								['National']			= /* NO MATCH*/null;
	
	$arrRates['mobrate']				['Tier 3 corporate capped(27cpm, 20c min)']		['Mobile']				= 'Mobile-27-00-01-130';
	$arrRates['mobrate']				['Tier 3 corporate Local Saver (9ff, 26cpm)']	['Mobile']				= 'Mobile-LL-26-09-01';
	$arrRates['mobrate']				['Virtual VOIP (30cpm, 30c min)']				['Mobile']				= 'Mobile-VOIP';
	$arrRates['mobrate']				['39cent cap (20min 27cpm)']					['Mobile']				= 'Mobile-27-00-01-89';
	$arrRates['mobrate']				['Business Saver Capped (10ff, 27cpm)']			['Mobile']				= 'Mobile-27-10-01-150';
	$arrRates['mobrate']				['Blue 15c CTM (15ff,15cpm)']					['Mobile']				= 'Mobile-15-15-01';
	$arrRates['mobrate']				['VoiceTalk standard (10ff, 27cpm)']			['Mobile']				= 'Mobile-27-10-01-99';
	$arrRates['mobrate']				['National 16 (30cpm, 20c min)']				['Mobile']				= 'Mobile-30-00-01';
	$arrRates['mobrate']				['Tier 3 corporate Mobile Saver (6ff, 26cpm)']	['Mobile']				= 'Mobile-26-06-01';
	$arrRates['mobrate']				['tier 3 corporate Long distance (5ff, 26cpm)']	['Mobile']				= /* PLAN DOESN'T EXIST ANYMORE */null;
	$arrRates['mobrate']				['Pinnacle (50cper call)']						['Mobile']				= 'Mobile-LL-Pinnacle';
	$arrRates['mobrate']				['CTM 26c no flag']								['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['True Blue Fleet (6ff, 25cpm)']				['Mobile']				= 'Mobile-25-06-01';
	$arrRates['mobrate']				['Voicetalk Feb06 cap']							['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['CTM 22 no flag']								['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['CTM 30 no ff']								['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['CTM 23cpm 0ff']								['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['Residential (20ff, 27.27cpm)']				['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['CTM 25c 0 flag']								['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['CTM 24c 0 flag']								['Mobile']				= /* NO MATCH*/null;
	$arrRates['mobrate']				['CTM 25pm 8ff']								['Mobile']				= /* NO MATCH*/null;
	
	$arrRates['intrate']				['Blue 15c CTM'] 								['IDD']					= 'IDD : Blue 15c CTM';
	$arrRates['intrate']				['Tier 3 corporate capped'] 					['IDD']					= 'IDD : Tier 3 corporate capped';
	$arrRates['intrate']				['Blue Virtual VOIP'] 							['IDD']					= 'IDD : Blue Virtual VOIP';
	$arrRates['intrate']				['39c Cap Intl'] 								['IDD']					= 'IDD : 39c Cap Intl';
	$arrRates['intrate']				['VoiceTalk'] 									['IDD']					= 'IDD : VoiceTalk';
	$arrRates['intrate']				['National 16'] 								['IDD']					= 'IDD : National 16';
	$arrRates['intrate']				['Tier 3 corporate Long Distance'] 				['IDD']					= 'IDD : Tier 3 corporate Long Distance';
	$arrRates['intrate']				['Mobile Zero Plan'] 							['IDD']					= 'IDD : Mobile Zero Plan';
	$arrRates['intrate']				['Tier 3 corporate Mobile Saver'] 				['IDD']					= 'IDD : Tier 3 corporate Mobile Saver';
	$arrRates['intrate']				['Residential'] 								['IDD']					= 'IDD : Residential';
	
	$arrRates['service_equip_rate']		['Tier 3 Corporate Capped']						['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['Tier 3 Corporate Local Saver']				['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['Blue Virtual VOIP']							['S&E']					= 'S&E-3635';
	$arrRates['service_equip_rate']		['Business Saver Capped']						['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['VoiceTalk']									['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['39cent cap']									['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['National 16']									['S&E']					= 'S&E-3595';
	$arrRates['service_equip_rate']		['Blue 15c CTM']								['S&E']					= 'S&E-3635';
	$arrRates['service_equip_rate']		['Tier 3 Corporate Mobile Saver']				['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['True Blue Fleet']								['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['Residential']									['S&E']					= 'S&E-3499';
	$arrRates['service_equip_rate']		['Pinnacle ($33.00)']							['S&E']					= 'S&E-3300';
	
	$arrRates['mobile']					['Mobile Zero Plan']							['Mobile']				= 'Mobile-30-10-01';
	$arrRates['mobile']					['Fleet Mobile 60']								['Mobile']				= 'Mobile-30-18-30';
	$arrRates['mobile']					['Pinnacle']									['Mobile']				= 'Mobile-Pinnacle';
	$arrRates['mobile']					['Fleet Mobile 30']								['Mobile']				= 'Mobile-40-18-30';
	$arrRates['mobile']					['Blue Shared 500']								['Mobile']				= 'Mobile-26-09-01';
	$arrRates['mobile']					['Fleet Mobile Peter K Special']				['Mobile']				= 'Mobile-30-15-30';
	
	$arrRates['mobilenational']			['Mobile Zero Plan']							['National']			= 'National-30-10-01';
	$arrRates['mobilenational']			['Fleet Mobile 60']								['National']			= 'National-30-18-30';
	$arrRates['mobilenational']			['Pinnacle']									['National']			= 'National-Pinnacle';
	$arrRates['mobilenational']			['Fleet Mobile 30']								['National']			= 'National-40-18-30';
	$arrRates['mobilenational']			['Blue Shared 500']								['National']			= 'National-26-09-01';
	$arrRates['mobilenational']			['Fleet Mobile Peter K special']				['National']			= 'National-30-15-30';
	
	$arrRates['mobile1800']				['Mobile Zero Plan']							['Freecall']			= 'Freecall-30-10-30';
	$arrRates['mobile1800']				['Fleet Mobile 60']								['Freecall']			= 'Freecall-30-18-30';
	$arrRates['mobile1800']				['Pinnacle']									['Freecall']			= 'Freecall-28-00-30';
	$arrRates['mobile1800']				['Fleet Mobile 30']								['Freecall']			= 'Freecall-40-18-30';
	$arrRates['mobile1800']				['Blue Shared 500']								['Freecall']			= 'Freecall-26-09-30';
	
	$arrRates['mobilevoicemail']		['Voicemail Retrievals']						['VoiceMailRetrieval']	= 'VoiceMailRetrieval-20-00-30';
	$arrRates['mobilevoicemail']		['Pinnacle']									['VoiceMailRetrieval']	= 'VoiceMailRetrieval-Pinnacle';
	
	$arrRates['mobilediverted']			['DiversionAll']								['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	$arrRates['mobilediverted']			['Pinnacle']									['VoiceMailDeposit']	= 'VoiceMailDeposit-10-00-30';
	
	$arrRates['mobilesms']				['25c inc SMS']									['SMS']					= 'SMS-22';
	$arrRates['mobilesms']				['Pinnacle 18c ex']								['SMS']					= 'SMS-18';
	$arrRates['mobilesms']				['22c inc SMS']									['SMS']					= 'SMS-20';
	
	$arrRates['mobilemms']				['MMS 75c inc (68.2 ex)']						['MMS']					= 'MMS-68';
	
	$arrRates['mobiledata']				['GPRS 2c ex (BS100,Fleet 30, Zero)']			['GPRS']				= 'GPRS-20-00-01';
	$arrRates['mobiledata']				['GPRS 1.5c ex (BS500, Fleet 60)']				['GPRS']				= 'GPRS-15-00-01';
	$arrRates['mobiledata']				['GPRS 1.8c ex (BS250)']						['GPRS']				= 'GPRS-18-00-01';
	
	$arrRates['mobileinternational']	['Mobile Zero Plan']							['IDD']					= 'Mobile Zero Plan';

	// set config rate array
	$arrConfig['Decode']['RateConvert'] = $arrRates;
	
// ---------------------------------------------------------------------------//
// SCRIPT
// ---------------------------------------------------------------------------//
	
	// setup a db query object
	$sqlQuery = new Query();
	
	// instanciate the etech decoder
	require_once('decode_etech.php');
	$objDecoder = new VixenDecode($arrConfig['Decode']);
	
	// instanciate the import object
	require_once('vixen_import.php');
	$objImport = new VixenImport($arrConfig['Import']);
	
	// Truncate Tables
	echo "Truncating Tables\n";
	$objImport->Truncate('Account');
	$objImport->Truncate('AccountGroup');
	$objImport->Truncate('CDR');
	$objImport->Truncate('Charge');
	$objImport->Truncate('ChargeType');
	$objImport->Truncate('Contact');
	$objImport->Truncate('CreditCard');
	$objImport->Truncate('DirectDebit');
	$objImport->Truncate('Employee');
	$objImport->Truncate('EmployeeAccountAudit');
	$objImport->Truncate('ErrorLog');
	$objImport->Truncate('FileDownLoad');
	$objImport->Truncate('FileImport');
	$objImport->Truncate('Invoice');
	$objImport->Truncate('InvoiceOutput');
	$objImport->Truncate('InvoicePayment');
	$objImport->Truncate('InvoiceTemp');
	$objImport->Truncate('Note');
	$objImport->Truncate('Payment');
	$objImport->Truncate('ProvisioningExport');
	$objImport->Truncate('ProvisioningLog');
	$objImport->Truncate('Rate');
	$objImport->Truncate('RateGroup');
	$objImport->Truncate('RateGroupRate');
	$objImport->Truncate('RatePlan');
	$objImport->Truncate('RatePlanRateGroup');
	$objImport->Truncate('RatePlanRecurringCharge');
	$objImport->Truncate('RecurringCharge');
	$objImport->Truncate('RecurringChargeType');
	$objImport->Truncate('Request');
	$objImport->Truncate('Service');
	$objImport->Truncate('ServiceAddress');
	$objImport->Truncate('ServiceRateGroup');
	$objImport->Truncate('ServiceRatePlan');
	$objImport->Truncate('ServiceRecurringCharge');
	$objImport->Truncate('ServiceTotal');
	$objImport->Truncate('ServiceTypeTotal');
	
	
	// clean import array
	$arrImport = Array();
	
	// Import Rates
	$arrImport['/home/vixen/vixen_seed/Rate/Rate.csv'] = 'Rate';
	$arrIDDRates = glob('/home/vixen/vixen_seed/Rate/IDD/*.csv');
	if (is_array($arrIDDRates))
	{
		foreach($arrIDDRates AS $strFilePath)
		{
			$arrImport[$strFilePath] = 'Rate';
		}
	}
	
	// Import RateGroup
	$arrImport['/home/vixen/vixen_seed/RateGroup/RateGroup.csv'] = 'RateGroup';
	
	// Import RatePlan
	$arrImport['/home/vixen/vixen_seed/RatePlan/RatePlan.csv'] = 'RatePlan';
	
	// Import Employee
	$arrImport['/home/vixen/vixen_seed/Employee/Employee.csv'] = 'Employee';
	
	// Import Charge Types
	$arrImport['/home/vixen/vixen_seed/RecurringChargeType/RecurringChargeType.csv'] = 'RecurringChargeType';
	$arrImport['/home/vixen/vixen_seed/ChargeType/ChargeType.csv'] = 'ChargeType';
	
	// Do Imports
	foreach ($arrImport AS $strFilePath=>$strTable)
	{
		$strFileName = basename($strFilePath, '.csv');
		echo "Importing $strTable : $strFileName\n";
		if (!$objImport->ImportCSV($strTable, $strFilePath))
		{
			echo "FATAL ERROR : Import $strFileName FAILED\n";
			Die();
		}
	}
	
	// Match RateGroups to Rates
	echo "matching RateGroups to Rates\n";
	if (!$objImport->CreateRateGroupRate())
	{
		echo "FATAL ERROR : Could not match RateGroups to Rates\n";
		Die();
	}
	
	// Match RatePlans to RateGroups
	echo "matching RatePlans to RateGroups\n";
	if (!$objImport->CreateRatePlanRateGroup())
	{
		echo "FATAL ERROR : Could not match RatePlans to RateGroups\n";
		Die();
	}
	echo $objImport->ErrorLog();
	
	// Match RatePlans to Recurring Charges
	echo "matching RatePlans to RecurringCharges\n";
	if (!$objImport->CreateRatePlanRecurringCharge())
	{
		echo "FATAL ERROR : Could not match RatePlans to RecurringCharges\n";
		Die();
	}
	
	// Add Customers
	
	while ($arrRow = $objDecoder->FetchCustomer())
	{	
		// get the etech customer details
		$arrScrape = $arrRow['DataArray'];
		$arrScrape['CustomerId'] = $arrRow['CustomerId'];

		// decode the customer
		echo "Decoding Customer   : {$arrRow['CustomerId']}\n";
		$arrCustomer = $objDecoder->DecodeCustomer($arrScrape);
		
		// counters
		$intServiceCount += (int)$arrCustomer['ServiceCount'];
		$intRawServiceCount += (int)$arrCustomer['RawServiceCount'];
		$intCustomerCount++;
		
		// check service count
		$intCountServices = count($arrCustomer['Service']);
		if ($intCountServices != $arrCustomer['ServiceCount'])
		{
			echo "WARN : Actual Services : $intCountServices DOES NOT = Service Count : {$arrCustomer['ServiceCount']}\n";
		}
		
		// add the customer
		echo "Importing Customer  : {$arrRow['CustomerId']}\n";
		if (!$objImport->AddCustomerWithId($arrCustomer))
		{
			echo "FATAL ERROR : Could not add Customer : {$arrRow['CustomerId']}\n";
			echo $objImport->ErrorLog();
			Die();
		}
		
	}

	// Add System Notes
	while ($arrRow = $objDecoder->FetchSystemNote())
	{	
		// get the etech note details
		$arrScrape = $arrRow['DataArray'];

		if ($arrScrape)
		{
			// decode the note
			echo "Decoding Note for   : {$arrRow['CustomerId']}\n";
			$arrNotes = $objDecoder->DecodeSystemNote($arrScrape);
			
			// add the note
			echo "Importing Notes for  : {$arrRow['CustomerId']}\n";
			$objImport->AddCustomerNote($arrNotes);
		}
		else
		{
			//echo "No Notes found for  : {$arrRow['CustomerId']}\n";
		}
	}
	
	// Add User Notes
	while ($arrRow = $objDecoder->FetchUserNote())
	{	
		// get the etech note details
		$arrScrape = $arrRow['DataArray'];

		if ($arrScrape)
		{
			// decode the note
			echo "Decoding Notes for   : {$arrRow['CustomerId']}\n";
			$arrNotes = $objDecoder->DecodeUserNote($arrScrape);
			
			// add the note
			echo "Importing Notes for  : {$arrRow['CustomerId']}\n";
			$objImport->AddCustomerNote($arrNotes);
		}
		else
		{
			//echo "No Notes found for  : {$arrRow['CustomerId']}\n";
		}
	}
	
	//finish
	echo "Done\n";
	echo "Added : $intCustomerCount Accounts\n";
	echo "Added : $intRawServiceCount Raw Services\n";
	echo "Added : $intServiceCount Actual Services\n";
	Die ();

// ---------------------------------------------------------------------------//
// IMPORT CLASS
// ---------------------------------------------------------------------------//	
	

?>

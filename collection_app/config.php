<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DEFINITIONS
//----------------------------------------------------------------------------//
/**
 * config
 *
 * ApplicationConfig Definitions
 *
 * This file exclusively declares application config constants
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

		/* Skeleton
		$arrConfig['Define']["Skeleton"]	["Name"]		= "Skeleton";
		$arrConfig['Define']["Skeleton"]	["Carrier"]		= CARRIER_UNITEL;
 		$arrConfig['Define']["Skeleton"]	["Type"]		= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Skeleton"]	["Server"]		= DEFAULT_FTP_SERVER;
 		$arrConfig['Define']["Skeleton"]	["Username"]	= DEFAULT_FTP_USERNAME;
 		$arrConfig['Define']["Skeleton"]	["PWord"]		= DEFAULT_FTP_PASSWORD;
 		$arrConfig['Define']["Skeleton"]	["Dir"][]		= 'skeleton/';
 		$arrConfig['Define']["Skeleton"]	["FinalDir"]	= DESTINATION_ROOT.'/skeleton/';
		$arrConfig['Define']["Skeleton"]	["FileType"][REGEX_SKELETON]	= CDR_SKELETON;
		$arrConfig['Define']["Skeleton"]	["FileType"]['/test.txt/']		= CDR_SKELETON;
		*/
		/* TODO: UNCOMMENT THIS WHEN WE GO LIVE
		// Unitel Definition
		$arrConfig['Define']["Unitel"]	["Name"]							= "Unitel";
		$arrConfig['Define']["Unitel"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["Unitel"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Unitel"]	["Server"]							= "rslcom.com.au";
 		$arrConfig['Define']["Unitel"]	["Username"]						= "sp058";
 		$arrConfig['Define']["Unitel"]	["PWord"]							= "BuzzaBee06*#";
 		$arrConfig['Define']["Unitel"]	["Dir"][]							= "cdrbatches/";
 		$arrConfig['Define']["Unitel"]	["Dir"][]							= "ebill_dailyorderfiles/dsc_reports/";
 		//$arrConfig['Define']["Unitel"]	["Dir"][]						= "cdrbatchesoffnet/";
 		//$arrConfig['Define']["Unitel"]	["Dir"][]						= "mobilecdrbatches/";
 		$arrConfig['Define']["Unitel"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSLCOM]			= CDR_UNITEL_RSLCOM;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_COMMANDER]		= CDR_UNITEL_COMMANDER;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_ORDER_RPT]	= PRV_UNITEL_DAILY_ORDER_RPT;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_STATUS_RPT]	= PRV_UNITEL_DAILY_STATUS_RPT;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_BASKETS]		= PRV_UNITEL_BASKETS_RPT;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_PRESELECTION]= PRV_UNITEL_PRESELECTION_RPT;

		// Optus Definition
		$arrConfig['Define']["Optus"]	["Name"]						= "Optus";
		$arrConfig['Define']["Optus"]	["Carrier"]						= CARRIER_OPTUS;
 		$arrConfig['Define']["Optus"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Optus"]	["Server"]						= "cyrene";				// FIXME
 		$arrConfig['Define']["Optus"]	["Username"]					= "flame";				// FIXME
 		$arrConfig['Define']["Optus"]	["PWord"]						= "flame";				// FIXME
 		$arrConfig['Define']["Optus"]	["Dir"][]						= "optustest/";			// FIXME
 		$arrConfig['Define']["Optus"]	["FinalDir"]					= DESTINATION_ROOT."optus/";
		$arrConfig['Define']["Optus"]	["FileType"][REGEX_OPTUS]		= CDR_OPTUS_STANDARD;

		// AAPT Definition
		$arrConfig['Define']["AAPT"]	["Name"]						= "AAPT";
		$arrConfig['Define']["AAPT"]	["Carrier"]						= CARRIER_AAPT;
 		$arrConfig['Define']["AAPT"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["AAPT"]	["Server"]						= "cyrene";				// FIXME
 		$arrConfig['Define']["AAPT"]	["Username"]					= "flame";				// FIXME
 		$arrConfig['Define']["AAPT"]	["PWord"]						= "flame";				// FIXME
 		$arrConfig['Define']["AAPT"]	["ZipPword"]					= "flame";				// FIXME
 		$arrConfig['Define']["AAPT"]	["Dir"][]						= "aapttest/";			// FIXME
 		$arrConfig['Define']["AAPT"]	["FinalDir"]					= DESTINATION_ROOT."aapt/";
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT]		= CDR_AAPT_STANDARD;
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_EOE]	= PRV_AAPT_EOE_RETURN;
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_LSD]	= PRV_AAPT_LSD;
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_REJECT]	= PRV_AAPT_REJECT;
		*/
		
		/*
		// iSeek Definition
		$arrConfig['Define']["iSeek"]	["Name"]						= "iSeek";
		$arrConfig['Define']["iSeek"]	["Carrier"]						= CARRIER_ISEEK;
 		$arrConfig['Define']["iSeek"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["iSeek"]	["Server"]						= DEFAULT_FTP_SERVER;				// FIXME
 		$arrConfig['Define']["iSeek"]	["Username"]					= DEFAULT_FTP_USERNAME;				// FIXME
 		$arrConfig['Define']["iSeek"]	["PWord"]						= DEFAULT_FTP_PASSWORD;				// FIXME
 		$arrConfig['Define']["iSeek"]	["Dir"][]						= 'dir1/';							// FIXME
 		$arrConfig['Define']["iSeek"]	["FinalDir"]					= DESTINATION_ROOT.'iseek/';
		$arrConfig['Define']["iSeek"]	["FileType"][REGEX_ISEEK]		= CDR_ISEEK_STANDARD;
		*/
		
		
		
		
		
		// TESTING DEFINITIONS
		$strTestUsername	= "download";
		$strTestPassword	= "password";
		$strTestDir			= "/home/CDR/";
		$strTestServer		= "10.11.12.13";
		
		// Unitel Definition
		$strUnitelTestDir = "2006/dec/";
		$arrConfig['Define']["Unitel"]	["Name"]							= "Unitel";
		$arrConfig['Define']["Unitel"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["Unitel"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Unitel"]	["Server"]							= $strTestServer;
 		$arrConfig['Define']["Unitel"]	["Username"]						= $strTestUsername;
 		$arrConfig['Define']["Unitel"]	["PWord"]							= $strTestPassword;
 		$arrConfig['Define']["Unitel"]	["Dir"][]							= $strTestDir."unitel/cdrbatches/".$strUnitelTestDir;
 		$arrConfig['Define']["Unitel"]	["Dir"][]							= $strTestDir."unitel/cdrbatchesoffnet/".$strUnitelTestDir;
 		$arrConfig['Define']["Unitel"]	["Dir"][]							= $strTestDir."unitel/mobilecdrbatches/".$strUnitelTestDir;
 		$arrConfig['Define']["Unitel"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSLCOM]			= CDR_UNITEL_RSLCOM;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_COMMANDER]		= CDR_UNITEL_COMMANDER;
		$arrConfig['Define']["Unitel"]	["FileType"][REGEX_UNITEL_SE]		= CDR_UNITEL_SE;
		//$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_ORDER_RPT]	= PRV_UNITEL_DAILY_ORDER_RPT;
		//$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_STATUS_RPT]	= PRV_UNITEL_DAILY_STATUS_RPT;
		//$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_BASKETS]		= PRV_UNITEL_BASKETS_RPT;
		//$arrConfig['Define']["Unitel"]	["FileType"][REGEX_RSL_PRESELECTION]= PRV_UNITEL_PRESELECTION_RPT;
		
		// Optus Definition
		$strOptusTestDir = "Dec 06/";
		$arrConfig['Define']["Optus"]	["Name"]						= "Optus";
		$arrConfig['Define']["Optus"]	["Carrier"]						= CARRIER_OPTUS;
 		$arrConfig['Define']["Optus"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Optus"]	["Server"]						= $strTestServer;
 		$arrConfig['Define']["Optus"]	["Username"]					= $strTestUsername;
 		$arrConfig['Define']["Optus"]	["PWord"]						= $strTestPassword;
 		$arrConfig['Define']["Optus"]	["Dir"][]						= $strTestDir."optus/".$strOptusTestDir."Speedi Files/";
 		$arrConfig['Define']["Optus"]	["FinalDir"]					= DESTINATION_ROOT."optus/";
		$arrConfig['Define']["Optus"]	["FileType"][REGEX_OPTUS]		= CDR_OPTUS_STANDARD;
		
		// AAPT Definition
		$arrConfig['Define']["AAPT"]	["Name"]						= "AAPT";
		$arrConfig['Define']["AAPT"]	["Carrier"]						= CARRIER_AAPT;
 		$arrConfig['Define']["AAPT"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["AAPT"]	["Server"]						= $strTestServer;
 		$arrConfig['Define']["AAPT"]	["Username"]					= $strTestUsername;
 		$arrConfig['Define']["AAPT"]	["PWord"]						= $strTestPassword;
 		$arrConfig['Define']["AAPT"]	["Dir"][]						= $strTestDir."aapt/".$strOptusTestDir;
 		$arrConfig['Define']["AAPT"]	["FinalDir"]					= DESTINATION_ROOT."aapt/";
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT]		= CDR_AAPT_STANDARD;
		//$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_EOE]	= PRV_AAPT_EOE_RETURN;
		//$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_LSD]	= PRV_AAPT_LSD;
		//$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_REJECT]	= PRV_AAPT_REJECT;
		
		
?>

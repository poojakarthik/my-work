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
		/*
		// eg. "Jan 07/"
		//$strDateDir				= date("M y/", strtotime("-1 day", time()));
		$strDateDir				= "Apr 07/";
		$strLocalFTPUsername	= "download";
		$strLocalFTPPassword	= "password";
		$strLocalFTPDir			= "/home/CDR/";
		$strLocalFTPServer		= "10.11.12.13";
		*/

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
/**/
		
		// Unitel Landline Definition
		$arrConfig['Define']["TBUnitel"]	["Name"]							= "TelcoBlue: Unitel Landline/S&E";
		$arrConfig['Define']["TBUnitel"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["TBUnitel"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["TBUnitel"]	["Server"]							= "rslcom.com.au";
 		$arrConfig['Define']["TBUnitel"]	["Username"]						= "sp058";
 		$arrConfig['Define']["TBUnitel"]	["PWord"]							= "BuzzaBee06*#";
 		$arrConfig['Define']["TBUnitel"]	["Dir"][]							= "cdrbatches/";
 		$arrConfig['Define']["TBUnitel"]	["Dir"][]							= "cdrbatchesoffnet/";
 		//$arrConfig['Define']["TBUnitel"]	["Dir"][]							= "ebill_dailyorderfiles/dsc_reports/";
 		//$arrConfig['Define']["TBUnitel"]	["Dir"][]							= "ebill_dailyorderfiles/dsc_reports/archive";
 		//$arrConfig['Define']["TBUnitel"]	["Dir"][]							= "dailychurn/";
 		$arrConfig['Define']["TBUnitel"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["TBUnitel"]	["FileType"][REGEX_RSLCOM]			= CDR_UNITEL_RSLCOM;
		$arrConfig['Define']["TBUnitel"]	["FileType"][REGEX_UNITEL_SE]		= CDR_UNITEL_RSLCOM;
		//$arrConfig['Define']["TBUnitel"]	["FileType"][REGEX_RSL_ORDER_RPT]	= PRV_UNITEL_DAILY_ORDER_RPT;
		//$arrConfig['Define']["TBUnitel"]	["FileType"][REGEX_RSL_STATUS_RPT]	= PRV_UNITEL_DAILY_STATUS_RPT;
		//$arrConfig['Define']["TBUnitel"]	["FileType"][REGEX_RSL_BASKETS]		= PRV_UNITEL_BASKETS_RPT;
		//$arrConfig['Define']["TBUnitel"]	["FileType"][REGEX_RSL_PRESELECTION]= PRV_UNITEL_PRESELECTION_RPT;
		
		$arrConfig['Define']["VTUnitel"]	["Name"]							= "VoiceTalk: Unitel Landline/S&E";
		$arrConfig['Define']["VTUnitel"]	["Carrier"]							= CARRIER_UNITEL_VOICETALK;
 		$arrConfig['Define']["VTUnitel"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["VTUnitel"]	["Server"]							= "rslcom.com.au";
 		$arrConfig['Define']["VTUnitel"]	["Username"]						= "sp321";
 		$arrConfig['Define']["VTUnitel"]	["PWord"]							= "KfYRSBOgm4Ci";
 		$arrConfig['Define']["VTUnitel"]	["Dir"][]							= "cdrbatches/";
 		$arrConfig['Define']["VTUnitel"]	["Dir"][]							= "cdrbatchesoffnet/";
 		$arrConfig['Define']["VTUnitel"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["VTUnitel"]	["FileType"][REGEX_RSLCOM]			= CDR_UNITEL_RSLCOM;
		$arrConfig['Define']["VTUnitel"]	["FileType"][REGEX_UNITEL_SE]		= CDR_UNITEL_RSLCOM;		
		
		
		// Unitel Mobile Definition
		// (needs a separate definition because the regex's for Commander and LL are the same, but different file format)
		$arrConfig['Define']["TBUnitelMobile"]	["Name"]							= "TelcoBlue: Unitel Commander Mobile";
		$arrConfig['Define']["TBUnitelMobile"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["TBUnitelMobile"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["TBUnitelMobile"]	["Server"]							= "rslcom.com.au";
 		$arrConfig['Define']["TBUnitelMobile"]	["Username"]						= "sp058";
 		$arrConfig['Define']["TBUnitelMobile"]	["PWord"]							= "BuzzaBee06*#";
 		$arrConfig['Define']["TBUnitelMobile"]	["Dir"][]							= "mobilecdrbatches/";
 		$arrConfig['Define']["TBUnitelMobile"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["TBUnitelMobile"]	["FileType"][REGEX_COMMANDER]		= CDR_UNITEL_COMMANDER;
		
		$arrConfig['Define']["VTUnitelMobile"]	["Name"]							= "VoiceTalk: Unitel Commander Mobile";
		$arrConfig['Define']["VTUnitelMobile"]	["Carrier"]							= CARRIER_UNITEL_VOICETALK;
 		$arrConfig['Define']["VTUnitelMobile"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["VTUnitelMobile"]	["Server"]							= "rslcom.com.au";
 		$arrConfig['Define']["VTUnitelMobile"]	["Username"]						= "sp321";
 		$arrConfig['Define']["VTUnitelMobile"]	["PWord"]							= "KfYRSBOgm4Ci";
 		$arrConfig['Define']["VTUnitelMobile"]	["Dir"][]							= "mobilecdrbatches/";
 		$arrConfig['Define']["VTUnitelMobile"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["VTUnitelMobile"]	["FileType"][REGEX_COMMANDER]		= CDR_UNITEL_COMMANDER;
		
		
		// Optus Definition	
		$arrConfig['Define']["Optus"]	["Name"]						= "Optus";
		$arrConfig['Define']["Optus"]	["Carrier"]						= CARRIER_OPTUS;
 		$arrConfig['Define']["Optus"]	["Type"]						= COLLECTION_TYPE_OPTUS;
 		$arrConfig['Define']["Optus"]	["Server"]						= "https://www.optus.com.au/wholesalenet/";
 		$arrConfig['Define']["Optus"]	["Username"]					= 948115;
 		$arrConfig['Define']["Optus"]	["PWord"]						= 720883;
 		//$arrConfig['Define']["Optus"]	["Dir"][]						= $strLocalFTPDir."optus/".$strDateDir."Speedi Files/";
 		//$arrConfig['Define']["Optus"]	["Dir"][]						= "/home/richdavis/ftp/optus/PPR/";
 		$arrConfig['Define']["Optus"]	["FinalDir"]					= DESTINATION_ROOT."optus/";
		$arrConfig['Define']["Optus"]	["FileType"][REGEX_OPTUS]		= CDR_OPTUS_STANDARD;
		//$arrConfig['Define']["Optus"]	["FileType"]["/^BPR\d{3}\_B\d{7}\_S\d{4}\_\d{8}$/"]	= PROV_OPTUS_IMPORT;
		

		// AAPT Definition
		$arrConfig['Define']["AAPT"]	["Name"]						= "AAPT";
		$arrConfig['Define']["AAPT"]	["Carrier"]						= CARRIER_AAPT;
 		$arrConfig['Define']["AAPT"]	["Type"]						= COLLECTION_TYPE_AAPT;
 		$arrConfig['Define']["AAPT"]	["AlwaysUnique"]				= TRUE;
 		$arrConfig['Define']["AAPT"]	["Server"]						= "https://wholesalebbs.aapt.com.au/";
 		$arrConfig['Define']["AAPT"]	["Username"]					= "telcoblue";
 		$arrConfig['Define']["AAPT"]	["PWord"]						= "zbj6v04ls";
 		$arrConfig['Define']["AAPT"]	["ZipPWord"]					= "zbj6v04ls";
 		$arrConfig['Define']["AAPT"]	["FinalDir"]					= DESTINATION_ROOT."aapt/";
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT]		= CDR_AAPT_STANDARD;
		//$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_EOE]	= PRV_AAPT_EOE_RETURN;
		//$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_LSD]	= PRV_AAPT_LSD;
		//$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT_REJECT]	= PRV_AAPT_REJECT;
		
		
		// iSeek Definition
		$arrConfig['Define']["iSeek"]	["Name"]						= "iSeek";
		$arrConfig['Define']["iSeek"]	["Carrier"]						= CARRIER_ISEEK;
 		$arrConfig['Define']["iSeek"]	["Type"]						= COLLECTION_TYPE_SSH2;
 		$arrConfig['Define']["iSeek"]	["Server"]						= "adsl2.iseek.com.au";
 		$arrConfig['Define']["iSeek"]	["Username"]					= "telcoblue";
 		$arrConfig['Define']["iSeek"]	["PWord"]						= "dh6aekZ8";
 		$arrConfig['Define']["iSeek"]	["Dir"][]						= 'speedi/';
 		$arrConfig['Define']["iSeek"]	["FinalDir"]					= DESTINATION_ROOT.'iseek/';
		$arrConfig['Define']["iSeek"]	["FileType"][REGEX_ISEEK]		= CDR_ISEEK_STANDARD;
		
		
		
		// FIXME: Uncomment this

		// TESTING DEFINITIONS
		$strTestUsername	= "download";
		$strTestPassword	= "password";
		$strTestDir			= "/home/CDR/";
		//$strTestServer		= "10.11.12.13";
		$strTestServer		= "192.168.2.13";
		
		// Unitel Landline and S&E Definition
		$strUnitelTestDir = strtolower(date("Y/M"));
		$arrConfig['Define']["RSLCOM"]	["Name"]							= "RSLCOM";
		$arrConfig['Define']["RSLCOM"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["RSLCOM"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["RSLCOM"]	["Server"]							= $strTestServer;
 		$arrConfig['Define']["RSLCOM"]	["Username"]						= $strTestUsername;
 		$arrConfig['Define']["RSLCOM"]	["PWord"]							= $strTestPassword;
 		$arrConfig['Define']["RSLCOM"]	["Dir"][]							= $strTestDir."unitel/cdrbatches/".$strUnitelTestDir;
 		$arrConfig['Define']["RSLCOM"]	["Dir"][]							= $strTestDir."unitel/cdrbatchesoffnet/".$strUnitelTestDir;
 		$arrConfig['Define']["RSLCOM"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_RSLCOM]			= CDR_UNITEL_RSLCOM;
		$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_UNITEL_SE]		= CDR_UNITEL_RSLCOM;
		//$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_RSL_ORDER_RPT]	= PRV_UNITEL_DAILY_ORDER_RPT;
		//$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_RSL_STATUS_RPT]	= PRV_UNITEL_DAILY_STATUS_RPT;
		//$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_RSL_BASKETS]		= PRV_UNITEL_BASKETS_RPT;
		//$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_RSL_PRESELECTION]= PRV_UNITEL_PRESELECTION_RPT;
/*
		// Unitel Landline and S&E Definition
		//$strUnitelTestDir = "2007/apr/";
		$arrConfig['Define']["Commander"]	["Name"]							= "Commander";
		$arrConfig['Define']["Commander"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["Commander"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Commander"]	["Server"]							= $strTestServer;
 		$arrConfig['Define']["Commander"]	["Username"]						= $strTestUsername;
 		$arrConfig['Define']["Commander"]	["PWord"]							= $strTestPassword;
 		$arrConfig['Define']["Commander"]	["Dir"][]							= $strTestDir."unitel/mobilecdrbatches/".$strUnitelTestDir;
 		$arrConfig['Define']["Commander"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["Commander"]	["FileType"][REGEX_COMMANDER]		= CDR_UNITEL_COMMANDER;
		//$arrConfig['Define']["Commander"]	["FileType"][REGEX_RSL_ORDER_RPT]	= PRV_UNITEL_DAILY_ORDER_RPT;
		//$arrConfig['Define']["Commander"]	["FileType"][REGEX_RSL_STATUS_RPT]	= PRV_UNITEL_DAILY_STATUS_RPT;
		//$arrConfig['Define']["Commander"]	["FileType"][REGEX_RSL_BASKETS]		= PRV_UNITEL_BASKETS_RPT;
		//$arrConfig['Define']["Commander"]	["FileType"][REGEX_RSL_PRESELECTION]= PRV_UNITEL_PRESELECTION_RPT;
		

		// Optus Definition
		$strOptusTestDir = "Apr 07/";
		$arrConfig['Define']["Optus"]	["Name"]						= "Optus";
		$arrConfig['Define']["Optus"]	["Carrier"]						= CARRIER_OPTUS;
 		$arrConfig['Define']["Optus"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Optus"]	["Server"]						= $strTestServer;
 		$arrConfig['Define']["Optus"]	["Username"]					= $strTestUsername;
 		$arrConfig['Define']["Optus"]	["PWord"]						= $strTestPassword;
 		//$arrConfig['Define']["Optus"]	["Dir"][]						= $strTestDir."optus/".$strOptusTestDir."Speedi Files/";
		$arrConfig['Define']["Optus"]	["Dir"][]						= $strTestDir."optus/".$strOptusTestDir;
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
*/

		// iSeek Temp Definition
		$strOptusTestDir = "Oct 07/";
		$arrConfig['Define']["iSeek"]	["Name"]						= "iSeek";
		$arrConfig['Define']["iSeek"]	["Carrier"]						= CARRIER_ISEEK;
 		$arrConfig['Define']["iSeek"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["iSeek"]	["Server"]						= $strTestServer;
 		$arrConfig['Define']["iSeek"]	["Username"]					= $strTestUsername;
 		$arrConfig['Define']["iSeek"]	["PWord"]						= $strTestPassword;
		$arrConfig['Define']["iSeek"]	["Dir"][]						= $strTestDir."iseek/".$strOptusTestDir;
 		$arrConfig['Define']["iSeek"]	["FinalDir"]					= DESTINATION_ROOT."iseek/";
		$arrConfig['Define']["iSeek"]	["FileType"][REGEX_ISEEK]		= CDR_ISEEK_STANDARD;



		//--------------------------------//
		// PAYMENT COLLECTION DEFINITIONS //
		//--------------------------------//
		
		$arrConfig['Define']["Payments"]	["Name"]															= "Payments";
		$arrConfig['Define']["Payments"]	["Carrier"]															= CARRIER_PAYMENT;
 		$arrConfig['Define']["Payments"]	["Type"]															= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Payments"]	["Server"]															= "10.11.12.13";
 		$arrConfig['Define']["Payments"]	["Username"]														= "telcoblue";
 		$arrConfig['Define']["Payments"]	["PWord"]															= "vixen1245";
 		$arrConfig['Define']["Payments"]	["Dir"][]															= 'payment/bill_express';
 		$arrConfig['Define']["Payments"]	["Dir"][]															= 'payment/bpay';
 		$arrConfig['Define']["Payments"]	["Dir"][]															= 'payment/secure_pay';
 		//$arrConfig['Define']["Payments"]	["Dir"][]															= 'payment/securepayrejects';
 		$arrConfig['Define']["Payments"]	["FinalDir"]														= DESTINATION_ROOT.'payment/';
		$arrConfig['Define']["Payments"]	["FileType"]['/^(TELCOBLUE|VOICETALK) \d{6}\.txt$/i']				= PAYMENT_TYPE_BILLEXPRESS;
		$arrConfig['Define']["Payments"]	["FileType"]['/^bpay \d{6}\.csv$/i']								= PAYMENT_TYPE_BPAY;
		$arrConfig['Define']["Payments"]	["FileType"]['/^ERP\_\d{1,2}-\d{1,2}-\d{4}\_\d{1,10}\.CSV$/i']		= PAYMENT_TYPE_BPAY;
		$arrConfig['Define']["Payments"]	["FileType"]['/^SAE\d{2}_\d{4}-\d{2}-\d{2}_\d{2}_\d{1,4}\.txt$/i']	= PAYMENT_TYPE_SECUREPAY;

		
?>

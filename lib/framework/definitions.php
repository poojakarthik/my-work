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
 * DEFINITIONS
 *
 * Global Definitions
 *
 * This file exclusively declares global constants
 *
 * @file		definitions.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONSTANTS
//----------------------------------------------------------------------------//

// viXen File Storage Base Directory
define("FILE_BASE_DIR"				, "/home/vixen/");

// db access
define("DB_WITH_ID"					, TRUE);

// debug mode
define("DEBUG_MODE"					, TRUE);

// file logging
define("LOG_TO_FILE"				, TRUE);
define("SAFE_LOGGING"				, FALSE);

// Backend's Employee Id
define("USER_ID"					, 999999999);

// Applications
define("APPLICATION_COLLECTION"		, 0);
define("APPLICATION_NORMALISATION"	, 1);
define("APPLICATION_RATING"			, 2);
define("APPLICATION_BILLING"		, 3);
define("APPLICATION_PROVISIONING"	, 4);
define("APPLICATION_PAYMENTS"		, 5);

// friendly error msg
//TODO!!!! - make this a little more friendly
define("ERROR_MESSAGE"				, "an error occured... sucks to be you");

// CDR TYPES
/*define("CDR_UNKNOWN"				, 0);
define("CDR_UNITEL_RSLCOM"			, 1);
define("CDR_UNITEL_COMMANDER"		, 2);
define("CDR_OPTUS_STANDARD"			, 3);
define("CDR_AAPT_STANDARD"			, 4);
define("CDR_ISEEK_STANDARD"			, 5);
define("CDR_UNITEL_SE"				, CDR_UNITEL_RSLCOM);
define("CDR_UNTIEL_RSLCOM"			, CDR_UNITEL_RSLCOM);		// Backwards Compatability
define("CDR_UNTIEL_COMMANDER"		, CDR_UNITEL_COMMANDER);	// Backwards Compatability*/
//----------------------------------------------------------------------------//
// These are now stored in the database and loaded from the LoadFramework()
//----------------------------------------------------------------------------//
/*
$GLOBALS['*arrConstant']	['CDRType']	[0]	['Constant']	= 'CDR_UNKNOWN';
$GLOBALS['*arrConstant']	['CDRType']	[0]	['Description']	= 'Unknown Type';
$GLOBALS['*arrConstant']	['CDRType']	[1]	['Constant']	= 'CDR_UNITEL_RSLCOM';
$GLOBALS['*arrConstant']	['CDRType']	[1]	['Description']	= 'Unitel Usage/S&E/OC&C';
$GLOBALS['*arrConstant']	['CDRType']	[2]	['Constant']	= 'CDR_UNITEL_COMMANDER';
$GLOBALS['*arrConstant']	['CDRType']	[2]	['Description']	= 'Unitel Mobile Usage';
$GLOBALS['*arrConstant']	['CDRType']	[3]	['Constant']	= 'CDR_OPTUS_STANDARD';
$GLOBALS['*arrConstant']	['CDRType']	[3]	['Description']	= 'Optus Usage';
$GLOBALS['*arrConstant']	['CDRType']	[4]	['Constant']	= 'CDR_AAPT_STANDARD';
$GLOBALS['*arrConstant']	['CDRType']	[4]	['Description']	= 'AAPT Usage';
$GLOBALS['*arrConstant']	['CDRType']	[5]	['Constant']	= 'CDR_ISEEK_STANDARD';
$GLOBALS['*arrConstant']	['CDRType']	[5]	['Description']	= 'iSeek Usage';
$GLOBALS['*arrConstant']	['CDRType']	[6]	['Constant']	= 'CDR_ISEEK_BROADBAND';
$GLOBALS['*arrConstant']	['CDRType']	[6]	['Description']	= 'iSeek Broadband Usage';
*/

// Collection Server Types
//----------------------------------------------------------------------------//
// These are now stored in the database and loaded from the LoadFramework()
//----------------------------------------------------------------------------//
/*
$GLOBALS['*arrConstant']	['CollectionType']	[100]	['Constant']	= 'COLLECTION_TYPE_FTP';
$GLOBALS['*arrConstant']	['CollectionType']	[100]	['Description']	= 'FTP';
$GLOBALS['*arrConstant']	['CollectionType']	[101]	['Constant']	= 'COLLECTION_TYPE_AAPT';
$GLOBALS['*arrConstant']	['CollectionType']	[101]	['Description']	= 'HTTPS/AAPT';
$GLOBALS['*arrConstant']	['CollectionType']	[102]	['Constant']	= 'COLLECTION_TYPE_OPTUS';
$GLOBALS['*arrConstant']	['CollectionType']	[102]	['Description']	= 'HTTPS/OPTUS';
$GLOBALS['*arrConstant']	['CollectionType']	[103]	['Constant']	= 'COLLECTION_TYPE_SSH2';
$GLOBALS['*arrConstant']	['CollectionType']	[103]	['Description']	= 'SSH2';
*/

// Provisioning Types
define("PRV_IMPORT_RANGE_MIN"			, 100);
define("PRV_IMPORT_RANGE_MAX"			, 199);
/*define("PRV_UNITEL_DAILY_ORDER_RPT"		, 100);
define("PRV_UNITEL_DAILY_STATUS_RPT"	, 101);
define("PRV_UNITEL_BASKETS_RPT"			, 102);
define("PRV_UNITEL_OUT"					, 103);
define("PRV_OPTUS_ALL"					, 104);
define("PRV_AAPT_ALL"					, 105);
define("PRV_UNITEL_PRESELECTION_RPT"	, 106);
define("PRV_AAPT_EOE_RETURN"			, 107);
define("PRV_AAPT_LSD"					, 108);
define("PRV_AAPT_REJECT"				, 109);
define("PRV_AAPT_LOSS"					, 110);

define("PRV_UNITEL_PRESELECTION_EXP"	, 150);
define("PRV_UNITEL_DAILY_ORDER_EXP"		, 151);
define("PRV_AAPT_EOE"					, 152);
*/
//----------------------------------------------------------------------------//
// These are now stored in the database and loaded from the LoadFramework()
//----------------------------------------------------------------------------//
/*
$GLOBALS['*arrConstant']	['ProvisioningType']	[100]	['Constant']	= 'PRV_UNITEL_DAILY_ORDER_RPT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[100]	['Description']	= 'Unitel Daily Order Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[101]	['Constant']	= 'PRV_UNITEL_DAILY_STATUS_RPT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[101]	['Description']	= 'Unitel Daily Status Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[102]	['Constant']	= 'PRV_UNITEL_BASKETS_RPT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[102]	['Description']	= 'Unitel Agreed Baskets Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[103]	['Constant']	= 'PRV_UNITEL_OUT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[103]	['Description']	= 'Unitel Standard Output File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[104]	['Constant']	= 'PROV_OPTUS_IMPORT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[104]	['Description']	= 'Optus Line Status Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[105]	['Constant']	= 'PRV_AAPT_ALL';
$GLOBALS['*arrConstant']	['ProvisioningType']	[105]	['Description']	= 'AAPT Provisioning Input File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[106]	['Constant']	= 'PRV_UNITEL_PRESELECTION_RPT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[106]	['Description']	= 'Unitel Preselection Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[107]	['Constant']	= 'PRV_AAPT_EOE_RETURN';
$GLOBALS['*arrConstant']	['ProvisioningType']	[107]	['Description']	= 'AAPT EOE Return File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[108]	['Constant']	= 'PRV_AAPT_LSD';
$GLOBALS['*arrConstant']	['ProvisioningType']	[108]	['Description']	= 'AAPT Line Status Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[109]	['Constant']	= 'PRV_AAPT_REJECT';
$GLOBALS['*arrConstant']	['ProvisioningType']	[109]	['Description']	= 'AAPT Rejections Report';
$GLOBALS['*arrConstant']	['ProvisioningType']	[110]	['Constant']	= 'PRV_AAPT_LOSS';
$GLOBALS['*arrConstant']	['ProvisioningType']	[110]	['Description']	= 'AAPT Loss Report';

$GLOBALS['*arrConstant']	['ProvisioningType']	[150]	['Constant']	= 'PRV_UNITEL_PRESELECTION_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[150]	['Description']	= 'Unitel Preselection Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[151]	['Constant']	= 'PRV_UNITEL_DAILY_ORDER_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[151]	['Description']	= 'Unitel Daily Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[152]	['Constant']	= 'PRV_AAPT_EOE';
$GLOBALS['*arrConstant']	['ProvisioningType']	[152]	['Description']	= 'AAPT EOE Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[153]	['Constant']	= 'PRV_OPTUS_PRESELECTION_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[153]	['Description']	= 'Optus Preselection Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[154]	['Constant']	= 'PRV_OPTUS_BAR_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[154]	['Description']	= 'Optus Barring Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[155]	['Constant']	= 'PRV_OPTUS_SUSPEND_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[155]	['Description']	= 'Optus Suspension Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[156]	['Constant']	= 'PRV_OPTUS_RESTORE_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[156]	['Description']	= 'Optus Restoration Order File';
$GLOBALS['*arrConstant']	['ProvisioningType']	[157]	['Constant']	= 'PRV_OPTUS_PRESELECTION_REV_EXP';
$GLOBALS['*arrConstant']	['ProvisioningType']	[157]	['Description']	= 'Optus Preselection Reversal Order File';
//*/

// File Status
$GLOBALS['*arrConstant']	['FileStatus']	[200]	['Constant']	= 'FILE_COLLECTED';
$GLOBALS['*arrConstant']	['FileStatus']	[200]	['Description']	= 'Collected';
$GLOBALS['*arrConstant']	['FileStatus']	[201]	['Constant']	= 'FILE_IMPORTING';
$GLOBALS['*arrConstant']	['FileStatus']	[201]	['Description']	= 'Importing';
$GLOBALS['*arrConstant']	['FileStatus']	[202]	['Constant']	= 'FILE_IMPORTED';
$GLOBALS['*arrConstant']	['FileStatus']	[202]	['Description']	= 'Imported';
$GLOBALS['*arrConstant']	['FileStatus']	[203]	['Constant']	= 'FILE_REIMPORT';
$GLOBALS['*arrConstant']	['FileStatus']	[203]	['Description']	= 'Re-attempt import in the next cycle';
$GLOBALS['*arrConstant']	['FileStatus']	[204]	['Constant']	= 'FILE_IGNORE';
$GLOBALS['*arrConstant']	['FileStatus']	[204]	['Description']	= 'Ignored';
$GLOBALS['*arrConstant']	['FileStatus']	[205]	['Constant']	= 'FILE_IMPORT_FAILED';
$GLOBALS['*arrConstant']	['FileStatus']	[205]	['Description']	= 'Importing Failed';
$GLOBALS['*arrConstant']	['FileStatus']	[206]	['Constant']	= 'FILE_NORMALISE_FAILED';
$GLOBALS['*arrConstant']	['FileStatus']	[206]	['Description']	= 'Normalisation Failed';
$GLOBALS['*arrConstant']	['FileStatus']	[207]	['Constant']	= 'FILE_NORMALISED';
$GLOBALS['*arrConstant']	['FileStatus']	[207]	['Description']	= 'Normalised';

$GLOBALS['*arrConstant']	['FileStatus']	[300]	['Constant']	= 'FILE_RENDERED';
$GLOBALS['*arrConstant']	['FileStatus']	[300]	['Description']	= 'Rendered';
$GLOBALS['*arrConstant']	['FileStatus']	[301]	['Constant']	= 'FILE_RENDER_FAILED';
$GLOBALS['*arrConstant']	['FileStatus']	[301]	['Description']	= 'Rendering Failed';
$GLOBALS['*arrConstant']	['FileStatus']	[302]	['Constant']	= 'FILE_DELIVERED';
$GLOBALS['*arrConstant']	['FileStatus']	[302]	['Description']	= 'Delivered';
$GLOBALS['*arrConstant']	['FileStatus']	[303]	['Constant']	= 'FILE_DELIVERY_FAILED';
$GLOBALS['*arrConstant']	['FileStatus']	[303]	['Description']	= 'Delivery Failed';


// EXPORT File Types
// PROVISIONING
$GLOBALS['*arrConstant']	['FileExport']	[1000]	['Constant']	= 'FILE_EXPORT_UNITEL_PRESELECTION';
$GLOBALS['*arrConstant']	['FileExport']	[1000]	['Description']	= 'Unitel Preselection';
$GLOBALS['*arrConstant']	['FileExport']	[1001]	['Constant']	= 'FILE_EXPORT_UNITEL_DAILY_ORDER';
$GLOBALS['*arrConstant']	['FileExport']	[1001]	['Description']	= 'Unitel Daily Order';

$GLOBALS['*arrConstant']	['FileExport']	[1100]	['Constant']	= 'FILE_EXPORT_AAPT_EOE';
$GLOBALS['*arrConstant']	['FileExport']	[1100]	['Description']	= 'AAPT EOE';

$GLOBALS['*arrConstant']	['FileExport']	[1200]	['Constant']	= 'FILE_EXPORT_OPTUS_PRESELECTION';
$GLOBALS['*arrConstant']	['FileExport']	[1200]	['Description']	= 'Optus Preselection';
$GLOBALS['*arrConstant']	['FileExport']	[1201]	['Constant']	= 'FILE_EXPORT_OPTUS_BAR';
$GLOBALS['*arrConstant']	['FileExport']	[1201]	['Description']	= 'Optus Barring';
$GLOBALS['*arrConstant']	['FileExport']	[1202]	['Constant']	= 'FILE_EXPORT_OPTUS_SUSPEND';
$GLOBALS['*arrConstant']	['FileExport']	[1202]	['Description']	= 'Optus Suspension';
$GLOBALS['*arrConstant']	['FileExport']	[1203]	['Constant']	= 'FILE_EXPORT_OPTUS_RESTORE';
$GLOBALS['*arrConstant']	['FileExport']	[1203]	['Description']	= 'Optus Restoration';
$GLOBALS['*arrConstant']	['FileExport']	[1204]	['Constant']	= 'FILE_EXPORT_OPTUS_PRESELECTION_REVERSAL';
$GLOBALS['*arrConstant']	['FileExport']	[1204]	['Description']	= 'Optus Preselection Reversal';
$GLOBALS['*arrConstant']	['FileExport']	[1205]	['Constant']	= 'FILE_EXPORT_OPTUS_DEACTIVATION';
$GLOBALS['*arrConstant']	['FileExport']	[1205]	['Description']	= 'Optus Deactivation';

// IMPORT File Types
// PROVISIONING
$GLOBALS['*arrConstant']	['FileImport']	[5000]	['Constant']	= 'FILE_IMPORT_UNITEL_DAILY_ORDER';
$GLOBALS['*arrConstant']	['FileImport']	[5000]	['Description']	= 'Unitel Daily Order Report';
$GLOBALS['*arrConstant']	['FileImport']	[5001]	['Constant']	= 'FILE_IMPORT_UNITEL_DAILY_STATUS';
$GLOBALS['*arrConstant']	['FileImport']	[5001]	['Description']	= 'Unitel Daily Status Report';
$GLOBALS['*arrConstant']	['FileImport']	[5002]	['Constant']	= 'FILE_IMPORT_UNITEL_BASKETS';
$GLOBALS['*arrConstant']	['FileImport']	[5002]	['Description']	= 'Unitel Agreed Baskets Report';
$GLOBALS['*arrConstant']	['FileImport']	[5003]	['Constant']	= 'FILE_IMPORT_UNITEL_PRESELECTION';
$GLOBALS['*arrConstant']	['FileImport']	[5003]	['Description']	= 'Unitel Preselection Report';
$GLOBALS['*arrConstant']	['FileImport']	[5004]	['Constant']	= 'FILE_IMPORT_UNITEL_LINE_STATUS';
$GLOBALS['*arrConstant']	['FileImport']	[5004]	['Description']	= 'Unitel Line Status Report';

$GLOBALS['*arrConstant']	['FileImport']	[5100]	['Constant']	= 'FILE_IMPORT_OPTUS_PPR';
$GLOBALS['*arrConstant']	['FileImport']	[5100]	['Description']	= 'Optus Line Status Report';

$GLOBALS['*arrConstant']	['FileImport']	[5200]	['Constant']	= 'FILE_IMPORT_AAPT_EOE_RETURN';
$GLOBALS['*arrConstant']	['FileImport']	[5200]	['Description']	= 'AAPT EOE Return File';
$GLOBALS['*arrConstant']	['FileImport']	[5201]	['Constant']	= 'FILE_IMPORT_AAPT_LSD';
$GLOBALS['*arrConstant']	['FileImport']	[5201]	['Description']	= 'AAPT Line Status Report';
$GLOBALS['*arrConstant']	['FileImport']	[5202]	['Constant']	= 'FILE_IMPORT_AAPT_REJECT';
$GLOBALS['*arrConstant']	['FileImport']	[5202]	['Description']	= 'AAPT Rejections Report';
$GLOBALS['*arrConstant']	['FileImport']	[5203]	['Constant']	= 'FILE_IMPORT_AAPT_LOSS';
$GLOBALS['*arrConstant']	['FileImport']	[5203]	['Description']	= 'AAPT Loss Report';



// Carriers
/*define("CARRIER_UNITEL"			, 1);
define("CARRIER_OPTUS"			, 2);
define("CARRIER_AAPT"			, 3);
define("CARRIER_ISEEK"			, 4);*/
//----------------------------------------------------------------------------//
// These are now stored in the database and loaded from the LoadFramework()
//----------------------------------------------------------------------------//
/*
$GLOBALS['*arrConstant']	['Carrier']	[1]	['Constant']	= 'CARRIER_UNITEL';
$GLOBALS['*arrConstant']	['Carrier']	[1]	['Description']	= 'Unitel';
$GLOBALS['*arrConstant']	['Carrier']	[2]	['Constant']	= 'CARRIER_OPTUS';
$GLOBALS['*arrConstant']	['Carrier']	[2]	['Description']	= 'Optus';
$GLOBALS['*arrConstant']	['Carrier']	[3]	['Constant']	= 'CARRIER_AAPT';
$GLOBALS['*arrConstant']	['Carrier']	[3]	['Description']	= 'AAPT';
$GLOBALS['*arrConstant']	['Carrier']	[4]	['Constant']	= 'CARRIER_ISEEK';
$GLOBALS['*arrConstant']	['Carrier']	[4]	['Description']	= 'iSeek';
$GLOBALS['*arrConstant']	['Carrier']	[5]	['Constant']	= 'CARRIER_UNITEL_VOICETALK';
$GLOBALS['*arrConstant']	['Carrier']	[5]	['Description']	= 'Unitel (VoiceTalk)';
$GLOBALS['*arrConstant']	['Carrier']	[10]['Constant']	= 'CARRIER_PAYMENT';
$GLOBALS['*arrConstant']	['Carrier']	[10]['Description']	= 'Payment';
*/

// ERROR TABLE
define("FATAL_ERROR_LEVEL"			, 10000);

define("NON_FATAL_TEST_EXCEPTION"	, 1337);
define("FATAL_TEST_EXCEPTION"		, 80085);

// Account Status
$GLOBALS['*arrConstant']['Account'][0]['Constant']		= 'ACCOUNT_ACTIVE';
$GLOBALS['*arrConstant']['Account'][0]['Description']	= 'Active';
$GLOBALS['*arrConstant']['Account'][1]['Constant']		= 'ACCOUNT_ARCHIVED';
$GLOBALS['*arrConstant']['Account'][1]['Description']	= 'Archived';
$GLOBALS['*arrConstant']['Account'][2]['Constant']		= 'ACCOUNT_CLOSED';
$GLOBALS['*arrConstant']['Account'][2]['Description']	= 'Closed';
$GLOBALS['*arrConstant']['Account'][3]['Constant']		= 'ACCOUNT_DEBT_COLLECTION';
$GLOBALS['*arrConstant']['Account'][3]['Description']	= 'Debt Collection';
$GLOBALS['*arrConstant']['Account'][4]['Constant']		= 'ACCOUNT_SUSPENDED';
$GLOBALS['*arrConstant']['Account'][4]['Description']	= 'Suspended';

// CDR status
// CDR Handling (Range is 100-199)
$GLOBALS['*arrConstant']['CDR'][100]['Constant']	= 'CDR_READY';
$GLOBALS['*arrConstant']['CDR'][100]['Description'] = 'Imported';
$GLOBALS['*arrConstant']['CDR'][101]['Constant']	= 'CDR_NORMALISED';
$GLOBALS['*arrConstant']['CDR'][101]['Description'] = 'Normalised';
$GLOBALS['*arrConstant']['CDR'][102]['Constant']	= 'CDR_CANT_NORMALISE';
$GLOBALS['*arrConstant']['CDR'][102]['Description'] = 'Unable to Normalise';
$GLOBALS['*arrConstant']['CDR'][103]['Constant']	= 'CDR_CANT_NORMALISE_RAW';
$GLOBALS['*arrConstant']['CDR'][103]['Description'] = 'Raw CDR Data Invalid';
$GLOBALS['*arrConstant']['CDR'][104]['Constant']	= 'CDR_CANT_NORMALISE_BAD_SEQ_NO';
$GLOBALS['*arrConstant']['CDR'][104]['Description'] = 'Unexpected CDR Sequence Number';
$GLOBALS['*arrConstant']['CDR'][105]['Constant']	= 'CDR_CANT_NORMALISE_HEADER';
$GLOBALS['*arrConstant']['CDR'][105]['Description'] = 'CDR File Header Row';
$GLOBALS['*arrConstant']['CDR'][106]['Constant']	= 'CDR_CANT_NORMALISE_NON_CDR';
$GLOBALS['*arrConstant']['CDR'][106]['Description'] = 'Non-CDR';
$GLOBALS['*arrConstant']['CDR'][107]['Constant']	= 'CDR_BAD_OWNER';
$GLOBALS['*arrConstant']['CDR'][107]['Description'] = 'Unable to Match Owner';
$GLOBALS['*arrConstant']['CDR'][108]['Constant']	= 'CDR_BAD_RECORD_TYPE';
$GLOBALS['*arrConstant']['CDR'][108]['Description'] = 'Unable to Determine Record Type';
$GLOBALS['*arrConstant']['CDR'][109]['Constant']	= 'CDR_BAD_DESTINATION';
$GLOBALS['*arrConstant']['CDR'][109]['Description'] = 'Unable to Determine Destination Code';
$GLOBALS['*arrConstant']['CDR'][110]['Constant']	= 'CDR_CANT_NORMALISE_NO_MODULE';
$GLOBALS['*arrConstant']['CDR'][110]['Description'] = 'No Normalisation Module';
$GLOBALS['*arrConstant']['CDR'][111]['Constant']	= 'CDR_CANT_NORMALISE_INVALID';
$GLOBALS['*arrConstant']['CDR'][111]['Description'] = 'Normalised Data Invalid';
$GLOBALS['*arrConstant']['CDR'][112]['Constant']	= 'CDR_FIND_OWNER';
$GLOBALS['*arrConstant']['CDR'][112]['Description'] = 'Awaiting Re-Application of Ownership';
$GLOBALS['*arrConstant']['CDR'][113]['Constant']	= 'CDR_RENORMALISE';
$GLOBALS['*arrConstant']['CDR'][113]['Description'] = 'Awaiting Re-Normalisation';
$GLOBALS['*arrConstant']['CDR'][114]['Constant']	= 'CDR_NORMALISE_NOW';
$GLOBALS['*arrConstant']['CDR'][114]['Description']	= 'Re-Normalise with current Owner';
$GLOBALS['*arrConstant']['CDR'][140]['Constant']	= 'CDR_IGNORE';
$GLOBALS['*arrConstant']['CDR'][140]['Description'] = 'Ignored';
$GLOBALS['*arrConstant']['CDR'][150]['Constant']	= 'CDR_RATED';
$GLOBALS['*arrConstant']['CDR'][150]['Description'] = 'Rated';
$GLOBALS['*arrConstant']['CDR'][151]['Constant']	= 'CDR_RATE_NOT_FOUND';
$GLOBALS['*arrConstant']['CDR'][151]['Description'] = 'Rate Not Found';
$GLOBALS['*arrConstant']['CDR'][152]['Constant']	= 'CDR_UNABLE_TO_RATE';
$GLOBALS['*arrConstant']['CDR'][152]['Description'] = 'Unable to Rate';
$GLOBALS['*arrConstant']['CDR'][153]['Constant']	= 'CDR_UNABLE_TO_CAP';
$GLOBALS['*arrConstant']['CDR'][153]['Description'] = 'Unable to Cap';
$GLOBALS['*arrConstant']['CDR'][154]['Constant']	= 'CDR_UNABLE_TO_PRORATE';
$GLOBALS['*arrConstant']['CDR'][154]['Description'] = 'Unable to Prorate';
$GLOBALS['*arrConstant']['CDR'][156]['Constant']	= 'CDR_IGNORE_INBOUND_SE';
$GLOBALS['*arrConstant']['CDR'][156]['Description'] = 'Inbound S&E Ignored';
$GLOBALS['*arrConstant']['CDR'][160]['Constant']	= 'CDR_RERATE';
$GLOBALS['*arrConstant']['CDR'][160]['Description'] = 'Awating Re-Rating';
$GLOBALS['*arrConstant']['CDR'][161]['Constant']	= 'CDR_UNRATE';
$GLOBALS['*arrConstant']['CDR'][161]['Description'] = 'Awating Un-Rating';
$GLOBALS['*arrConstant']['CDR'][155]['Constant']	= 'CDR_TOTALS_UPDATE_FAILED';
$GLOBALS['*arrConstant']['CDR'][155]['Description'] = 'Unable to Update Service Totals';
$GLOBALS['*arrConstant']['CDR'][198]['Constant']	= 'CDR_TEMP_INVOICE';
$GLOBALS['*arrConstant']['CDR'][198]['Description'] = 'Temporarily Invoiced';
$GLOBALS['*arrConstant']['CDR'][199]['Constant']	= 'CDR_INVOICED';
$GLOBALS['*arrConstant']['CDR'][199]['Description'] = 'Invoiced';
$GLOBALS['*arrConstant']['CDR'][180]['Constant'] 	= 'CDR_ETECH_PERFECT_MATCH';
$GLOBALS['*arrConstant']['CDR'][180]['Description'] = 'Perfect Match';
$GLOBALS['*arrConstant']['CDR'][181]['Constant'] 	= 'CDR_ETECH_IMPERFECT_MATCH';
$GLOBALS['*arrConstant']['CDR'][181]['Description'] = 'Close Match';
$GLOBALS['*arrConstant']['CDR'][182]['Constant'] 	= 'CDR_ETECH_NO_MATCH';
$GLOBALS['*arrConstant']['CDR'][182]['Description'] = 'No Match';
$GLOBALS['*arrConstant']['CDR'][183]['Constant']	= 'CDR_ETECH_INVOICED';
$GLOBALS['*arrConstant']['CDR'][183]['Description'] = 'Invoiced by Etech';
$GLOBALS['*arrConstant']['CDR'][184]['Constant']	= 'CDR_ETECH_RATED';
$GLOBALS['*arrConstant']['CDR'][184]['Description'] = 'Not Invoiced by Etech';

$GLOBALS['*arrConstant']['CDR'][170]['Constant']	= 'CDR_TEMP_CREDIT';
$GLOBALS['*arrConstant']['CDR'][170]['Description'] = 'Temporarily Credited';
$GLOBALS['*arrConstant']['CDR'][171]['Constant'] 	= 'CDR_CREDITED';
$GLOBALS['*arrConstant']['CDR'][171]['Description'] = 'Credited';

$GLOBALS['*arrConstant']['CDR'][179]['Constant'] 	= 'CDR_CREDIT_MATCH_NOT_FOUND';
$GLOBALS['*arrConstant']['CDR'][179]['Description'] = 'Unmatched Credit';
$GLOBALS['*arrConstant']['CDR'][171]['Constant'] 	= 'CDR_CREDIT_MATCHED';
$GLOBALS['*arrConstant']['CDR'][171]['Description'] = 'Matched Credit';
$GLOBALS['*arrConstant']['CDR'][175]['Constant'] 	= 'CDR_DEBIT_MATCHED';
$GLOBALS['*arrConstant']['CDR'][175]['Description'] = 'CDR Credited';
$GLOBALS['*arrConstant']['CDR'][176]['Constant'] 	= 'CDR_CREDIT_ADDED';
$GLOBALS['*arrConstant']['CDR'][176]['Description'] = 'CDR Credit Added';


//TODO!rich! when you have time, update all constant definitons to work like the CDR ones
//$GLOBALS['*arrConstant']['CDR'][100]['Constant'] = 'CDR_READY';
//$GLOBALS['*arrConstant']['CDR'][100]['Description'] = 'CDR is ready to normalise';


// CDR File Handling (Range is 200-299)
/*define("CDRFILE_WAITING"			, 200);
define("CDRFILE_IMPORTING"			, 201);
define("CDRFILE_IMPORTED"			, 202);
define("CDRFILE_REIMPORT"			, 203);
define("CDRFILE_IGNORE"				, 204);
define("CDRFILE_IMPORT_FAILED"		, 205);
define("CDRFILE_NORMALISE_FAILED"	, 206);
define("CDRFILE_NORMALISED"			, 207);*/
$GLOBALS['*arrConstant']	['CDRFileStatus']	[200]	['Constant']	= 'CDRFILE_WAITING';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[200]	['Description']	= 'Collected';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[201]	['Constant']	= 'CDRFILE_IMPORTING';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[201]	['Description']	= 'Importing';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[202]	['Constant']	= 'CDRFILE_IMPORTED';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[202]	['Description']	= 'Imported';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[203]	['Constant']	= 'CDRFILE_REIMPORT';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[203]	['Description']	= 'Re-attempt import in the next cycle';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[204]	['Constant']	= 'CDRFILE_IGNORE';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[204]	['Description']	= 'Ignored';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[205]	['Constant']	= 'CDRFILE_IMPORT_FAILED';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[205]	['Description']	= 'Importing Failed';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[206]	['Constant']	= 'CDRFILE_NORMALISE_FAILED';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[206]	['Description']	= 'Normalisation Failed';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[207]	['Constant']	= 'CDRFILE_NORMALISED';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[207]	['Description']	= 'Normalised';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[210]	['Constant']	= 'CDRFILE_ETECH_INVOICED';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[210]	['Description']	= 'Invoiced by Etech';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[215]	['Constant']	= 'CDRFILE_NAME_NOT_UNIQUE';
$GLOBALS['*arrConstant']	['CDRFileStatus']	[215]	['Description']	= 'File Name Exists, but Hash is Unique';



// Provisioning File Handling
/*define("PROVFILE_WAITING"			, 250);
define("PROVFILE_READING"			, 251);
define("PROVFILE_IGNORE"			, 252);
define("PROVFILE_COMPLETE"			, 253);*/
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[250]	['Constant']	= 'PROVFILE_WAITING';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[250]	['Description']	= 'Collected';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[251]	['Constant']	= 'PROVFILE_READING';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[251]	['Description']	= 'Importing';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[252]	['Constant']	= 'PROVFILE_IGNORE';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[252]	['Description']	= 'Ignored';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[253]	['Constant']	= 'PROVFILE_COMPLETE';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[253]	['Description']	= 'Imported';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[280]	['Constant']	= 'PROVFILE_SENT';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[280]	['Description']	= 'Sent';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[281]	['Constant']	= 'PROVFILE_REJECTED';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[281]	['Description']	= 'Rejected';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[282]	['Constant']	= 'PROVFILE_SENT';
$GLOBALS['*arrConstant']	['ProvisioningFileStatus']	[282]	['Description']	= 'Sent';


// Invoice Status
/*define("INVOICE_TEMP"				, 100);
define("INVOICE_COMMITTED"			, 101);
define("INVOICE_DISPUTED"			, 102);
define("INVOICE_SETTLED"			, 103);
define("INVOICE_DISPUTED_SETTLED"	, 104); // undisputed portion paid in full*/
$GLOBALS['*arrConstant']	['InvoiceStatus']	[100]	['Constant']	= 'INVOICE_TEMP';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[100]	['Description']	= 'Temporary Invoice';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[101]	['Constant']	= 'INVOICE_COMMITTED';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[101]	['Description']	= 'Committed';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[102]	['Constant']	= 'INVOICE_DISPUTED';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[102]	['Description']	= 'In Dispute';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[103]	['Constant']	= 'INVOICE_SETTLED';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[103]	['Description']	= 'Settled';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[104]	['Constant']	= 'INVOICE_DISPUTED_SETTLED';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[104]	['Description']	= 'Dispute Settled';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[105]	['Constant']	= 'INVOICE_PRINT';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[105]	['Description']	= 'Printing';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[106]	['Constant']	= 'INVOICE_WRITTEN_OFF';
$GLOBALS['*arrConstant']	['InvoiceStatus']	[106]	['Description']	= 'Written Off';


//Where Constants
$GLOBALS['*arrConstant']	['Where']			[100]	['Constant']	= 'WHERE_EQUALS';
$GLOBALS['*arrConstant']	['Where']			[100]	['Description']	= '=';
$GLOBALS['*arrConstant']	['Where']			[101]	['Constant']	= 'WHERE_NOT_EQUALS';
$GLOBALS['*arrConstant']	['Where']			[101]	['Description']	= '!=';
$GLOBALS['*arrConstant']	['Where']			[102]	['Constant']	= 'WHERE_LESS_THAN';
$GLOBALS['*arrConstant']	['Where']			[102]	['Description']	= '<';
$GLOBALS['*arrConstant']	['Where']			[103]	['Constant']	= 'WHERE_GREATER_THAN';
$GLOBALS['*arrConstant']	['Where']			[103]	['Description']	= '>';
$GLOBALS['*arrConstant']	['Where']			[104]	['Constant']	= 'WHERE_LESS_GREATER';
$GLOBALS['*arrConstant']	['Where']			[104]	['Description']	= '<>';
$GLOBALS['*arrConstant']	['Where']			[105]	['Constant']	= 'WHERE_LESS_EQUAL';
$GLOBALS['*arrConstant']	['Where']			[105]	['Description']	= '<=';
$GLOBALS['*arrConstant']	['Where']			[106]	['Constant']	= 'WHERE_GREATER_EQUAL';
$GLOBALS['*arrConstant']	['Where']			[106]	['Description']	= '>=';
$GLOBALS['*arrConstant']	['Where']			[107]	['Constant']	= 'WHERE_LIKE';
$GLOBALS['*arrConstant']	['Where']			[107]	['Description']	= 'LIKE';
$GLOBALS['*arrConstant']	['Where']			[108]	['Constant']	= 'WHERE_NOT_LIKE';
$GLOBALS['*arrConstant']	['Where']			[108]	['Description']	= 'NOT LIKE';
$GLOBALS['*arrConstant']	['Where']			[109]	['Constant']	= 'WHERE_NULL';
$GLOBALS['*arrConstant']	['Where']			[109]	['Description']	= 'ISNULL';
$GLOBALS['*arrConstant']	['Where']			[110]	['Constant']	= 'WHERE_NOT_NULL';
$GLOBALS['*arrConstant']	['Where']			[110]	['Description']	= 'NOT ISNULL';
$GLOBALS['*arrConstant']	['Where']			[111]	['Constant']	= 'WHERE_SEARCH';
$GLOBALS['*arrConstant']	['Where']			[111]	['Description']	= 'SEARCH';


// Charge Status
/*define("CHARGE_WAITING"				, 100);
define("CHARGE_APPROVED"			, 101);
define("CHARGE_DECLINED"			, 104);
define("CHARGE_TEMP_INVOICE"		, 102);
define("CHARGE_INVOICED"			, 103);*/
$GLOBALS['*arrConstant']	['ChargeStatus']	[100]	['Constant']	= 'CHARGE_WAITING';
$GLOBALS['*arrConstant']	['ChargeStatus']	[100]	['Description']	= 'Awaiting Approval';
$GLOBALS['*arrConstant']	['ChargeStatus']	[101]	['Constant']	= 'CHARGE_APPROVED';
$GLOBALS['*arrConstant']	['ChargeStatus']	[101]	['Description']	= 'Approved';
$GLOBALS['*arrConstant']	['ChargeStatus']	[102]	['Constant']	= 'CHARGE_TEMP_INVOICE';
$GLOBALS['*arrConstant']	['ChargeStatus']	[102]	['Description']	= 'Temporarily Invoiced';
$GLOBALS['*arrConstant']	['ChargeStatus']	[103]	['Constant']	= 'CHARGE_INVOICED';
$GLOBALS['*arrConstant']	['ChargeStatus']	[103]	['Description']	= 'Invoiced';
$GLOBALS['*arrConstant']	['ChargeStatus']	[104]	['Constant']	= 'CHARGE_DECLINED';
$GLOBALS['*arrConstant']	['ChargeStatus']	[104]	['Description']	= 'Declined';
$GLOBALS['*arrConstant']	['ChargeStatus']	[105]	['Constant']	= 'CHARGE_DELETED';
$GLOBALS['*arrConstant']	['ChargeStatus']	[105]	['Description']	= 'Deleted';


// Charge Link Types
$GLOBALS['*arrConstant']	['ChargeLink']		[500]	['Constant']	= 'CHARGE_LINK_PAYMENT';
$GLOBALS['*arrConstant']	['ChargeLink']		[500]	['Description']	= 'Payment Surcharge';
$GLOBALS['*arrConstant']	['ChargeLink']		[501]	['Constant']	= 'CHARGE_LINK_RECURRING';
$GLOBALS['*arrConstant']	['ChargeLink']		[501]	['Description']	= 'Recurring Adjustment';
$GLOBALS['*arrConstant']	['ChargeLink']		[502]	['Constant']	= 'CHARGE_LINK_RECURRING_CANCEL';
$GLOBALS['*arrConstant']	['ChargeLink']		[502]	['Description']	= 'Recurring Adjustment Cancellation';
$GLOBALS['*arrConstant']	['ChargeLink']		[503]	['Constant']	= 'CHARGE_LINK_OVERCHARGE_CREDIT';
$GLOBALS['*arrConstant']	['ChargeLink']		[503]	['Description']	= 'Invoice Overcharge Credit';
$GLOBALS['*arrConstant']	['ChargeLink']		[504]	['Constant']	= 'CHARGE_LINK_CDR_CREDIT';
$GLOBALS['*arrConstant']	['ChargeLink']		[504]	['Description']	= 'CDR Credit';
$GLOBALS['*arrConstant']	['ChargeLink']		[505]	['Constant']	= 'CHARGE_LINK_CHARGE';
$GLOBALS['*arrConstant']	['ChargeLink']		[505]	['Description']	= 'Adjustment Negation';


// Customer Group Constants
/*define("CUSTOMER_GROUP_TELCOBLUE"	, 1);
define("CUSTOMER_GROUP_VOICETALK"	, 2);
define("CUSTOMER_GROUP_IMAGINE"		, 3);*/
// Deprecated now that CustomerGroups are defined in the CustomerGroup table of the database
/* These CustomerGroup constants are currently created when the Database constants are loaded
$GLOBALS['*arrConstant']	['CustomerGroup']	[1]	['Constant']	= 'CUSTOMER_GROUP_TELCOBLUE';
$GLOBALS['*arrConstant']	['CustomerGroup']	[1]	['Description']	= 'Telco Blue';
$GLOBALS['*arrConstant']	['CustomerGroup']	[2]	['Constant']	= 'CUSTOMER_GROUP_VOICETALK';
$GLOBALS['*arrConstant']	['CustomerGroup']	[2]	['Description']	= 'VoiceTalk';
$GLOBALS['*arrConstant']	['CustomerGroup']	[3]	['Constant']	= 'CUSTOMER_GROUP_IMAGINE';
$GLOBALS['*arrConstant']	['CustomerGroup']	[3]	['Description']	= 'Imagine';
*/

/* Deprecated now that CustomerGroups are defined in the CustomerGroup table of the database
$GLOBALS['*arrConstant']	['CustomerGroupEmail']	[1]	['Constant']	= 'CUSTOMER_GROUP_EMAIL_TELCOBLUE';
$GLOBALS['*arrConstant']	['CustomerGroupEmail']	[1]	['Description']	= 'billing@telcoblue.com.au';
$GLOBALS['*arrConstant']	['CustomerGroupEmail']	[2]	['Constant']	= 'CUSTOMER_GROUP_EMAIL_VOICETALK';
$GLOBALS['*arrConstant']	['CustomerGroupEmail']	[2]	['Description']	= 'billing@voicetalk.com.au';
$GLOBALS['*arrConstant']	['CustomerGroupEmail']	[3]	['Constant']	= 'CUSTOMER_GROUP_EMAIL_IMAGINE';
$GLOBALS['*arrConstant']	['CustomerGroupEmail']	[3]	['Description']	= 'Imagine';
*/

// Credit Card Constants
$GLOBALS['*arrConstant']	['CreditCard']	[1]	['Constant']	= 'CREDIT_CARD_VISA';
$GLOBALS['*arrConstant']	['CreditCard']	[1]	['Description']	= 'VISA';
$GLOBALS['*arrConstant']	['CreditCard']	[2]	['Constant']	= 'CREDIT_CARD_MASTERCARD';
$GLOBALS['*arrConstant']	['CreditCard']	[2]	['Description']	= 'MasterCard';
$GLOBALS['*arrConstant']	['CreditCard']	[3]	['Constant']	= 'CREDIT_CARD_BANKCARD';
$GLOBALS['*arrConstant']	['CreditCard']	[3]	['Description']	= 'Bankcard';
$GLOBALS['*arrConstant']	['CreditCard']	[4]	['Constant']	= 'CREDIT_CARD_AMEX';
$GLOBALS['*arrConstant']	['CreditCard']	[4]	['Description']	= 'American Express';
$GLOBALS['*arrConstant']	['CreditCard']	[5]	['Constant']	= 'CREDIT_CARD_DINERS';
$GLOBALS['*arrConstant']	['CreditCard']	[5]	['Description']	= 'Diners Club';

define("INVOICE_EMAIL_CONTENT", "Please find attached your invoice from <custgrp>.\r\n\r\n" .
								"Regards\r\n\r\n" .
								"The Team at <custgrp>.");
define("INVOICE_EMAIL_SUBJECT", "Telephone Billing for <billperiod>.");

// DONKEY (neither TRUE nor FALSE)
define("DONKEY"						, -1);

// Service Types
/*define("SERVICE_TYPE_ADSL"			, 100);
define("SERVICE_TYPE_MOBILE"		, 101);
define("SERVICE_TYPE_LAND_LINE"		, 102);
define("SERVICE_TYPE_INBOUND"		, 103);
define("SERVICE_TYPE_DIALUP"		, 104);*/
$GLOBALS['*arrConstant']	['ServiceType']	[100]	['Constant']	= 'SERVICE_TYPE_ADSL';
$GLOBALS['*arrConstant']	['ServiceType']	[100]	['Description']	= 'ADSL';
$GLOBALS['*arrConstant']	['ServiceType']	[101]	['Constant']	= 'SERVICE_TYPE_MOBILE';
$GLOBALS['*arrConstant']	['ServiceType']	[101]	['Description']	= 'Mobile';
$GLOBALS['*arrConstant']	['ServiceType']	[102]	['Constant']	= 'SERVICE_TYPE_LAND_LINE';
$GLOBALS['*arrConstant']	['ServiceType']	[102]	['Description']	= 'Land Line';
$GLOBALS['*arrConstant']	['ServiceType']	[103]	['Constant']	= 'SERVICE_TYPE_INBOUND';
$GLOBALS['*arrConstant']	['ServiceType']	[103]	['Description']	= 'Inbound 1300/1800';
$GLOBALS['*arrConstant']	['ServiceType']	[104]	['Constant']	= 'SERVICE_TYPE_DIALUP';
$GLOBALS['*arrConstant']	['ServiceType']	[104]	['Description']	= 'Dialup Internet';

// Context
define("CONTEXT_NORMAL"			, 0);
define("CONTEXT_IDD"			, 1);
define("CONTEXT_S_AND_E"		, 2);

// TAX RATES
define("TAX_RATE_GST"				, 10);

// Report Messages
define("MSG_HORIZONTAL_RULE"		, "\n================================================================================\n");
define("MSG_IGNORE"					, "\t\t[ IGNORE ]");

// SQL Modes
define("SQL_QUERY"				, 100);
define("SQL_STATEMENT"			, 200);

// Provisioning Request Status
/*define("REQUEST_STATUS_WAITING"			, 300);
define("REQUEST_STATUS_PENDING"			, 301);
define("REQUEST_STATUS_REJECTED"		, 302);
define("REQUEST_STATUS_COMPLETED"		, 303);
define("REQUEST_STATUS_CANCELLED"		, 304);*/
$GLOBALS['*arrConstant']	['RequestStatus']	[300]	['Constant']	= 'REQUEST_STATUS_WAITING';
$GLOBALS['*arrConstant']	['RequestStatus']	[300]	['Description']	= 'Awaiting Dispatch';
$GLOBALS['*arrConstant']	['RequestStatus']	[301]	['Constant']	= 'REQUEST_STATUS_PENDING';
$GLOBALS['*arrConstant']	['RequestStatus']	[301]	['Description']	= 'Pending';
$GLOBALS['*arrConstant']	['RequestStatus']	[302]	['Constant']	= 'REQUEST_STATUS_REJECTED';
$GLOBALS['*arrConstant']	['RequestStatus']	[302]	['Description']	= 'Rejected';
$GLOBALS['*arrConstant']	['RequestStatus']	[303]	['Constant']	= 'REQUEST_STATUS_COMPLETED';
$GLOBALS['*arrConstant']	['RequestStatus']	[303]	['Description']	= 'Completed';
$GLOBALS['*arrConstant']	['RequestStatus']	[304]	['Constant']	= 'REQUEST_STATUS_CANCELLED';
$GLOBALS['*arrConstant']	['RequestStatus']	[304]	['Description']	= 'Cancelled';
$GLOBALS['*arrConstant']	['RequestStatus']	[305]	['Constant']	= 'REQUEST_STATUS_DUPLICATE';
$GLOBALS['*arrConstant']	['RequestStatus']	[305]	['Description']	= 'Duplicated (Ignored)';
$GLOBALS['*arrConstant']	['RequestStatus']	[306]	['Constant']	= 'REQUEST_STATUS_EXPORTING';
$GLOBALS['*arrConstant']	['RequestStatus']	[306]	['Description']	= 'Currently Exporting';
$GLOBALS['*arrConstant']	['RequestStatus']	[307]	['Constant']	= 'REQUEST_STATUS_DELIVERED';
$GLOBALS['*arrConstant']	['RequestStatus']	[307]	['Description']	= 'Waiting on Carrier Response';
$GLOBALS['*arrConstant']	['RequestStatus']	[308]	['Constant']	= 'REQUEST_STATUS_NO_MODULE';
$GLOBALS['*arrConstant']	['RequestStatus']	[308]	['Description']	= 'Request Not Supported by Flex';

$GLOBALS['*arrConstant']	['ResponseStatus']	[400]	['Constant']	= 'RESPONSE_STATUS_CANT_NORMALISE';
$GLOBALS['*arrConstant']	['ResponseStatus']	[400]	['Description']	= 'Unable to Normalise';
$GLOBALS['*arrConstant']	['ResponseStatus']	[401]	['Constant']	= 'RESPONSE_STATUS_BAD_OWNER';
$GLOBALS['*arrConstant']	['ResponseStatus']	[401]	['Description']	= 'Unable to Find Owner';
$GLOBALS['*arrConstant']	['ResponseStatus']	[402]	['Constant']	= 'RESPONSE_STATUS_IMPORTED';
$GLOBALS['*arrConstant']	['ResponseStatus']	[402]	['Description']	= 'Successfully Imported';
$GLOBALS['*arrConstant']	['ResponseStatus']	[403]	['Constant']	= 'RESPONSE_STATUS_REDUNDANT';
$GLOBALS['*arrConstant']	['ResponseStatus']	[403]	['Description']	= 'Redundant';


// Provisioning Request Status
/*define("REQUEST_DIRECTION_OUTGOING"		, 0);
define("REQUEST_DIRECTION_INCOMING"		, 1);*/
$GLOBALS['*arrConstant']	['RequestDirection']	[0]	['Constant']	= 'REQUEST_DIRECTION_OUTGOING';
$GLOBALS['*arrConstant']	['RequestDirection']	[0]	['Description']	= 'Outgoing';
$GLOBALS['*arrConstant']	['RequestDirection']	[1]	['Constant']	= 'REQUEST_DIRECTION_INCOMING';
$GLOBALS['*arrConstant']	['RequestDirection']	[1]	['Description']	= 'Incoming';



// Service Status
$GLOBALS['*arrConstant']	['Service']	[400]	['Constant']	= 'SERVICE_ACTIVE';
$GLOBALS['*arrConstant']	['Service']	[400]	['Description']	= 'Active';
/*$GLOBALS['*arrConstant']	['Service']	[401]	['Constant']	= 'SERVICE_BARRED';
$GLOBALS['*arrConstant']	['Service']	[401]	['Description']	= 'Barred';*/
$GLOBALS['*arrConstant']	['Service']	[402]	['Constant']	= 'SERVICE_DISCONNECTED';
$GLOBALS['*arrConstant']	['Service']	[402]	['Description']	= 'Disconnected';
$GLOBALS['*arrConstant']	['Service']	[403]	['Constant']	= 'SERVICE_ARCHIVED';
$GLOBALS['*arrConstant']	['Service']	[403]	['Description']	= 'Archived';


// Service Line Status
$GLOBALS['*arrConstant']	['LineStatus']	[500]	['Constant']	= 'SERVICE_LINE_PENDING';
$GLOBALS['*arrConstant']	['LineStatus']	[500]	['Description']	= 'Pending Connection';
$GLOBALS['*arrConstant']	['LineStatus']	[501]	['Constant']	= 'SERVICE_LINE_ACTIVE';
$GLOBALS['*arrConstant']	['LineStatus']	[501]	['Description']	= 'Active';
$GLOBALS['*arrConstant']	['LineStatus']	[502]	['Constant']	= 'SERVICE_LINE_DISCONNECTED';
$GLOBALS['*arrConstant']	['LineStatus']	[502]	['Description']	= 'Disconnected';
$GLOBALS['*arrConstant']	['LineStatus']	[503]	['Constant']	= 'SERVICE_LINE_BARRED';
$GLOBALS['*arrConstant']	['LineStatus']	[503]	['Description']	= 'Barred';
$GLOBALS['*arrConstant']	['LineStatus']	[504]	['Constant']	= 'SERVICE_LINE_TEMPORARY_DISCONNECT';
$GLOBALS['*arrConstant']	['LineStatus']	[504]	['Description']	= 'Temporarily Disconnected';
$GLOBALS['*arrConstant']	['LineStatus']	[505]	['Constant']	= 'SERVICE_LINE_REJECTED';
$GLOBALS['*arrConstant']	['LineStatus']	[505]	['Description']	= 'Connection Request Rejected';
$GLOBALS['*arrConstant']	['LineStatus']	[506]	['Constant']	= 'SERVICE_LINE_CHURNED';
$GLOBALS['*arrConstant']	['LineStatus']	[506]	['Description']	= 'Churned Away';
$GLOBALS['*arrConstant']	['LineStatus']	[507]	['Constant']	= 'SERVICE_LINE_REVERSED';
$GLOBALS['*arrConstant']	['LineStatus']	[507]	['Description']	= 'Churn Reversed';


// Provisioning Request Types
/*define("REQUEST_FULL_SERVICE"			, 900);
define("REQUEST_PRESELECTION"			, 901);
define("REQUEST_BAR_SOFT"				, 902);
define("REQUEST_UNBAR_SOFT"				, 903);
define("REQUEST_ACTIVATION"				, 904);
define("REQUEST_DEACTIVATION"			, 905);
define("REQUEST_PRESELECTION_REVERSE"	, 906);
define("REQUEST_FULL_SERVICE_REVERSE"	, 907);
define("REQUEST_BAR_HARD"				, 908);
define("REQUEST_UNBAR_HARD"				, 909);*/
$GLOBALS['*arrConstant']	['Request']	[900]	['Constant']	= 'REQUEST_FULL_SERVICE';
$GLOBALS['*arrConstant']	['Request']	[900]	['Description']	= 'Full Service';
$GLOBALS['*arrConstant']	['Request']	[901]	['Constant']	= 'REQUEST_PRESELECTION';
$GLOBALS['*arrConstant']	['Request']	[901]	['Description']	= 'Preselection';
$GLOBALS['*arrConstant']	['Request']	[902]	['Constant']	= 'REQUEST_BAR';
$GLOBALS['*arrConstant']	['Request']	[902]	['Description']	= 'Bar';
$GLOBALS['*arrConstant']	['Request']	[903]	['Constant']	= 'REQUEST_UNBAR';
$GLOBALS['*arrConstant']	['Request']	[903]	['Description']	= 'UnBar';
$GLOBALS['*arrConstant']	['Request']	[904]	['Constant']	= 'REQUEST_ACTIVATION';
$GLOBALS['*arrConstant']	['Request']	[904]	['Description']	= 'Activation';
$GLOBALS['*arrConstant']	['Request']	[905]	['Constant']	= 'REQUEST_DEACTIVATION';
$GLOBALS['*arrConstant']	['Request']	[905]	['Description']	= 'Deactivation';
$GLOBALS['*arrConstant']	['Request']	[906]	['Constant']	= 'REQUEST_PRESELECTION_REVERSE';
$GLOBALS['*arrConstant']	['Request']	[906]	['Description']	= 'Preselection Reversal';
$GLOBALS['*arrConstant']	['Request']	[907]	['Constant']	= 'REQUEST_FULL_SERVICE_REVERSE';
$GLOBALS['*arrConstant']	['Request']	[907]	['Description']	= 'Full Service Reversal';
$GLOBALS['*arrConstant']	['Request']	[908]	['Constant']	= 'REQUEST_DISCONNECT_TEMPORARY';
$GLOBALS['*arrConstant']	['Request']	[908]	['Description']	= 'Temporary Disconnection';
$GLOBALS['*arrConstant']	['Request']	[909]	['Constant']	= 'REQUEST_RECONNECT_TEMPORARY';
$GLOBALS['*arrConstant']	['Request']	[909]	['Description']	= 'Temporary Disconnection Reversal';
$GLOBALS['*arrConstant']	['Request']	[910]	['Constant']	= 'REQUEST_LOSS_FULL';
$GLOBALS['*arrConstant']	['Request']	[910]	['Description']	= 'Full Service Lost';
$GLOBALS['*arrConstant']	['Request']	[911]	['Constant']	= 'REQUEST_LOSS_PRESELECT';
$GLOBALS['*arrConstant']	['Request']	[911]	['Description']	= 'Preselection Lost';
$GLOBALS['*arrConstant']	['Request']	[912]	['Constant']	= 'REQUEST_CHANGE_ADDRESS';
$GLOBALS['*arrConstant']	['Request']	[912]	['Description']	= 'Address Changed';
$GLOBALS['*arrConstant']	['Request']	[913]	['Constant']	= 'REQUEST_VIRTUAL_PRESELECTION';
$GLOBALS['*arrConstant']	['Request']	[913]	['Description']	= 'Virtual Preselection';
$GLOBALS['*arrConstant']	['Request']	[914]	['Constant']	= 'REQUEST_VIRTUAL_PRESELECTION_REVERSE';
$GLOBALS['*arrConstant']	['Request']	[914]	['Description']	= 'Virtual Preselection Reversal';
$GLOBALS['*arrConstant']	['Request']	[915]	['Constant']	= 'REQUEST_LOSS_VIRTUAL_PRESELECTION';
$GLOBALS['*arrConstant']	['Request']	[915]	['Description']	= 'Virtual Preselection Lost';


// Provisioning Line Actions (Log)
/*define("LINE_ACTION_OTHER"				, 600);
define("LINE_ACTION_GAIN"				, 601);
define("LINE_ACTION_LOSS"				, 602);*/
$GLOBALS['*arrConstant']	['LineAction']	[600]	['Constant']	= 'LINE_ACTION_OTHER';
$GLOBALS['*arrConstant']	['LineAction']	[600]	['Description']	= 'Other';
$GLOBALS['*arrConstant']	['LineAction']	[601]	['Constant']	= 'LINE_ACTION_GAIN';
$GLOBALS['*arrConstant']	['LineAction']	[601]	['Description']	= 'Gain';
$GLOBALS['*arrConstant']	['LineAction']	[602]	['Constant']	= 'LINE_ACTION_LOSS';
$GLOBALS['*arrConstant']	['LineAction']	[602]	['Description']	= 'Loss';

// God help me ...
// Service Address Types
/*
define("SERVICE_ADDR_TYPE_APARTMENT"				, "APT");
define("SERVICE_ADDR_TYPE_ATCO_PORTABLE_DWELLING"	, "ATC");
define("SERVICE_ADDR_TYPE_BASEMENT"					, "BMT");
define("SERVICE_ADDR_TYPE_BAY"						, "BAY");
define("SERVICE_ADDR_TYPE_BERTH"					, "BT");
define("SERVICE_ADDR_TYPE_BLOCK"					, "BLK");
define("SERVICE_ADDR_TYPE_BUILDING"					, "BG");
define("SERVICE_ADDR_TYPE_BUILDING_2"				, "BLG");
define("SERVICE_ADDR_TYPE_CARAVAN"					, "CRV");
define("SERVICE_ADDR_TYPE_CARE_PO"					, "CPO");
define("SERVICE_ADDR_TYPE_CHAMBERS"					, "CB");
define("SERVICE_ADDR_TYPE_CMA"						, "CMA");
define("SERVICE_ADDR_TYPE_CMB"						, "CMB");
define("SERVICE_ADDR_TYPE_COMPLEX"					, "CX");
define("SERVICE_ADDR_TYPE_COTTAGE"					, "CTG");
define("SERVICE_ADDR_TYPE_COUNTER"					, "CN");
define("SERVICE_ADDR_TYPE_DUPLEX"					, "DUP");
define("SERVICE_ADDR_TYPE_ENTRANCE"					, "ENT");
define("SERVICE_ADDR_TYPE_FACTORY"					, "FY");
define("SERVICE_ADDR_TYPE_FARM"						, "FAR");
define("SERVICE_ADDR_TYPE_FLAT"						, "FL");
define("SERVICE_ADDR_TYPE_FLAT_2"					, "FLA");
define("SERVICE_ADDR_TYPE_FLAT_3"					, "FLT");
define("SERVICE_ADDR_TYPE_FLOOR"					, "FLR");
define("SERVICE_ADDR_TYPE_GATE"						, "GT");
define("SERVICE_ADDR_TYPE_GATE_A"					, "GTE");
define("SERVICE_ADDR_TYPE_GPO_BOX"					, "GPO");
define("SERVICE_ADDR_TYPE_GROUND_GROUND_FLOOR"		, "G");
define("SERVICE_ADDR_TYPE_HANGAR"					, "HG");
define("SERVICE_ADDR_TYPE_HOUSE"					, "HSE");
define("SERVICE_ADDR_TYPE_IGLOO"					, "IG");
define("SERVICE_ADDR_TYPE_JETTY"					, "JT");
define("SERVICE_ADDR_TYPE_KIOSK"					, "KSK");
define("SERVICE_ADDR_TYPE_LANE"						, "LN");
define("SERVICE_ADDR_TYPE_LEVEL"					, "LV");
define("SERVICE_ADDR_TYPE_LEVEL_2"					, "LVL");
define("SERVICE_ADDR_TYPE_LOCKED_BAG"				, "LB");
define("SERVICE_ADDR_TYPE_LOT"						, "LOT");
define("SERVICE_ADDR_TYPE_LOWER_GROUND_FLOOR"		, "LG");
define("SERVICE_ADDR_TYPE_MAISONETTE"				, "MST");
define("SERVICE_ADDR_TYPE_MEZZANINE"				, "M");
define("SERVICE_ADDR_TYPE_MS"						, "MS");
define("SERVICE_ADDR_TYPE_OFFICE"					, "OF");
define("SERVICE_ADDR_TYPE_OFFICE_2"					, "OFC");
define("SERVICE_ADDR_TYPE_PENTHOUSE"				, "PHS");
define("SERVICE_ADDR_TYPE_PIER"						, "PR");
define("SERVICE_ADDR_TYPE_PO_BOX"					, "POB");
define("SERVICE_ADDR_TYPE_FF_OFFICE"				, "PO");
define("SERVICE_ADDR_TYPE_PRIVATE_BAG"				, "BAG");
define("SERVICE_ADDR_TYPE_PRIVATE_BAG_2"			, "PB");
define("SERVICE_ADDR_TYPE_RMB"						, "RMB");
define("SERVICE_ADDR_TYPE_RMS"						, "RMS");
define("SERVICE_ADDR_TYPE_ROOM"						, "RM");
define("SERVICE_ADDR_TYPE_RSD"						, "RSD");
define("SERVICE_ADDR_TYPE_RURAL_MAIL_DELIVERY"		, "RMD");
define("SERVICE_ADDR_TYPE_SHED"						, "SD");
define("SERVICE_ADDR_TYPE_SHED_2"					, "SHD");
define("SERVICE_ADDR_TYPE_SHOP"						, "SHP");
define("SERVICE_ADDR_TYPE_SHOP_2"					, "SP");
define("SERVICE_ADDR_TYPE_SITE"						, "SIT");
define("SERVICE_ADDR_TYPE_STALL"					, "SL");
define("SERVICE_ADDR_TYPE_STALL_2"					, "STL");
define("SERVICE_ADDR_TYPE_STUDIO"					, "STU");
define("SERVICE_ADDR_TYPE_SUITE"					, "STE");
define("SERVICE_ADDR_TYPE_TIER"						, "TR");
define("SERVICE_ADDR_TYPE_TOWER"					, "TW");
define("SERVICE_ADDR_TYPE_TOWER_2"					, "TWR");
define("SERVICE_ADDR_TYPE_TOWNHOUSE"				, "THS");
define("SERVICE_ADDR_TYPE_UNIT"						, "UN");
define("SERVICE_ADDR_TYPE_UNIT_2"					, "UNT");
define("SERVICE_ADDR_TYPE_UPPER_GROUND_FLOOR"		, "UG");
define("SERVICE_ADDR_TYPE_VILLA"					, "VIL");
define("SERVICE_ADDR_TYPE_WARD"						, "WRD");
define("SERVICE_ADDR_TYPE_WHARF"					, "WF");
*/
// Service Address Types
$GLOBALS['*arrConstant']	['ServiceAddrType']	["APT"]	['Constant']	= 'SERVICE_ADDR_TYPE_APARTMENT';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["APT"]	['Description']	= 'Apartment';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["ATC"]	['Constant']	= 'SERVICE_ADDR_TYPE_ATCO_PORTABLE_DWELLING';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["ATC"]	['Description']	= 'ATCO Portable Dwelling';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BMT"]	['Constant']	= 'SERVICE_ADDR_TYPE_BASEMENT';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BMT"]	['Description']	= 'Basement';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BAY"]	['Constant']	= 'SERVICE_ADDR_TYPE_BAY';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BAY"]	['Description']	= 'Bay';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BT"]	['Constant']	= 'SERVICE_ADDR_TYPE_BERTH';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BT"]	['Description']	= 'Berth';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BLK"]	['Constant']	= 'SERVICE_ADDR_TYPE_BLOCK';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BLK"]	['Description']	= 'Block';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BG"]	['Constant']	= 'SERVICE_ADDR_TYPE_BUILDING';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BG"]	['Description']	= 'Building';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BLG"]	['Constant']	= 'SERVICE_ADDR_TYPE_BUILDING_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BLG"]	['Description']	= 'Building';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CRV"]	['Constant']	= 'SERVICE_ADDR_TYPE_CARAVAN';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CRV"]	['Description']	= 'Caravan';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CPO"]	['Constant']	= 'SERVICE_ADDR_TYPE_CARE_PO';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CPO"]	['Description']	= 'Care PO';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CB"]	['Constant']	= 'SERVICE_ADDR_TYPE_CHAMBERS';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CB"]	['Description']	= 'Chambers';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CMA"]	['Constant']	= 'SERVICE_ADDR_TYPE_CMA';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CMA"]	['Description']	= 'Community Mail Agent';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CMB"]	['Constant']	= 'SERVICE_ADDR_TYPE_CMB';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CMB"]	['Description']	= 'Community Mail Bag';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CX"]	['Constant']	= 'SERVICE_ADDR_TYPE_COMPLEX';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CX"]	['Description']	= 'Complex';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CTG"]	['Constant']	= 'SERVICE_ADDR_TYPE_COTTAGE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CTG"]	['Description']	= 'Cottage';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CN"]	['Constant']	= 'SERVICE_ADDR_TYPE_COUNTER';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["CN"]	['Description']	= 'Counter';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["DUP"]	['Constant']	= 'SERVICE_ADDR_TYPE_DUPLEX';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["DUP"]	['Description']	= 'Duplex';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["ENT"]	['Constant']	= 'SERVICE_ADDR_TYPE_ENTRANCE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["ENT"]	['Description']	= 'Entrance';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FY"]	['Constant']	= 'SERVICE_ADDR_TYPE_FACTORY';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FY"]	['Description']	= 'Factory';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FAR"]	['Constant']	= 'SERVICE_ADDR_TYPE_FARM';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FAR"]	['Description']	= 'Farm';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FL"]	['Constant']	= 'SERVICE_ADDR_TYPE_FLAT';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FL"]	['Description']	= 'Flat';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FLA"]	['Constant']	= 'SERVICE_ADDR_TYPE_FLAT_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FLA"]	['Description']	= 'Flat';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FLT"]	['Constant']	= 'SERVICE_ADDR_TYPE_FLAT_3';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FLT"]	['Description']	= 'Flat';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FLR"]	['Constant']	= 'SERVICE_ADDR_TYPE_FLOOR';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["FLR"]	['Description']	= 'Floor';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["GT"]	['Constant']	= 'SERVICE_ADDR_TYPE_GATE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["GT"]	['Description']	= 'Gate';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["GTE"]	['Constant']	= 'SERVICE_ADDR_TYPE_GATE_A';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["GTE"]	['Description']	= 'Gate';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["GPO"]	['Constant']	= 'SERVICE_ADDR_TYPE_GPO_BOX';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["GPO"]	['Description']	= 'GPO Box';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["G"]	['Constant']	= 'SERVICE_ADDR_TYPE_GROUND_GROUND_FLOOR';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["G"]	['Description']	= 'Ground / Ground Floor';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["HG"]	['Constant']	= 'SERVICE_ADDR_TYPE_HANGAR';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["HG"]	['Description']	= 'Hangar';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["HSE"]	['Constant']	= 'SERVICE_ADDR_TYPE_HOUSE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["HSE"]	['Description']	= 'House';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["IG"]	['Constant']	= 'SERVICE_ADDR_TYPE_IGLOO';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["IG"]	['Description']	= 'Igloo';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["JT"]	['Constant']	= 'SERVICE_ADDR_TYPE_JETTY';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["JT"]	['Description']	= 'Jetty';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["KSK"]	['Constant']	= 'SERVICE_ADDR_TYPE_KIOSK';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["KSK"]	['Description']	= 'Kiosk';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LN"]	['Constant']	= 'SERVICE_ADDR_TYPE_LANE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LN"]	['Description']	= 'Lane';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LV"]	['Constant']	= 'SERVICE_ADDR_TYPE_LEVEL';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LV"]	['Description']	= 'Level';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LVL"]	['Constant']	= 'SERVICE_ADDR_TYPE_LEVEL_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LVL"]	['Description']	= 'Level';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LB"]	['Constant']	= 'SERVICE_ADDR_TYPE_LOCKED_BAG';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LB"]	['Description']	= 'Locked Bag';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LOT"]	['Constant']	= 'SERVICE_ADDR_TYPE_LOT';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LOT"]	['Description']	= 'Lot';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LG"]	['Constant']	= 'SERVICE_ADDR_TYPE_LOWER_GROUND_FLOOR';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["LG"]	['Description']	= 'Lower Ground Floor';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["MST"]	['Constant']	= 'SERVICE_ADDR_TYPE_MAISONETTE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["MST"]	['Description']	= 'Maisonette';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["M"]	['Constant']	= 'SERVICE_ADDR_TYPE_MEZZANINE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["M"]	['Description']	= 'Mezzanine';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["MS"]	['Constant']	= 'SERVICE_ADDR_TYPE_MS';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["MS"]	['Description']	= 'Mail Service';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["OF"]	['Constant']	= 'SERVICE_ADDR_TYPE_OFFICE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["OF"]	['Description']	= 'Office';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["OFC"]	['Constant']	= 'SERVICE_ADDR_TYPE_OFFICE_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["OFC"]	['Description']	= 'Office';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PHS"]	['Constant']	= 'SERVICE_ADDR_TYPE_PENTHOUSE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PHS"]	['Description']	= 'Penthouse';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PR"]	['Constant']	= 'SERVICE_ADDR_TYPE_PIER';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PR"]	['Description']	= 'Pier';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["POB"]	['Constant']	= 'SERVICE_ADDR_TYPE_PO_BOX';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["POB"]	['Description']	= 'PO Box';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PO"]	['Constant']	= 'SERVICE_ADDR_TYPE_POST_OFFICE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PO"]	['Description']	= 'Post Office';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BAG"]	['Constant']	= 'SERVICE_ADDR_TYPE_PRIVATE_BAG';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["BAG"]	['Description']	= 'Private Bag';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PB"]	['Constant']	= 'SERVICE_ADDR_TYPE_PRIVATE_BAG_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["PB"]	['Description']	= 'Private Bag';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RMB"]	['Constant']	= 'SERVICE_ADDR_TYPE_RMB';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RMB"]	['Description']	= 'Roadside Mail Box / Bag';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RMS"]	['Constant']	= 'SERVICE_ADDR_TYPE_RMS';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RMS"]	['Description']	= 'Roadside Mail Service';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RM"]	['Constant']	= 'SERVICE_ADDR_TYPE_ROOM';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RM"]	['Description']	= 'Room';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RSD"]	['Constant']	= 'SERVICE_ADDR_TYPE_RSD';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RSD"]	['Description']	= 'Roadside Delivery';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RMD"]	['Constant']	= 'SERVICE_ADDR_TYPE_RURAL_MAIL_DELIVERY';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["RMD"]	['Description']	= 'Rural Mail Delivery';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SD"]	['Constant']	= 'SERVICE_ADDR_TYPE_SHED';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SD"]	['Description']	= 'Shed';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SHD"]	['Constant']	= 'SERVICE_ADDR_TYPE_SHED_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SHD"]	['Description']	= 'Shed';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SHP"]	['Constant']	= 'SERVICE_ADDR_TYPE_SHOP';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SHP"]	['Description']	= 'Shop';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SP"]	['Constant']	= 'SERVICE_ADDR_TYPE_SHOP_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SP"]	['Description']	= 'Shop';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SIT"]	['Constant']	= 'SERVICE_ADDR_TYPE_SITE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SIT"]	['Description']	= 'Site';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SL"]	['Constant']	= 'SERVICE_ADDR_TYPE_STALL';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["SL"]	['Description']	= 'Stall';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["STL"]	['Constant']	= 'SERVICE_ADDR_TYPE_STALL_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["STL"]	['Description']	= 'Stall';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["STU"]	['Constant']	= 'SERVICE_ADDR_TYPE_STUDIO';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["STU"]	['Description']	= 'Studio';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["STE"]	['Constant']	= 'SERVICE_ADDR_TYPE_SUITE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["STE"]	['Description']	= 'Suite';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["TR"]	['Constant']	= 'SERVICE_ADDR_TYPE_TIER';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["TR"]	['Description']	= 'Tier';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["TW"]	['Constant']	= 'SERVICE_ADDR_TYPE_TOWER';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["TW"]	['Description']	= 'Tower';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["TWR"]	['Constant']	= 'SERVICE_ADDR_TYPE_TOWER_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["TWR"]	['Description']	= 'Tower';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["THS"]	['Constant']	= 'SERVICE_ADDR_TYPE_TOWNHOUSE';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["THS"]	['Description']	= 'Townhouse';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["UN"]	['Constant']	= 'SERVICE_ADDR_TYPE_UNIT';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["UN"]	['Description']	= 'Unit';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["UNT"]	['Constant']	= 'SERVICE_ADDR_TYPE_UNIT_2';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["UNT"]	['Description']	= 'Unit';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["UG"]	['Constant']	= 'SERVICE_ADDR_TYPE_UPPER_GROUND_FLOOR';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["UG"]	['Description']	= 'Upper Ground Floor';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["VIL"]	['Constant']	= 'SERVICE_ADDR_TYPE_VILLA';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["VIL"]	['Description']	= 'Villa';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["WRD"]	['Constant']	= 'SERVICE_ADDR_TYPE_WARD';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["WRD"]	['Description']	= 'Ward';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["WF"]	['Constant']	= 'SERVICE_ADDR_TYPE_WHARF';
$GLOBALS['*arrConstant']	['ServiceAddrType']	["WF"]	['Description']	= 'Wharf';


// Postal Address Types
/*
define("POSTAL_ADDR_TYPE_PO_BOX"					, "POB");
define("POSTAL_ADDR_TYPE_POST_OFFICE"				, "PO");
define("POSTAL_ADDR_TYPE_PRIVATE_BAG"				, "BAG");
define("POSTAL_ADDR_TYPE_COMMUNITY_MAIL_AGENT"		, "CMA");
define("POSTAL_ADDR_TYPE_COMMUNITY_MAIL_BAG"		, "CMB");
define("POSTAL_ADDR_TYPE_PRIVATE_BAG_2"				, "PB");
define("POSTAL_ADDR_TYPE_GPO_BOX"					, "GPO");
define("POSTAL_ADDR_TYPE_MAIL_SERVICE"				, "MS");
define("POSTAL_ADDR_TYPE_RURAL_MAIL_DELIVERY"		, "RMD");
define("POSTAL_ADDR_TYPE_ROADSIDE_MAIL_BAG_BOX"		, "RMB");
define("POSTAL_ADDR_TYPE_LOCKED_BAG"				, "LB");
define("POSTAL_ADDR_TYPE_ROADSIDE_MAIL_SERVICE"		, "RMS");
define("POSTAL_ADDR_TYPE_ROADSIDE_DELIVERY"			, "RD");
*/
// Postal Address Types
$GLOBALS['*arrConstant']	['PostalAddrType']	["POB"]	['Constant']	= 'POSTAL_ADDR_TYPE_PO_BOX';
$GLOBALS['*arrConstant']	['PostalAddrType']	["POB"]	['Description']	= 'PO Box';
$GLOBALS['*arrConstant']	['PostalAddrType']	["PO"]	['Constant']	= 'POSTAL_ADDR_TYPE_POST_OFFICE';
$GLOBALS['*arrConstant']	['PostalAddrType']	["PO"]	['Description']	= 'Post Office';
$GLOBALS['*arrConstant']	['PostalAddrType']	["BAG"]	['Constant']	= 'POSTAL_ADDR_TYPE_PRIVATE_BAG';
$GLOBALS['*arrConstant']	['PostalAddrType']	["BAG"]	['Description']	= 'Private Bag';
$GLOBALS['*arrConstant']	['PostalAddrType']	["CMA"]	['Constant']	= 'POSTAL_ADDR_TYPE_COMMUNITY_MAIL_AGENT';
$GLOBALS['*arrConstant']	['PostalAddrType']	["CMA"]	['Description']	= 'Community Mail Agent';
$GLOBALS['*arrConstant']	['PostalAddrType']	["CMB"]	['Constant']	= 'POSTAL_ADDR_TYPE_COMMUNITY_MAIL_BAG';
$GLOBALS['*arrConstant']	['PostalAddrType']	["CMB"]	['Description']	= 'Community Mail Bag';
$GLOBALS['*arrConstant']	['PostalAddrType']	["PB"]	['Constant']	= 'POSTAL_ADDR_TYPE_PRIVATE_BAG_2';
$GLOBALS['*arrConstant']	['PostalAddrType']	["PB"]	['Description']	= 'Private Bag';
$GLOBALS['*arrConstant']	['PostalAddrType']	["GPO"]	['Constant']	= 'POSTAL_ADDR_TYPE_GPO_BOX';
$GLOBALS['*arrConstant']	['PostalAddrType']	["GPO"]	['Description']	= 'GPO Box';
$GLOBALS['*arrConstant']	['PostalAddrType']	["MS"]	['Constant']	= 'POSTAL_ADDR_TYPE_MAIL_SERVICE';
$GLOBALS['*arrConstant']	['PostalAddrType']	["MS"]	['Description']	= 'Mail Service';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RMD"]	['Constant']	= 'POSTAL_ADDR_TYPE_RURAL_MAIL_DELIVERY';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RMD"]	['Description']	= 'Rural Mail Delivery';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RMB"]	['Constant']	= 'POSTAL_ADDR_TYPE_ROADSIDE_MAIL_BAG_BOX';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RMB"]	['Description']	= 'Roadside Mail Bag / Box';
$GLOBALS['*arrConstant']	['PostalAddrType']	["LB"]	['Constant']	= 'POSTAL_ADDR_TYPE_LOCKED_BAG';
$GLOBALS['*arrConstant']	['PostalAddrType']	["LB"]	['Description']	= 'Locked Bag';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RMS"]	['Constant']	= 'POSTAL_ADDR_TYPE_ROADSIDE_MAIL_SERVICE';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RMS"]	['Description']	= 'Roadside Mail Service';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RD"]	['Constant']	= 'POSTAL_ADDR_TYPE_ROADSIDE_DELIVERY';
$GLOBALS['*arrConstant']	['PostalAddrType']	["RD"]	['Description']	= 'Roadside Delivery';


// Service Street Type
/*
define("SERVICE_STREET_TYPE_ACCESS"					, "ACCS");
define("SERVICE_STREET_TYPE_ALLEY"					, "ALLY");
define("SERVICE_STREET_TYPE_ALLEYWAY"				, "ALWY");
define("SERVICE_STREET_TYPE_AMBLE"					, "AMBL");
define("SERVICE_STREET_TYPE_ANCHORAGE"				, "ANCG");
define("SERVICE_STREET_TYPE_APPROACH"				, "APP");
define("SERVICE_STREET_TYPE_ARCADE"					, "ARC");
define("SERVICE_STREET_TYPE_ARTERIAL"				, "ARTL");
define("SERVICE_STREET_TYPE_ARTERY"					, "ART");
define("SERVICE_STREET_TYPE_AVENUE"					, "AV");
define("SERVICE_STREET_TYPE_AVENUE_2"				, "AVE");
define("SERVICE_STREET_TYPE_BANK"					, "BNK");
define("SERVICE_STREET_TYPE_BARRACKS"				, "BRKS");
define("SERVICE_STREET_TYPE_BASIN"					, "BASN");
define("SERVICE_STREET_TYPE_BAY"					, "BAY");
define("SERVICE_STREET_TYPE_BAY_2"					, "BY");
define("SERVICE_STREET_TYPE_BEACH"					, "BCH");
define("SERVICE_STREET_TYPE_BEND"					, "BEND");
define("SERVICE_STREET_TYPE_BLOCK"					, "BLK");
define("SERVICE_STREET_TYPE_BOULEVARD"				, "BLV");
define("SERVICE_STREET_TYPE_BOULEVARD_2"			, "BVD");
define("SERVICE_STREET_TYPE_BOUNDARY"				, "BNDY");
define("SERVICE_STREET_TYPE_BOWL"					, "BWL");
define("SERVICE_STREET_TYPE_BRACE"					, "BR");
define("SERVICE_STREET_TYPE_BRACE_2"				, "BRCE");
define("SERVICE_STREET_TYPE_BRAE"					, "BRAE");
define("SERVICE_STREET_TYPE_BRANCH"					, "BRCH");
define("SERVICE_STREET_TYPE_BREA"					, "BREA");
define("SERVICE_STREET_TYPE_BREAK"					, "BRK");
define("SERVICE_STREET_TYPE_BRIDGE"					, "BDGE");
define("SERVICE_STREET_TYPE_BRIDGE_2"				, "BRDG");
define("SERVICE_STREET_TYPE_BROADWAY"				, "BDWY");
define("SERVICE_STREET_TYPE_BROW"					, "BROW");
define("SERVICE_STREET_TYPE_BYPASS"					, "BYPA");
define("SERVICE_STREET_TYPE_BYWAY"					, "BYWY");
define("SERVICE_STREET_TYPE_CAUSEWAY"				, "CAUS");
define("SERVICE_STREET_TYPE_CENTRE"					, "CNTR");
define("SERVICE_STREET_TYPE_CENTRE_2"				, "CTR");
define("SERVICE_STREET_TYPE_CENTREWAY"				, "CNWY");
define("SERVICE_STREET_TYPE_CHASE"					, "CH");
define("SERVICE_STREET_TYPE_CIRCLE"					, "CIR");
define("SERVICE_STREET_TYPE_CIRCLET"				, "CLT");
define("SERVICE_STREET_TYPE_CIRCUIT"				, "CCT");
define("SERVICE_STREET_TYPE_CIRCUIT_2"				, "CRCT");
define("SERVICE_STREET_TYPE_CIRCUS"					, "CRCS");
define("SERVICE_STREET_TYPE_CLOSE"					, "CL");
define("SERVICE_STREET_TYPE_COLONNADE"				, "CLDE");
define("SERVICE_STREET_TYPE_COMMON"					, "CMMN");
define("SERVICE_STREET_TYPE_COMMUNITY"				, "COMM");
define("SERVICE_STREET_TYPE_CONCOURSE"				, "CON");
define("SERVICE_STREET_TYPE_CONNECTION"				, "CNTN");
define("SERVICE_STREET_TYPE_COPSE"					, "CPS");
define("SERVICE_STREET_TYPE_CORNER"					, "CNR");
define("SERVICE_STREET_TYPE_CORSO"					, "CSO");
define("SERVICE_STREET_TYPE_COURSE"					, "CORS");
define("SERVICE_STREET_TYPE_COURT"					, "CT");
define("SERVICE_STREET_TYPE_COURTYARD"				, "CTYD");
define("SERVICE_STREET_TYPE_COVE"					, "COVE");
define("SERVICE_STREET_TYPE_CREEK"					, "CK");
define("SERVICE_STREET_TYPE_CREEK_2"				, "CRK");
define("SERVICE_STREET_TYPE_CRESCENT"				, "CR");
define("SERVICE_STREET_TYPE_CRESCENT_2"				, "CRES");
define("SERVICE_STREET_TYPE_CREST"					, "CRST");
define("SERVICE_STREET_TYPE_CRIEF"					, "CRF");
define("SERVICE_STREET_TYPE_CROSS"					, "CRSS");
define("SERVICE_STREET_TYPE_CROSSING"				, "CRSG");
define("SERVICE_STREET_TYPE_CROSSROADS"				, "CRD");
define("SERVICE_STREET_TYPE_CROSSWAY"				, "COWY");
define("SERVICE_STREET_TYPE_CRUISEWAY"				, "CUWY");
define("SERVICE_STREET_TYPE_CUL_DE_SAC"				, "CDS");
define("SERVICE_STREET_TYPE_CUTTING"				, "CTTG");
define("SERVICE_STREET_TYPE_DALE"					, "DALE");
define("SERVICE_STREET_TYPE_DELL"					, "DELL");
define("SERVICE_STREET_TYPE_DEVIATION"				, "DEVN");
define("SERVICE_STREET_TYPE_DIP"					, "DIP");
define("SERVICE_STREET_TYPE_DISTRIBUTOR"			, "DSTR");
define("SERVICE_STREET_TYPE_DOWNS"					, "DWNS");
define("SERVICE_STREET_TYPE_DRIVE"					, "DR");
define("SERVICE_STREET_TYPE_DRIVE_2"				, "DRV");
define("SERVICE_STREET_TYPE_DRIVEWAY"				, "DRWY");
define("SERVICE_STREET_TYPE_EASEMENT"				, "EMNT");
define("SERVICE_STREET_TYPE_EDGE"					, "EDGE");
define("SERVICE_STREET_TYPE_ELBOW"					, "ELB");
define("SERVICE_STREET_TYPE_END"					, "END");
define("SERVICE_STREET_TYPE_ENTRANCE"				, "ENT");
define("SERVICE_STREET_TYPE_ESPLANADE"				, "ESP");
define("SERVICE_STREET_TYPE_ESTATE"					, "EST");
define("SERVICE_STREET_TYPE_EXPRESSWAY"				, "EXP");
define("SERVICE_STREET_TYPE_EXPRESSWAY_2"			, "EXWY");
define("SERVICE_STREET_TYPE_EXTENSION"				, "EXT");
define("SERVICE_STREET_TYPE_EXTENSION_2"			, "EXTN");
define("SERVICE_STREET_TYPE_FAIR"					, "FAIR");
define("SERVICE_STREET_TYPE_FAIRWAY"				, "FAWY");
define("SERVICE_STREET_TYPE_FIRE_TRACK"				, "FTRK");
define("SERVICE_STREET_TYPE_FIRETRAIL"				, "FITR");
define("SERVICE_STREET_TYPE_FIRETRALL"				, "FTRL");
define("SERVICE_STREET_TYPE_FLAT"					, "FLAT");
define("SERVICE_STREET_TYPE_FOLLOW"					, "FOWL");
define("SERVICE_STREET_TYPE_FOOTWAY"				, "FTWY");
define("SERVICE_STREET_TYPE_FORESHORE"				, "FSHR");
define("SERVICE_STREET_TYPE_FORMATION"				, "FORM");
define("SERVICE_STREET_TYPE_FREEWAY"				, "FRWY");
define("SERVICE_STREET_TYPE_FREEWAY_2"				, "FWY");
define("SERVICE_STREET_TYPE_FRONT"					, "FRNT");
define("SERVICE_STREET_TYPE_FRONTAGE"				, "FRTG");
define("SERVICE_STREET_TYPE_GAP"					, "GAP");
define("SERVICE_STREET_TYPE_GARDEN"					, "GDN");
define("SERVICE_STREET_TYPE_GARDENS"				, "GDNS");
define("SERVICE_STREET_TYPE_GATE"					, "GTE");
define("SERVICE_STREET_TYPE_GATES"					, "GTES");
define("SERVICE_STREET_TYPE_GATEWAY"				, "GTWY");
define("SERVICE_STREET_TYPE_GLADE"					, "GLD");
define("SERVICE_STREET_TYPE_GLEN"					, "GLEN");
define("SERVICE_STREET_TYPE_GRANGE"					, "GRA");
define("SERVICE_STREET_TYPE_GREEN"					, "GRN");
define("SERVICE_STREET_TYPE_GROUND"					, "GRND");
define("SERVICE_STREET_TYPE_GROVE"					, "GR");
define("SERVICE_STREET_TYPE_GROVE_2"				, "GV");
define("SERVICE_STREET_TYPE_GULLY"					, "GLY");
define("SERVICE_STREET_TYPE_HEATH"					, "HTH");
define("SERVICE_STREET_TYPE_HEIGHTS"				, "HTS");
define("SERVICE_STREET_TYPE_HIGHROAD"				, "HRD");
define("SERVICE_STREET_TYPE_HIGHWAY"				, "HWY");
define("SERVICE_STREET_TYPE_HILL"					, "HILL");
define("SERVICE_STREET_TYPE_HILLSIDE"				, "HLSD");
define("SERVICE_STREET_TYPE_HOUSE"					, "HSE");
define("SERVICE_STREET_TYPE_INTERCHANGE"			, "INTG");
define("SERVICE_STREET_TYPE_INTERSECTION"			, "INTN");
define("SERVICE_STREET_TYPE_ISLAND"					, "IS");
define("SERVICE_STREET_TYPE_JUNCTION"				, "JNC");
define("SERVICE_STREET_TYPE_JUNCTION_2"				, "JNCT");
define("SERVICE_STREET_TYPE_KEY"					, "KEY");
define("SERVICE_STREET_TYPE_KNOLL"					, "KNLL");
define("SERVICE_STREET_TYPE_LANDING"				, "LDG");
define("SERVICE_STREET_TYPE_LANE"					, "L");
define("SERVICE_STREET_TYPE_LANE_2"					, "LANE");
define("SERVICE_STREET_TYPE_LANE_3"					, "LN");
define("SERVICE_STREET_TYPE_LANEWAY"				, "LNWY");
define("SERVICE_STREET_TYPE_LEES"					, "LEES");
define("SERVICE_STREET_TYPE_LINE"					, "LINE");
define("SERVICE_STREET_TYPE_LINK"					, "LINK");
define("SERVICE_STREET_TYPE_LITTLE"					, "LT");
define("SERVICE_STREET_TYPE_LOCATION"				, "LOCN");
define("SERVICE_STREET_TYPE_LOOKOUT"				, "LKT");
define("SERVICE_STREET_TYPE_LOOP"					, "LOOP");
define("SERVICE_STREET_TYPE_LOWER"					, "LWR");
define("SERVICE_STREET_TYPE_MALL"					, "MALL");
define("SERVICE_STREET_TYPE_MARKETLAND"				, "MKLD");
define("SERVICE_STREET_TYPE_MARKETTOWN"				, "MKTN");
define("SERVICE_STREET_TYPE_MEAD"					, "MEAD");
define("SERVICE_STREET_TYPE_MEANDER"				, "MNDR");
define("SERVICE_STREET_TYPE_MEW"					, "MEW");
define("SERVICE_STREET_TYPE_MEWS"					, "MEWS");
define("SERVICE_STREET_TYPE_MOTORWAY"				, "MWY");
define("SERVICE_STREET_TYPE_MOUNT"					, "MT");
define("SERVICE_STREET_TYPE_MOUNTAIN"				, "MTN");
define("SERVICE_STREET_TYPE_NOOK"					, "NOOK");
define("SERVICE_STREET_TYPE_NOT_REQUIRED"			, "NR");
define("SERVICE_STREET_TYPE_OUTLOOK"				, "OTLK");
define("SERVICE_STREET_TYPE_OVAL"					, "OVAL");
define("SERVICE_STREET_TYPE_PARADE"					, "PDE");
define("SERVICE_STREET_TYPE_PARADISE"				, "PDSE");
define("SERVICE_STREET_TYPE_PARK"					, "PARK");
define("SERVICE_STREET_TYPE_PARK_2"					, "PK");
define("SERVICE_STREET_TYPE_PARKLANDS"				, "PKLD");
define("SERVICE_STREET_TYPE_PARKWAY"				, "PKWY");
define("SERVICE_STREET_TYPE_PART"					, "PART");
define("SERVICE_STREET_TYPE_PASS"					, "PASS");
define("SERVICE_STREET_TYPE_PATH"					, "PATH");
define("SERVICE_STREET_TYPE_PATHWAY"				, "PWAY");
define("SERVICE_STREET_TYPE_PATHWAY_2"				, "PWY");
define("SERVICE_STREET_TYPE_PENINSULA"				, "PEN");
define("SERVICE_STREET_TYPE_PIAZZA"					, "PIAZ");
define("SERVICE_STREET_TYPE_PIER"					, "PR");
define("SERVICE_STREET_TYPE_PLACE"					, "PL");
define("SERVICE_STREET_TYPE_PLATEAU"				, "PLAT");
define("SERVICE_STREET_TYPE_PLAZA"					, "PLZA");
define("SERVICE_STREET_TYPE_POCKET"					, "PKT");
define("SERVICE_STREET_TYPE_POINT"					, "PNT");
define("SERVICE_STREET_TYPE_PORT"					, "PORT");
define("SERVICE_STREET_TYPE_PORT_2"					, "PRT");
define("SERVICE_STREET_TYPE_PROMENADE"				, "PROM");
define("SERVICE_STREET_TYPE_PURSUIT"				, "PUR");
define("SERVICE_STREET_TYPE_QUAD"					, "QUAD");
define("SERVICE_STREET_TYPE_QUADRANGLE"				, "QDGL");
define("SERVICE_STREET_TYPE_QUADRANT"				, "QDRT");
define("SERVICE_STREET_TYPE_QUAY"					, "QY");
define("SERVICE_STREET_TYPE_QUAYS"					, "QYS");
define("SERVICE_STREET_TYPE_RACECOURSE"				, "RCSE");
define("SERVICE_STREET_TYPE_RAMBLE"					, "RMBL");
define("SERVICE_STREET_TYPE_RAMP"					, "RAMP");
define("SERVICE_STREET_TYPE_RANGE"					, "RNGE");
define("SERVICE_STREET_TYPE_REACH"					, "RCH");
define("SERVICE_STREET_TYPE_RESERVE"				, "RES");
define("SERVICE_STREET_TYPE_REST"					, "REST");
define("SERVICE_STREET_TYPE_RETREAT"				, "RTT");
define("SERVICE_STREET_TYPE_RETURN"					, "RTRN");
define("SERVICE_STREET_TYPE_RIDE"					, "RIDE");
define("SERVICE_STREET_TYPE_RIDGE"					, "RDGE");
define("SERVICE_STREET_TYPE_RIDGEWAY"				, "RGWY");
define("SERVICE_STREET_TYPE_RIGHT_OF_WAY"			, "ROWY");
define("SERVICE_STREET_TYPE_RING"					, "RING");
define("SERVICE_STREET_TYPE_RISE"					, "RISE");
define("SERVICE_STREET_TYPE_RIVER"					, "RVR");
define("SERVICE_STREET_TYPE_RIVERWAY"				, "RVWY");
define("SERVICE_STREET_TYPE_RIVIERA"				, "RVRA");
define("SERVICE_STREET_TYPE_ROAD"					, "RD");
define("SERVICE_STREET_TYPE_ROADS"					, "RDS");
define("SERVICE_STREET_TYPE_ROADSIDE"				, "RDSD");
define("SERVICE_STREET_TYPE_ROADWAY"				, "RDWY");
define("SERVICE_STREET_TYPE_RONDE"					, "RNDE");
define("SERVICE_STREET_TYPE_ROSEBOWL"				, "RSBL");
define("SERVICE_STREET_TYPE_ROTARY"					, "RTY");
define("SERVICE_STREET_TYPE_ROUND"					, "RND");
define("SERVICE_STREET_TYPE_ROUTE"					, "RTE");
define("SERVICE_STREET_TYPE_ROW"					, "ROW");
define("SERVICE_STREET_TYPE_ROWE"					, "RWE");
define("SERVICE_STREET_TYPE_RUE"					, "RUE");
define("SERVICE_STREET_TYPE_RUN"					, "RUN");
define("SERVICE_STREET_TYPE_SECTION"				, "SEC");
define("SERVICE_STREET_TYPE_SERVICE_WAY"			, "SWY");
define("SERVICE_STREET_TYPE_SIDING"					, "SDNG");
define("SERVICE_STREET_TYPE_SLOPE"					, "SLPE");
define("SERVICE_STREET_TYPE_SOUND"					, "SND");
define("SERVICE_STREET_TYPE_SPUR"					, "SPUR");
define("SERVICE_STREET_TYPE_SQUARE"					, "SQ");
define("SERVICE_STREET_TYPE_STAIRS"					, "STRS");
define("SERVICE_STREET_TYPE_STATE_HIGHWAY"			, "SHWY");
define("SERVICE_STREET_TYPE_STATION"				, "STN");
define("SERVICE_STREET_TYPE_STEPS"					, "STPS");
define("SERVICE_STREET_TYPE_STOP"					, "STOP");
define("SERVICE_STREET_TYPE_STRAIGHT"				, "STGT");
define("SERVICE_STREET_TYPE_STRAND"					, "STRA");
define("SERVICE_STREET_TYPE_STREET"					, "ST");
define("SERVICE_STREET_TYPE_STRIP"					, "STP");
define("SERVICE_STREET_TYPE_STRIP_2"				, "STRP");
define("SERVICE_STREET_TYPE_SUBWAY"					, "SBWY");
define("SERVICE_STREET_TYPE_TARN"					, "TARN");
define("SERVICE_STREET_TYPE_TERRACE"				, "TCE");
define("SERVICE_STREET_TYPE_THOROUGHFARE"			, "THOR");
define("SERVICE_STREET_TYPE_TOLLWAY"				, "TLWY");
define("SERVICE_STREET_TYPE_TOP"					, "TOP");
define("SERVICE_STREET_TYPE_TOR"					, "TOR");
define("SERVICE_STREET_TYPE_TOWER"					, "TWR");
define("SERVICE_STREET_TYPE_TOWERS"					, "TWRS");
define("SERVICE_STREET_TYPE_TRACK"					, "TRK");
define("SERVICE_STREET_TYPE_TRAIL"					, "TRL");
define("SERVICE_STREET_TYPE_TRAILER"				, "TRLR");
define("SERVICE_STREET_TYPE_TRIANGLE"				, "TRI");
define("SERVICE_STREET_TYPE_TRUNKWAY"				, "TKWY");
define("SERVICE_STREET_TYPE_TURN"					, "TURN");
define("SERVICE_STREET_TYPE_UNDERPASS"				, "UPAS");
define("SERVICE_STREET_TYPE_UPPER"					, "UPR");
define("SERVICE_STREET_TYPE_VALE"					, "VALE");
define("SERVICE_STREET_TYPE_VALLEY"					, "VLY");
define("SERVICE_STREET_TYPE_VIADUCT"				, "VDCT");
define("SERVICE_STREET_TYPE_VIEW"					, "VIEW");
define("SERVICE_STREET_TYPE_VILLAGE"				, "VLGE");
define("SERVICE_STREET_TYPE_VILLAS"					, "VLLS");
define("SERVICE_STREET_TYPE_VISTA"					, "VSTA");
define("SERVICE_STREET_TYPE_WADE"					, "WADE");
define("SERVICE_STREET_TYPE_WALK"					, "WALK");
define("SERVICE_STREET_TYPE_WALK_2"					, "WK");
define("SERVICE_STREET_TYPE_WALKWAY"				, "WKWY");
define("SERVICE_STREET_TYPE_WATERS"					, "WTRS");
define("SERVICE_STREET_TYPE_WAY"					, "WAY");
define("SERVICE_STREET_TYPE_WAY_2"					, "WY");
define("SERVICE_STREET_TYPE_WEST"					, "WEST");
define("SERVICE_STREET_TYPE_WHARF"					, "WHF");
define("SERVICE_STREET_TYPE_WHARF_2"				, "WHRF");
define("SERVICE_STREET_TYPE_WOOD"					, "WOOD");
define("SERVICE_STREET_TYPE_WYND"					, "WYND");
define("SERVICE_STREET_TYPE_YARD"					, "YARD");
define("SERVICE_STREET_TYPE_YARD_2"					, "YRD");
*/
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ACCS"]	['Constant']	= 'SERVICE_STREET_TYPE_ACCESS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ACCS"]	['Description']	= 'Access';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ALLY"]	['Constant']	= 'SERVICE_STREET_TYPE_ALLEY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ALLY"]	['Description']	= 'Alley';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ALWY"]	['Constant']	= 'SERVICE_STREET_TYPE_ALLEYWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ALWY"]	['Description']	= 'Alleyway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["AMBL"]	['Constant']	= 'SERVICE_STREET_TYPE_AMBLE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["AMBL"]	['Description']	= 'Amble';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ANCG"]	['Constant']	= 'SERVICE_STREET_TYPE_ANCHORAGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ANCG"]	['Description']	= 'Anchorage';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["APP"]		['Constant']	= 'SERVICE_STREET_TYPE_APPROACH';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["APP"]		['Description']	= 'Approach';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ARC"]		['Constant']	= 'SERVICE_STREET_TYPE_ARCADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ARC"]		['Description']	= 'Arcade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ARTL"]	['Constant']	= 'SERVICE_STREET_TYPE_ARTERIAL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ARTL"]	['Description']	= 'Arterial';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ART"]		['Constant']	= 'SERVICE_STREET_TYPE_ARTERY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ART"]		['Description']	= 'Artery';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["AV"]		['Constant']	= 'SERVICE_STREET_TYPE_AVENUE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["AV"]		['Description']	= 'Avenue';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["AVE"]		['Constant']	= 'SERVICE_STREET_TYPE_AVENUE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["AVE"]		['Description']	= 'Avenue';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BNK"]		['Constant']	= 'SERVICE_STREET_TYPE_BANK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BNK"]		['Description']	= 'Bank';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRKS"]	['Constant']	= 'SERVICE_STREET_TYPE_BARRACKS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRKS"]	['Description']	= 'Barracks';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BASN"]	['Constant']	= 'SERVICE_STREET_TYPE_BASIN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BASN"]	['Description']	= 'Basin';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BAY"]		['Constant']	= 'SERVICE_STREET_TYPE_BAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BAY"]		['Description']	= 'Bay';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BY"]		['Constant']	= 'SERVICE_STREET_TYPE_BAY_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BY"]		['Description']	= 'Bay';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BCH"]		['Constant']	= 'SERVICE_STREET_TYPE_BEACH';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BCH"]		['Description']	= 'Beach';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BEND"]	['Constant']	= 'SERVICE_STREET_TYPE_BEND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BEND"]	['Description']	= 'Bend';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BLK"]		['Constant']	= 'SERVICE_STREET_TYPE_BLOCK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BLK"]		['Description']	= 'Block';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BLV"]		['Constant']	= 'SERVICE_STREET_TYPE_BOULEVARD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BLV"]		['Description']	= 'Boulevard';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BVD"]		['Constant']	= 'SERVICE_STREET_TYPE_BOULEVARD_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BVD"]		['Description']	= 'Boulevard';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BNDY"]	['Constant']	= 'SERVICE_STREET_TYPE_BOUNDARY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BNDY"]	['Description']	= 'Boundary';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BWL"]		['Constant']	= 'SERVICE_STREET_TYPE_BOWL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BWL"]		['Description']	= 'Bowl';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BR"]		['Constant']	= 'SERVICE_STREET_TYPE_BRACE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BR"]		['Description']	= 'Brace';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRCE"]	['Constant']	= 'SERVICE_STREET_TYPE_BRACE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRCE"]	['Description']	= 'Brace';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRAE"]	['Constant']	= 'SERVICE_STREET_TYPE_BRAE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRAE"]	['Description']	= 'Brae';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRCH"]	['Constant']	= 'SERVICE_STREET_TYPE_BRANCH';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRCH"]	['Description']	= 'Branch';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BREA"]	['Constant']	= 'SERVICE_STREET_TYPE_BREA';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BREA"]	['Description']	= 'Brea';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRK"]		['Constant']	= 'SERVICE_STREET_TYPE_BREAK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRK"]		['Description']	= 'Break';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BDGE"]	['Constant']	= 'SERVICE_STREET_TYPE_BRIDGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BDGE"]	['Description']	= 'Bridge';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRDG"]	['Constant']	= 'SERVICE_STREET_TYPE_BRIDGE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BRDG"]	['Description']	= 'Bridge';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BDWY"]	['Constant']	= 'SERVICE_STREET_TYPE_BROADWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BDWY"]	['Description']	= 'Broadway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BROW"]	['Constant']	= 'SERVICE_STREET_TYPE_BROW';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BROW"]	['Description']	= 'Brow';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BYPA"]	['Constant']	= 'SERVICE_STREET_TYPE_BYPASS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BYPA"]	['Description']	= 'Bypass';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BYWY"]	['Constant']	= 'SERVICE_STREET_TYPE_BYWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["BYWY"]	['Description']	= 'Byway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CAUS"]	['Constant']	= 'SERVICE_STREET_TYPE_CAUSEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CAUS"]	['Description']	= 'Causeway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNTR"]	['Constant']	= 'SERVICE_STREET_TYPE_CENTRE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNTR"]	['Description']	= 'Centre';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CTR"]		['Constant']	= 'SERVICE_STREET_TYPE_CENTRE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CTR"]		['Description']	= 'Centre';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNWY"]	['Constant']	= 'SERVICE_STREET_TYPE_CENTREWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNWY"]	['Description']	= 'Centreway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CH"]		['Constant']	= 'SERVICE_STREET_TYPE_CHASE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CH"]		['Description']	= 'Chase';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CIR"]		['Constant']	= 'SERVICE_STREET_TYPE_CIRCLE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CIR"]		['Description']	= 'Circle';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CLT"]		['Constant']	= 'SERVICE_STREET_TYPE_CIRCLET';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CLT"]		['Description']	= 'Circlet';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CCT"]		['Constant']	= 'SERVICE_STREET_TYPE_CIRCUIT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CCT"]		['Description']	= 'Circuit';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRCT"]	['Constant']	= 'SERVICE_STREET_TYPE_CIRCUIT_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRCT"]	['Description']	= 'Circuit';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRCS"]	['Constant']	= 'SERVICE_STREET_TYPE_CIRCUS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRCS"]	['Description']	= 'Circus';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CL"]		['Constant']	= 'SERVICE_STREET_TYPE_CLOSE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CL"]		['Description']	= 'Close';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CLDE"]	['Constant']	= 'SERVICE_STREET_TYPE_COLONNADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CLDE"]	['Description']	= 'Colonnade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CMMN"]	['Constant']	= 'SERVICE_STREET_TYPE_COMMON';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CMMN"]	['Description']	= 'Common';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["COMM"]	['Constant']	= 'SERVICE_STREET_TYPE_COMMUNITY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["COMM"]	['Description']	= 'Community';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CON"]		['Constant']	= 'SERVICE_STREET_TYPE_CONCOURSE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CON"]		['Description']	= 'Concourse';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNTN"]	['Constant']	= 'SERVICE_STREET_TYPE_CONNECTION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNTN"]	['Description']	= 'Connection';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CPS"]		['Constant']	= 'SERVICE_STREET_TYPE_COPSE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CPS"]		['Description']	= 'Copse';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNR"]		['Constant']	= 'SERVICE_STREET_TYPE_CORNER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CNR"]		['Description']	= 'Corner';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CSO"]		['Constant']	= 'SERVICE_STREET_TYPE_CORSO';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CSO"]		['Description']	= 'Corso';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CORS"]	['Constant']	= 'SERVICE_STREET_TYPE_COURSE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CORS"]	['Description']	= 'Course';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CT"]		['Constant']	= 'SERVICE_STREET_TYPE_COURT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CT"]		['Description']	= 'Court';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CTYD"]	['Constant']	= 'SERVICE_STREET_TYPE_COURTYARD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CTYD"]	['Description']	= 'Courtyard';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["COVE"]	['Constant']	= 'SERVICE_STREET_TYPE_COVE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["COVE"]	['Description']	= 'Cove';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CK"]		['Constant']	= 'SERVICE_STREET_TYPE_CREEK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CK"]		['Description']	= 'Creek';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRK"]		['Constant']	= 'SERVICE_STREET_TYPE_CREEK_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRK"]		['Description']	= 'Creek';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CR"]		['Constant']	= 'SERVICE_STREET_TYPE_CRESCENT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CR"]		['Description']	= 'Crescent';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRES"]	['Constant']	= 'SERVICE_STREET_TYPE_CRESCENT_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRES"]	['Description']	= 'Crescent';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRST"]	['Constant']	= 'SERVICE_STREET_TYPE_CREST';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRST"]	['Description']	= 'Crest';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRF"]		['Constant']	= 'SERVICE_STREET_TYPE_CRIEF';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRF"]		['Description']	= 'Crief';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRSS"]	['Constant']	= 'SERVICE_STREET_TYPE_CROSS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRSS"]	['Description']	= 'Cross';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRSG"]	['Constant']	= 'SERVICE_STREET_TYPE_CROSSING';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRSG"]	['Description']	= 'Crossing';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRD"]		['Constant']	= 'SERVICE_STREET_TYPE_CROSSROADS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CRD"]		['Description']	= 'Crossroads';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["COWY"]	['Constant']	= 'SERVICE_STREET_TYPE_CROSSWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["COWY"]	['Description']	= 'Crossway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CUWY"]	['Constant']	= 'SERVICE_STREET_TYPE_CRUISEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CUWY"]	['Description']	= 'Cruiseway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CDS"]		['Constant']	= 'SERVICE_STREET_TYPE_CUL_DE_SAC';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CDS"]		['Description']	= 'Cul De Sac';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CTTG"]	['Constant']	= 'SERVICE_STREET_TYPE_CUTTING';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["CTTG"]	['Description']	= 'Cutting';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DALE"]	['Constant']	= 'SERVICE_STREET_TYPE_DALE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DALE"]	['Description']	= 'Dale';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DELL"]	['Constant']	= 'SERVICE_STREET_TYPE_DELL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DELL"]	['Description']	= 'Dell';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DEVN"]	['Constant']	= 'SERVICE_STREET_TYPE_DEVIATION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DEVN"]	['Description']	= 'Deviation';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DIP"]		['Constant']	= 'SERVICE_STREET_TYPE_DIP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DIP"]		['Description']	= 'Dip';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DSTR"]	['Constant']	= 'SERVICE_STREET_TYPE_DISTRIBUTOR';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DSTR"]	['Description']	= 'Distributor';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DWNS"]	['Constant']	= 'SERVICE_STREET_TYPE_DOWNS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DWNS"]	['Description']	= 'Downs';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DR"]		['Constant']	= 'SERVICE_STREET_TYPE_DRIVE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DR"]		['Description']	= 'Drive';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DRV"]		['Constant']	= 'SERVICE_STREET_TYPE_DRIVE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DRV"]		['Description']	= 'Drive';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DRWY"]	['Constant']	= 'SERVICE_STREET_TYPE_DRIVEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["DRWY"]	['Description']	= 'Driveway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EMNT"]	['Constant']	= 'SERVICE_STREET_TYPE_EASEMENT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EMNT"]	['Description']	= 'Easement';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EDGE"]	['Constant']	= 'SERVICE_STREET_TYPE_EDGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EDGE"]	['Description']	= 'Edge';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ELB"]		['Constant']	= 'SERVICE_STREET_TYPE_ELBOW';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ELB"]		['Description']	= 'Elbow';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["END"]		['Constant']	= 'SERVICE_STREET_TYPE_END';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["END"]		['Description']	= 'End';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ENT"]		['Constant']	= 'SERVICE_STREET_TYPE_ENTRANCE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ENT"]		['Description']	= 'Entrance';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ESP"]		['Constant']	= 'SERVICE_STREET_TYPE_ESPLANADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ESP"]		['Description']	= 'Esplanade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EST"]		['Constant']	= 'SERVICE_STREET_TYPE_ESTATE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EST"]		['Description']	= 'Estate';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXP"]		['Constant']	= 'SERVICE_STREET_TYPE_EXPRESSWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXP"]		['Description']	= 'Expressway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXWY"]	['Constant']	= 'SERVICE_STREET_TYPE_EXPRESSWAY_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXWY"]	['Description']	= 'Expressway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXT"]		['Constant']	= 'SERVICE_STREET_TYPE_EXTENSION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXT"]		['Description']	= 'Extension';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXTN"]	['Constant']	= 'SERVICE_STREET_TYPE_EXTENSION_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["EXTN"]	['Description']	= 'Extension';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FAIR"]	['Constant']	= 'SERVICE_STREET_TYPE_FAIR';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FAIR"]	['Description']	= 'Fair';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FAWY"]	['Constant']	= 'SERVICE_STREET_TYPE_FAIRWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FAWY"]	['Description']	= 'Fairway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FTRK"]	['Constant']	= 'SERVICE_STREET_TYPE_FIRE_TRACK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FTRK"]	['Description']	= 'Fire Track';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FITR"]	['Constant']	= 'SERVICE_STREET_TYPE_FIRETRAIL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FITR"]	['Description']	= 'Firetrail';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FTRL"]	['Constant']	= 'SERVICE_STREET_TYPE_FIRETRALL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FTRL"]	['Description']	= 'Firetrall';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FLAT"]	['Constant']	= 'SERVICE_STREET_TYPE_FLAT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FLAT"]	['Description']	= 'Flat';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FOWL"]	['Constant']	= 'SERVICE_STREET_TYPE_FOLLOW';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FOWL"]	['Description']	= 'Follow';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FTWY"]	['Constant']	= 'SERVICE_STREET_TYPE_FOOTWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FTWY"]	['Description']	= 'Footway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FSHR"]	['Constant']	= 'SERVICE_STREET_TYPE_FORESHORE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FSHR"]	['Description']	= 'Foreshore';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FORM"]	['Constant']	= 'SERVICE_STREET_TYPE_FORMATION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FORM"]	['Description']	= 'Formation';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FRWY"]	['Constant']	= 'SERVICE_STREET_TYPE_FREEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FRWY"]	['Description']	= 'Freeway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FWY"]		['Constant']	= 'SERVICE_STREET_TYPE_FREEWAY_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FWY"]		['Description']	= 'Freeway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FRNT"]	['Constant']	= 'SERVICE_STREET_TYPE_FRONT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FRNT"]	['Description']	= 'Front';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FRTG"]	['Constant']	= 'SERVICE_STREET_TYPE_FRONTAGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["FRTG"]	['Description']	= 'Frontage';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GAP"]		['Constant']	= 'SERVICE_STREET_TYPE_GAP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GAP"]		['Description']	= 'Gap';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GDN"]		['Constant']	= 'SERVICE_STREET_TYPE_GARDEN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GDN"]		['Description']	= 'Garden';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GDNS"]	['Constant']	= 'SERVICE_STREET_TYPE_GARDENS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GDNS"]	['Description']	= 'Gardens';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GTE"]		['Constant']	= 'SERVICE_STREET_TYPE_GATE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GTE"]		['Description']	= 'Gate';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GTES"]	['Constant']	= 'SERVICE_STREET_TYPE_GATES';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GTES"]	['Description']	= 'Gates';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GTWY"]	['Constant']	= 'SERVICE_STREET_TYPE_GATEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GTWY"]	['Description']	= 'Gateway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GLD"]		['Constant']	= 'SERVICE_STREET_TYPE_GLADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GLD"]		['Description']	= 'Glade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GLEN"]	['Constant']	= 'SERVICE_STREET_TYPE_GLEN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GLEN"]	['Description']	= 'Glen';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GRA"]		['Constant']	= 'SERVICE_STREET_TYPE_GRANGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GRA"]		['Description']	= 'Grange';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GRN"]		['Constant']	= 'SERVICE_STREET_TYPE_GREEN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GRN"]		['Description']	= 'Green';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GRND"]	['Constant']	= 'SERVICE_STREET_TYPE_GROUND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GRND"]	['Description']	= 'Ground';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GR"]		['Constant']	= 'SERVICE_STREET_TYPE_GROVE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GR"]		['Description']	= 'Grove';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GV"]		['Constant']	= 'SERVICE_STREET_TYPE_GROVE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GV"]		['Description']	= 'Grove';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GLY"]		['Constant']	= 'SERVICE_STREET_TYPE_GULLY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["GLY"]		['Description']	= 'Gully';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HTH"]		['Constant']	= 'SERVICE_STREET_TYPE_HEATH';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HTH"]		['Description']	= 'Heath';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HTS"]		['Constant']	= 'SERVICE_STREET_TYPE_HEIGHTS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HTS"]		['Description']	= 'Heights';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HRD"]		['Constant']	= 'SERVICE_STREET_TYPE_HIGHROAD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HRD"]		['Description']	= 'Highroad';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HWY"]		['Constant']	= 'SERVICE_STREET_TYPE_HIGHWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HWY"]		['Description']	= 'Highway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HILL"]	['Constant']	= 'SERVICE_STREET_TYPE_HILL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HILL"]	['Description']	= 'Hill';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HLSD"]	['Constant']	= 'SERVICE_STREET_TYPE_HILLSIDE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HLSD"]	['Description']	= 'Hillside';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HSE"]		['Constant']	= 'SERVICE_STREET_TYPE_HOUSE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["HSE"]		['Description']	= 'House';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["INTG"]	['Constant']	= 'SERVICE_STREET_TYPE_INTERCHANGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["INTG"]	['Description']	= 'Interchange';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["INTN"]	['Constant']	= 'SERVICE_STREET_TYPE_INTERSECTION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["INTN"]	['Description']	= 'Intersection';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["IS"]		['Constant']	= 'SERVICE_STREET_TYPE_ISLAND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["IS"]		['Description']	= 'Island';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["JNC"]		['Constant']	= 'SERVICE_STREET_TYPE_JUNCTION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["JNC"]		['Description']	= 'Junction';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["JNCT"]	['Constant']	= 'SERVICE_STREET_TYPE_JUNCTION_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["JNCT"]	['Description']	= 'Junction';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["KEY"]		['Constant']	= 'SERVICE_STREET_TYPE_KEY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["KEY"]		['Description']	= 'Key';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["KNLL"]	['Constant']	= 'SERVICE_STREET_TYPE_KNOLL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["KNLL"]	['Description']	= 'Knoll';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LDG"]		['Constant']	= 'SERVICE_STREET_TYPE_LANDING';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LDG"]		['Description']	= 'Landing';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["L"]		['Constant']	= 'SERVICE_STREET_TYPE_LANE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["L"]		['Description']	= 'Lane';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LANE"]	['Constant']	= 'SERVICE_STREET_TYPE_LANE_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LANE"]	['Description']	= 'Lane';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LN"]		['Constant']	= 'SERVICE_STREET_TYPE_LANE_3';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LN"]		['Description']	= 'Lane';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LNWY"]	['Constant']	= 'SERVICE_STREET_TYPE_LANEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LNWY"]	['Description']	= 'Laneway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LEES"]	['Constant']	= 'SERVICE_STREET_TYPE_LEES';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LEES"]	['Description']	= 'Lees';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LINE"]	['Constant']	= 'SERVICE_STREET_TYPE_LINE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LINE"]	['Description']	= 'Line';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LINK"]	['Constant']	= 'SERVICE_STREET_TYPE_LINK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LINK"]	['Description']	= 'Link';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LT"]		['Constant']	= 'SERVICE_STREET_TYPE_LITTLE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LT"]		['Description']	= 'Little';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LOCN"]	['Constant']	= 'SERVICE_STREET_TYPE_LOCATION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LOCN"]	['Description']	= 'Location';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LKT"]		['Constant']	= 'SERVICE_STREET_TYPE_LOOKOUT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LKT"]		['Description']	= 'Lookout';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LOOP"]	['Constant']	= 'SERVICE_STREET_TYPE_LOOP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LOOP"]	['Description']	= 'Loop';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LWR"]		['Constant']	= 'SERVICE_STREET_TYPE_LOWER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["LWR"]		['Description']	= 'Lower';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MALL"]	['Constant']	= 'SERVICE_STREET_TYPE_MALL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MALL"]	['Description']	= 'Mall';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MKLD"]	['Constant']	= 'SERVICE_STREET_TYPE_MARKETLAND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MKLD"]	['Description']	= 'Marketland';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MKTN"]	['Constant']	= 'SERVICE_STREET_TYPE_MARKETTOWN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MKTN"]	['Description']	= 'Markettown';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MEAD"]	['Constant']	= 'SERVICE_STREET_TYPE_MEAD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MEAD"]	['Description']	= 'Mead';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MNDR"]	['Constant']	= 'SERVICE_STREET_TYPE_MEANDER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MNDR"]	['Description']	= 'Meander';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MEW"]		['Constant']	= 'SERVICE_STREET_TYPE_MEW';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MEW"]		['Description']	= 'Mew';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MEWS"]	['Constant']	= 'SERVICE_STREET_TYPE_MEWS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MEWS"]	['Description']	= 'Mews';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MWY"]		['Constant']	= 'SERVICE_STREET_TYPE_MOTORWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MWY"]		['Description']	= 'Motorway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MT"]		['Constant']	= 'SERVICE_STREET_TYPE_MOUNT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MT"]		['Description']	= 'Mount';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MTN"]		['Constant']	= 'SERVICE_STREET_TYPE_MOUNTAIN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["MTN"]		['Description']	= 'Mountain';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["NOOK"]	['Constant']	= 'SERVICE_STREET_TYPE_NOOK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["NOOK"]	['Description']	= 'Nook';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["NR"]		['Constant']	= 'SERVICE_STREET_TYPE_NOT_REQUIRED';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["NR"]		['Description']	= 'Not Required';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["OTLK"]	['Constant']	= 'SERVICE_STREET_TYPE_OUTLOOK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["OTLK"]	['Description']	= 'Outlook';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["OVAL"]	['Constant']	= 'SERVICE_STREET_TYPE_OVAL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["OVAL"]	['Description']	= 'Oval';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PDE"]		['Constant']	= 'SERVICE_STREET_TYPE_PARADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PDE"]		['Description']	= 'Parade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PDSE"]	['Constant']	= 'SERVICE_STREET_TYPE_PARADISE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PDSE"]	['Description']	= 'Paradise';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PARK"]	['Constant']	= 'SERVICE_STREET_TYPE_PARK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PARK"]	['Description']	= 'Park';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PK"]		['Constant']	= 'SERVICE_STREET_TYPE_PARK_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PK"]		['Description']	= 'Park';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PKLD"]	['Constant']	= 'SERVICE_STREET_TYPE_PARKLANDS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PKLD"]	['Description']	= 'Parklands';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PKWY"]	['Constant']	= 'SERVICE_STREET_TYPE_PARKWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PKWY"]	['Description']	= 'Parkway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PART"]	['Constant']	= 'SERVICE_STREET_TYPE_PART';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PART"]	['Description']	= 'Part';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PASS"]	['Constant']	= 'SERVICE_STREET_TYPE_PASS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PASS"]	['Description']	= 'Pass';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PATH"]	['Constant']	= 'SERVICE_STREET_TYPE_PATH';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PATH"]	['Description']	= 'Path';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PWAY"]	['Constant']	= 'SERVICE_STREET_TYPE_PATHWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PWAY"]	['Description']	= 'Pathway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PWY"]		['Constant']	= 'SERVICE_STREET_TYPE_PATHWAY_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PWY"]		['Description']	= 'Pathway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PEN"]		['Constant']	= 'SERVICE_STREET_TYPE_PENINSULA';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PEN"]		['Description']	= 'Peninsula';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PIAZ"]	['Constant']	= 'SERVICE_STREET_TYPE_PIAZZA';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PIAZ"]	['Description']	= 'Piazza';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PR"]		['Constant']	= 'SERVICE_STREET_TYPE_PIER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PR"]		['Description']	= 'Pier';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PL"]		['Constant']	= 'SERVICE_STREET_TYPE_PLACE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PL"]		['Description']	= 'Place';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PLAT"]	['Constant']	= 'SERVICE_STREET_TYPE_PLATEAU';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PLAT"]	['Description']	= 'Plateau';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PLZA"]	['Constant']	= 'SERVICE_STREET_TYPE_PLAZA';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PLZA"]	['Description']	= 'Plaza';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PKT"]		['Constant']	= 'SERVICE_STREET_TYPE_POCKET';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PKT"]		['Description']	= 'Pocket';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PNT"]		['Constant']	= 'SERVICE_STREET_TYPE_POINT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PNT"]		['Description']	= 'Point';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PORT"]	['Constant']	= 'SERVICE_STREET_TYPE_PORT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PORT"]	['Description']	= 'Port';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PRT"]		['Constant']	= 'SERVICE_STREET_TYPE_PORT_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PRT"]		['Description']	= 'Port';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PROM"]	['Constant']	= 'SERVICE_STREET_TYPE_PROMENADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PROM"]	['Description']	= 'Promenade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PUR"]		['Constant']	= 'SERVICE_STREET_TYPE_PURSUIT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["PUR"]		['Description']	= 'Pursuit';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QUAD"]	['Constant']	= 'SERVICE_STREET_TYPE_QUAD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QUAD"]	['Description']	= 'Quad';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QDGL"]	['Constant']	= 'SERVICE_STREET_TYPE_QUADRANGLE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QDGL"]	['Description']	= 'Quadrangle';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QDRT"]	['Constant']	= 'SERVICE_STREET_TYPE_QUADRANT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QDRT"]	['Description']	= 'Quadrant';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QY"]		['Constant']	= 'SERVICE_STREET_TYPE_QUAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QY"]		['Description']	= 'Quay';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QYS"]		['Constant']	= 'SERVICE_STREET_TYPE_QUAYS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["QYS"]		['Description']	= 'Quays';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RCSE"]	['Constant']	= 'SERVICE_STREET_TYPE_RACECOURSE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RCSE"]	['Description']	= 'Racecourse';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RMBL"]	['Constant']	= 'SERVICE_STREET_TYPE_RAMBLE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RMBL"]	['Description']	= 'Ramble';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RAMP"]	['Constant']	= 'SERVICE_STREET_TYPE_RAMP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RAMP"]	['Description']	= 'Ramp';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RNGE"]	['Constant']	= 'SERVICE_STREET_TYPE_RANGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RNGE"]	['Description']	= 'Range';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RCH"]		['Constant']	= 'SERVICE_STREET_TYPE_REACH';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RCH"]		['Description']	= 'Reach';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RES"]		['Constant']	= 'SERVICE_STREET_TYPE_RESERVE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RES"]		['Description']	= 'Reserve';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["REST"]	['Constant']	= 'SERVICE_STREET_TYPE_REST';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["REST"]	['Description']	= 'Rest';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTT"]		['Constant']	= 'SERVICE_STREET_TYPE_RETREAT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTT"]		['Description']	= 'Retreat';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTRN"]	['Constant']	= 'SERVICE_STREET_TYPE_RETURN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTRN"]	['Description']	= 'Return';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RIDE"]	['Constant']	= 'SERVICE_STREET_TYPE_RIDE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RIDE"]	['Description']	= 'Ride';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDGE"]	['Constant']	= 'SERVICE_STREET_TYPE_RIDGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDGE"]	['Description']	= 'Ridge';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RGWY"]	['Constant']	= 'SERVICE_STREET_TYPE_RIDGEWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RGWY"]	['Description']	= 'Ridgeway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ROWY"]	['Constant']	= 'SERVICE_STREET_TYPE_RIGHT_OF_WAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ROWY"]	['Description']	= 'Right Of Way';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RING"]	['Constant']	= 'SERVICE_STREET_TYPE_RING';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RING"]	['Description']	= 'Ring';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RISE"]	['Constant']	= 'SERVICE_STREET_TYPE_RISE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RISE"]	['Description']	= 'Rise';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RVR"]		['Constant']	= 'SERVICE_STREET_TYPE_RIVER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RVR"]		['Description']	= 'River';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RVWY"]	['Constant']	= 'SERVICE_STREET_TYPE_RIVERWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RVWY"]	['Description']	= 'Riverway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RVRA"]	['Constant']	= 'SERVICE_STREET_TYPE_RIVIERA';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RVRA"]	['Description']	= 'Riviera';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RD"]		['Constant']	= 'SERVICE_STREET_TYPE_ROAD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RD"]		['Description']	= 'Road';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDS"]		['Constant']	= 'SERVICE_STREET_TYPE_ROADS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDS"]		['Description']	= 'Roads';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDSD"]	['Constant']	= 'SERVICE_STREET_TYPE_ROADSIDE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDSD"]	['Description']	= 'Roadside';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDWY"]	['Constant']	= 'SERVICE_STREET_TYPE_ROADWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RDWY"]	['Description']	= 'Roadway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RNDE"]	['Constant']	= 'SERVICE_STREET_TYPE_RONDE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RNDE"]	['Description']	= 'Ronde';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RSBL"]	['Constant']	= 'SERVICE_STREET_TYPE_ROSEBOWL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RSBL"]	['Description']	= 'Rosebowl';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTY"]		['Constant']	= 'SERVICE_STREET_TYPE_ROTARY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTY"]		['Description']	= 'Rotary';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RND"]		['Constant']	= 'SERVICE_STREET_TYPE_ROUND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RND"]		['Description']	= 'Round';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTE"]		['Constant']	= 'SERVICE_STREET_TYPE_ROUTE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RTE"]		['Description']	= 'Route';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ROW"]		['Constant']	= 'SERVICE_STREET_TYPE_ROW';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ROW"]		['Description']	= 'Row';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RWE"]		['Constant']	= 'SERVICE_STREET_TYPE_ROWE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RWE"]		['Description']	= 'Rowe';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RUE"]		['Constant']	= 'SERVICE_STREET_TYPE_RUE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RUE"]		['Description']	= 'Rue';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RUN"]		['Constant']	= 'SERVICE_STREET_TYPE_RUN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["RUN"]		['Description']	= 'Run';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SEC"]		['Constant']	= 'SERVICE_STREET_TYPE_SECTION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SEC"]		['Description']	= 'Section';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SWY"]		['Constant']	= 'SERVICE_STREET_TYPE_SERVICE_WAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SWY"]		['Description']	= 'Service Way';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SDNG"]	['Constant']	= 'SERVICE_STREET_TYPE_SIDING';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SDNG"]	['Description']	= 'Siding';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SLPE"]	['Constant']	= 'SERVICE_STREET_TYPE_SLOPE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SLPE"]	['Description']	= 'Slope';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SND"]		['Constant']	= 'SERVICE_STREET_TYPE_SOUND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SND"]		['Description']	= 'Sound';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SPUR"]	['Constant']	= 'SERVICE_STREET_TYPE_SPUR';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SPUR"]	['Description']	= 'Spur';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SQ"]		['Constant']	= 'SERVICE_STREET_TYPE_SQUARE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SQ"]		['Description']	= 'Square';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STRS"]	['Constant']	= 'SERVICE_STREET_TYPE_STAIRS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STRS"]	['Description']	= 'Stairs';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SHWY"]	['Constant']	= 'SERVICE_STREET_TYPE_STATE_HIGHWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SHWY"]	['Description']	= 'State Highway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STN"]		['Constant']	= 'SERVICE_STREET_TYPE_STATION';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STN"]		['Description']	= 'Station';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STPS"]	['Constant']	= 'SERVICE_STREET_TYPE_STEPS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STPS"]	['Description']	= 'Steps';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STOP"]	['Constant']	= 'SERVICE_STREET_TYPE_STOP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STOP"]	['Description']	= 'Stop';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STGT"]	['Constant']	= 'SERVICE_STREET_TYPE_STRAIGHT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STGT"]	['Description']	= 'Straight';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STRA"]	['Constant']	= 'SERVICE_STREET_TYPE_STRAND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STRA"]	['Description']	= 'Strand';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ST"]		['Constant']	= 'SERVICE_STREET_TYPE_STREET';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["ST"]		['Description']	= 'Street';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STP"]		['Constant']	= 'SERVICE_STREET_TYPE_STRIP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STP"]		['Description']	= 'Strip';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STRP"]	['Constant']	= 'SERVICE_STREET_TYPE_STRIP_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["STRP"]	['Description']	= 'Strip';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SBWY"]	['Constant']	= 'SERVICE_STREET_TYPE_SUBWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["SBWY"]	['Description']	= 'Subway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TARN"]	['Constant']	= 'SERVICE_STREET_TYPE_TARN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TARN"]	['Description']	= 'Tarn';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TCE"]		['Constant']	= 'SERVICE_STREET_TYPE_TERRACE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TCE"]		['Description']	= 'Terrace';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["THOR"]	['Constant']	= 'SERVICE_STREET_TYPE_THOROUGHFARE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["THOR"]	['Description']	= 'Thoroughfare';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TLWY"]	['Constant']	= 'SERVICE_STREET_TYPE_TOLLWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TLWY"]	['Description']	= 'Tollway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TOP"]		['Constant']	= 'SERVICE_STREET_TYPE_TOP';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TOP"]		['Description']	= 'Top';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TOR"]		['Constant']	= 'SERVICE_STREET_TYPE_TOR';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TOR"]		['Description']	= 'Tor';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TWR"]		['Constant']	= 'SERVICE_STREET_TYPE_TOWER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TWR"]		['Description']	= 'Tower';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TWRS"]	['Constant']	= 'SERVICE_STREET_TYPE_TOWERS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TWRS"]	['Description']	= 'Towers';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRK"]		['Constant']	= 'SERVICE_STREET_TYPE_TRACK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRK"]		['Description']	= 'Track';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRL"]		['Constant']	= 'SERVICE_STREET_TYPE_TRAIL';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRL"]		['Description']	= 'Trail';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRLR"]	['Constant']	= 'SERVICE_STREET_TYPE_TRAILER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRLR"]	['Description']	= 'Trailer';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRI"]		['Constant']	= 'SERVICE_STREET_TYPE_TRIANGLE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TRI"]		['Description']	= 'Triangle';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TKWY"]	['Constant']	= 'SERVICE_STREET_TYPE_TRUNKWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TKWY"]	['Description']	= 'Trunkway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TURN"]	['Constant']	= 'SERVICE_STREET_TYPE_TURN';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["TURN"]	['Description']	= 'Turn';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["UPAS"]	['Constant']	= 'SERVICE_STREET_TYPE_UNDERPASS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["UPAS"]	['Description']	= 'Underpass';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["UPR"]		['Constant']	= 'SERVICE_STREET_TYPE_UPPER';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["UPR"]		['Description']	= 'Upper';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VALE"]	['Constant']	= 'SERVICE_STREET_TYPE_VALE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VALE"]	['Description']	= 'Vale';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VLY"]		['Constant']	= 'SERVICE_STREET_TYPE_VALLEY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VLY"]		['Description']	= 'Valley';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VDCT"]	['Constant']	= 'SERVICE_STREET_TYPE_VIADUCT';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VDCT"]	['Description']	= 'Viaduct';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VIEW"]	['Constant']	= 'SERVICE_STREET_TYPE_VIEW';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VIEW"]	['Description']	= 'View';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VLGE"]	['Constant']	= 'SERVICE_STREET_TYPE_VILLAGE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VLGE"]	['Description']	= 'Village';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VLLS"]	['Constant']	= 'SERVICE_STREET_TYPE_VILLAS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VLLS"]	['Description']	= 'Villas';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VSTA"]	['Constant']	= 'SERVICE_STREET_TYPE_VISTA';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["VSTA"]	['Description']	= 'Vista';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WADE"]	['Constant']	= 'SERVICE_STREET_TYPE_WADE';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WADE"]	['Description']	= 'Wade';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WALK"]	['Constant']	= 'SERVICE_STREET_TYPE_WALK';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WALK"]	['Description']	= 'Walk';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WK"]		['Constant']	= 'SERVICE_STREET_TYPE_WALK_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WK"]		['Description']	= 'Walk';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WKWY"]	['Constant']	= 'SERVICE_STREET_TYPE_WALKWAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WKWY"]	['Description']	= 'Walkway';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WTRS"]	['Constant']	= 'SERVICE_STREET_TYPE_WATERS';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WTRS"]	['Description']	= 'Waters';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WAY"]		['Constant']	= 'SERVICE_STREET_TYPE_WAY';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WAY"]		['Description']	= 'Way';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WY"]		['Constant']	= 'SERVICE_STREET_TYPE_WAY_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WY"]		['Description']	= 'Way';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WEST"]	['Constant']	= 'SERVICE_STREET_TYPE_WEST';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WEST"]	['Description']	= 'West';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WHF"]		['Constant']	= 'SERVICE_STREET_TYPE_WHARF';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WHF"]		['Description']	= 'Wharf';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WHRF"]	['Constant']	= 'SERVICE_STREET_TYPE_WHARF_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WHRF"]	['Description']	= 'Wharf';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WOOD"]	['Constant']	= 'SERVICE_STREET_TYPE_WOOD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WOOD"]	['Description']	= 'Wood';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WYND"]	['Constant']	= 'SERVICE_STREET_TYPE_WYND';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["WYND"]	['Description']	= 'Wynd';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["YARD"]	['Constant']	= 'SERVICE_STREET_TYPE_YARD';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["YARD"]	['Description']	= 'Yard';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["YRD"]		['Constant']	= 'SERVICE_STREET_TYPE_YARD_2';
$GLOBALS['*arrConstant']	['ServiceStreetType']	["YRD"]		['Description']	= 'Yard';


// Service Street Suffix Type
/*
define("SERVICE_STREET_SUFFIX_TYPE_CENTRAL"			, "CN");
define("SERVICE_STREET_SUFFIX_TYPE_EAST"			, "E");
define("SERVICE_STREET_SUFFIX_TYPE_EXTENSION"		, "EX");
define("SERVICE_STREET_SUFFIX_TYPE_LOWER"			, "L");
define("SERVICE_STREET_SUFFIX_TYPE_NORTH"			, "N");
define("SERVICE_STREET_SUFFIX_TYPE_NORTH_EAST"		, "NE");
define("SERVICE_STREET_SUFFIX_TYPE_NORTH_WEST"		, "NW");
define("SERVICE_STREET_SUFFIX_TYPE_SOUTH"			, "S");
define("SERVICE_STREET_SUFFIX_TYPE_SOUTH_EAST"		, "SE");
define("SERVICE_STREET_SUFFIX_TYPE_SOUTH_WEST"		, "SW");
define("SERVICE_STREET_SUFFIX_TYPE_UPPER"			, "U");
define("SERVICE_STREET_SUFFIX_TYPE_WEST"			, "W");
*/
// Service Street Suffix Type
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["CN"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_CENTRAL';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["CN"]	['Description']	= 'Central';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["E"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_EAST';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["E"]	['Description']	= 'East';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["EX"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_EXTENSION';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["EX"]	['Description']	= 'Extension';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["L"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_LOWER';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["L"]	['Description']	= 'Lower';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["N"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_NORTH';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["N"]	['Description']	= 'North';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["NE"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_NORTH_EAST';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["NE"]	['Description']	= 'North East';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["NW"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_NORTH_WEST';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["NW"]	['Description']	= 'North West';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["S"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_SOUTH';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["S"]	['Description']	= 'South';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["SE"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_SOUTH_EAST';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["SE"]	['Description']	= 'South East';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["SW"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_SOUTH_WEST';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["SW"]	['Description']	= 'South West';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["U"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_UPPER';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["U"]	['Description']	= 'Upper';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["W"]	['Constant']	= 'SERVICE_STREET_SUFFIX_TYPE_WEST';
$GLOBALS['*arrConstant']	['ServiceStreetSuffixType']	["W"]	['Description']	= 'West';


// End User Titles
/*
define("END_USER_TITLE_TYPE_DOCTOR"					, "DR");
define("END_USER_TITLE_TYPE_MASTER"					, "MSTR");
define("END_USER_TITLE_TYPE_MISS"					, "MISS");
define("END_USER_TITLE_TYPE_MISTER"					, "MR");
define("END_USER_TITLE_TYPE_MRS"					, "MRS");
define("END_USER_TITLE_TYPE_MS"						, "MS");
define("END_USER_TITLE_TYPE_PROFESSOR"				, "PROF");
*/
$GLOBALS['*arrConstant']	['EndUserTitleType']	['DR']		['Constant']	= 'END_USER_TITLE_TYPE_DOCTOR';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['DR']		['Description']	= 'Dr';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MSTR']	['Constant']	= 'END_USER_TITLE_TYPE_MASTER';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MSTR']	['Description']	= 'Master';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MISS']	['Constant']	= 'END_USER_TITLE_TYPE_MISS';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MISS']	['Description']	= 'Miss';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MR']		['Constant']	= 'END_USER_TITLE_TYPE_MISTER';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MR']		['Description']	= 'Mr';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MRS']		['Constant']	= 'END_USER_TITLE_TYPE_MRS';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MRS']		['Description']	= 'Mrs';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MS']		['Constant']	= 'END_USER_TITLE_TYPE_MS';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['MS']		['Description']	= 'Ms';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['PROF']	['Constant']	= 'END_USER_TITLE_TYPE_PROFESSOR';
$GLOBALS['*arrConstant']	['EndUserTitleType']	['PROF']	['Description']	= 'Prof';

// Service State Type
$GLOBALS['*arrConstant']	['ServiceStateType']	['ACT']	['Constant']	= 'SERVICE_STATE_TYPE_ACT';
$GLOBALS['*arrConstant']	['ServiceStateType']	['ACT']	['Description']	= 'Australian Capital Territory';
$GLOBALS['*arrConstant']	['ServiceStateType']	['NSW']	['Constant']	= 'SERVICE_STATE_TYPE_NSW';
$GLOBALS['*arrConstant']	['ServiceStateType']	['NSW']	['Description']	= 'New South Wales';
$GLOBALS['*arrConstant']	['ServiceStateType']	['NT']	['Constant']	= 'SERVICE_STATE_TYPE_NT';
$GLOBALS['*arrConstant']	['ServiceStateType']	['NT']	['Description']	= 'Northern Territory';
$GLOBALS['*arrConstant']	['ServiceStateType']	['QLD']	['Constant']	= 'SERVICE_STATE_TYPE_QLD';
$GLOBALS['*arrConstant']	['ServiceStateType']	['QLD']	['Description']	= 'Queensland';
$GLOBALS['*arrConstant']	['ServiceStateType']	['SA']	['Constant']	= 'SERVICE_STATE_TYPE_SA';
$GLOBALS['*arrConstant']	['ServiceStateType']	['SA']	['Description']	= 'South Australia';
$GLOBALS['*arrConstant']	['ServiceStateType']	['TAS']	['Constant']	= 'SERVICE_STATE_TYPE_TAS';
$GLOBALS['*arrConstant']	['ServiceStateType']	['TAS']	['Description']	= 'Tasmania';
$GLOBALS['*arrConstant']	['ServiceStateType']	['VIC']	['Constant']	= 'SERVICE_STATE_TYPE_VIC';
$GLOBALS['*arrConstant']	['ServiceStateType']	['VIC']	['Description']	= 'Victoria';
$GLOBALS['*arrConstant']	['ServiceStateType']	['WA']	['Constant']	= 'SERVICE_STATE_TYPE_WA';
$GLOBALS['*arrConstant']	['ServiceStateType']	['WA']	['Description']	= 'Western Australia';




// Billing Methods
/*define("BILLING_METHOD_POST"			, 0);
define("BILLING_METHOD_EMAIL"			, 1);
define("BILLING_METHOD_DO_NOT_SEND"		, 2);*/
$GLOBALS['*arrConstant']	['BillingMethod']	[0]	['Constant']	= 'BILLING_METHOD_POST';
$GLOBALS['*arrConstant']	['BillingMethod']	[0]	['Description']	= 'Post';
$GLOBALS['*arrConstant']	['BillingMethod']	[1]	['Constant']	= 'BILLING_METHOD_EMAIL';
$GLOBALS['*arrConstant']	['BillingMethod']	[1]	['Description']	= 'Email';
$GLOBALS['*arrConstant']	['BillingMethod']	[2]	['Constant']	= 'BILLING_METHOD_DO_NOT_SEND';
$GLOBALS['*arrConstant']	['BillingMethod']	[2]	['Description']	= 'Do Not Send';
$GLOBALS['*arrConstant']	['BillingMethod']	[3]	['Constant']	= 'BILLING_METHOD_EMAIL_SENT';
$GLOBALS['*arrConstant']	['BillingMethod']	[3]	['Description']	= 'Email Sent';

// Billing Types
/*define("BILLING_TYPE_DIRECT_DEBIT"		, 1);
define("BILLING_TYPE_CREDIT_CARD"		, 2);
define("BILLING_TYPE_ACCOUNT"			, 3);*/
$GLOBALS['*arrConstant']	['BillingType']	[1]	['Constant']	= 'BILLING_TYPE_DIRECT_DEBIT';
$GLOBALS['*arrConstant']	['BillingType']	[1]	['Description']	= 'Direct Debit';
$GLOBALS['*arrConstant']	['BillingType']	[2]	['Constant']	= 'BILLING_TYPE_CREDIT_CARD';
$GLOBALS['*arrConstant']	['BillingType']	[2]	['Description']	= 'Credit Card';
$GLOBALS['*arrConstant']	['BillingType']	[3]	['Constant']	= 'BILLING_TYPE_ACCOUNT';
$GLOBALS['*arrConstant']	['BillingType']	[3]	['Description']	= 'Account';

// Payment Terms
define("PAYMENT_TERMS_DEFAULT"			, 14);

// Billing Frequency Constants
define("BILLING_FREQ_DAY"				, 1);
define("BILLING_FREQ_MONTH"				, 2);
define("BILLING_FREQ_HALF_MONTH"		, 3);

// Billing Minimum Total
// Now defined in database
//define("BILLING_MINIMUM_TOTAL"			, 5.0);

// Set the defaults for Billing (Once every Month on the First)
//define("BILLING_DEFAULT_DATE"			, 1); Now defined in database
define("BILLING_DEFAULT_FREQ"			, 1);
define("BILLING_DEFAULT_FREQ_TYPE"		, BILLING_FREQ_MONTH);

// Record Type Display Types
/*define("RECORD_DISPLAY_CALL"		, 1);
define("RECORD_DISPLAY_S_AND_E"		, 2);
define("RECORD_DISPLAY_DATA"		, 3);
define("RECORD_DISPLAY_SMS"			, 4);*/
$GLOBALS['*arrConstant']	['DisplayType']	[1]	['Constant']	= 'RECORD_DISPLAY_CALL';
$GLOBALS['*arrConstant']	['DisplayType']	[1]	['Description']	= 'Call';
$GLOBALS['*arrConstant']	['DisplayType']	[2]	['Constant']	= 'RECORD_DISPLAY_S_AND_E';
$GLOBALS['*arrConstant']	['DisplayType']	[2]	['Description']	= 'Service and Equipment';
$GLOBALS['*arrConstant']	['DisplayType']	[3]	['Constant']	= 'RECORD_DISPLAY_DATA';
$GLOBALS['*arrConstant']	['DisplayType']	[3]	['Description']	= 'Data Transfer';
$GLOBALS['*arrConstant']	['DisplayType']	[4]	['Constant']	= 'RECORD_DISPLAY_SMS';
$GLOBALS['*arrConstant']	['DisplayType']	[4]	['Description']	= 'SMS';

// Debit and Credit
define("NATURE_CR"					, 'CR');
define("NATURE_DR"					, 'DR');

// Pablo's Tips
define("PABLO_TIP_POLITE"			, 1);
define("PABLO_TIP_SLEAZY"			, 2);
define("PABLO_TIP_HATES_YOU"		, 3);
define("PABLO_TIP_DRUNK"			, 4);

// Payments
// payment status
/*define("PAYMENT_IMPORTED"				, 100);
define("PAYMENT_WAITING"				, 101);
define("PAYMENT_PAYING"					, 103);
define("PAYMENT_FINISHED"				, 150);
define("PAYMENT_BAD_IMPORT"				, 200);
define("PAYMENT_BAD_PROCESS"			, 201);
define("PAYMENT_BAD_NORMALISE"			, 202);
define("PAYMENT_CANT_NORMALISE_HEADER"	, 203);
define("PAYMENT_CANT_NORMALISE_FOOTER"	, 204);
define("PAYMENT_CANT_NORMALISE_INVALID"	, 205);*/
$GLOBALS['*arrConstant']	['PaymentStatus']	[100]	['Constant']	= 'PAYMENT_IMPORTED';
$GLOBALS['*arrConstant']	['PaymentStatus']	[100]	['Description']	= 'Imported';
$GLOBALS['*arrConstant']	['PaymentStatus']	[101]	['Constant']	= 'PAYMENT_WAITING';
$GLOBALS['*arrConstant']	['PaymentStatus']	[101]	['Description']	= 'Waiting';
$GLOBALS['*arrConstant']	['PaymentStatus']	[103]	['Constant']	= 'PAYMENT_PAYING';
$GLOBALS['*arrConstant']	['PaymentStatus']	[103]	['Description']	= 'Paying';
$GLOBALS['*arrConstant']	['PaymentStatus']	[150]	['Constant']	= 'PAYMENT_FINISHED';
$GLOBALS['*arrConstant']	['PaymentStatus']	[150]	['Description']	= 'Finished';
$GLOBALS['*arrConstant']	['PaymentStatus']	[200]	['Constant']	= 'PAYMENT_BAD_IMPORT';
$GLOBALS['*arrConstant']	['PaymentStatus']	[200]	['Description']	= 'Import Failed';
$GLOBALS['*arrConstant']	['PaymentStatus']	[201]	['Constant']	= 'PAYMENT_BAD_PROCESS';
$GLOBALS['*arrConstant']	['PaymentStatus']	[201]	['Description']	= 'Process Failed';
$GLOBALS['*arrConstant']	['PaymentStatus']	[202]	['Constant']	= 'PAYMENT_BAD_NORMALISE';
$GLOBALS['*arrConstant']	['PaymentStatus']	[202]	['Description']	= 'Normalisation Failed';
$GLOBALS['*arrConstant']	['PaymentStatus']	[203]	['Constant']	= 'PAYMENT_CANT_NORMALISE_HEADER';
$GLOBALS['*arrConstant']	['PaymentStatus']	[203]	['Description']	= 'Cannot Normalise Header Row';
$GLOBALS['*arrConstant']	['PaymentStatus']	[204]	['Constant']	= 'PAYMENT_CANT_NORMALISE_FOOTER';
$GLOBALS['*arrConstant']	['PaymentStatus']	[204]	['Description']	= 'Cannot Normalise Footer Row';
$GLOBALS['*arrConstant']	['PaymentStatus']	[205]	['Constant']	= 'PAYMENT_CANT_NORMALISE_INVALID';
$GLOBALS['*arrConstant']	['PaymentStatus']	[205]	['Description']	= 'Cannot Normalise Unrecognised Row';
$GLOBALS['*arrConstant']	['PaymentStatus']	[206]	['Constant']	= 'PAYMENT_BAD_OWNER';
$GLOBALS['*arrConstant']	['PaymentStatus']	[206]	['Description']	= 'Cannot Match to an Account';
$GLOBALS['*arrConstant']	['PaymentStatus']	[207]	['Constant']	= 'PAYMENT_INVALID_CHECK_DIGIT';
$GLOBALS['*arrConstant']	['PaymentStatus']	[207]	['Description']	= 'Check Digit is Invalid';
$GLOBALS['*arrConstant']	['PaymentStatus']	[250]	['Constant']	= 'PAYMENT_REVERSED';
$GLOBALS['*arrConstant']	['PaymentStatus']	[250]	['Description']	= 'Reversed';


// payment types
/*define("PAYMENT_TYPE_BILLEXPRESS"	, 1);
define("PAYMENT_TYPE_BPAY"			, 2);
define("PAYMENT_TYPE_CHEQUE"		, 3);
define("PAYMENT_TYPE_SECUREPAY"		, 4);
define("PAYMENT_TYPE_CREDIT_CARD"	, 5);*/

$GLOBALS['*arrConstant']	['PaymentType']	[1]	['Constant']	= 'PAYMENT_TYPE_BILLEXPRESS';
$GLOBALS['*arrConstant']	['PaymentType']	[1]	['Description']	= 'BillExpress';
$GLOBALS['*arrConstant']	['PaymentType']	[2]	['Constant']	= 'PAYMENT_TYPE_BPAY';
$GLOBALS['*arrConstant']	['PaymentType']	[2]	['Description']	= 'BPay';
$GLOBALS['*arrConstant']	['PaymentType']	[3]	['Constant']	= 'PAYMENT_TYPE_CHEQUE';
$GLOBALS['*arrConstant']	['PaymentType']	[3]	['Description']	= 'Cheque';
$GLOBALS['*arrConstant']	['PaymentType']	[4]	['Constant']	= 'PAYMENT_TYPE_SECUREPAY';
$GLOBALS['*arrConstant']	['PaymentType']	[4]	['Description']	= 'SecurePay';
$GLOBALS['*arrConstant']	['PaymentType']	[5]	['Constant']	= 'PAYMENT_TYPE_CREDIT_CARD';
$GLOBALS['*arrConstant']	['PaymentType']	[5]	['Description']	= 'Credit Card';
$GLOBALS['*arrConstant']	['PaymentType']	[6]	['Constant']	= 'PAYMENT_TYPE_EFT';
$GLOBALS['*arrConstant']	['PaymentType']	[6]	['Description']	= 'EFT';
$GLOBALS['*arrConstant']	['PaymentType']	[7]	['Constant']	= 'PAYMENT_TYPE_CASH';
$GLOBALS['*arrConstant']	['PaymentType']	[7]	['Description']	= 'Cash';
$GLOBALS['*arrConstant']	['PaymentType']	[8]	['Constant']	= 'PAYMENT_TYPE_AUSTRAL';
$GLOBALS['*arrConstant']	['PaymentType']	[8]	['Description']	= 'Austral';
$GLOBALS['*arrConstant']	['PaymentType']	[9]	['Constant']	= 'PAYMENT_TYPE_CONTRA';
$GLOBALS['*arrConstant']	['PaymentType']	[9]	['Description']	= 'Contra';


// Charge Types/Codes
define("CHARGE_CODE_CALL_CREDIT"	, "Call Credit");

// Bug Type
/*define("BUG_UNREAD"					, 100);
define("BUG_UNASSIGNED"				, 101);
define("BUG_UNRESOLVED"				, 102);
define("BUG_RESOLVED"				, 103);*/
$GLOBALS['*arrConstant']	['BugStatus']	[100]	['Constant']	= 'BUG_UNREAD';
$GLOBALS['*arrConstant']	['BugStatus']	[100]	['Description']	= 'New';
$GLOBALS['*arrConstant']	['BugStatus']	[101]	['Constant']	= 'BUG_UNASSIGNED';
$GLOBALS['*arrConstant']	['BugStatus']	[101]	['Description']	= 'Unassigned';
$GLOBALS['*arrConstant']	['BugStatus']	[102]	['Constant']	= 'BUG_UNRESOLVED';
$GLOBALS['*arrConstant']	['BugStatus']	[102]	['Description']	= 'Unresolved';
$GLOBALS['*arrConstant']	['BugStatus']	[103]	['Constant']	= 'BUG_RESOLVED';
$GLOBALS['*arrConstant']	['BugStatus']	[103]	['Description']	= 'Resolved';

// Note parsing
/*
 * These are now stored in the ConfigConstant table of the database
 *
define("SYSTEM_NOTE_TYPE",	7);
define("GENERAL_NOTE_TYPE",	1);
*/

//Report Result Types
define("REPORT_RESULT_TYPE_CSV"		, "CSV");
define("REPORT_RESULT_TYPE_HTML"	, "HTML");

// RemoteCopy Auto-Disconnect on Reconnect
define("RCOPY_AUTO_DISCONNECT"		, TRUE);

// RemoteCopy Backup Methods
$GLOBALS['*arrConstant']	['CopyMethod']	[1]	['Constant']	= 'RCOPY_BACKUP';
$GLOBALS['*arrConstant']	['CopyMethod']	[1]	['Description']	= 'Backup Files';
$GLOBALS['*arrConstant']	['CopyMethod']	[2]	['Constant']	= 'RCOPY_REMOVE';
$GLOBALS['*arrConstant']	['CopyMethod']	[2]	['Description']	= 'Remove Files and Directories First';
$GLOBALS['*arrConstant']	['CopyMethod']	[3]	['Constant']	= 'RCOPY_OVERWRITE';
$GLOBALS['*arrConstant']	['CopyMethod']	[3]	['Description']	= 'Overwrite Files';



// resolve dispute status codes
/*define("DISPUTE_RESOLVE_FULL_PAYMENT"		, 1);
define("DISPUTE_RESOLVE_PARTIAL_PAYMENT"	, 2);
define("DISPUTE_RESOLVE_NO_PAYMENT"			, 3);*/
$GLOBALS['*arrConstant']	['DistputeResolve']	[1]	['Constant']	= 'DISPUTE_RESOLVE_FULL_PAYMENT';
$GLOBALS['*arrConstant']	['DistputeResolve']	[1]	['Description']	= 'Full Payment';
$GLOBALS['*arrConstant']	['DistputeResolve']	[2]	['Constant']	= 'DISPUTE_RESOLVE_PARTIAL_PAYMENT';
$GLOBALS['*arrConstant']	['DistputeResolve']	[2]	['Description']	= 'Partial Payment';
$GLOBALS['*arrConstant']	['DistputeResolve']	[3]	['Constant']	= 'DISPUTE_RESOLVE_NO_PAYMENT';
$GLOBALS['*arrConstant']	['DistputeResolve']	[3]	['Description']	= 'No Payment';

// Remote Copy Protocols
$GLOBALS['*arrConstant']	['CopyProtocol']	[600]	['Constant']	= 'PROTOCOL_SSH2';
$GLOBALS['*arrConstant']	['CopyProtocol']	[600]	['Description']	= 'Secure Shell 2 (SSH2)';
$GLOBALS['*arrConstant']	['CopyProtocol']	[601]	['Constant']	= 'PROTOCOL_FTP';
$GLOBALS['*arrConstant']	['CopyProtocol']	[601]	['Description']	= 'File Transfer Protocal (FTP)';

// CLI Window States
$GLOBALS['*arrConstant']	['WindowState']		[100]	['Constant']	= 'WINDOW_REGULAR';
$GLOBALS['*arrConstant']	['WindowState']		[100]	['Description']	= 'Regular';
$GLOBALS['*arrConstant']	['WindowState']		[101]	['Constant']	= 'WINDOW_MAXIMISED';
$GLOBALS['*arrConstant']	['WindowState']		[101]	['Description']	= 'Maximised';
$GLOBALS['*arrConstant']	['WindowState']		[102]	['Constant']	= 'WINDOW_MINIMISED';
$GLOBALS['*arrConstant']	['WindowState']		[102]	['Description']	= 'Minimised';
$GLOBALS['*arrConstant']	['WindowState']		[103]	['Constant']	= 'WINDOW_TASKBAR_ITEM';
$GLOBALS['*arrConstant']	['WindowState']		[103]	['Description']	= 'Taskbar Item';
$GLOBALS['*arrConstant']	['WindowState']		[104]	['Constant']	= 'WINDOW_PROGRESS';
$GLOBALS['*arrConstant']	['WindowState']		[104]	['Description']	= 'Progress Bar';

// Data Report Render Mode
$GLOBALS['*arrConstant']	['ReportRender']	[0]	['Constant']	= 'REPORT_RENDER_INSTANT';
$GLOBALS['*arrConstant']	['ReportRender']	[0]	['Description']	= 'Instant Render-to-Screen';
$GLOBALS['*arrConstant']	['ReportRender']	[1]	['Constant']	= 'REPORT_RENDER_EMAIL';
$GLOBALS['*arrConstant']	['ReportRender']	[1]	['Description']	= 'Email Report after Rendering';

// Data Report Render Targets
$GLOBALS['*arrConstant']	['ReportTarget']	[0]	['Constant']	= 'REPORT_TARGET_XLS';
$GLOBALS['*arrConstant']	['ReportTarget']	[0]	['Description']	= 'Renders to an Excel 5.0 Spreadsheet';
$GLOBALS['*arrConstant']	['ReportTarget']	[1]	['Constant']	= 'REPORT_TARGET_CSV';
$GLOBALS['*arrConstant']	['ReportTarget']	[1]	['Description']	= 'Renders to a CSV document';

// Data Report Status
$GLOBALS['*arrConstant']	['ReportStatus']	[200]	['Constant']	= 'REPORT_WAITING';
$GLOBALS['*arrConstant']	['ReportStatus']	[200]	['Description']	= 'Waiting to be Processed';
$GLOBALS['*arrConstant']	['ReportStatus']	[201]	['Constant']	= 'REPORT_GENERATE_FAILED';
$GLOBALS['*arrConstant']	['ReportStatus']	[201]	['Description']	= 'Report Failed while attempting to Generate';
$GLOBALS['*arrConstant']	['ReportStatus']	[202]	['Constant']	= 'REPORT_EMAIL_FAILED';
$GLOBALS['*arrConstant']	['ReportStatus']	[202]	['Description']	= 'Attempt to Email the Report failed';
$GLOBALS['*arrConstant']	['ReportStatus']	[203]	['Constant']	= 'REPORT_BAD_RENDER_TARGET';
$GLOBALS['*arrConstant']	['ReportStatus']	[203]	['Description']	= 'Invalid Render Target Specified';
$GLOBALS['*arrConstant']	['ReportStatus']	[204]	['Constant']	= 'REPORT_GENERATED';
$GLOBALS['*arrConstant']	['ReportStatus']	[204]	['Description']	= 'Report Generated';

// Data Report XLS Types
$GLOBALS['*arrConstant']	['XLSType']	[500]	['Constant']	= 'EXCEL_TYPE_CURRENCY';
$GLOBALS['*arrConstant']	['XLSType']	[500]	['Description']	= 'Currency';
$GLOBALS['*arrConstant']	['XLSType']	[501]	['Constant']	= 'EXCEL_TYPE_INTEGER';
$GLOBALS['*arrConstant']	['XLSType']	[501]	['Description']	= 'Integer';
$GLOBALS['*arrConstant']	['XLSType']	[502]	['Constant']	= 'EXCEL_TYPE_PERCENTAGE';
$GLOBALS['*arrConstant']	['XLSType']	[502]	['Description']	= 'Percentage';
$GLOBALS['*arrConstant']	['XLSType']	[503]	['Constant']	= 'EXCEL_TYPE_FNN';
$GLOBALS['*arrConstant']	['XLSType']	[503]	['Description']	= 'FNN';

//Data Report XLS Total Types
$GLOBALS['*arrConstant']	['XLSTotal']	[600]	['Constant']	= 'EXCEL_TOTAL_SUM';
$GLOBALS['*arrConstant']	['XLSTotal']	[600]	['Description']	= 'Sum';
$GLOBALS['*arrConstant']	['XLSTotal']	[601]	['Constant']	= 'EXCEL_TOTAL_AVG';
$GLOBALS['*arrConstant']	['XLSTotal']	[601]	['Description']	= 'Average';

// Output Report Modes
$GLOBALS['*arrConstant']	['ReportMode']	[800]	['Constant']	= 'REPORT_MODE_VERBOSE';
$GLOBALS['*arrConstant']	['ReportMode']	[800]	['Description']	= 'Everything';
$GLOBALS['*arrConstant']	['ReportMode']	[801]	['Constant']	= 'REPORT_MODE_ERRORS';
$GLOBALS['*arrConstant']	['ReportMode']	[801]	['Description']	= 'Errors and Totals Only';
$GLOBALS['*arrConstant']	['ReportMode']	[802]	['Constant']	= 'REPORT_MODE_NONE';
$GLOBALS['*arrConstant']	['ReportMode']	[802]	['Description']	= 'Nothing';



// CLI Console Docked
define("CONSOLE_DOCKED"		, DONKEY);


// RecordType DisplayType Suffix
// The values of these constants have to reflect the DisplayType constants
// These should be used instead of the $GLOBALS['RecordDisplayRateSuffix'] array
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[1]	['Constant']	= 'RECORD_DISPLAY_SUFFIX_CALL';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[1]	['Description']	= 'Second(s)';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[2]	['Constant']	= 'RECORD_DISPLAY_SUFFIX_S_AND_E';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[2]	['Description']	= 'Unit(s)';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[3]	['Constant']	= 'RECORD_DISPLAY_SUFFIX_DATA';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[3]	['Description']	= 'KB(s)';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[4]	['Constant']	= 'RECORD_DISPLAY_SUFFIX_SMS';
$GLOBALS['*arrConstant']	['DisplayTypeSuffix']	[4]	['Description']	= 'Unit(s)';

// RateStatus
// These are used for RatePlans, RateGroups and Rates to define their "Archived" property
$GLOBALS['*arrConstant']	['RateStatus']	[0]	['Constant']	= 'RATE_STATUS_ACTIVE';
$GLOBALS['*arrConstant']	['RateStatus']	[0]	['Description']	= 'Active';
$GLOBALS['*arrConstant']	['RateStatus']	[1]	['Constant']	= 'RATE_STATUS_ARCHIVED';
$GLOBALS['*arrConstant']	['RateStatus']	[1]	['Description']	= 'Archived';
$GLOBALS['*arrConstant']	['RateStatus']	[2]	['Constant']	= 'RATE_STATUS_DRAFT';
$GLOBALS['*arrConstant']	['RateStatus']	[2]	['Description']	= 'Draft';


// Account Letter Types
$GLOBALS['*arrConstant']	['LetterType']	[1]	['Constant']	= 'LETTER_TYPE_OVERDUE';
$GLOBALS['*arrConstant']	['LetterType']	[1]	['Description']	= 'Overdue Notice';
$GLOBALS['*arrConstant']	['LetterType']	[2]	['Constant']	= 'LETTER_TYPE_SUSPENSION';
$GLOBALS['*arrConstant']	['LetterType']	[2]	['Description']	= 'Suspension Notice';
$GLOBALS['*arrConstant']	['LetterType']	[3]	['Constant']	= 'LETTER_TYPE_FINAL_DEMAND';
$GLOBALS['*arrConstant']	['LetterType']	[3]	['Description']	= 'Final Demand Notice';
$GLOBALS['*arrConstant']	['LetterType']	[4]	['Constant']	= 'LETTER_TYPE_INVOICE';
$GLOBALS['*arrConstant']	['LetterType']	[4]	['Description']	= 'Invoice';

// DataTypes for Constants
$GLOBALS['*arrConstant']	['DataType']	[1]	['Constant']	= 'DATA_TYPE_STRING';
$GLOBALS['*arrConstant']	['DataType']	[1]	['Description']	= 'String';
$GLOBALS['*arrConstant']	['DataType']	[2]	['Constant']	= 'DATA_TYPE_INTEGER';
$GLOBALS['*arrConstant']	['DataType']	[2]	['Description']	= 'Integer';
$GLOBALS['*arrConstant']	['DataType']	[3]	['Constant']	= 'DATA_TYPE_FLOAT';
$GLOBALS['*arrConstant']	['DataType']	[3]	['Description']	= 'Float';
$GLOBALS['*arrConstant']	['DataType']	[4]	['Constant']	= 'DATA_TYPE_BOOLEAN';
$GLOBALS['*arrConstant']	['DataType']	[4]	['Description']	= 'Boolean';


// Special paths
define("PATH_PAYMENT_UPLOADS"			, FILES_BASE_PATH."payments/");
define("PATH_INVOICE_PDFS"				, FILES_BASE_PATH."invoices/");
// The function GetBasePath() does not yet exist
//define("PATH_INVOICE_PDFS"			, GetBasePath() . "vixen_invoices/");
//define("PATH_PAYMENT_UPLOADS"			, GetBasePath() . "vixen_payments/");

// Define all Constants
foreach ($GLOBALS['*arrConstant'] AS $arrConstants)
{
	foreach ($arrConstants AS $intConstant=>$arrConstant)
	{
		define($arrConstant['Constant'], $intConstant);
	}
}


//TODO!bash! make this (and whatever uses it) work with GetConstantDescription
$arrRecordDisplayRateName[RECORD_DISPLAY_CALL]			= "Voice Calls";
$arrRecordDisplayRateName[RECORD_DISPLAY_S_AND_E]		= "Service & Equipment";
$arrRecordDisplayRateName[RECORD_DISPLAY_DATA]			= "GPRS and ADSL Data";
$arrRecordDisplayRateName[RECORD_DISPLAY_SMS]			= "SMS (Short Message Service)";
$GLOBALS['RecordDisplayRateName'] = $arrRecordDisplayRateName;

$arrRecordDisplayRateSuffix[RECORD_DISPLAY_CALL]		= "Second(s)";
$arrRecordDisplayRateSuffix[RECORD_DISPLAY_S_AND_E]		= "Unit(s)";
$arrRecordDisplayRateSuffix[RECORD_DISPLAY_DATA]		= "KB(s)";
$arrRecordDisplayRateSuffix[RECORD_DISPLAY_SMS]			= "Unit(s)";
$GLOBALS['RecordDisplayRateSuffix'] = $arrRecordDisplayRateSuffix;
?>

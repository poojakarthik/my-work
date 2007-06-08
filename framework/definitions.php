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



// Carriers
/*define("CARRIER_UNITEL"			, 1);
define("CARRIER_OPTUS"			, 2);
define("CARRIER_AAPT"			, 3);
define("CARRIER_ISEEK"			, 4);*/
$GLOBALS['*arrConstant']	['Carrier']	[1]	['Constant']	= 'CARRIER_UNITEL';
$GLOBALS['*arrConstant']	['Carrier']	[1]	['Description']	= 'Unitel';
$GLOBALS['*arrConstant']	['Carrier']	[2]	['Constant']	= 'CARRIER_OPTUS';
$GLOBALS['*arrConstant']	['Carrier']	[2]	['Description']	= 'Optus';
$GLOBALS['*arrConstant']	['Carrier']	[3]	['Constant']	= 'CARRIER_AAPT';
$GLOBALS['*arrConstant']	['Carrier']	[3]	['Description']	= 'AAPT';
$GLOBALS['*arrConstant']	['Carrier']	[4]	['Constant']	= 'CARRIER_ISEEK';
$GLOBALS['*arrConstant']	['Carrier']	[4]	['Description']	= 'iSeek';
$GLOBALS['*arrConstant']	['Carrier']	[10]	['Constant']	= 'CARRIER_PAYMENT';
$GLOBALS['*arrConstant']	['Carrier']	[10]	['Description']	= 'Payment';

// ERROR TABLE
define("FATAL_ERROR_LEVEL"			, 10000);

define("NON_FATAL_TEST_EXCEPTION"	, 1337);
define("FATAL_TEST_EXCEPTION"		, 80085);

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
//$GLOBALS['*arrConstant']['CDR'][170]['Description'] = 'Temporarily Credited';
$GLOBALS['*arrConstant']['CDR'][171]['Constant'] 	= 'CDR_CREDITED';
//$GLOBALS['*arrConstant']['CDR'][171]['Description'] = 'Credited';

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

// Customer Group Constants
/*define("CUSTOMER_GROUP_TELCOBLUE"	, 1);
define("CUSTOMER_GROUP_VOICETALK"	, 2);
define("CUSTOMER_GROUP_IMAGINE"		, 3);*/
$GLOBALS['*arrConstant']	['CustomerGroup']	[1]	['Constant']	= 'CUSTOMER_GROUP_TELCOBLUE';
$GLOBALS['*arrConstant']	['CustomerGroup']	[1]	['Description']	= 'Telco Blue';
$GLOBALS['*arrConstant']	['CustomerGroup']	[2]	['Constant']	= 'CUSTOMER_GROUP_VOICETALK';
$GLOBALS['*arrConstant']	['CustomerGroup']	[2]	['Description']	= 'VoiceTalk';
$GLOBALS['*arrConstant']	['CustomerGroup']	[3]	['Constant']	= 'CUSTOMER_GROUP_IMAGINE';
$GLOBALS['*arrConstant']	['CustomerGroup']	[3]	['Description']	= 'Imagine';

// Credit Card Constants
define("CREDIT_CARD_VISA"			, 1);
define("CREDIT_CARD_MASTERCARD"		, 2);
define("CREDIT_CARD_AMEX"			, 4);
define("CREDIT_CARD_DINERS"			, 5);

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

// Provisioning Request Status
/*define("REQUEST_DIRECTION_OUTGOING"		, 0);
define("REQUEST_DIRECTION_INCOMING"		, 1);*/
$GLOBALS['*arrConstant']	['RequestDirection']	[0]	['Constant']	= 'REQUEST_DIRECTION_OUTGOING';
$GLOBALS['*arrConstant']	['RequestDirection']	[0]	['Description']	= 'Outgoing';
$GLOBALS['*arrConstant']	['RequestDirection']	[1]	['Constant']	= 'REQUEST_DIRECTION_INCOMING';
$GLOBALS['*arrConstant']	['RequestDirection']	[1]	['Description']	= 'Incoming';



// Serivce Line Status
/*define("LINE_ACTIVE"					, 400);
define("LINE_DEACTIVATED"				, 401);
define("LINE_PENDING"					, 402);
define("LINE_SOFT_BARRED"				, 403);
define("LINE_HARD_BARRED"				, 404);*/
$GLOBALS['*arrConstant']	['LineStatus']	[400]	['Constant']	= 'LINE_ACTIVE';
$GLOBALS['*arrConstant']	['LineStatus']	[400]	['Description']	= 'Active';
$GLOBALS['*arrConstant']	['LineStatus']	[401]	['Constant']	= 'LINE_DEACTIVATED';
$GLOBALS['*arrConstant']	['LineStatus']	[401]	['Description']	= 'Deactivated';
$GLOBALS['*arrConstant']	['LineStatus']	[402]	['Constant']	= 'LINE_PENDING';
$GLOBALS['*arrConstant']	['LineStatus']	[402]	['Description']	= 'Pending';
$GLOBALS['*arrConstant']	['LineStatus']	[403]	['Constant']	= 'LINE_SOFT_BARRED';
$GLOBALS['*arrConstant']	['LineStatus']	[403]	['Description']	= 'Soft-barred';
$GLOBALS['*arrConstant']	['LineStatus']	[404]	['Constant']	= 'LINE_HARD_BARRED';
$GLOBALS['*arrConstant']	['LineStatus']	[404]	['Description']	= 'Hard-barred';

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
$GLOBALS['*arrConstant']	['Request']	[902]	['Constant']	= 'REQUEST_BAR_SOFT';
$GLOBALS['*arrConstant']	['Request']	[902]	['Description']	= 'Activate Soft Bar';
$GLOBALS['*arrConstant']	['Request']	[903]	['Constant']	= 'REQUEST_UNBAR_SOFT';
$GLOBALS['*arrConstant']	['Request']	[903]	['Description']	= 'Remove Soft Bar';
$GLOBALS['*arrConstant']	['Request']	[904]	['Constant']	= 'REQUEST_ACTIVATION';
$GLOBALS['*arrConstant']	['Request']	[904]	['Description']	= 'Activation';
$GLOBALS['*arrConstant']	['Request']	[905]	['Constant']	= 'REQUEST_DEACTIVATION';
$GLOBALS['*arrConstant']	['Request']	[905]	['Description']	= 'Deactivation';
$GLOBALS['*arrConstant']	['Request']	[906]	['Constant']	= 'REQUEST_PRESELECTION_REVERSE';
$GLOBALS['*arrConstant']	['Request']	[906]	['Description']	= 'Preselection Reversal';
$GLOBALS['*arrConstant']	['Request']	[907]	['Constant']	= 'REQUEST_FULL_SERVICE_REVERSE';
$GLOBALS['*arrConstant']	['Request']	[907]	['Description']	= 'Full Service Reversal';
$GLOBALS['*arrConstant']	['Request']	[908]	['Constant']	= 'REQUEST_BAR_HARD';
$GLOBALS['*arrConstant']	['Request']	[908]	['Description']	= 'Activate Hard Bar';
$GLOBALS['*arrConstant']	['Request']	[909]	['Constant']	= 'REQUEST_UNBAR_HARD';
$GLOBALS['*arrConstant']	['Request']	[909]	['Description']	= 'Remove Hard Bar';

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
define("SERVICE_ADDR_TYPE_POST_OFFICE"				, "PO");
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

// Postal Address Types
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

// Service Street Type
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

// Service Street Suffix Type

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

// End User Titles
define("END_USER_TITLE_TYPE_DOCTOR"					, "DR");
define("END_USER_TITLE_TYPE_MASTER"					, "MSTR");
define("END_USER_TITLE_TYPE_MISS"					, "MISS");
define("END_USER_TITLE_TYPE_MISTER"					, "MR");
define("END_USER_TITLE_TYPE_MRS"					, "MRS");
define("END_USER_TITLE_TYPE_MS"						, "MS");
define("END_USER_TITLE_TYPE_PROFESSOR"				, "PROF");

// Service State Type
define("SERVICE_STATE_TYPE_ACT"		, "ACT");
define("SERVICE_STATE_TYPE_NSW"		, "NSW");
define("SERVICE_STATE_TYPE_NT"		, "NT");
define("SERVICE_STATE_TYPE_QLD"		, "QLD");
define("SERVICE_STATE_TYPE_SA"		, "SA");
define("SERVICE_STATE_TYPE_TAS"		, "TAS");
define("SERVICE_STATE_TYPE_VIC"		, "VIC");
define("SERVICE_STATE_TYPE_WA"		, "WA");

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
define("BILLING_MINIMUM_TOTAL"			, 5.0);

// Set the defaults for Billing (Once every Month on the First)
define("BILLING_DEFAULT_DATE"			, 1);
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
define("SYSTEM_NOTE_TYPE"			, 7);

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

// Commonly used Email Addresses
$GLOBALS['*arrConstant']	['EmailAddress']	['david.g@telcoblue.com.au']	['Constant']	= 'EMAIL_CREDIT_MANAGER';
$GLOBALS['*arrConstant']	['EmailAddress']	['david.g@telcoblue.com.au']	['Description']	= 'Credit Manager';

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

//Data Report XLS Total Types
$GLOBALS['*arrConstant']	['XLSTotal']	[600]	['Constant']	= 'EXCEL_TOTAL_SUM';
$GLOBALS['*arrConstant']	['XLSTotal']	[600]	['Description']	= 'Sum';
$GLOBALS['*arrConstant']	['XLSTotal']	[601]	['Constant']	= 'EXCEL_TOTAL_AVG';
$GLOBALS['*arrConstant']	['XLSTotal']	[601]	['Description']	= 'Average';



// CLI Console Docked
define("CONSOLE_DOCKED"		, DONKEY);

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

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

// debug mode
define("DEBUG_MODE"					, TRUE);

// Applications
define("APPLICATION_COLLECTION"		, 0);
define("APPLICATION_NORMALISATION"	, 1);
define("APPLICATION_RATING"			, 2);
define("APPLICATION_BILLING"		, 3);
define("APPLICATION_PROVISIONING"	, 4);

// friendly error msg
define("ERROR_MESSAGE"				, "an error occured... sucks to be you");

// CDR TYPES
define("CDR_UNKNOWN"				, 0);
define("CDR_UNTIEL_RSLCOM"			, 1);
define("CDR_UNTIEL_COMMANDER"		, 2);
define("CDR_OPTUS_STANDARD"			, 3);
define("CDR_AAPT_STANDARD"			, 4);
define("CDR_ISEEK_STANDARD"			, 5);

// Provisioning Types
define("PRV_UNITEL_DAILY_ORDER_RPT"		, 100);
define("PRV_UNITEL_DAILY_STATUS_RPT"	, 101);
define("PRV_UNITEL_BASKETS_RPT"			, 102);
define("PRV_UNITEL_OUT"					, 103);
define("PRV_OPTUS_ALL"					, 104);
define("PRV_AAPT_ALL"					, 105);
define("PRV_UNITEL_PRESELECTION_RPT"	, 106);

define("PRV_UNITEL_PRESELECTION_EXP"	, 150);
define("PRV_UNITEL_DAILY_ORDER_EXP"		, 151);

define("PRV_IMPORT_RANGE_MIN"			, 100);
define("PRV_IMPORT_RANGE_MAX"			, 149);

// Carriers
define("CARRIER_UNITEL"	, 1);
define("CARRIER_OPTUS"	, 2);
define("CARRIER_AAPT"	, 3);
define("CARRIER_ISEEK"	, 4);

// ERROR TABLE
define("FATAL_ERROR_LEVEL"			, 10000);

define("NON_FATAL_TEST_EXCEPTION"	, 1337);
define("FATAL_TEST_EXCEPTION"		, 80085);

// CDR status


// CDR Handling (Range is 100-199)
define("CDR_READY"						, 100);
define("CDR_NORMALISED"					, 101);
define("CDR_CANT_NORMALISE"				, 102); // TODO: Expand to define specific reasons for failed processing
define("CDR_CANT_NORMALISE_RAW"			, 103);
define("CDR_CANT_NORMALISE_BAD_SEQ_NO"	, 104);
define("CDR_CANT_NORMALISE_HEADER"		, 105);
define("CDR_CANT_NORMALISE_NON_CDR"		, 106);
define("CDR_BAD_OWNER"					, 107);
define("CDR_CANT_NORMALISE_NO_MODULE"	, 108);
define("CDR_CANT_NORMALISE_INVALID"		, 109);
define("CDR_IGNORE"						, 110);
define("CDR_RATED"						, 111);
define("CDR_TEMP_INVOICE"				, 198);
define("CDR_INVOICED"					, 199);

// CDR File Handling (Range is 200-299)
define("CDRFILE_WAITING"			, 200);
define("CDRFILE_IMPORTING"			, 201);
define("CDRFILE_IMPORTED"			, 202);
define("CDRFILE_REIMPORT"			, 203);
define("CDRFILE_IGNORE"				, 204);
define("CDRFILE_IMPORT_FAILED"		, 205);
define("CDRFILE_NORMALISE_FAILED"	, 206);
define("CDRFILE_NORMALISED"			, 207);

// Provisioning File Handling
define("PROVFILE_WAITING"			, 250);
define("PROVFILE_READING"			, 251);
define("PROVFILE_IGNORE"			, 252);
define("PROVFILE_COMPLETE"			, 253);


// Invoice Status
define("INVOICE_TEMP"				, 100);
define("INVOICE_COMMITTED"			, 101);
define("INVOICE_DISPUTED"			, 102);
define("INVOICE_SETTLED"			, 103);

// Customer Group Constants
define("CUSTOMER_GROUP_TELCOBLUE"	, 1);
define("CUSTOMER_GROUP_VOICETALK"	, 2);
define("CUSTOMER_GROUP_IMAGINE"		, 3);

// Credit Card Constants
define("CREDIT_CARD_VISA"			, 1);
define("CREDIT_CARD_MASTERCARD"		, 2);
define("CREDIT_CARD_BANKCARD"		, 3);
define("CREDIT_CARD_AMEX"			, 4);
define("CREDIT_CARD_DINERS"			, 5);

// DONKEY (neither TRUE nor FALSE)
define("DONKEY"						, -1);

// Service Types
define("SERVICE_TYPE_ADSL"			, 100);
define("SERVICE_TYPE_MOBILE"		, 101);
define("SERVICE_TYPE_LAND_LINE"		, 102);
define("SERVICE_TYPE_INBOUND"		, 103);

// TAX RATES
define("TAX_RATE_GST"				, 10);

// Report Messages
define("MSG_HORIZONTAL_RULE"		, "\n================================================================================\n");

// SQL Modes
define("SQL_QUERY"				, 100);
define("SQL_STATEMENT"			, 200);

?>

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
define("PRV_AAPT_EOE_RETURN"			, 107);
define("PRV_AAPT_LSD"					, 108);
define("PRV_AAPT_REJECT"				, 109);
define("PRV_AAPT_LOSS"					, 110);


define("PRV_UNITEL_PRESELECTION_EXP"	, 150);
define("PRV_UNITEL_DAILY_ORDER_EXP"		, 151);
define("PRV_AAPT_EOE"					, 152);

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
define("SERVICE_TYPE_DIALUP"		, 104);

// TAX RATES
define("TAX_RATE_GST"				, 10);

// Report Messages
define("MSG_HORIZONTAL_RULE"		, "\n================================================================================\n");

// SQL Modes
define("SQL_QUERY"				, 100);
define("SQL_STATEMENT"			, 200);

// Provisioning Request Status
define("REQUEST_STATUS_WAITING"			, 300);
define("REQUEST_STATUS_PENDING"			, 301);
define("REQUEST_STATUS_REJECTED"		, 302);
define("REQUEST_STATUS_COMPLETED"		, 303);
define("REQUEST_STATUS_CANCELLED"		, 304);

// Provisioning Request Status
define("REQUEST_DIRECTION_OUTGOING"		, 0);
define("REQUEST_DIRECTION_INCOMING"		, 1);


// Serivce Line Status
define("LINE_ACTIVE"					, 400);
define("LINE_DEACTIVATED"				, 401);
define("LINE_PENDING"					, 402);
define("LINE_SOFT_BARRED"				, 403);
define("LINE_HARD_BARRED"				, 404);

// Provisioning Request Types
define("REQUEST_FULL_SERVICE"			, 900);
define("REQUEST_PRESELECTION"			, 901);
define("REQUEST_BAR_SOFT"				, 902);
define("REQUEST_UNBAR_SOFT"				, 903);
define("REQUEST_ACTIVATION"				, 904);
define("REQUEST_DEACTIVATION"			, 905);
define("REQUEST_PRESELECTION_REVERSE"	, 906);
define("REQUEST_FULL_SERVICE_REVERSE"	, 907);
define("REQUEST_PRESELECTION_REVERSAL"	, REQUEST_PRESELECTION_REVERSE);
define("REQUEST_BAR_HARD"				, 908);
define("REQUEST_UNBAR_HARD"				, 909);

// Provisioning Line Actions (Log)
define("LINE_ACTION_OTHER"				, 600);
define("LINE_ACTION_GAIN"				, 601);
define("LINE_ACTION_LOSS"				, 602);

// God help me ...
// Service Address Types
define("SERVICE_ADDR_TYPE_APARTMENT"				, "APT");
define("SERVICE_ADDR_TYPE_ATCO_PORTABLE_DWELLING"	, "APC");
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
define("SERVICE_ADDR_TYPE_STU"						, "STU");
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
define("POSTAL_ADDR_TYPE_PRIVATE_BAG"				, "PB");
define("POSTAL_ADDR_TYPE_GPO_BOX"					, "GPO");
define("POSTAL_ADDR_TYPE_MAIL_SERVICE"				, "MS");
define("POSTAL_ADDR_TYPE_RURAL_MAIL_DELIVERY"		, "RMD");
define("POSTAL_ADDR_TYPE_ROADSIDE_MAIL_BAG_BOX"		, "RMB");
define("POSTAL_ADDR_TYPE_LOCKED_BAG"				, "LB");
define("POSTAL_ADDR_TYPE_ROADSIDE_MAIL_SERVICE"		, "RMS");
define("POSTAL_ADDR_TYPE_ROADSIDE_DELIVERY"			, "RD");

?>

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
define("SERVICE_STREET_TYPE_BOARDWAY"				, "BDWY");
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
define("SERVICE_STREET_TYPE_VALLEY"					, "VALLEY");
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

?>

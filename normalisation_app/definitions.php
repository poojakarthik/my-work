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
 * This file exclusively declares application constants
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

// user name
define("USER_NAME"						, "Normilisation_app");

// Normalisation Report Messages
define("CDR_FILE_IMPORT_SUCCESS"		, "\tIMPORT SUCCESSFUL\t\t\t: CDR File <object> was imported successfully");
define("CDR_FILE_IMPORT_FAIL"			, "\tIMPORT FAILED\t\t\t\t: CDR File <object> failed to import.  Reason: <reason>");
define("CDR_FILE_NORMALISE_SUCCESS"		, "\tNORMALISATION SUCCESSFUL\t: CDR File <object> was normalised successfully");
define("CDR_FILE_NORMALISE_FAIL"		, "\tNORMALISATION FAILED\t\t: CDR File <object> failed to normalise.  Reason: <reason>");
define("CDR_NORMALISE_SUCCESS"			, "\tNORMALISATION SUCCESSFUL\t: CDR <object> was normalised successfully");
define("CDR_NORMALISE_FAILED"			, "\tNORMALISATION FAILED\t\t: CDR <object> failed to normalise.  Reason: <reason>");

define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_NO_NORMALISATION_MODULE"	, "NO NORMALISATION MODULE\t: <FriendlyName> (<Type>)\n\n");
define("MSG_START_IMPORT"				, "\n[ STARTING IMPORT ]\n");
define("MSG_START_NORMALISET"			, "\n[ STARTING NORMALISATION ]\n");
define("MSG_NORMALISE_TOTALS"			, MSG_HORIZONTAL_RULE."Total files Normalised\t: <TotalFiles>\n".MSG_HORIZONTAL_RULE);
define("MSG_IMPORTING_TOTALS"			, MSG_HORIZONTAL_RULE."Total files Imported\t: <TotalFiles>\n".MSG_HORIZONTAL_RULE);


// CDR Handling (Range is 100-199)
define("CDR_READY"						, 100);
define("CDR_NORMALISED"					, 101);
define("CDR_CANT_NORMALISE"				, 102); // TODO: Expand to a define specific reasons for failed processing
define("CDR_CANT_NORMALISE_RAW"			, 103);
define("CDR_CANT_NORMALISE_BAD_SEQ_NO"	, 104);
define("CDR_CANT_NORMALISE_HEADER"		, 105);
define("CDR_CANT_NORMALISE_NON_CDR"		, 106);
define("CDR_BAD_OWNER"					, 107);
define("CDR_CANT_NORMALISE_NO_MODULE"	, 108);
define("CDR_CANT_NORMALISE_INVALID"		, 109);
define("CDR_IGNORE"						, 110);


// CDR File Handling (Range is 200-299)
define("CDRFILE_WAITING"			, 200);
define("CDRFILE_IMPORTING"			, 201);
define("CDRFILE_IMPORTED"			, 202);
define("CDRFILE_REIMPORT"			, 203);
define("CDRFILE_IGNORE"				, 204);
define("CDRFILE_IMPORT_FAILED"		, 205);
define("CDRFILE_NORMALISE_FAILED"	, 206);
define("CDRFILE_NORMALISED"			, 207);

// Non-Fatal Exceptions
define("INVALID_CDRFILE_STATUS"		, 5000);
define("UNEXPECTED_CDRFILE_STATUS"	, 5001);
define("CDR_FILE_DOESNT_EXIST"		, 5002);
define("NO_NORMALISATION_MODULE"	, 5003);

// Fatal Exceptions

// Service Types
define("SERVICE_TYPE_ADSL"			, 100);
define("SERVICE_TYPE_MOBILE"		, 101);
define("SERVICE_TYPE_LAND_LINE"		, 102);
define("SERVICE_TYPE_INBOUND"		, 103);


?>

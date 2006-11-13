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
define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_NO_NORMALISATION_MODULE"	, "NO NORMALISATION MODULE\t: <FriendlyName> (<Type>)\n\n");
define("MSG_IMPORTING_TITLE"			, "\n[ Importing CDRs ]\n\n");
define("MSG_NORMALISATION_TITLE"		, "\n[ Normalising CDRs ]\n\n");
define("MSG_LINE"						, "\t+ <Action> CDR <SeqNo> from <FileName>");
define("MSG_OK"							, "\t[   OK   ]\n");
define("MSG_FAILED"						, "\t[ FAILED ]\n");
define("MSG_FAIL_CORRUPT"				, "\t\t- File is corrupt\n");
define("MSG_FAIL_RAW"					, "\t\t- Raw Data Invalid\n");
define("MSG_FAIL_NORM"					, "\t\t- Normalised Data Invalid\n");
define("MSG_FAIL_MODULE"				, "\t\t- Missing Normalisation Module: <Module>\n");
define("MSG_FAIL_FILE_MISSING"			, "\t- File not found: <Path>\n");
define("MSG_IMPORT_REPORT"				, "\n\t<Action> <Total> CDRs in <Time> seconds.  <Pass> passed, <Fail> failed.\n");
define("MSG_FOOTER"						, "\nNormalisation completed in a total of <Time> seconds.");
define("MSG_MAX_FILENAME_LENGTH"		, 30);


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

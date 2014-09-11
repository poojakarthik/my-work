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
define("USER_NAME"						, "Normalisation_app");

// Log path (with trailing /)
define("LOG_PATH"						, FILES_BASE_PATH."log/normalisation/");

// Normalisation Report Messages
//define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_NO_NORMALISATION_MODULE"	, "NO NORMALISATION MODULE\t: <FriendlyName> (<Type>)\n\n");
define("MSG_IMPORTING_TITLE"			, "\n[ Importing CDRs ]\n\n");
define("MSG_NORMALISATION_TITLE"		, "\n[ Normalising CDRs ]\n\n");
define("MSG_LINE"						, "\t+ <Action> CDR <SeqNo> from <FileName>");
define("MSG_OK"							, "\t[   OK   ]\n");
define("MSG_FAILED"						, "\t[ FAILED ]\n");
define("MSG_FAIL_CORRUPT"				, "\t\t- File is corrupt\n");
define("MSG_FAIL_LINE"					, "\t\t- <Reason>\n");
define("MSG_FAIL_MODULE"				, "\t\t- Missing Normalisation Module: <Module>\n");
define("MSG_FAIL_FILE_MISSING"			, "\t- File not found: <Path>\n");
define("MSG_REPORT"						, "\n\t<Action> <Total> CDRs in <Time> seconds.  <Pass> passed, <Fail> failed.\n");
define("MSG_FOOTER"						, "\nNormalisation completed in a total of <Time> seconds.");
define("MSG_MAX_FILENAME_LENGTH"		, 30);

// Non-Fatal Exceptions
define("INVALID_CDRFILE_STATUS"		, 5000);
define("UNEXPECTED_CDRFILE_STATUS"	, 5001);
define("CDR_FILE_DOESNT_EXIST"		, 5002);
define("NO_NORMALISATION_MODULE"	, 5003);

// Fatal Exceptions


// Fast normalisation test
define("FAST_NORMALISATION_TEST"		, FALSE);

?>

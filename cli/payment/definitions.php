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
define("USER_NAME"	, "Payment_app");

// Log path (with trailing /)
define("LOG_PATH"						, FILES_BASE_PATH."log/payment/");

// reporting messages
define("MSG_IMPORT_TITLE"				, "[ Importing Payment Records ]\n");
define("MSG_NORMALISE_TITLE"			, "[ Normalising Payment Records ]\n");
define("MSG_PROCESS_TITLE"				, "[ Processing Payment Records ]\n");
define("MSG_FAIL"						, "[ FAILED ]");
define("MSG_REASON"						, "\n\t\t- Reason: ");
define("MSG_OK"							, "[   OK   ]");
define("MSG_IGNORE"						, "[ IGNORE ]");
define("MSG_NORMALISE_SUBTOTALS"		, "\t~ Normalised <Total> records in <Time> seconds.");
define("MSG_PROCESS_SUBTOTALS"			, "\t~ Processed <Total> records in <Time> seconds.");
define("MSG_IMPORT_FOOTER"				, "\nImported <Total> files in <Time> seconds.  <Passed> passed, <Failed> failed.\n");
define("MSG_NORMALISE_FOOTER"			, "\nNormalised <Total> records in <Time> seconds.  <Pass> passed, <Ignore> ignored, <Fail> failed.\n");
define("MSG_PROCESS_FOOTER"				, "\nProcessed <Total> payments in <Time> seconds.  <Pass> passed, <Fail> failed.\n");
define("MSG_IMPORT_LINE"				, "\t+ Importing payment file <Id>...\t\t");
define("MSG_NORMALISE_LINE"				, "\t+ Normalising record #<Id>...\t\t\t\t");
define("MSG_PROCESS_LINE"				, "\t* Processing payment #<Id>...\t\t\t\t");
define("MSG_INVOICE_LINE"				, "\t\t+ Paying Invoice #<Id>...\t\t\t");
?>

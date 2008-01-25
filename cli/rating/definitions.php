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
define("USER_NAME"						, "Rating_app");

// Log path (with trailing /)
define("LOG_PATH"						, FILES_BASE_PATH."log/rating/");

// Rating Report Messages
define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_RATING_TITLE"				, "\n[ Rating ]\n\n");
define("MSG_LINE"						, "\t+ CDR <SeqNo>");
define("MSG_OK"							, "[   OK   ]\n");
define("MSG_FAILED"						, "[ FAILED ]\n");
define("MSG_FAIL_LINE"					, "\t\t- <Reason>\n");
define("MSG_REPORT"						, "\nRated <Total> CDRs in <Time> seconds.  <Pass> passed, <Fail> failed.\n");
?>

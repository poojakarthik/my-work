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
define("USER_NAME"						, "Charges_app");

// Log path (with trailing /)
define("LOG_PATH"						, FILES_BASE_PATH."log/charges/");

define("MSG_GENERATE_CHARGES"			, "[ Generating Charges ]\n");
define("MSG_LINE"						, " + Generating charge for #<Id>...");
define("MSG_OK"							, "\t\t\t\t[   OK   ]");
define("MSG_FAIL"						, "\t\t\t\t[ FAILED ]");
define("MSG_REASON"						, "\t Reason: ");
define("MSG_FOOTER"						, "\tGenerated <Total> charges in <Time> seconds.  <Passed> passed, <Failed> failed.\n".MSG_HORIZONTAL_RULE);
?>

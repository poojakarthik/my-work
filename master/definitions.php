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
define("USER_NAME"						, "_Master");

// Log path (with trailing /)
define("LOG_PATH"						, "/home/vixen_log/master/");

// State
define("STATE_INIT"						, 1);
define("STATE_RUN"						, 2);
define("STATE_SCRIPT_RUN"				, 3);
define("STATE_SLEEP"					, 4);
define("STATE_HALT"						, 99);

// instructions
define("INSTRUCTION_COMMAND"			, 1);
define("INSTRUCTION_WAIT"				, 2);
define("INSTRUCTION_RESUME"				, 3);
define("INSTRUCTION_HALT"				, 99);

?>

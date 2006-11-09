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
define("DEBUG_MODE"					, FALSE);

// friendly error msg
define("ERROR_MESSAGE"				, "an error occured... sucks to be you");

// CDR TYPES
define("CDR_UNTIEL_RSLCOM"			, 1);
define("CDR_UNTIEL_COMMANDER"		, 2);
define("CDR_OPTUS_STANDARD"			, 3);
define("CDR_AAPT_STANDARD"			, 4);
define("CDR_ISEEK_STANDARD"			, 5);

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
define("CDR_RATED"						, 201);

?>

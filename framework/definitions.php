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

// friendly error msg
define("ERROR_MESSAGE"				, "an error occured... sucks to be you");

// CDR TYPES
define("CDR_UNKNOWN"				, 0);
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
define("CDR_RATED"						, 111);


// CDR File Handling (Range is 200-299)
define("CDRFILE_WAITING"			, 200);
define("CDRFILE_IMPORTING"			, 201);
define("CDRFILE_IMPORTED"			, 202);
define("CDRFILE_REIMPORT"			, 203);
define("CDRFILE_IGNORE"				, 204);
define("CDRFILE_IMPORT_FAILED"		, 205);
define("CDRFILE_NORMALISE_FAILED"	, 206);
define("CDRFILE_NORMALISED"			, 207);

?>

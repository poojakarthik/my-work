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

// Normalisation Report Messages
define("CDR_FILE_IMPORT_SUCCESS"		, "IMPORT SUCCESSFUL		: CDR File <object> was imported successfully");
define("CDR_FILE_IMPORT_FAIL"			, "IMPORT FAILED			: CDR File <object> failed to import.  Reason: <reason>");
define("CDR_FILE_NORMALISE_SUCCESS"		, "NORMALISATION SUCCESSFUL	: CDR File <object> was normalised successfully");
define("CDR_FILE_NORMALISE_FAIL"		, "NORMALISATION FAILED		: CDR File <object> failed to normalise.  Reason: <reason>");
define("CDR_NORMALISE_SUCCESS"			, "NORMALISATION SUCCESSFUL : CDR <object> was normalised successfully");
define("CDR_NORMALISE_FAILED"			, "NORMALISATION FAILED		: CDR <object> failed to normalise.  Reason: <reason>");

// CDR Handling (Range is 100-199)
define("CDR_READY"					, 100);
define("CDR_NORMALISED"				, 101);
define("CDR_CANT_NORMALISE"			, 102); // TODO: Expand to a define specific reasons for failed processing
define("CDR_IGNORE"					, 110);

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


?>

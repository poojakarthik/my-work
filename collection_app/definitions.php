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
 * Collection Definitions
 *
 * This file exclusively declares application constants
 *
 * @file		definitions.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONSTANTS
//----------------------------------------------------------------------------//

// Diectories
define("TEMP_DOWNLOAD_DIR",				"/tmp/vixen_download");
define("TEMP_DOWNLOAD_DIR_PERMISSIONS",	0777);

// Collection Types
define("COLLECTION_TYPE_FTP",			100);

// CDR File Handling (Range is 200-299)
define("RAWFILE_DOWNLOADED"			, 250);
define("RAWFILE_DOWNLOAD_FAILED"	, 251);
define("RAWFILE_UNZIP_FAILED"		, 252);
define("RAWFILE_IMPORT_FAILED"		, 253);
define("RAWFILE_IMPORTED"			, 254);
define("CDRFILE_DOWNLOAD_FAILED"	, 255);
define("CDRFILE_MOVE_FAILED"		, 256);
define("CDRFILE_BAD_TYPE"			, 257);
define("CDRFILE_NOT_UNIQUE"			, 258);
define("CDRFILE_WAITING"			, 200);
define("CDRFILE_IMPORTING"			, 201);
define("CDRFILE_IMPORTED"			, 202);
define("CDRFILE_REIMPORT"			, 203);
define("CDRFILE_IGNORE"				, 204);
define("CDRFILE_IMPORT_FAILED"		, 205);
define("CDRFILE_NORMALISE_FAILED"	, 206);
define("CDRFILE_NORMALISED"			, 207);

// Non-Fatal Exceptions


// Fatal Exceptions

?>

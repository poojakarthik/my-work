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

// Collection Report Messages
define("MSG_HORIZONTAL_RULE"		, "================================================================================\n");
define("MSG_NO_COLLECTION_MODULE"	, "NO COLLECTION MODULE\t: <FriendlyName> (<Type>)\n\n");
define("MSG_CONNECTION_FAILED"		, "CONNECTION FAILED\t: <FriendlyName> (<Type>)\n\n");
define("MSG_CONNECTED"				, MSG_HORIZONTAL_RULE."CONNECTED TO\t\t: <FriendlyName> (<Type>)\n".MSG_HORIZONTAL_RULE);
define("MSG_DOWNLOADING_FROM"		, "Downloading from:\n");
define("MSG_DIRS"					, "\t<Dir>\n");
define("MSG_GRABBING_FILE"			, "Grabbing File\t: <FileName>\n");
define("MSG_UNZIPPED_FILES"			, "\t\tUnzipped Files:\n");
define("MSG_UNZIPPED_FILE"			, "\t\t\t<FileName>\n");
define("MSG_IMPORTED"				, "\t[Imported OK]\n\n");
define("MSG_IMPORT_FAILED"			, "\t[Import FAILED]\n\t[Reason : <Reason>]\n\n");
define("MSG_TOTALS"					, MSG_HORIZONTAL_RULE."Total files Imported\t: <TotalFiles>".MSG_HORIZONTAL_RULE);

// TODO: Filetype PREG Strings
define("FILE_PREG_RSLCOM"			, "");
define("FILE_PREG_COMMANDER"		, "");
define("FILE_PREG_OPTUS"			, "");
define("FILE_PREG_AAPT"				, "");
define("FILE_PREG_ISEEK"			, "");

// Non-Fatal Exceptions


// Fatal Exceptions

?>

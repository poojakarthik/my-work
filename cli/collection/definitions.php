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

// user name
define("USER_NAME"						, "Collection_app");

// Log path (with trailing /)
define("LOG_PATH"						, FILES_BASE_PATH."log/collection/");

// Diectories
define("TEMP_DOWNLOAD_DIR"				, FILES_BASE_PATH."download/");
define("TEMP_DOWNLOAD_DIR_PERMISSIONS"	, 0777);
define("UNZIP_DIR"						, TEMP_DOWNLOAD_DIR."unzip/");
define("DESTINATION_ROOT"				, FILES_BASE_PATH."vixen_import/");

// FTP Defaults
define("DEFAULT_FTP_SERVER"				, "10.11.12.212");
define("DEFAULT_FTP_USERNAME"			, "flame");
define("DEFAULT_FTP_PWORD"				, "flame");

// Collection Types
@define("COLLECTION_TYPE_FTP"		, 100);
@define("COLLECTION_TYPE_AAPT"		, 101);
@define("COLLECTION_TYPE_OPTUS"		, 102);
@define("COLLECTION_TYPE_SSH2"		, 103);
$GLOBALS['CollectionType'][COLLECTION_TYPE_FTP]		= "FTP";
$GLOBALS['CollectionType'][COLLECTION_TYPE_AAPT]	= "HTTPS/AAPT";
$GLOBALS['CollectionType'][COLLECTION_TYPE_OPTUS]	= "HTTPS/OPTUS";
$GLOBALS['CollectionType'][COLLECTION_TYPE_SSH2]	= "SSH2";

// Filename Regex's
define("REGEX_OPTUS"				, "/^tap_[A-Za-z]{3}\d_\d{14}_\d{4}[01]\d[0-3]\d_\d{6}_a_[sl]\.dat$/");
define("REGEX_AAPT"					, "/^W\d{9}\.[A-La-l][0-3]\d$/");
define("REGEX_RSLCOM"				, "/^[A-Za-z]\d{7}\.csv$/");
define("REGEX_COMMANDER"			, "/^[A-Za-z]\d{7}\.csv$/");
define("REGEX_UNITEL_SE"			, "/^[A-Za-z]{3}(On|Off)netBatch_SE_[A-Z]\d{5}_\d{8}.csv$/");
define("REGEX_ISEEK"				, "/^tap_isk\d_\d{14}_\d{4}[01]\d[0-3]\d_\d{6}_a_s\.dat$/");
define("REGEX_RSL_ORDER_RPT"		, "/^rsl\d{3}w\d{4}\d{4}[01]\d[0-3]\d.txt$/");
define("REGEX_RSL_STATUS_RPT"		, "/^rsl\d{3}d\d{4}[01]\d[0-3]\d\_[0-2]\d[0-5]\d[0-5]\d.txt$/");
define("REGEX_RSL_BASKETS"			, "/^rsl\d{3}a\d{4}\d{4}[01]\d[0-3]\d.txt$/");
define("REGEX_RSL_PRESELECTION"		, "/^rssaw\d{4}\d{4}[01]\d[0-3]\d$/");
define("REGEX_AAPT_EOE"				, "/^..\d{6}\.\d{3}$/");
define("REGEX_AAPT_EOE_RETURN"		, "/^..\d{6}\.\d{2}$/");
define("REGEX_AAPT_LSD"				, "/^\d{8}\.(LSD|lsd)$/");
define("REGEX_AAPT_REJECT"			, "/^R\d{9}\.\d{5}$/");

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

// Collection Report Messages
define("MSG_NO_COLLECTION_MODULE"	, "NO COLLECTION MODULE\t: <FriendlyName> (<Type>)\n\n");
define("MSG_CONNECTION_FAILED"		, "CONNECTION FAILED\t: <FriendlyName> (<Type>)\n\n");
define("MSG_CONNECTED"				, MSG_HORIZONTAL_RULE."CONNECTED TO\t\t: <FriendlyName> (<Type>)".MSG_HORIZONTAL_RULE);
define("MSG_DOWNLOADING_FROM"		, "Downloading from:\n");
define("MSG_DIRS"					, "\t<Dir>\n");
define("MSG_GRABBING_FILE"			, "Grabbing File\t: <FileName> (<FileSize> KB)\n");
define("MSG_UNZIPPED_FILES"			, "\t\tUnzipped Files:\n");
define("MSG_UNZIPPED_FILE"			, "\t\t\t<FileName>\n");
define("MSG_IMPORTED"				, "\t[Imported OK]\n\n");
define("MSG_BAD_FILE"				, "\t[File Corrupt]\n");
define("MSG_IMPORT_FAILED"			, "\t[Import FAILED]\n\t[Reason : <Reason>]\n\n");
define("MSG_TOTALS"					, MSG_HORIZONTAL_RULE."Imported <TotalFiles> in <Time> seconds.\n".MSG_HORIZONTAL_RULE);
define("MSG_MOVE_FILE_FAILED"		, "\t\t[File Move FAILED]\n\t\t\t<FileName>\n");
define("MSG_UNKNOWN_FILETYPE"		, "\t\t[Unknown File Type]\n\t\t\t<FileName>\n");
define("MSG_NOT_UNIQUE"				, "\t\t[File Not Unique]\n\t\t\t<FileName>\n");

// Non-Fatal Exceptions


// Fatal Exceptions

?>

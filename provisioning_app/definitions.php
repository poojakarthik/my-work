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
define("USER_NAME"						, "Provisioning_app");

// Log path (with trailing /)
define("LOG_PATH"						, "/home/vixen_log/provisioning_app/");

// Reporting messages
define("MSG_PROV_IMPORT"				, "[ Importing Provisioning Files ]\n");
define("MSG_PROV_BUILD"					, "[ Building Provisioning Requests ]\n");
define("MSG_PROV_SEND"					, "[ Sending Provisioning Requests ]\n");
define("MSG_IMPORT_LINE"				, "\t+ Importing <Filename>...\t\t\t\t");
define("MSG_READING_LINE"				, "\t\t+ Reading Line <LineNo>...\t\t\t\t");
define("MSG_BUILDING_LINE"				, "\t+ Building request #<RequestId>...\t\t\t\t");
define("MSG_SENDING_LINE"				, "\t+ Sending requests for <Carrier>...\t\t\t\t");
define("MSG_ERROR_LINE_SHALLOW"			, "\t\t- <Reason>");
define("MSG_ERROR_LINE_DEEP"			, "\t\t\t- <Reason>\n");
define("MSG_IMPORT_REPORT"				, "\n\tImported <Lines> lines (<Files> files) in <Time> seconds.\n\tLines: <LinesPassed> passed, <LinesFailed> failed; Files: <FilesPassed> passed, <FilesFailed> failed.\n");
define("MSG_REPORT"						, "\n<Action> <Total> files in <Time> seconds.  <Pass> passed, <Fail> failed.");
define("MSG_PROVISIONING_FOOTER"		, "\nProvisioning completed in <Time> seconds.");
define("MSG_OK"							, "[   OK   ]");
define("MSG_FAILED"						, "[ FAILED ]");
define("MSG_IGNORE"						, "[ IGNORE ]");

// Success/Failure codes
define("PRV_SUCCESS"					, 100);
define("PRV_TRAILER_RECORD"				, 101);
define("PRV_HEADER_RECORD"				, 102);
define("PRV_BAD_RECORD_TYPE"			, 103);
define("PRV_NO_SERVICE"					, 104);
define("PRV_OLD_STATUS"					, 105);

// Log Descriptions
define("DESCRIPTION_LOST_TO"			, "Service lost to ");
define("DESCRIPTION_CANCELLED"			, "Service cancelled");

// File Status
define("PROV_COMPLETED"					, 999);

// Sequence number starts
define("SEQUENCE_START_UNITEL"			, 1);

// File Directories (incl trailing "/")
define("UNITEL_DAILY_ORDER_DIR"			, "/home/vixen_upload/unitel/dailyorderfiles/");
define("UNITEL_PRESELECTION_DIR"		, "/home/vixen_upload/unitel/preselectionfiles/");

// Additional Preselection Constants
define("CUSTOMER_NUMBER_OPTUS"			,"23139716000139");

// The email message sent when a request response comes in
define("REQUEST_EMAIL_MESSAGE"			,	"Hi <Employee>,\n\n" .
											"Your request made on <RequestDate> for the service <FNN> has been responded to.  Below are the most up to date details.\n\n" .
											"\tRequest Date\t: <RequestDate>\n" .
											"\tService\t\t: <FNN>\n" .
											"\tAccount\t\t: <Account>\n" .
											"\tBusiness Name\t: <BusinessName>\n\n" .
											"\tResponse Date\t: <ResponseDate>\n" .
											"\tRequest Type\t: <RequestType>\n" .
											"\tCarrier\t\t: <Carrier>\n" .
											"\tStatus\t\t: <Status>\n" .
											"\tDescription\t: <Description>\n\n" .
											" - Pablo, the Helpful Donkey\n\n" .
											"(NOTE: This is an automated message.  Do NOT reply to this email.)");
define("REQUEST_EMAIL_ADMIN"			,	"rich@voiptelsystems.com.au");
?>

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

// Success/Failure codes
define("PRV_SUCCESS"					, 100);
define("PRV_TRAILER_RECORD"				, 101);
define("PRV_HEADER_RECORD"				, 102);
define("PRV_BAD_RECORD_TYPE"			, 103);
define("PRV_NO_SERVICE"					, 104);

// Request Types
define("REQUEST_FULL_SERVICE"			, 900);
define("REQUEST_PRESELECTION"			, 901);

// Line actions (Log)
define("LINE_ACTION_OTHER"				, 600);
define("LINE_ACTION_GAIN"				, 601);
define("LINE_ACTION_LOSS"				, 602);

// Serivce Status
define("LINE_ACTIVE"					, 400);
define("LINE_DEACTIVATED"				, 401);
define("LINE_PENDING"					, 402);

// Log Descriptions
define("DESCRIPTION_LOST_TO"			, "Service lost to ");
define("DESCRIPTION_CANCELLED"			, "Service cancelled");

// Request Status
define("REQUEST_STATUS_PENDING"			, 300);
define("REQUEST_STATUS_REJECTED"		, 301);
define("REQUEST_STATUS_COMPLETED"		, 302);

// File Status
define("PROV_COMPLETED"					, 999);

?>

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
define("USER_NAME"						, "Billing_app");

// Reporting Messages
define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_OK"							, "\t\t\t\t[   OK   ]");
define("MSG_FAILED"						, "\t\t\t\t[ FAILED ]");
define("MSG_CLEAR_TEMP_TABLE"			, "Clearing Temporary Invoice Table...\t");
define("MSG_BUILD_TEMP_INVOICES"		, "\n[ Building Temporary Invoices ]");
define("MSG_LINE"						, "\t+ Billing Account #<AccountNo>...");
define("MSG_LINE_FAILED"				, "\n\t\t- <Reason>");
define("MSG_BUILD_REPORT"				, "\n\tGenerated <Total> Invoices in <Time> seconds.  <Pass> passed, <Fail> failed.\n\n");
define("MSG_BILLING_FOOTER"				, "\nBilling completed in <Time> seconds.");
define("MSG_COMMIT_TEMP_INVOICES"		, "Committing Temporary Invoices...\t");
define("MSG_UPDATE_CDRS"				, "Linking CDRs to Invoices...\t\t");
define("MSG_REVERT_CDRS"				, "Reverting CDR status...\t\t\t");
define("MSG_CHECK_TEMP_INVOICES"		, "Checking for failed invoices...\t\t");
define("MSG_UPDATE_TEMP_INVOICE_STATUS"	, "Updating status on temporary invoices...");
define("MSG_UPDATE_INVOICE_STATUS"		, "Updating status on committed invoices...");
define("MSG_BILLING_TITLE"				, "[ GENERATING INVOICES ]");
define("MSG_REVOKE_TITLE"				, "[ REVOKING INVOICES ]");
define("MSG_COMMIT_TITLE"				, "[ COMMITTING INVOICES ]");

?>

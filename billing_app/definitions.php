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
define("USER_NAME"						, "Skell_app");

// Reporting Messages
define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_OK"							, "[   OK   ]");
define("MSG_FAILED"						, "[ FAILED ]");
define("MSG_CLEAR_TEMP_TABLE"			, "Clearing Temporary Invoice Table\t\t\t\t");
define("MSG_BUILD_TEMP_INVOICES"		, "[ Building Temporary Invoices ]");
define("MSG_LINE"						, "\t+ Billing Account #<AccountNo>...\t\t\t\t");
define("MSG_LINE_FAILED"				, "\n\t\t- <Reason>");
define("MSG_BUILD_REPORT"				, "\n\tGenerated <Total> Invoices in <Time> seconds.  <Pass> passed, <Fail> failed.");
define("MSG_BILLING_FOOTER"				, "\nBilling completed in <Time> seconds.");
define("MSG_COMMIT_TEMP_INVOICES"		, "\nCommitting Temporary Invoices...\t\t\t\t");
define("MSG_UPDATE_CDRS"				, "Linking CDRs to Invoices...\t\t\t\t");
define("MSG_REVERT_CDRS"				, "Reverting CDR status...\t\t\t\t\t");

?>

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

// Log path (with trailing /)
define("LOG_PATH"						, FILES_BASE_PATH."log/billing/");

// Billy Printing Variations
define("BILL_PRINT"						, 900);
define("BILL_PRINT_ETECH"				, 901);
define("BILL_FLEX_XML"					, 902);

// Charge Modules
define("CHARGE_MODULE_NON_DDR"			, 666);
define("CHARGE_MODULE_LATE_PAYMENT"		, 667);
define("CHARGE_MODULE_INBOUND"			, 668);
define("CHARGE_MODULE_PINNACLE"			, 669);
define("CHARGE_MODULE_PLAN"				, 670);
define("CHARGE_MODULE_PLAN_ADVANCE"		, 671);
define("CHARGE_MODULE_PLAN_ARREARS"		, 672);
define("CHARGE_MODULE_PLAN_CREDIT"		, 673);

// Non-DDR minimum invoice total
define("NON_DDR_MINIMUM_CHARGE"			, 2.50);

// Reporting Messages
define("MSG_HORIZONTAL_RULE"			, "================================================================================\n");
define("MSG_OK"							, "\t\t[   OK   ]");
define("MSG_FAILED"						, "\t\t[ FAILED ]");
define("MSG_IGNORE"						, "\t\t[ IGNORE ]");
define("MSG_CLEAR_TEMP_TABLE"			, "Clearing Temporary Invoice Table...\t\t\t");
define("MSG_BUILD_TEMP_INVOICES"		, "\n[ Building Temporary Invoices ]");
define("MSG_ACCOUNT_TITLE"				, "\t[ Account #<AccountNo> ]");
define("MSG_LINE_TOTALS"				, "\t\tCalculating Totals...");
define("MSG_LINE_CREDITS"				, "\t\tApplying Credits...");
define("MSG_LINK_CDRS"					, "\t+ Linking CDRs...\t\t");
define("MSG_SERVICE_TOTAL"				, "\t\t+ Generating Service Totals...");
define("MSG_CALCULATE_CAPS"				, "\t\t+ Calculating Caps...\t");
define("MSG_UPDATE_CHARGES"				, "\t\t+ Updating Charges...\t");
define("MSG_DEBITS_CREDITS"				, "\t\t+ Calculating DRs and CRs...");
define("MSG_GET_SERVICES"				, "\t+ Retrieving Service Data...\t");
define("MSG_SERVICE_TITLE"				, "\t-> <FNN>");
define("MSG_TEMP_INVOICE"				, "\t* Generating Temporary Invoice...\t\t");
define("MSG_LINE_FAILED"				, "\n\t\t- <Reason>");
define("MSG_BUILD_REPORT"				, "\n\tGenerated <Total> Invoices in <Time> seconds.  <Pass> passed, <Fail> failed.\n\n");
define("MSG_BILLING_FOOTER"				, "\nBilling completed in <Time> seconds.");
define("MSG_COMMIT_TEMP_INVOICES"		, "Committing Temporary Invoices...\t");
define("MSG_UPDATE_CDRS"				, "Updating CDR Status...\t\t");
define("MSG_REVERT_CDRS"				, "Reverting CDR status...\t\t\t\t\t");
define("MSG_CHECK_TEMP_INVOICES"		, "Checking for failed invoices...\t\t");
define("MSG_UPDATE_TEMP_INVOICE_STATUS"	, "Updating status on temporary invoices...");
define("MSG_UPDATE_INVOICE_STATUS"		, "Updating status on committed invoices...");
define("MSG_BILLING_TITLE"				, "[ GENERATING INVOICES ]");
define("MSG_REVOKE_TITLE"				, "[ REVOKING INVOICES ]");
define("MSG_COMMIT_TITLE"				, "[ COMMITTING INVOICES ]");
define("MSG_GENERATE_AUDIT"				, "[ GENERATE AUDIT REPORT ]\t\t\t\t");
define("MSG_INVOICE_SUMMARY"			, "[ INVOICE SUMMARY ]\n\n" .
										  "\tTotal Invoices\t\t\t\t: <TotalInvoices>\n" .
										  "\tTotal \$ Invoiced (Ex GST)\t\t: \$<TotalInvoicedExGST>\n" .
										  "\tTotal \$ Invoiced (Inc GST)\t\t: \$<TotalInvoicedIncGST>\n" .
										  "\tTotal Cost of CDRs\t\t\t: \$<TotalCDRCost>\n" .
										  "\tTotal \$ Rated\t\t\t\t: \$<TotalRated>\n" .
										  "\tTotal No. of CDRs\t\t\t: <TotalCDRs>\n\n");
define("MSG_CARRIER_SUMMARY"			, "[ CARRIER SUMMARY ]\n\n" .
										  "<Summaries>");
define("MSG_CARRIER_BREAKDOWN"			, "\t[ <Carrier> ]\n" .
										  "\t\tTotal Cost of CDRs\t\t: \$<TotalCDRCost>\n" .
										  "\t\tTotal \$ Rated\t\t\t: \$<TotalRated>\n" .
										  "\t\tTotal No. of CDRs\t\t: <TotalCDRs>\n\n" .
										  "<RecordTypes>\n");
define("MSG_RECORD_TYPES"				, "\t\t- <RecordType>\n" .
										  "\t\t\tTotal Cost of CDRs\t: \$<TotalCDRCost>\n" .
										  "\t\t\tTotal \$ Rated\t\t: \$<TotalRated>\n" .
										  "\t\t\tTotal No. of CDRs\t: <TotalCDRs>\n");
define("MSG_SERVICE_TYPE_SUMMARY"		, "[ SERVICE TYPE SUMMARY ]\n\n" .
										  "<Summaries>");
define("MSG_SERVICE_TYPE_BREAKDOWN"		, "\t[ <ServiceType> ]\n" .
										  "\t\tTotal Cost of CDRs\t\t: \$<TotalCDRCost>\n" .
										  "\t\tTotal \$ Rated\t\t\t: \$<TotalRated>\n" .
										  "\t\tTotal \$ Charged\t\t\t: \$<TotalCharged>\n" .
										  "\t\tTotal No. of CDRs\t\t: <TotalCDRs>\n\n" .
										  "<RecordTypes>\n");
define("MSG_LAST_BILLED"				, "Updating Account Last Billed date...");
define("MSG_UPDATE_CHARGE"				, "Update Charge statuses...\t");
define("MSG_BUILD_SEND_OUTPUT"			, "Building and Sending Invoice Output...\t");
define("MSG_FAILED_LINE"				, "\t- Reason: <Reason>");

// Data Types for Bill Printing
define("BILL_TYPE_INTEGER"				, 700);
define("BILL_TYPE_CHAR"					, 701);
define("BILL_TYPE_BINARY"				, 708);
define("BILL_TYPE_FLOAT"				, 709);
define("BILL_TYPE_SHORTDATE"			, 702);
define("BILL_TYPE_LONGDATE"				, 703);
define("BILL_TYPE_TIME"					, 704);
define("BILL_TYPE_DURATION"				, 705);
define("BILL_TYPE_SHORTCURRENCY"		, 707);

// Datatypes for Etech's Bill Output Format
define("ETECH_DATE_YYYYMMDD"			, 800);
define("ETECH_STRING"					, 801);
define("ETECH_DATE_MONTH_YY"			, 802);
define("ETECH_INTEGER"					, 803);
define("ETECH_FNN"						, 804);
define("ETECH_DATE_YYYY_MM_DD"			, 805);
define("ETECH_SHORT_CURRENCY"			, 806);
define("ETECH_DATETIME"					, 807);
define("ETECH_DURATION"					, 808);
define("ETECH_LONG_CURRENCY"			, 809);
define("ETECH_ROW"						, 810);
define("ETECH_DATE_RANGE"				, 811);

// Etech Brand Ids
define("ETECH_BRAND_TELCOBLUE"			, 1);
define("ETECH_BRAND_VOICETALK"			, 2);
define("ETECH_BRAND_IMGAINE"			, 4);

// Bill Designs for Bill Printing
define("BILL_DESIGN_TELCOBLUE"			, 1);
define("BILL_DESIGN_VOICETALK"			, 2);

// Delivery methods for Bill Printing
define("BILL_DESIGN_PRINT"				, 0);
define("BILL_DESIGN_EMAIL"				, 1);
define("BILL_DESIGN_BOTH"				, 2);

// Graph Types for Bill Printing
define("GRAPH_TYPE_VERTICALBAR"				, "01");
define("GRAPH_TYPE_VERTICALBARBREAKDOWN"	, "02");
define("GRAPH_TYPE_HORIZONTALBARSPLIT"		, "03");
define("GRAPH_TYPE_LINEXY"					, "04");
define("GRAPH_TYPE_LINEXYZ"					, "05");

// Miscellaneous Bill Printing Constants
define("BILL_PRINT_HISTORY_LIMIT"			, 6);
define("BILL_PRINT_SAMPLE_LIMIT"			, 10);
define("BILLING_LOCAL_PATH"					, FILES_BASE_PATH."bill_output/");
define("BILLING_LOCAL_PATH_SAMPLE"			, BILLING_LOCAL_PATH."sample/");
define("INVOICE_MIN_CHARGE"					, 10);
define("INVOICE_XML_PATH"					, FILES_BASE_PATH."invoices/xml/");

// Bill Printing FTP data
define("BILL_PRINT_HOST"					, "");	// TODO
define("BILL_PRINT_USERNAME"				, "");	// TODO
define("BILL_PRINT_PASSWORD"				, "");	// TODO
define("BILL_PRINT_REMOTE_DIR"				, "");	// TODO
define("BILL_PRINT_REMOTE_DIR_SAMPLE"		, "");	// TODO

// Error Codes
define("ERROR_NO_INVOICE_DATA"				, -1);

// Bill Printing Run Types
define("BILL_COMPLETE"						, 0);
define("BILL_SAMPLE"						, 1);
define("BILL_REPRINT"						, 2);
define("BILL_REPRINT_TEMP"					, 3);

// Bill Factory Types
define("BILL_FACTORY_SERVICE_SUMMARY"		, 200);
define("BILL_FACTORY_ITEMISE_RECORD_TYPES"	, 201);
define("BILL_FACTORY_ITEMISE_CALLS"			, 202);
define("BILL_FACTORY_ITEMISE_CHARGES"		, 203);
define("BILL_FACTORY_SERVICE_TOTAL"			, 204);
define("BILL_FACTORY_SERVICE_CHARGES_TOTAL"	, 205);
define("BILL_FACTORY_RECORD_TYPES"			, 206);
define("BILL_FACTORY_PLAN_ADJUSTMENTS"		, 207);


?>

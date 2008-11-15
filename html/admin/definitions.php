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
 * @package		ui_app
 * @author		Jared 'flame' Herbohn
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONSTANTS
//----------------------------------------------------------------------------//
$strVixenBaseDir = GetVixenBase();
Define('TEMPLATE_BASE_DIR', 	$strVixenBaseDir . "html/admin/");
Define('MODULE_BASE_DIR', 		$strVixenBaseDir . "html/ui/");
Define('MODULE_DEFAULT_CSS', 	$strVixenBaseDir . "html/admin/style_template/default.css");
Define('LOCAL_BASE_DIR', 		$strVixenBaseDir . "html/admin/");
Define('FRAMEWORK_BASE_DIR', 	$strVixenBaseDir . "html/ui/");


// Define which flavour of browsers are supported by this application
Define ('BROWSER_IE',	0x1);
Define ('BROWSER_NS',	0x2);
Define ('BROWSER_PR',	0x4);
Define ('BROWSER_SF',	0x8);
Define ('SUPPORTED_BROWSERS', BROWSER_NS | BROWSER_PR | BROWSER_SF);
Define ('SUPPORTED_BROWSERS_DESCRIPTION', "Firefox, Prism, Safari");
// If the app were to support both IE and NS then SUPPORTED_BROWSERS = BROWSER_IE | BROWSER_NS
Define ("APPLICATION_VIXEN",	101);
Define ("APPLICAITON_CLIENT",	102);
Define ("APPLICATION",			APPLICATION_VIXEN);
Define ("APP_NAME",				"Vixen Internal System");


Define ('COLUMN_ONE'	, 1);
Define ('COLUMN_TWO'	, 2);
Define ('COLUMN_THREE'	, 3);
Define ('COLUMN_FOUR'	, 4);

Define ('AJAX_MODE'		, 1);
Define ('HTML_MODE'		, 2);

Define ('USER_TIMEOUT'	, 3600);
Define ('GOD_TIMEOUT'	, 60*60*24*7);


/* Depricated
define('DATABASE_URL', '10.11.12.13');
define('DATABASE_NAME', vixen);
define('DATABASE_USER', vixen);
define('DATABASE_PWORD', V1x3n);
*/

// database documentation contexts
define('CONTEXT_DEFAULT', 0);
define('CONTEXT_INCLUDES_GST', 1);

define('OUTPUT_TYPE_LABEL', 1);
define('OUTPUT_TYPE_RADIO', 2);

// CSS classes
define('CLASS_DEFAULT', 'Default');

// Note Classes
define('NOTE_CLASS_ACCOUNT_NOTES', 1);
define('NOTE_CLASS_CONTACT_NOTES', 2);
define('NOTE_CLASS_SERVICE_NOTES', 3);

// NoteTypes
define('NOTE_FILTER_ALL',		1);
define('NOTE_FILTER_USER',		2);
define('NOTE_FILTER_SYSTEM',	3);

// ProvisioningHistoryCategories
define('PROVISIONING_HISTORY_CATEGORY_REQUESTS',	1);
define('PROVISIONING_HISTORY_CATEGORY_RESPONSES',	2);
define('PROVISIONING_HISTORY_CATEGORY_BOTH',		3);

// Provisioning History Filters (beyond using the Provisioning Request constants which can also be used as filters)
define('PROVISIONING_HISTORY_FILTER_ALL',			0);
define('PROVISIONING_HISTORY_FILTER_BARRINGS_ONLY',	1);


// Object Status
define('STATUS_NEW',	 	100);
define('STATUS_CLEANED', 	101);
define('STATUS_LOADED', 	102);
define('STATUS_UPDATED', 	103);
define('STATUS_MERGED', 	104);
define('STATUS_SAVED', 		105);

// HTML Template contexts - defines in which context a HTML Template will be displayed
define('HTML_CONTEXT_DEFAULT',				100);
define('HTML_CONTEXT_FULL_DETAIL',			101);
define('HTML_CONTEXT_MINIMUM_DETAIL',		102);
define('HTML_CONTEXT_LEDGER_DETAIL',		103);
define('HTML_CONTEXT_BARE_DETAIL',			104);
define('HTML_CONTEXT_NO_DETAIL',			105);
define('HTML_CONTEXT_DETAILS',				106);
define('HTML_CONTEXT_FORM_START',			110);
define('HTML_CONTEXT_FORM_END',				111);
define('HTML_CONTEXT_ACCOUNT_NOTE',			300);
define('HTML_CONTEXT_CONTACT_NOTE',			301);
define('HTML_CONTEXT_CONTACT_ADD',			200);
define('HTML_CONTEXT_CONTACT_EDIT',			201);
define('HTML_CONTEXT_SERVICE_ADD',			400);
define('HTML_CONTEXT_SERVICE_EDIT',			401);
define('HTML_CONTEXT_RELATED_ARTICLES', 	101);
define('HTML_CONTEXT_SERVICE_NOTE',			402);
define('HTML_CONTEXT_RATE_GROUPS', 			501);
define('HTML_CONTEXT_RATE_GROUPS_EMPTY',	502);
define('HTML_CONTEXT_RATES', 				503);
define('HTML_CONTEXT_EDIT_DETAIL',			504);
define('HTML_CONTEXT_TABULAR_DETAIL',		505);
define('HTML_CONTEXT_POPUP', 				506);
define('HTML_CONTEXT_PAGE', 				507);
define('HTML_CONTEXT_OVERVIEW_PAGE', 		508);
define('HTML_CONTEXT_VIEW',					509);
define('HTML_CONTEXT_EDIT',					510);
define('HTML_CONTEXT_CURRENT_PLAN',			511);
define('HTML_CONTEXT_FUTURE_PLAN',			512);
define('HTML_CONTEXT_IFRAME',				513);
define('HTML_CONTEXT_ALL',					514);
define('HTML_CONTEXT_SINGLE',				515);
define('HTML_CONTEXT_NEW',					516);
define('HTML_CONTEXT_SERVICE_BULK_ADD',		517);
define('HTML_CONTEXT_TABLE',				518);

// constants for the cap/excess rates used within the rate add page
define('RATE_CAP_NO_CAP',					100);
define('RATE_CAP_CAP_UNITS',				101);
define('RATE_CAP_CAP_COST',					102);
define('RATE_CAP_NO_CAP_LIMITS',			103);
define('RATE_CAP_CAP_LIMIT',				104);
define('RATE_CAP_CAP_USAGE',				105);
define('RATE_CAP_EXS_RATE_PER_UNIT',		106);
define('RATE_CAP_EXS_MARKUP',				107);
define('RATE_CAP_EXS_PERCENTAGE',			108);
define('RATE_CAP_STANDARD_RATE_PER_UNIT',	109);
define('RATE_CAP_STANDARD_MARKUP',			110);
define('RATE_CAP_STANDARD_PERCENTAGE',		111);
define('RATE_CHARGES_SHOW',					112);
define('RATE_CHARGES_HIDE',					113);

// Target Types - used to determine how to handle an ajax call
define("TARGET_TYPE_DIV", 	"Div");
define("TARGET_TYPE_POPUP",	"Popup");
define("TARGET_TYPE_PAGE",	"Page");

// PropertyToken Render method constants
define('RENDER_VALUE',		"Value");
define('RENDER_OUTPUT',		"Output");
define('RENDER_INPUT',		"Input");
define('RENDER_PASSWORD',	"Password");

// Properties for the summary popup table (Rate Allocation Status)
define('RATE_ALLOCATION_STATUS_UNDER_ALLOCATED',				0);
define('RATE_ALLOCATION_STATUS_CORRECTLY_ALLOCATED',			1);
define('RATE_ALLOCATION_STATUS_OVER_ALLOCATED',					2);
define('RATE_ALLOCATION_STATUS_BOTH_OVER_AND_UNDER_ALLOCATED',	3);

// Permissions
// These are described as Hexedecimal values so they can be logically ORed together, without influencing eachother
// When giving a user multiple permissions, they should be logically ORed together, not added together
														$arrPermissions = Array ();
define("PERMISSION_PUBLIC"					, 0x01);	$arrPermissions[PERMISSION_PUBLIC]			= "Public";			// 1
//define("PERMISSION_ADMIN"					, 0x02);	$arrPermissions[PERMISSION_ADMIN]			= "Admin";			// 2	
define("PERMISSION_ADMIN"					, 0x9F);	$arrPermissions[PERMISSION_ADMIN]			= "Admin";			// 159 (2 + PERMISSION_PUBLIC + PERMISSION_OPERATOR + PERMISSION_SALES + PERMISSION_ACCOUNTS + OPERATOR_VIEW)
define("PERMISSION_OPERATOR"				, 0x04);	$arrPermissions[PERMISSION_OPERATOR]		= "Operator";		// 4
define("PERMISSION_SALES"					, 0x08);	$arrPermissions[PERMISSION_SALES]			= "Sales";			// 8
define("PERMISSION_ACCOUNTS"				, 0x10);	$arrPermissions[PERMISSION_ACCOUNTS]		= "Accounts";		// 16
define("PERMISSION_RATE_MANAGEMENT"			, 0x20);	$arrPermissions[PERMISSION_RATE_MANAGEMENT]	= "Rate Management";// 32
define("PERMISSION_CREDIT_CARD"				, 0x40);	$arrPermissions[PERMISSION_CREDIT_CARD]		= "Credit Card";	// 64
define("PERMISSION_OPERATOR_VIEW"			, 0x80);	$arrPermissions[PERMISSION_OPERATOR_VIEW]	= "Operator View";	// 128
define("PERMISSION_CUSTOMER_GROUP_ADMIN"	, 0x200);	$arrPermissions[PERMISSION_CUSTOMER_GROUP_ADMIN]	= "Customer Group Admin";	// 512
define("PERMISSION_KB_USER"					, 0x400);	$arrPermissions[PERMISSION_KB_USER] = "KB User";	// 1024
define("PERMISSION_KB_ADMIN_USER"			, 0xC00);	$arrPermissions[PERMISSION_KB_ADMIN_USER] = "KB Admin User"; // 3072 (2048 + 1024) 
define("PERMISSION_SUPER_ADMIN"				, 0x7FFFFFFF);	$arrPermissions[PERMISSION_SUPER_ADMIN]		= "Super Admin";	// 2147483647 (All permissions except DEBUG and GOD) 
define("PERMISSION_DEBUG"					, 0x80000000);
define("USER_PERMISSION_GOD"				, 0x7FFFFFFFFFFFFFFF); // This constant is legacy
define("PERMISSION_GOD"						, 0x7FFFFFFFFFFFFFFF);


$GLOBALS['Permissions']	= $arrPermissions;

// This is used in the datbase to represent an "end Date" or "Closed on date" that should never be reached
define('END_OF_TIME', 	'9999-12-31 23:59:59');

// This defines how many notes to retrieve.  The most recent ones should be retrieved
define("DEFAULT_NOTES_LIMIT", 5);

// Custom Events (These are Fired from PHP code and handled by Javascript code)
// They are also defined locally within the javscript classes that use them, which means that they are defined twice
define("EVENT_ON_NEW_NOTE"							, "OnNewNote");
define("EVENT_ON_SERVICE_UPDATE"					, "OnServiceUpdate");
define("EVENT_ON_ACCOUNT_DETAILS_UPDATE"			, "OnAccountDetailsUpdate");
define("EVENT_ON_ACCOUNT_SERVICES_UPDATE"			, "OnAccountServicesUpdate");
define("EVENT_ON_ACCOUNT_PRIMARY_CONTACT_UPDATE"	, "OnAccountPrimaryContactUpdate");
define("EVENT_ON_EDIT_ACCOUNT_DETAILS_CANCEL"		, "OnEditAccountDetailsCancel");
define("EVENT_ON_CUSTOMER_GROUP_DETAILS_UPDATE"		, "OnCustomerGroupDetailsUpdate");
define("EVENT_ON_CONFIG_CONSTANT_UPDATE"			, "OnConfigConstantUpdate");
define("EVENT_ON_SERVICE_RATE_GROUPS_UPDATE"		, "OnServiceRateGroupsUpdate");
define("EVENT_ON_PROVISIONING_REQUEST_SUBMISSION"	, "OnProvisioningRequestSubmission");
define("EVENT_ON_PROVISIONING_REQUEST_CANCELLATION"	, "OnProvisioningRequestCancellation");


// Maximum file upload size is 1 megabyte
define("RATEGROUP_IMPORT_MAXSIZE", 1048576);
define("RESOURCE_FILE_MAX_SIZE", 1048576 * 2);


?>

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

Define ('VIXEN_BASE_DIR', "../");
Define ('TEMPLATE_BASE_DIR', "../ui_app/");
Define ('TEMPLATE_STYLE_DIR', "../ui_app/style_template/");
Define ('MODULE_BASE_DIR', "../ui_app/");
Define ('JAVASCRIPT_BASE_DIR', "../ui_app/");

Define ('COLUMN_ONE'	, 1);
Define ('COLUMN_TWO'	, 2);
Define ('COLUMN_THREE'	, 3);
Define ('COLUMN_FOUR'	, 4);

Define ('AJAX_MODE'		, 1);
Define ('HTML_MODE'		, 2);

Define ('USER_TIMEOUT'	, 1200);
Define ('GOD_TIMEOUT'	, 60*60*24*7);

Define("PERMISSION_DEBUG"		, 0x80000000);
Define("USER_PERMISSION_GOD"	, 0x7FFFFFFFFFFFFFFF);

define('DATABASE_URL', '10.11.12.13');
define('DATABASE_NAME', vixen);
define('DATABASE_USER', vixen);
define('DATABASE_PWORD', V1x3n);

// database documentation contexts
define('CONTEXT_DEFAULT', 0);
define('CONTEXT_SUPRESS_LABEL', 1);	//Depricated
define('CONTEXT_TABLE_ROW', 2);		//Depricated
define('CONTEXT_INVALID', 10);		//Depricated
define('CONTEXT_INCLUDES_GST', 1);

define('OUTPUT_TYPE_LABEL', 1);
define('OUTPUT_TYPE_RADIO', 2);

// CSS classes
define('CLASS_DEFAULT', 'Default');

define('SYSTEM_NOTE', 7);

// Note Classes
define('NOTE_CLASS_ACCOUNT_NOTES', 1);
define('NOTE_CLASS_CONTACT_NOTES', 2);
define('NOTE_CLASS_SERVICE_NOTES', 3);

// Object Status
define('STATUS_NEW',	 	100);
define('STATUS_CLEANED', 	101);
define('STATUS_LOADED', 	102);
define('STATUS_UPDATED', 	103);
define('STATUS_MERGED', 	104);
define('STATUS_SAVED', 		105);

// HTML Template contexts - defines in which context a HTML Template will be displayed
define('HTML_CONTEXT_DEFAULT',			100);
define('HTML_CONTEXT_FULL_DETAIL',		101);
define('HTML_CONTEXT_MINIMUM_DETAIL',	102);
define('HTML_CONTEXT_LEDGER_DETAIL',	103);
define('HTML_CONTEXT_ACCOUNT_NOTE',		300);
define('HTML_CONTEXT_CONTACT_NOTE',		301);
define('HTML_CONTEXT_CONTACT_ADD',		200);
define('HTML_CONTEXT_CONTACT_EDIT',		201);

// Target Types - used to determine how to handle an ajax call
define("TARGET_TYPE_DIV", 	"Div");
define("TARGET_TYPE_POPUP",	"Popup");
define("TARGET_TYPE_PAGE",	"Page");

// PropertyToken Render method constants
define('RENDER_VALUE',	"Value");
define('RENDER_OUTPUT',	"Output");
define('RENDER_INPUT',	"Input");

// constant Employee Ids
define('SYSTEM_EMPLOYEE_ID',	0);
define('SYSTEM_EMPLOYEE_NAME', 	"Automated System");

													$arrPermissions = Array ();
define("PERMISSION_PUBLIC"				, 0x1);		$arrPermissions[PERMISSION_PUBLIC]		= "Public";			// 1
define("PERMISSION_ADMIN"				, 0x2);		$arrPermissions[PERMISSION_ADMIN]		= "Admin";			// 2	
define("PERMISSION_OPERATOR"			, 0x4);		$arrPermissions[PERMISSION_OPERATOR]	= "Operator";		// 4
define("PERMISSION_SALES"				, 0x8);		$arrPermissions[PERMISSION_SALES]		= "Sales";			// 8
define("PERMISSION_ACCOUNTS"			, 0x10);	$arrPermissions[PERMISSION_ACCOUNTS]	= "Accounts";		// 16
													$GLOBALS['Permissions']	= $arrPermissions;


?>

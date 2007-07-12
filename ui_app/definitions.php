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
Define ('PRIVILEGE_ADMIN', 2);

Define("PERMISSION_DEBUG"		, 0x80000000);
Define("USER_PERMISSION_GOD"	, 0x7FFFFFFFFFFFFFFF);

define('DATABASE_URL', '10.11.12.13');
define('DATABASE_NAME', vixen);
define('DATABASE_USER', vixen);
define('DATABASE_PWORD', V1x3n);

// database documentation contexts
define('CONTEXT_DEFAULT', 0);
define('CONTEXT_SUPRESS_LABEL', 1);
define('CONTEXT_TABLE_ROW', 2);
define('CONTEXT_INVALID', 10);

define('OUTPUT_TYPE_LABEL', 1);
define('OUTPUT_TYPE_RADIO', 2);

// CSS classes
define('CLASS_DEFAULT', 'Default');

define('SYSTEM_NOTE', 7);

// Object Status
define('STATUS_NEW',	 	100);
define('STATUS_CLEANED', 	101);
define('STATUS_LOADED', 	102);
define('STATUS_UPDATED', 	102);
define('STATUS_MERGED', 	102);
define('STATUS_SAVED', 		102);

// HTML Template contexts - defines in which context a HTML Template will be displayed
define('HTML_CONTEXT_DEFAULT',			100);
define('HTML_CONTEXT_FULL_DETAIL',		101);
define('HTML_CONTEXT_MINIMUM_DETAIL',	102);
define('HTML_CONTEXT_LEDGER_DETAIL',	103);
define('HTML_CONTEXT_ACCOUNT_NOTE',		300);
define('HTML_CONTEXT_SEANS_DETAIL',		500);

// PropertyToken Render method constants
define('RENDER_VALUE',	"Value");
define('RENDER_OUTPUT',	"Output");
define('RENDER_INPUT',	"Input");

// constant Employee Ids
define('SYSTEM_EMPLOYEE_ID',	0);
define('SYSTEM_EMPLOYEE_NAME', 	"Automated System");


?>

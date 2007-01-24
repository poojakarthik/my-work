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
define("USER_NAME"						, "Intranet_app");


// IMPORTANT NOTE !!!! - Permissions & Modules
//
// On a 64 bit system the largest usable single Permission integer is 0x4000000000000000
// Permission All is 0x7FFFFFFFFFFFFFFF

// Permissions - used for page permission
													$arrPermissions = Array ();
define("PERMISSION_PUBLIC"				, 0x1);		$arrPermissions[PERMISSION_PUBLIC]		= "Public";			// 1
define("PERMISSION_ADMIN"				, 0x2);		$arrPermissions[PERMISSION_ADMIN]		= "Admin";			// 2	
define("PERMISSION_OPERATOR"			, 0x4);		$arrPermissions[PERMISSION_OPERATOR]	= "Operator";		// 4
define("PERMISSION_SALES"				, 0x8);		$arrPermissions[PERMISSION_SALES]		= "Sales";			// 8
define("PERMISSION_ACCOUNTS"			, 0x10);	$arrPermissions[PERMISSION_ACCOUNTS]	= "Accounts";		// 16
													$GLOBALS['Permissions']	= $arrPermissions;

/*
define("PERMISSION_"					, 0x20);
define("PERMISSION_"					, 0x40);
define("PERMISSION_"					, 0x80);
define("PERMISSION_"					, 0x100);
define("PERMISSION_"					, 0x200);
define("PERMISSION_"					, 0x400);
define("PERMISSION_"					, 0x800);
define("PERMISSION_"					, 0x1000);
define("PERMISSION_"					, 0x2000);
define("PERMISSION_"					, 0x4000);
define("PERMISSION_"					, 0x8000);
define("PERMISSION_"					, 0x10000);
define("PERMISSION_"					, 0x20000);
define("PERMISSION_"					, 0x40000);
define("PERMISSION_"					, 0x80000);
define("PERMISSION_"					, 0x100000);
define("PERMISSION_"					, 0x200000);
define("PERMISSION_"					, 0x400000);
define("PERMISSION_"					, 0x800000);
define("PERMISSION_"					, 0x1000000);
define("PERMISSION_"					, 0x2000000);
define("PERMISSION_"					, 0x4000000);
define("PERMISSION_"					, 0x8000000);
define("PERMISSION_"					, 0x10000000);
define("PERMISSION_"					, 0x20000000);
define("PERMISSION_"					, 0x40000000);
*/

define("PERMISSION_DEBUG"				, 0x80000000);

// Maximum single Permission
define("PERMISSION_MAXIMUM"				, 0x4000000000000000); // 4611686018427387904

// Admin User Permissions
define("USER_PERMISSION_ADMIN"			, 0xFFFF);

// All User Permissions
define("USER_PERMISSION_ALL"			, 0x7FFFFFFFFFFFFFFF);
define("USER_PERMISSION_GOD"			, 0x7FFFFFFFFFFFFFFF); // 9223372036854775807


// Modules
define("MODULE_AUDIT"					, 0x1);
define("MODULE_SEARCH"					, 0x2);
define("MODULE_ACCOUNT"					, 0x4);
define("MODULE_ACCOUNT_GROUP"			, 0x8);
define("MODULE_CUSTOMER_GROUP"			, 0x10);
define("MODULE_CREDIT_CARD"				, 0x20);
define("MODULE_CHARGE"					, 0x40);
define("MODULE_CDR"						, 0x80);
define("MODULE_NOTE"					, 0x100);
define("MODULE_BILLING"					, 0x200);
define("MODULE_CHARGE"					, 0x400);
define("MODULE_CONTACT"					, 0x800);
define("MODULE_RATE"					, 0x1000);
define("MODULE_SERVICE"					, 0x2000);
define("MODULE_INVOICE"					, 0x4000);
define("MODULE_CARRIER"					, 0x8000);
define("MODULE_PROVISIONING"			, 0x10000);
define("MODULE_RECORD_TYPE"				, 0x20000);
define("MODULE_DOCUMENTATION"			, 0x40000);
define("MODULE_CHARGE_TYPE"				, 0x80000);
define("MODULE_RECURRING_CHARGE"		, 0x100000);
define("MODULE_RATE_GROUP"				, 0x200000);
define("MODULE_RATE_PLAN"				, 0x400000);
define("MODULE_EMPLOYEE"				, 0x800000);
define("MODULE_DIRECT_DEBIT"			, 0x1000000);
define("MODULE_PAYMENT"					, 0x2000000);
define("MODULE_SERVICE_TOTAL"			, 0x4000000);
define("MODULE_TIP"						, 0x8000000);
define("MODULE_PERMISSION"				, 0x10000000);
define("MODULE_BUG"						, 0x20000000);
define("MODULE_REPORT"					, 0x40000000);

define("MODULE_MAXIMUM"					, 0x4000000000000000);
define("MODULE_ALL"						, 0x7FFFFFFFFFFFFFFF);
?>

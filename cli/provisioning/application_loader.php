<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// application_loader
//----------------------------------------------------------------------------//
/**
 * application_loader
 *
 * Handles loading of applications
 *
 * Loads the base classes and sets up the application framework
 *
 * @file		application_loader.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

//----------------------------------------------------------------//
// strFrameworkDir
//----------------------------------------------------------------//
/**
 * strFrameworkDir
 *
 * full path to the framework directory
 *
 * full path to the framework directory, including trailing slash /
 *
 * @type	string
 * @variable
 * @package	framework
 */
 $strFrameworkDir		= SHARED_BASE_PATH."framework/";

//----------------------------------------------------------------//
// strApplicationDir
//----------------------------------------------------------------//
/**
 * strApplicationDir
 *
 * full path to the application directory
 *
 * full path to the application directory, including trailing slash /
 *
 * @type	string
 * @variable
 * @package	framework
 */
 $strApplicationDir		= "";

//----------------------------------------------------------------//
// strWebDir
//----------------------------------------------------------------//
/**
 * strWebDir
 *
 * full path to the application web directory
 *
 * full path to the application web directory, including trailing slash /
 *
 * @type	string
 * @variable
 * @package	framework
 */
 $strWebDir		= "";

//----------------------------------------------------------------//
// strModuleDir
//----------------------------------------------------------------//
/**
 * strModuleDir
 *
 * full path to the provisioning module web directory
 *
 * full path to the provisioning module web directory, including trailing slash /
 *
 * @type	string
 * @variable
 * @package	framework
 */
 $strModuleDir		= $strApplicationDir."modules/";



//----------------------------------------------------------------------------//
// LOGIC
//----------------------------------------------------------------------------//

// load framework
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");
require_once($strFrameworkDir."psxlsgen.php");
require_once($strFrameworkDir."mail_attachment.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

// load modules
require_once($strModuleDir."base_import_module.php");
require_once($strModuleDir."base_export_module.php");

require_once($strModuleDir."unitel/legacy/export_module_unitel_order.php");
require_once($strModuleDir."unitel/legacy/export_module_unitel_preselection.php");
require_once($strModuleDir."unitel/legacy/export_module_unitel_voicetalk_order.php");
require_once($strModuleDir."unitel/legacy/export_module_unitel_voicetalk_preselection.php");
require_once($strModuleDir."unitel/legacy/import_module_unitel_order.php");
require_once($strModuleDir."unitel/legacy/import_module_unitel_preselection.php");
require_once($strModuleDir."unitel/legacy/import_module_unitel_status.php");

require_once($strModuleDir."optus/export_module_optus_preselection.php");
require_once($strModuleDir."optus/export_module_optus_preselect_reverse.php");
require_once($strModuleDir."optus/export_module_optus_bar.php");
require_once($strModuleDir."optus/export_module_optus_restore.php");
require_once($strModuleDir."optus/export_module_optus_suspend.php");
require_once($strModuleDir."optus/import_module_optus_status.php");

require_once($strModuleDir."aapt/export_module_aapt_eoe.php");
require_once($strModuleDir."aapt/import_module_aapt_eoe.php");
require_once($strModuleDir."aapt/import_module_aapt_lsd.php");
require_once($strModuleDir."aapt/import_module_aapt_reject.php");

//require_once($strModuleDir."module_optus.php");
//require_once($strModuleDir."module_aapt.php");

// load application
require_once($strApplicationDir."definitions.php");
require_once($strApplicationDir."config.php");
require_once($strApplicationDir."application.php");

 ?>

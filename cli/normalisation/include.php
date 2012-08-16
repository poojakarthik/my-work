<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// web_loader
//----------------------------------------------------------------------------//
/**
 * web_loader
 *
 * Handles web based loading of applications
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
 $strFrameworkDir		= "../framework/"; 
 
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



//----------------------------------------------------------------------------//
// LOGIC
//----------------------------------------------------------------------------//

// no timeout
set_time_limit(0);

// load framework
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

// load application modules
require_once($strApplicationDir."normalisation_modules/base_module.php");
require_once($strApplicationDir."normalisation_modules/module_aapt.php");
require_once($strApplicationDir."normalisation_modules/module_commander.php");
require_once($strApplicationDir."normalisation_modules/module_iseek.php");
require_once($strApplicationDir."normalisation_modules/module_optus.php");
require_once($strApplicationDir."normalisation_modules/module_rslcom.php");

// load application 
require_once($strApplicationDir."definitions.php");
require_once($strApplicationDir."config.php");
 
 ?>

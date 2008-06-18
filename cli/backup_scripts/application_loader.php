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
 $strFrameworkDir		= "/usr/share/vixen/framework/"; 
 
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
 $strApplicationDir		= "/usr/share/vixen/backup_scripts/"; 
 
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

// load application 
//require_once($strApplicationDir."definitions.php");
//require_once($strApplicationDir."config.php");
require_once($strApplicationDir."application.php");
 
 ?>

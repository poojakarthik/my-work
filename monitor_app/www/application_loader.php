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
 $strFrameworkDir		= "../../framework/"; 
 
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
 $strApplicationDir		= "../";
 
//----------------------------------------------------------------//
// strVixenBaseDir
//----------------------------------------------------------------//
/**
 * strVixenBaseDir
 *
 * full path to the viXen base directory
 *
 * full path to the viXen base directory, including trailing slash /
 *
 * @type	string
 * @variable
 * @package	framework
 */
 $strVixenBaseDir	= "../../";
 
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

// load framework
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

// load application 
require_once($strApplicationDir."definitions.php");
require_once($strApplicationDir."config.php");
require_once($strApplicationDir."database_define.php");
require_once($strApplicationDir."application.php");

// load page classes
require_once('page.php');
require_once('monitor_page.php');

// normalisation modules
$strNormalisationDir = $strVixenBaseDir."normalisation_app/";
require_once($strNormalisationDir."normalisation_modules/base_module.php");
require_once($strNormalisationDir."normalisation_modules/module_aapt.php");
require_once($strNormalisationDir."normalisation_modules/module_commander.php");
require_once($strNormalisationDir."normalisation_modules/module_iseek.php");
require_once($strNormalisationDir."normalisation_modules/module_optus.php");
require_once($strNormalisationDir."normalisation_modules/module_rslcom.php");

//rating engine
require_once($strVixenBaseDir."rating_app/application.php");

// Create an Instance of the Rating Engine
$arrConfig['Reporting'] = FALSE;
$appRating = new ApplicationRating($arrConfig);

// Create an Instance of the Page object
$objPage = new MonitorPage($arrConfig);
 
 ?>

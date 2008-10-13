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

// Before we do anything is check that we're using Firefox
if (stristr ($_SERVER ['HTTP_USER_AGENT'], 'Firefox') === FALSE && stristr ($_SERVER ['HTTP_USER_AGENT'], 'Prism') === FALSE && stristr ($_SERVER ['HTTP_USER_AGENT'], 'Safari') === FALSE)
{
	echo "<p>Firefox Only - <a href='http://www.getfirefox.com/'>Get Firefox</a></p>";
	echo "<p>Your browser identifies itself as: " . $_SERVER ['HTTP_USER_AGENT'] . ".</p>";
	exit;
}

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
 $thisDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
 $strFrameworkDir		= $thisDir."../../../lib/framework/"; 
 
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
// strObLibDir
//----------------------------------------------------------------//
/**
 * strObLibDir
 *
 * full path to the oblib class directory
 *
 * full path to the oblib class directory, including trailing slash /
 *
 * @type	string
 * @variable
 * @package	framework
 */
 $strObLibDir		= $thisDir."../../oblib/"; 
 
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

Define ('FLEX_SESSION_NAME',	'flex_admin_sess_id');

//LoadFramework();
require_once($thisDir."../../../flex.require.php");

// load application 
require_once($strApplicationDir."definitions.php");
require_once($strApplicationDir."config.php");

 ?>

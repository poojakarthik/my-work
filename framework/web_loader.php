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
 * @file		web_loader.php
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
// strConfigFile
//----------------------------------------------------------------//
/**
 * strConfigFile
 *
 * full path to the config file
 *
 * full path to the config file
 *
 * @type	string
 * @variable
 * @package	framework
 */
$strConfigFile		= ""; 

//----------------------------------------------------------------//
// strDefinitionFile
//----------------------------------------------------------------//
/**
 * strDefinitionFile
 *
 * full path to the definition file
 *
 * full path to the definition file
 *
 * @type	string
 * @variable
 * @package	framework
 */
$strDefinitionFile	= "";

//----------------------------------------------------------------//
// strDatabaseDefinitionFile
//----------------------------------------------------------------//
/**
 * strDatabaseDefinitionFile
 *
 * full path to the database table definition file
 *
 * full path to the database table definition file
 *
 * @type	string
 * @variable
 * @package	framework
 */
$strDatabaseDefinitionFile	= "";

//----------------------------------------------------------------//
// strFrameworkFile
//----------------------------------------------------------------//
/**
 * strFrameworkFile
 *
 * full path to the framework file
 *
 * full path to the framework file
 *
 * @type	string
 * @variable
 * @package	framework
 */
$strFrameworkFile	= "framework.php";



//----------------------------------------------------------------------------//
// LOGIC
//----------------------------------------------------------------------------//

// load config file
// require_once($strConfigFile);
 
// load definition file
// require_once($strDefinitionFile);

// load database define file
// require_once($strDatabaseDefinitionFile);

// load framework
require_once($strFrameworkFile);

// create a framework instance
//fwkFramework = new Framework();

// load application 
// require_once($strDatabaseDefinitionFile);
 
 ?>

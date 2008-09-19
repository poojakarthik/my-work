<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// flex
//----------------------------------------------------------------------------//
/**
 * flex
 *
 * The main page loading script
 *
 * The main page loading script
 * Executes a method of an ApplicationTemplate
 *
 * @file		flex.php
 * @language	PHP
 * @package		web_app
 * @author		Jared
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

Define ('FLEX_SESSION_NAME',	'flex_cust_sess_id');

// Work out the application template and method from the URL
// takes a URL like : http://.../vixen.php/ApplicationTemplate/Method/?Object.Property=Value
$arrScript 		= explode('.php', $_SERVER['PHP_SELF'], 2);
$strScript 		= ltrim($arrScript[1], '/'); 
$arrScript 		= explode('/', $strScript);
$strTemplate 	= $arrScript[0];
$strMethod 		= $arrScript[1];

// Include the Flax class
require_once("../../lib/classes/Flex.php");

// Load Framework
Flex::load();

// load framework
require_once('require.php');

// instanciate application
$Application = Application::instance();

// load application
$TemplateName = $strTemplate .".". $strMethod;
$Application->WebLoad($TemplateName);
?>

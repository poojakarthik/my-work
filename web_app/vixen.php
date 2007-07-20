<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// vixen
//----------------------------------------------------------------------------//
/**
 * vixen
 *
 * The main page loading script
 *
 * The main page loading script
 * Executes a method of an ApplicationTemplate
 *
 * @file		vixen.php
 * @language	PHP
 * @package		web_app
 * @author		Jared
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Work out the application template and method from the URL
// takes a URL like : http://.../vixen.php/ApplicationTemplate/Method/?Object.Property=Value
$arrScript 		= explode('.php', $_SERVER['PHP_SELF'], 2);
$strScript 		= ltrim($arrScript[1], '/'); 
$arrScript 		= explode('/', $strScript);
$strTemplate 	= $arrScript[0];
$strMethod 		= $arrScript[1];

// load framework
require_once('require.php');

// instanciate application
$Application = Singleton::Instance('Application');

// load application
$TemplateName = $strTemplate .".". $strMethod;
$Application->Load($TemplateName);


?>

<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// framework.php
//----------------------------------------------------------------------------//
/**
 * framework
 *
 * Defines the framework classes for ui_app
 *
 * Defines the framework classes for ui_app
 *
 * @file		framework.php
 * @language	PHP
 * @package		framework
 * @author		Jared
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$thisDir = dirname(__FILE__) . '/';
require_once $thisDir.'classes/Page.php';
require_once $thisDir.'classes/DBOFramework.php';
require_once $thisDir.'classes/DBLFramework.php';
require_once $thisDir.'classes/VixenTableFramework.php';
require_once $thisDir.'classes/Config.php';
require_once $thisDir.'classes/BrowserInfo.php';
require_once $thisDir.'classes/Validation.php';
require_once $thisDir.'classes/OutputMasks.php';
require_once $thisDir.'classes/ContextMenuFramework.php';
require_once $thisDir.'classes/AjaxFramework.php';
require_once $thisDir.'classes/HrefFramework.php';
require_once $thisDir.'classes/BreadCrumbFramework.php';

?>

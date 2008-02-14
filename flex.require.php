<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// flex.require
//----------------------------------------------------------------------------//
/**
 * flex.require
 *
 * Loads the framework
 *
 * Loads the framework
 *
 * @file		flex.require.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste' Davis
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// Load flex.cfg.php for path constants
$strPath	= dirname(__FILE__);
require_once("$strPath/flex.cfg.php");

// Load functions.php and call LoadFramework()
require_once("$strPath/lib/framework/functions.php");
LoadFramework();
?>
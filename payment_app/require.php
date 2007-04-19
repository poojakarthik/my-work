<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// require
//----------------------------------------------------------------------------//
/**
 * require
 *
 * Handles all file requirements for an application
 *
 * This file should load all files required by an application.
 * This file should not set up any objects or produce any output
 *
 * @file		require.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		7.04
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// load PEAR Mail classes
require_once("Mail.php");
require_once("Mail/mime.php");

// load modules
require_once($strApplicationDir."payment_modules/base_module.php");
require_once($strApplicationDir."payment_modules/module_billexpress.php");
require_once($strApplicationDir."payment_modules/module_bpay.php");
require_once($strApplicationDir."payment_modules/module_securepay.php");

// load application 
require_once($strApplicationDir."definitions.php");
require_once($strApplicationDir."config.php");
require_once($strApplicationDir."database_define.php");
require_once($strApplicationDir."application.php");

?>

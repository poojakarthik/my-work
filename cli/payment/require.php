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
 
// load modules
$strApplicationDir = "cli/payment/modules/";
VixenRequire($strApplicationDir."base_module.php");
VixenRequire($strApplicationDir."module_billexpress.php");
VixenRequire($strApplicationDir."module_bpay_westpac.php");
VixenRequire($strApplicationDir."module_securepay.php");

?>

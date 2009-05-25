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
 * @author		Rich 'Waste' Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// load application modules
$strApplicationDir = "cli/normalisation/modules/";
VixenRequire($strApplicationDir."base_module.php");
VixenRequire($strApplicationDir."module_aapt.php");
VixenRequire($strApplicationDir."module_commander.php");
VixenRequire($strApplicationDir."module_iseek.php");
VixenRequire($strApplicationDir."module_optus.php");
VixenRequire($strApplicationDir."module_rslcom.php");
VixenRequire($strApplicationDir."module_m2.php");
VixenRequire($strApplicationDir."module_iseek_data.php");
 ?>
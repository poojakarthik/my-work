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

$strModuleDir = "cli/billing/modules/";

// load printing modules
VixenRequire($strModuleDir."module_printing.php");
VixenRequire($strModuleDir."module_etech.php");
VixenRequire($strModuleDir."module_reports.php");

// load charge modules
VixenRequire($strModuleDir."charge_base.php");
VixenRequire($strModuleDir."charge_latepayment.php");
VixenRequire($strModuleDir."charge_nonddr.php");

 ?>
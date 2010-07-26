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

// Application modules
$strModuleDir = "cli/provisioning/modules/";

VixenRequire($strModuleDir."import_base.php");
VixenRequire($strModuleDir."export_base.php");

VixenRequire($strModuleDir."optus/import_ppr.php");

VixenRequire($strModuleDir."optus/export_bar.php");
VixenRequire($strModuleDir."optus/export_deactivate.php");
VixenRequire($strModuleDir."optus/export_preselect_reversal.php");
VixenRequire($strModuleDir."optus/export_preselect.php");
VixenRequire($strModuleDir."optus/export_unbar.php");

VixenRequire($strModuleDir."unitel/import_dsc.php");
VixenRequire($strModuleDir."unitel/import_preselection.php");
VixenRequire($strModuleDir."unitel/import_line_status.php");

VixenRequire($strModuleDir."unitel/export_preselection.php");
VixenRequire($strModuleDir."unitel/export_daily_order.php");

VixenRequire($strModuleDir."aapt/export_preselection.php");
VixenRequire($strModuleDir."aapt/export_fullservicerebill.php");
VixenRequire($strModuleDir."aapt/export_deactivation.php");

VixenRequire($strModuleDir."aapt/import_dailyevent.php");

// Remote Copy
VixenRequire("lib/framework/remote_copy.php");

require_once("Mail.php");
require_once("Mail/mime.php");
 ?>
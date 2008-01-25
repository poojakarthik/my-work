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
$strModuleDir = "provisioning_app/modules/";
/*VixenRequire($strModuleDir."base_import_module.php");
VixenRequire($strModuleDir."base_export_module.php");

VixenRequire($strModuleDir."unitel/export_module_unitel_order.php");
VixenRequire($strModuleDir."unitel/export_module_unitel_preselection.php");
VixenRequire($strModuleDir."unitel/import_module_unitel_order.php");
VixenRequire($strModuleDir."unitel/import_module_unitel_preselection.php");
VixenRequire($strModuleDir."unitel/import_module_unitel_status.php");

VixenRequire($strModuleDir."optus/export_module_optus_preselection.php");
VixenRequire($strModuleDir."optus/export_module_optus_preselect_reverse.php");
VixenRequire($strModuleDir."optus/export_module_optus_bar.php");
VixenRequire($strModuleDir."optus/export_module_optus_restore.php");
VixenRequire($strModuleDir."optus/export_module_optus_suspend.php");
VixenRequire($strModuleDir."optus/import_module_optus_status.php");

VixenRequire($strModuleDir."aapt/export_module_aapt_eoe.php");
VixenRequire($strModuleDir."aapt/import_module_aapt_eoe.php");
VixenRequire($strModuleDir."aapt/import_module_aapt_lsd.php");
VixenRequire($strModuleDir."aapt/import_module_aapt_reject.php");*/

// New Modules

VixenRequire($strModuleDir."import_base.php");
VixenRequire($strModuleDir."export_base.php");

VixenRequire($strModuleDir."unitel/import_dsc.php");
VixenRequire($strModuleDir."unitel/export_preselection.php");

 ?>
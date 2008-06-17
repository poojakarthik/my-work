<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DEFINITIONS
//----------------------------------------------------------------------------//
/**
 * config
 *
 * ApplicationConfig Definitions
 *
 * This file exclusively declares application config
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//
VixenRequire('cli/provisioning/modules/export_base.php');
VixenRequire('cli/provisioning/modules/import_base.php');

VixenRequire('cli/provisioning/modules/optus/export_bar.php');
VixenRequire('cli/provisioning/modules/optus/export_deactivate.php');
VixenRequire('cli/provisioning/modules/optus/export_preselect_reversal.php');
VixenRequire('cli/provisioning/modules/optus/export_preselect.php');
VixenRequire('cli/provisioning/modules/optus/export_unbar.php');
VixenRequire('cli/provisioning/modules/optus/import_ppr.php');

VixenRequire('cli/provisioning/modules/unitel/export_daily_order.php');
VixenRequire('cli/provisioning/modules/unitel/export_preselection.php');
VixenRequire('cli/provisioning/modules/unitel/import_dsc.php');
VixenRequire('cli/provisioning/modules/unitel/import_line_status.php');
VixenRequire('cli/provisioning/modules/unitel/import_preselection.php');
?>

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
 
 // Collection modules
VixenRequire("cli/collection/modules/module_base.php");
VixenRequire("cli/collection/modules/module_local.php");
VixenRequire("cli/collection/modules/module_ftp.php");
VixenRequire("cli/collection/modules/module_aapt.php");
VixenRequire("cli/collection/modules/module_optus.php");
VixenRequire("cli/collection/modules/module_ssh.php");

VixenRequire("cli/collection/modules/module_fopen.php");
VixenRequire("cli/collection/modules/module_fopen_sftp.php");
?>
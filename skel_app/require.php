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
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// Define File Locations
Define ('VIXEN_BASE_DIR', "../");
Define ('VIXEN_APP_DIR', "");

// Framework Requirements
require_once(VIXEN_BASE_DIR."framework/require.php");

// Application Requirements
require_once(VIXEN_APP_DIR."application.php");
require_once(VIXEN_APP_DIR."definitions.php");
require_once(VIXEN_APP_DIR."config.php");

// Module Requirements
// OPTIONAL
 
 ?>

<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-7 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DEFINITIONS
//----------------------------------------------------------------------------//
/**
 * config
 *
 * Per-Customer Config Definitions
 *
 * This file exclusively declares global config constants
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.07
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

// Data Access constants
// run setup_scripts/config.sh as root to add a default config file
if (!@include_once("/etc/vixen/vixen.conf"))
{
	echo "Missing config script";
	die;
}

$GLOBALS['**arrVixenConfig'] = Array();

?>

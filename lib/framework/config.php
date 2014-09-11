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

// Load viXen/Flex Global Config File
$sPath = dirname(dirname(dirname(__FILE__))) . "/flex.cfg.php";
if (!@include_once($sPath)) {
	echo "\nFATAL ERROR: Unable to find Flex configuration file at location '$sPath'\n\n";
	die;
}

// Set PHP Timezone
if (defined('FLEX_LOCAL_TIMEZONE')) {
	if (!putenv("TZ=".FLEX_LOCAL_TIMEZONE)) {
		// Unable to set timezone
		// Do we want to do anything here?
	}
}

?>

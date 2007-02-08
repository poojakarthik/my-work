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
 * Config Definitions
 *
 * This file exclusively declares global config constants
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

 
// Reporting constants
define("AUTOMATED_REPORT_HEADER", 	"===================================\n" .
									"THIS IS AN AUTOMATED REPORT MESSAGE\n" .
									"===================================\n\n");
									
define("AUTOMATED_REPORT_FOOTER", 	"\n -- END OF REPORT --\n");

// Data Access constants
// MOVED TO /etc/vixen/vixen.conf
// run setup_scripts/config.sh as root to add a default config file
if (!@include_once("/etc/vixen/vixen.conf"))
{
	echo "Missing config script";
	Die;
}


define("DATABASE_ERROR_TABLE", "Error");

define("PATH_PAYMENT_UPLOADS"			, "/home/vixen_payments/");
?>

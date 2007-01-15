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
define("DATABASE_URL", "10.11.12.13");
define("DATABASE_NAME", "vixen");
define("DATABASE_USER", "vixen");
define("DATABASE_PWORD", "V1x3n");

define("DATABASE_ERROR_TABLE", "Error");

define("PATH_PAYMENT_UPLOADS"			, "/home/vixen_payments/");
?>

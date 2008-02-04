<?php

//----------------------------------------------------------------------------//
// FLEX SYSTEM GLOBAL CONFIGURATION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// GENERAL CONSTANTS
//----------------------------------------------------------------------------//
define("CUSTOMER_URL_NAME"				, "flexdemodev");
define("VIXEN_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".yellowbilling.com.au/html/");
define("BACKEND_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".yellowbilling.com.au/cli/");
define("FRONTEND_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".yellowbilling.com.au/html/");
define("SHARED_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".yellowbilling.com.au/html/");
define("FLEX_LOCAL_TIMEZONE"			, "Australia/Brisbane");

//----------------------------------------------------------------------------//
// DATABASE CONFIG
//----------------------------------------------------------------------------//
$GLOBALS['**arrDatabase']['URL']		= "10.11.12.13";
$GLOBALS['**arrDatabase']['User']		= "vixen";
$GLOBALS['**arrDatabase']['Password']	= "V1x3n";
$GLOBALS['**arrDatabase']['Database']	= "vixenworking";
$GLOBALS['**arrDatabase']['Timezone']	= "Australia/Brisbane";
//$GLOBALS['**arrDatabase']['Port']		= "";


?>
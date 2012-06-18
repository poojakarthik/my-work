<?php

//----------------------------------------------------------------------------//
// FLEX SYSTEM GLOBAL CONFIGURATION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// GENERAL CONSTANTS
//----------------------------------------------------------------------------//
define("CUSTOMER_URL_NAME"				, "rforrester");
define("FLEX_BASE_PATH"					, "/data/www/".CUSTOMER_URL_NAME.".ybs.net.au/trunk/");
define("BACKEND_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".ybs.net.au/trunk/cli/");
define("FRONTEND_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".ybs.net.au/trunk/html/");
define("SHARED_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".ybs.net.au/trunk/lib/");
define("FILES_BASE_PATH"				, "/data/www/".CUSTOMER_URL_NAME.".ybs.net.au/trunk/files/");
define("FLEX_LOCAL_TIMEZONE"			, "Australia/Brisbane");

//----------------------------------------------------------------------------//
// DATABASE CONFIG
//----------------------------------------------------------------------------//
//
//$GLOBALS['**arrDatabase']['URL']		= ($GLOBALS['**arrDatabase']['URL'])		? $GLOBALS['**arrDatabase']['URL']		: "10.50.50.132";
//$GLOBALS['**arrDatabase']['User']		= ($GLOBALS['**arrDatabase']['User'])		? $GLOBALS['**arrDatabase']['User']		: "flex_telcoblue";
//$GLOBALS['**arrDatabase']['Password']	= ($GLOBALS['**arrDatabase']['Password'])	? $GLOBALS['**arrDatabase']['Password']	: 'fl3Xyb$_telcoblue';
//$GLOBALS['**arrDatabase']['Database']	= ($GLOBALS['**arrDatabase']['Database'])	? $GLOBALS['**arrDatabase']['Database']	: "flex_telcoblue";
//$GLOBALS['**arrDatabase']['Timezone']	= "Australia/Brisbane";
//$GLOBALS['**arrDatabase']['Port']		= "";
//$GLOBALS['**arrDatabase']['URL']        = "10.50.50.132";
//$GLOBALS['**arrDatabase']['User']       = "flex_telcoblue";
//$GLOBALS['**arrDatabase']['Password']   = 'fl3Xyb$_telcoblue';
//$GLOBALS['**arrDatabase']['Database']   = "flex_telcoblue";

// Normal user with read/write access
$GLOBALS['**arrDatabase']['flex']['Type']       = "mysqli";
//$GLOBALS['**arrDatabase']['flex']['URL']        = "192.168.2.223";
$GLOBALS['**arrDatabase']['flex']['URL']        = "localhost";
$GLOBALS['**arrDatabase']['flex']['User']       = "dev_admin";
$GLOBALS['**arrDatabase']['flex']['Password']   = 'LsZGYEXLApxPFByX';
$GLOBALS['**arrDatabase']['flex']['Database']   = "dev_flex";
$GLOBALS['**arrDatabase']['flex']['Timezone']   = FLEX_LOCAL_TIMEZONE;

// Admin user should have permissions to add/drop/alter tables, as well as having read/write access
$GLOBALS['**arrDatabase']['admin']		= $GLOBALS['**arrDatabase']['flex'];
/*
$GLOBALS['**arrDatabase']['admin']['Type']      = "mysqli";
$GLOBALS['**arrDatabase']['admin']['URL']       = "10.50.50.236";
$GLOBALS['**arrDatabase']['admin']['User']      = "ybs_admin";
$GLOBALS['**arrDatabase']['admin']['Password']  = "4fc73c2dc7a77382c9a9c5c61ac196d2";
$GLOBALS['**arrDatabase']['admin']['Database']  = "flex_telcoblue";
$GLOBALS['**arrDatabase']['admin']['Timezone']  = FLEX_LOCAL_TIMEZONE;
*/
$GLOBALS['**arrDatabase']['admin']['DataModel']	= false;

// Normal user with read access (there is currently no requirement for write access)
/*$GLOBALS['**arrDatabase']['cdr']['Type']        = "pgsql";
$GLOBALS['**arrDatabase']['cdr']['URL']         = "10.50.50.236";
$GLOBALS['**arrDatabase']['cdr']['User']        = "flex_telcoblue";
$GLOBALS['**arrDatabase']['cdr']['Password']    = '4c3c33738a5fc5492105ee3263a414d3';
$GLOBALS['**arrDatabase']['cdr']['Database']    = "flex_telcoblue_cdr";
$GLOBALS['**arrDatabase']['cdr']['Timezone']    = FLEX_LOCAL_TIMEZONE;
$GLOBALS['**arrDatabase']['cdr']['DataModel']	= false;
*/
// Normal user with read access (there is currently no requirement for write access)
$GLOBALS['**arrDatabase']['sales']['Type']        = "pgsql";
$GLOBALS['**arrDatabase']['sales']['URL']         = "localhost";                                   
$GLOBALS['**arrDatabase']['sales']['User']        = "flex_dev";
$GLOBALS['**arrDatabase']['sales']['Password']    = 'LsZGYEXLApxPFByX';
$GLOBALS['**arrDatabase']['sales']['Database']    = "sp_rdavis";
$GLOBALS['**arrDatabase']['sales']['Timezone']    = FLEX_LOCAL_TIMEZONE;
$GLOBALS['**arrDatabase']['sales']['DataModel']    = false;

//----------------------------------------------------------------------------//
// Encryption Key
//----------------------------------------------------------------------------//
$GLOBALS['**arrCustomerConfig']['Key']      = 'fl3X_telcoB!ue_S#cUr3';

//----------------------------------------------------------------------------//
// CREDIT CARD STUFF
//----------------------------------------------------------------------------//
//
// Flag to denote Credit Card Payments are either in TEST MODE or LIVE (if this constant is not defined (or defined but set to TRUE), then it assumes it is in test mode)
define('CREDIT_CARD_PAYMENT_TEST_MODE', TRUE);

?>

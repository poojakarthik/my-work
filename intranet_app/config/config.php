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

// Modules
$arrConfig['Modules'][MODULE_AUDIT] 			= "audit";
$arrConfig['Modules'][MODULE_SEARCH] 			= "search";
$arrConfig['Modules'][MODULE_ACCOUNT] 			= "accounts";
$arrConfig['Modules'][MODULE_ACCOUNT_GROUP] 	= "accountgroups";
$arrConfig['Modules'][MODULE_CUSTOMER_GROUP] 	= "customergroup";
$arrConfig['Modules'][MODULE_CREDIT_CARD] 		= "creditcard";
$arrConfig['Modules'][MODULE_CHARGE] 			= "charges";
$arrConfig['Modules'][MODULE_CDR] 				= "CDRs";
$arrConfig['Modules'][MODULE_NOTE] 				= "notes";
$arrConfig['Modules'][MODULE_BILLING] 			= "billing";
$arrConfig['Modules'][MODULE_CHARGE] 			= "charges";
$arrConfig['Modules'][MODULE_CONTACT] 			= "contacts";
$arrConfig['Modules'][MODULE_RATE] 				= "rates";
$arrConfig['Modules'][MODULE_SERVICE] 			= "service";
$arrConfig['Modules'][MODULE_INVOICE] 			= "invoices";
$arrConfig['Modules'][MODULE_CARRIER] 			= "carrier";
$arrConfig['Modules'][MODULE_PROVISIONING] 		= "provisioning";
$arrConfig['Modules'][MODULE_RECORD_TYPE] 		= "recordtype";
$arrConfig['Modules'][MODULE_DOCUMENTATION] 	= "documentation";

// Base Modules
define("MODULE_BASE"			, MODULE_DOCUMENTATION);


?>

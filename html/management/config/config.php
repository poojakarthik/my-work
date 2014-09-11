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
$arrConfig['Modules'][MODULE_AUDIT] 					= "audit";
$arrConfig['Modules'][MODULE_SEARCH] 					= "search";
$arrConfig['Modules'][MODULE_ACCOUNT] 					= "accounts";
$arrConfig['Modules'][MODULE_ACCOUNT_GROUP] 			= "accountgroups";
$arrConfig['Modules'][MODULE_CUSTOMER_GROUP]		 	= "customergroup";
$arrConfig['Modules'][MODULE_CREDIT_CARD] 				= "creditcard";
$arrConfig['Modules'][MODULE_DIRECT_DEBIT] 				= "directdebit";
$arrConfig['Modules'][MODULE_CHARGE] 					= "charges";
$arrConfig['Modules'][MODULE_CHARGE_TYPE] 				= "chargetype";
$arrConfig['Modules'][MODULE_RECURRING_CHARGE] 			= "recurringcharge";
$arrConfig['Modules'][MODULE_CDR] 						= "CDRs";
$arrConfig['Modules'][MODULE_NOTE] 						= "notes";
$arrConfig['Modules'][MODULE_BILLING] 					= "billing";
$arrConfig['Modules'][MODULE_CONTACT] 					= "contacts";
$arrConfig['Modules'][MODULE_RATE] 						= "rate";
$arrConfig['Modules'][MODULE_RATE_GROUP] 				= "rategroup";
$arrConfig['Modules'][MODULE_RATE_PLAN] 				= "rateplan";
$arrConfig['Modules'][MODULE_SERVICE] 					= "service";
$arrConfig['Modules'][MODULE_SERVICE_TYPE] 				= "servicetype";
$arrConfig['Modules'][MODULE_SERVICE_ADDRESS] 			= "serviceaddress";
$arrConfig['Modules'][MODULE_INVOICE] 					= "invoices";
$arrConfig['Modules'][MODULE_CARRIER] 					= "carrier";
$arrConfig['Modules'][MODULE_PROVISIONING] 				= "provisioning";
$arrConfig['Modules'][MODULE_RECORD_TYPE] 				= "recordtype";
$arrConfig['Modules'][MODULE_DOCUMENTATION] 			= "documentation";
$arrConfig['Modules'][MODULE_EMPLOYEE]		 			= "employee";
$arrConfig['Modules'][MODULE_PAYMENT]		 			= "payments";
$arrConfig['Modules'][MODULE_SERVICE_TOTAL]	 			= "servicetotal";
$arrConfig['Modules'][MODULE_TIP]			 			= "tip";
$arrConfig['Modules'][MODULE_PERMISSION]	 			= "permission";
$arrConfig['Modules'][MODULE_BUG]			 			= "bug";
$arrConfig['Modules'][MODULE_DATA_REPORT]	 			= "datareport";
$arrConfig['Modules'][MODULE_FILE]	 					= "file";
$arrConfig['Modules'][MODULE_MOBILE_DETAIL]				= "servicemobile";
$arrConfig['Modules'][MODULE_STATE]						= "state";
$arrConfig['Modules'][MODULE_TITLE]						= "title";
$arrConfig['Modules'][MODULE_COST_CENTRE]				= "costcentre";
$arrConfig['Modules'][MODULE_INBOUND]					= "serviceinbound";


// Base Modules
define("MODULE_BASE"			, MODULE_SEARCH | MODULE_DOCUMENTATION | MODULE_ACCOUNT | MODULE_CONTACT);


?>

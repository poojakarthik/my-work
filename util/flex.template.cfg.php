<?php
// FLEX SYSTEM GLOBAL CONFIGURATION
//----------------------------------------------------------------------------//

// GENERAL CONSTANTS
//----------------------------------------------------------------------------//
define('FLEX_BASE_PATH', dirname(__FILE__) . '/');
define('BACKEND_BASE_PATH', FLEX_BASE_PATH.'cli/');
define('FRONTEND_BASE_PATH', FLEX_BASE_PATH.'html/');
define('SHARED_BASE_PATH', FLEX_BASE_PATH.'lib/');
define('FILES_BASE_PATH', FLEX_BASE_PATH.'files/');
define('FLEX_LOCAL_TIMEZONE', 'Australia/Brisbane'); // There _may_ be assumptions of Australia/Brisbane. At the very least, must be consistent across deployments sharing the same database instance

// DATABASE CONFIG
//----------------------------------------------------------------------------//
// Flex: Normal user with read/write access
$GLOBALS['**arrDatabase']['flex'] = array(
	'Type' => 'mysqli',
	'URL' => '',
	'User' => '',
	'Password' => '',
	'Database' => '',
	'Timezone' => FLEX_LOCAL_TIMEZONE
);

// Flex: Admin user should have permissions to add/drop/alter tables, as well as having read/write access
/*
$GLOBALS['**arrDatabase']['admin'] = array(
	'Type' => 'mysqli',
	'URL' => '',
	'User' => '',
	'Password' => '',
	'Database' => '',
	'Timezone' => FLEX_LOCAL_TIMEZONE,
	'DataModel' => false
);
*/
$GLOBALS['**arrDatabase']['admin'] = array_merge($GLOBALS['**arrDatabase']['flex'], array(
	'DataModel' => false
));

// OPTIONAL: Flex CDR Archive: Normal user with read access (there is currently no requirement for write access)
/*
$GLOBALS['**arrDatabase']['cdr'] = array(
	'Type' => 'pgsql',
	'URL' => '',
	'User' => '',
	'Password' => '',
	'Database' => '',
	'Timezone' => FLEX_LOCAL_TIMEZONE
);
*/

// OPTIONAL: Sales Portal: Normal user with read access (there is currently no requirement for write access)
/*
$GLOBALS['**arrDatabase']['sales'] = array(
	'Type' => 'pgsql',
	'URL' => '',
	'User' => '',
	'Password' => '',
	'Database' => '',
	'Timezone' => FLEX_LOCAL_TIMEZONE
);
*/

// Encryption Key (must be consistent between deployments sharing the same database instance)
//----------------------------------------------------------------------------//
$GLOBALS['**arrCustomerConfig']['Key'] = '[DEPLOYMENT_ENCRYPTION_KEY]';

// OPTIONAL: Invoice XML Sync
//----------------------------------------------------------------------------//
$GLOBALS['**aBillingXMLCopyDestination'] = array(
	/*
	'sHost' => '[DEPLOYMENT_UI_HOST]',
	'sUser' => '[SCP_SSH_USER]',
	'sSSHKeyfilePath' => '[SCP_SSH_IDENT_DSA_PATH]'
	*/
);

//----------------------------------------------------------------------------//
// OPTIONAL: API (frontend -> backend)
//----------------------------------------------------------------------------//
/*
$GLOBALS['**API'] = array(
    'host' => '',
    'port' => 443,
    'user' => '',
    'pass' => ''
);
*/

// Credit Card Stuff
//----------------------------------------------------------------------------//
// Flag to denote Credit Card Payments are either in TEST MODE or LIVE (if this constant is not defined (or defined but set to TRUE), then it assumes it is in test mode)
define('CREDIT_CARD_PAYMENT_TEST_MODE', true);
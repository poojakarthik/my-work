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
 * DEFINITIONS
 *
 * Global Definitions
 *
 * This file exclusively declares application constants
 *
 * @file		definitions.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// CONSTANTS
//----------------------------------------------------------------------------//

// user name
define("USER_NAME"	, "payment_app");

// payment status
define("PAYMENT_IMPORTED"				, 100);
define("PAYMENT_WAITING"				, 101);
define("PAYMENT_PAYING"					, 103);
define("PAYMENT_FINISHED"				, 150);
define("PAYMENT_BAD_IMPORT"				, 200);
define("PAYMENT_BAD_PROCESS"			, 201);
define("PAYMENT_BAD_NORMALISE"			, 202);
define("PAYMENT_CANT_NORMALISE_HEADER"	, 203);
define("PAYMENT_CANT_NORMALISE_FOOTER"	, 204);

// payment types
define("PAYMENT_TYPE_BILLEXPRESS"	, 1);
define("PAYMENT_TYPE_BPAY"			, 2);
define("PAYMENT_TYPE_CHEQUE"		, 3);
define("PAYMENT_TYPE_SECUREPAY"		, 4);
?>

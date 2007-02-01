<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		client_app
 * @author		Bashkim 'bash' Isai
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// load oblib library
require_once($strObLibDir."oblib.php");

// load accounts
require_once($strWebDir."classes/cdr/cdr.php");
require_once($strWebDir."classes/service/servicetype.php");
require_once($strWebDir."classes/service/service.php");
require_once($strWebDir."classes/unbilled/unbilledcalls.php");
require_once($strWebDir."classes/unbilled/unbilledcharges.php");
require_once($strWebDir."classes/charge/charge.php");

require_once($strWebDir."classes/invoice/invoiceservicecalls.php");
require_once($strWebDir."classes/invoice/invoiceservicecharges.php");
require_once($strWebDir."classes/invoice/invoiceservice.php");
require_once($strWebDir."classes/invoice/invoice.php");
require_once($strWebDir."classes/account/account.php");
require_once($strWebDir."classes/rateplan/rateplan.php");
// load the authentication module
require_once($strWebDir."classes/authentication/authentication.php");
require_once($strWebDir."classes/contact/authenticatedcontact.php");
require_once($strWebDir."classes/contact/contact.php");
require_once($strWebDir."classes/contact/contacts.php");

$athAuthentication = new Authentication ();

$Style = new Style ($strWebDir);
$Style->attachObject ($athAuthentication);

?>

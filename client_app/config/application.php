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
// load the object library oblib
require_once($strObLibDir."data.abstract.php");
// load the oblib primitives
require_once($strObLibDir."dataPrimitive/dataPrimitive.abstract.php");
require_once($strObLibDir."dataPrimitive/dataBoolean.class.php");
require_once($strObLibDir."dataPrimitive/dataCDATA.class.php");
require_once($strObLibDir."dataPrimitive/dataFloat.class.php");
require_once($strObLibDir."dataPrimitive/dataInteger.class.php");
require_once($strObLibDir."dataPrimitive/dataString.class.php");
require_once($strObLibDir."dataPrimitive/dataDuration.class.php");
// load the oblib objects
require_once($strObLibDir."dataObject/dataObject.abstract.php");
require_once($strObLibDir."dataObject/dataDate.class.php");
require_once($strObLibDir."dataObject/dataTime.class.php");
require_once($strObLibDir."dataObject/dataDatetime.class.php");
// load the oblib multiples
require_once($strObLibDir."dataMultiple/dataArray.class.php");
require_once($strObLibDir."dataMultiple/dataCollation.abstract.php");
require_once($strObLibDir."dataMultiple/dataCollection.abstract.php");
require_once($strObLibDir."dataMultiple/dataEnumerative.abstract.php");
require_once($strObLibDir."dataMultiple/dataSample.class.php");
// load the ObLib XSLT stylesheet module
require_once($strObLibDir."style.php");

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
// load the authentication module
require_once($strWebDir."classes/authentication/authentication.php");
require_once($strWebDir."classes/contact/authenticatedcontact.php");
require_once($strWebDir."classes/contact/contact.php");
require_once($strWebDir."classes/contact/contacts.php");

$athAuthentication = new Authentication ();

$Style = new Style ($strWebDir);
$Style->attachObject ($athAuthentication);

?>

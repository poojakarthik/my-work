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
require_once($strWebDir."classes/oblib/data.abstract.php");
// load the oblib primitives
require_once($strWebDir."classes/oblib/dataPrimitive/dataPrimitive.abstract.php");
require_once($strWebDir."classes/oblib/dataPrimitive/dataBoolean.class.php");
require_once($strWebDir."classes/oblib/dataPrimitive/dataCDATA.class.php");
require_once($strWebDir."classes/oblib/dataPrimitive/dataFloat.class.php");
require_once($strWebDir."classes/oblib/dataPrimitive/dataInteger.class.php");
require_once($strWebDir."classes/oblib/dataPrimitive/dataString.class.php");
// load the oblib objects
require_once($strWebDir."classes/oblib/dataObject/dataObject.abstract.php");
require_once($strWebDir."classes/oblib/dataObject/dataDate.class.php");
require_once($strWebDir."classes/oblib/dataObject/dataTime.class.php");
require_once($strWebDir."classes/oblib/dataObject/dataDatetime.class.php");
// load the oblib multiples
require_once($strWebDir."classes/oblib/dataMultiple/dataArray.class.php");
require_once($strWebDir."classes/oblib/dataMultiple/dataCollation.abstract.php");
require_once($strWebDir."classes/oblib/dataMultiple/dataCollection.abstract.php");
require_once($strWebDir."classes/oblib/dataMultiple/dataEnumerative.abstract.php");
require_once($strWebDir."classes/oblib/dataMultiple/dataSample.class.php");

// load accounts
require_once($strWebDir."classes/service/service.php");
require_once($strWebDir."classes/invoice/invoiceservice.php");
require_once($strWebDir."classes/invoice/invoice.php");
require_once($strWebDir."classes/account/account.php");
// load the authentication module
require_once($strWebDir."classes/authentication/authentication.php");
require_once($strWebDir."classes/contact/authenticatedcontact.php");
// load the XSLT stylesheet module
require_once($strWebDir."classes/style/style.php");

$athAuthentication = new Authentication ();

$Style = new Style ($strWebDir);
$Style->attachObject ($athAuthentication);

?>

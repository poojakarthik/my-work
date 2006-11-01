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
// load the authentication module
require_once($strApplicationDir."classes/authentication/authentication.php");
// load the XSLT stylesheet module
require_once($strApplicationDir."classes/style/style.php");

// load the object library oblib
require_once($strApplicationDir."classes/oblib/data.abstract.php");
// load the oblib primitives
require_once($strApplicationDir."classes/oblib/dataPrimitive/dataPrimitive.abstract.php");
require_once($strApplicationDir."classes/oblib/dataPrimitive/dataBoolean.class.php");
require_once($strApplicationDir."classes/oblib/dataPrimitive/dataCDATA.class.php");
require_once($strApplicationDir."classes/oblib/dataPrimitive/dataFloat.class.php");
require_once($strApplicationDir."classes/oblib/dataPrimitive/dataInteger.class.php");
require_once($strApplicationDir."classes/oblib/dataPrimitive/dataString.class.php");
// load the oblib objects
require_once($strApplicationDir."classes/oblib/dataObject/dataObject.abstract.php");
require_once($strApplicationDir."classes/oblib/dataObject/dataDate.class.php");
// load the oblib multiples
require_once($strApplicationDir."classes/oblib/dataMultiple/dataArray.class.php");
require_once($strApplicationDir."classes/oblib/dataMultiple/dataCollation.abstract.php");
require_once($strApplicationDir."classes/oblib/dataMultiple/dataCollection.abstract.php");
require_once($strApplicationDir."classes/oblib/dataMultiple/dataEnumerative.abstract.php");
require_once($strApplicationDir."classes/oblib/dataMultiple/dataSample.class.php");

$athAuthentication = new Authentication ();

$Style = new Style ($strApplicationDir);

?>

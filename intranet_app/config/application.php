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
 * @package		Skeleton_application
 * @author		Jared 'flame' Herbohn
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

// load Classes

//authentication
require ("classes/authentication/authentication.php");

//employee
require ("classes/employee/authenticatedemployee.php");
require ("classes/employee/authenticatedemployeeaudit.php");

//audit
require ("classes/audit/accountcontactaudit.php");

//searching
require ("classes/search/search.php");
require ("classes/search/searchconstraint.php");
require ("classes/search/searchorder.php");
require ("classes/search/searchresults.php");

//accounts
require ("classes/accounts/ABN.php");
require ("classes/accounts/ACN.php");
require ("classes/accounts/account.php");
require ("classes/accounts/accounts.php");

//account groups
require ("classes/accountgroups/accountgroup.php");
require ("classes/accountgroups/accountgroups.php");

//accounts
require ("classes/notes/note.php");
require ("classes/notes/notes.php");
require ("classes/notes/notetype.php");
require ("classes/notes/notetypes.php");

//contacts
require ("classes/contacts/contact.php");
require ("classes/contacts/contacts.php");

//rates, rate groups and rate plans
require ("classes/rates/rate.php");
require ("classes/rates/rategroup.php");
require ("classes/rates/rateplan.php");
require ("classes/rates/rateplans.php");
require ("classes/rates/rategroups.php");
require ("classes/rates/rates.php");

//service types
require ("classes/service/servicetype.php");
require ("classes/service/servicetypes.php");

//carriers
require ("classes/carrier/carrier.php");
require ("classes/carrier/carriers.php");

//service
require ("classes/service/serviceaddress.php");
require ("classes/service/service.php");
require ("classes/service/services.php");

//provisioning requests
require ("classes/provisioning/provisioningrequest.php");
require ("classes/provisioning/provisioningrequests.php");
require ("classes/provisioning/provisioningrequestresponse.php");

//provisioning request types
require ("classes/provisioning/provisioningrequesttype.php");
require ("classes/provisioning/provisioningrequesttypes.php");

//provisioning response types
require ("classes/provisioning/provisioningresponsetype.php");

//service provisioning log
require ("classes/provisioning/provisioninglog.php");
require ("classes/provisioning/provisioningrecord.php");

//record type information
require ("classes/recordtype/recordtype.php");
require ("classes/recordtype/recordtypes.php");

//documentation
require ("classes/documentation/documentation.php");
require ("classes/documentation/documentationentity.php");
require ("classes/documentation/documentationfield.php");

//style (intranet-specific)
require ("classes/style/intranetstyle.php");

$athAuthentication = new Authentication ();

$Style = new IntranetStyle ($strWebDir, $athAuthentication);

$docDocumentation = new Documentation ();
$docDocumentation = $Style->attachObject ($docDocumentation);

?>

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
//searching
require ("classes/search/search.php");
require ("classes/search/searchconstraint.php");
require ("classes/search/searchorder.php");
require ("classes/search/searchresults.php");
//accounts
require ("classes/accounts/account.php");
require ("classes/accounts/accountsearch.php");
//rates, rate groups and rate plans
require ("classes/rates/rate.php");
require ("classes/rates/rategroup.php");
require ("classes/rates/rateplan.php");

require ("classes/rates/rateplans.php");
require ("classes/rates/rategroups.php");
require ("classes/rates/rates.php");
//service information
require ("classes/service/servicetype.php");
//documentation
require ("classes/documentation/documentation.php");
require ("classes/documentation/documentationentity.php");
require ("classes/documentation/documentationfield.php");

$athAuthentication = new Authentication ();

$Style = new Style ($strWebDir);
$Style->attachObject ($athAuthentication);

$docDocumentation = new Documentation ();
$docDocumentation = $Style->attachObject ($docDocumentation);

?>

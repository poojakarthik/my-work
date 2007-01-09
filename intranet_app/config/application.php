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

//----------------------------------------------------------------------------//
// PRE AUTH
//----------------------------------------------------------------------------//

// sanitize page details
$arrPage['Permission']	= (int)$arrPage['Permission'];
$arrPage['Modules']		= (int)$arrPage['Modules'];
if ($arrPage['PopUp'])
{
	$arrPage['PopUp'] = TRUE;
}
else
{
	$arrPage['PopUp'] = FALSE;
}

// all pages must have a permission > 0
if ($arrPage['Permission'] == 0)
{
	// if not we die in the arse
	//TODO!!!! - build the error page
	header ("Location: permission_error.php");
	Exit;
}

//----------------------------------------------------------------------------//
// OBLIB
//----------------------------------------------------------------------------//

// load oblib library
//TODO!!!! - load oblib from a single file
//require_once($strObLibDir."oblib.php");

	//TODO!!!! - make all this a single file
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

//----------------------------------------------------------------------------//
// AUTHENTICATION
//----------------------------------------------------------------------------//

// load authentication class
require ("classes/authentication/authentication.php");

// load employee classes
require ("classes/employee/authenticatedemployee.php");
require ("classes/employee/authenticatedemployeeaudit.php");
require ("classes/employee/authenticatedemployeepriviledges.php");

// Do Authentication
$athAuthentication = new Authentication ();

// If the User is not logged into the system
if (!$athAuthentication->isAuthenticated ())
{
	// Foward to Login Interface
	if ($arrPage['PopUp'] === TRUE)
	{
		// for popup windows
		//TODO!!!! - build the error page
		header ("Location: popup_login.php");
	}
	else
	{
		// for normal pages
		header ("Location: login.php");
	}
	exit;
}

// Get user permission
$intUserPermission = $athAuthentication->AuthenticatedEmployee()->Pull('Permission')->GetValue();

// Check if the user is allowed to view this page
if (!HasPermission($intUserPermission, $arrPage['Permission']))
{
	// User has no permission... KILL THEM
	//TODO!!!! - build the error page
	header ("Location: user_permission_error.php");
	exit;
}



//----------------------------------------------------------------------------//
// LOAD CLASSES
//----------------------------------------------------------------------------//

// for each module type
foreach($arrConfig['Modules'] as $intModule=>$strLocation)
{
	// check if we need to load the module
	if HasPermission($arrPage['Modules'], $intModule)
	{
		// load all files for the module
		foreach (glob("classes/$strLocation/*.php") as $strFile)
		{
			require_once ("classes/$strLocation/$strFile");
		}
	}
}

//style (intranet-specific)
require ("classes/style/intranetstyle.php");


//----------------------------------------------------------------------------//
// SOME CRAP AT THE END OF THE FILE
//----------------------------------------------------------------------------//

$Style = new IntranetStyle ($strWebDir, $athAuthentication);

$docDocumentation = new Documentation ();
$docDocumentation = $Style->attachObject ($docDocumentation);

?>

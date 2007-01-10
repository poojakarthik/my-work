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
	// load oblib from a single file
	require_once($strObLibDir."oblib.php");

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


// Check if the authentication is needed
if (HasPermission($arrPage['Permission'], PERMISSION_PUBLIC))
{
	// This page does not require authentication
	// However, we will be restricted to only loading the documentation module 
	// if we are not authenticated. ANd we only allow this as it is required to
	// load a page
	if (!$athAuthentication->isAuthenticated ())
	{
		$arrPage['Modules'] = MODULE_DOCUMENTATION;
	}
}
else
{
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
}

/*
TODO: Uncomment and fix this ...

// Get user permission
$intUserPermission = $athAuthentication->AuthenticatedEmployee()->Pull('Priviledges')->GetValue();

// Check if the user is allowed to view this page
if (!HasPermission($intUserPermission, $arrPage['Permission']))
{
	// User has no permission... KILL THEM
	//TODO!!!! - build the error page
	header ("Location: user_permission_error.php");
	exit;
}
*/


//----------------------------------------------------------------------------//
// LOAD CLASSES
//----------------------------------------------------------------------------//

// for each module type
foreach($arrConfig['Modules'] as $intModule=>$strLocation)
{
	// check if we need to load the module
	if (HasPermission($arrPage['Modules'], $intModule))
	{
		// load all files for the module
		foreach (glob("classes/$strLocation/*.php") as $strFile)
		{
			require_once ("$strFile");
		}
	}
}

//----------------------------------------------------------------------------//
// STYLE
//----------------------------------------------------------------------------//

//style (intranet-specific)
require ("classes/style/intranetstyle.php");
$Style = new IntranetStyle ($strWebDir, $athAuthentication);


//----------------------------------------------------------------------------//
// DOCUMENTATION
//----------------------------------------------------------------------------//

$docDocumentation = new Documentation ();
$docDocumentation = $Style->attachObject ($docDocumentation);

?>

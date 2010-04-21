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

// Never Cache
// FIXME: We should probably look into a better solution for this...
/*
header( 'Expires: Mon, 20 Oct 1985 10:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Cache-Control: max-age=0', false );
header( 'Pragma: no-cache' );
*/
// sanitize page details

if (is_array($arrPage['Permission']))
{
	foreach($arrPage['Permission'] AS $mixKey=>$intPermission)
	{
		$arrPage['Permission'][$mixKey] = (int)$intPermission;
	}
}
else
{
	$arrPage['Permission']	= Array((int)$arrPage['Permission']);
}

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
foreach($arrPage['Permission'] AS $mixKey=>$intPermission)
{
	if ($intPermission == 0)
	{
		// if not we die in the arse
		header ("Location: error_permission.php");
		Exit;
	}
}

//----------------------------------------------------------------------------//
// OBLIB
//----------------------------------------------------------------------------//

// load oblib library
require_once($strObLibDir."oblib.php");

//----------------------------------------------------------------------------//
// AUTHENTICATION
//----------------------------------------------------------------------------//


// load authentication class
require_once (dirname(__FILE__).'/'."../classes/authentication/authentication.php");

// load employee classes
require_once (dirname(__FILE__).'/'."../classes/employee/authenticatedemployee.php");
require_once (dirname(__FILE__).'/'."../classes/employee/authenticatedemployeeaudit.php");
require_once (dirname(__FILE__).'/'."../classes/employee/authenticatedemployeeprivileges.php");
require_once (dirname(__FILE__).'/'."../classes/permission/permission.php");

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

// Get user permission
if (!HasPermission(PERMISSION_PUBLIC, $arrPage['Permission']))
{
	if ($athAuthentication->isAuthenticated ())
	{
		$intUserPermission = $athAuthentication->AuthenticatedEmployee()->Pull('Privileges')->getValue();
		
		// Check if the user is allowed to view this page
		if (!HasPermission($intUserPermission, $arrPage['Permission']))
		{
			// User has no permission... KILL THEM
			header ("Location: error_user_permission.php");
			exit;
		}
	}
	else
	{
		// User has no permission... KILL THEM
		header ("Location: error_user_permission.php");
		exit;
	}
}

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
		foreach (glob(dirname(__FILE__).'/'."../classes/$strLocation/*.php") as $strFile)
		{
			$strFile = realpath($strFile);
			require_once $strFile;
		}
	}
}

//----------------------------------------------------------------------------//
// STYLE
//----------------------------------------------------------------------------//

//style (intranet-specific)
require_once (dirname(__FILE__).'/'."../classes/style/intranetstyle.php");
$Style = new IntranetStyle ($strWebDir, $athAuthentication);


//----------------------------------------------------------------------------//
// DOCUMENTATION
//----------------------------------------------------------------------------//
$strFile = realpath(dirname(__FILE__).'/'."../classes/documentation/documentation.php");
require_once $strFile;
$docDocumentation = new Documentation ();
$docDocumentation = $Style->attachObject ($docDocumentation);

//----------------------------------------------------------------------------//
// PAGE LOCKING
//----------------------------------------------------------------------------//
/*
// Is this page locked?
$selLocked = new StatementSelect("Lock", "*");
if ($selLocked->Execute())
{
	// Yes, disallow access
	$Style->Output('xsl/content/locked.xsl');
	die;
}
*/
?>

<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// default
//----------------------------------------------------------------------------//
/**
 * default
 *
 * contains all ApplicationTemplate extended classes relating to Default functionality
 *
 * contains all ApplicationTemplate extended classes relating to Default functionality
 *
 * @file		default.php
 * @language	PHP
 * @package		framework
 * @author		Sean, Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateDefault
//----------------------------------------------------------------------------//
/**
 * AppTemplateDefault
 *
 * The AppTemplateDefault class
 *
 * The AppTemplateDefault class.  This incorporates all logic for all pages
 * relating to default
 *
 *
 * @package	ui_app
 * @class	AppTemplateDefault
 * @extends	ApplicationTemplate
 */
class AppTemplateDefault extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// Console INCOMPLETE
	//------------------------------------------------------------------------//
	/**
	 * Console()
	 *
	 * Performs the logic for the console.php webpage
	 * 
	 * Performs the logic for the console.php webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Console()
	{	
		
		$pagePerms = PERMISSION_PUBLIC;
		
		AuthenticatedUser()->CheckAuth();
		// Check perms
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PUBLIC);	// dies if no permissions
		//AuthenticatedUser()->PermissionOrDie(USER_PERMISSION_GOD);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			//echo "God!";
			// add in debug info
		}

				
		// Context menu options
		// context menu
		ContextMenu()->Logout();
		
		// add to breadcrumb menu
		BreadCrumb()->Console();

		// Load page
		$this->LoadPage('Default_Console');

	}
	

	
}
?>
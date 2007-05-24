<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// application_loader
//----------------------------------------------------------------------------//
/**
 * application_loader
 *
 * contains the Application class and the __autoload function
 *
 * contains the Application class and the __autoload function.
 * The __autoload function is used to dynamically include the php file
 * required to instantiate a class
 *
 * @file		application_loader.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// Definitions

Define ('TEMPLATE_BASE_DIR', "");
Define ('TEMPLATE_STYLE_DIR', "style_template/");

Define ('COLUMN_ONE'	, 1);
Define ('COLUMN_TWO'	, 2);
Define ('COLUMN_THREE'	, 3);
Define ('COLUMN_FOUR'	, 4);

require_once('functions.php');
require_once('framework.php');
$myApplication = new Application;


//------------------------------------------------------------------------//
// __autoload
//------------------------------------------------------------------------//
/**
 * __autoload()
 *
 * Dynamically loads a php file
 *
 * Dynamically loads a php file.  If it cannot be loaded then an exception
 * is thrown.
 * 
 *
 *
 *
 * @param	string	$strClassName	The class to load
 *									Note that there is a very specific format for this class name to be in.
 *									Class names must be like:
 *									ClassName					Location
 *									AppTemplateAccount			app_template/account.php
 *									HtmlTemplateAccountView		html_template/account_view.php
 *									HtmlTemplateCdrView			html_template/cdr_view.php
 *									The function explodes $strClassName on "template" to retrieve 
 *									the desired class name and its associated directory
 *									relative to TEMPLATE_BASE_DIR
 * @return	void
 *
 * @function
 */
function __autoload($strClassName)
{
	/* 	What the function currently does:
	 *		if the class is a template
	 *			load the appropriate file	
	 *		else
	 *			nothing for now
	 */		

	// retrieve the class name and its associated directory
	$arrClassName = explode("template", strtolower($strClassName));
	$strClassPath = $arrClassName[0] . "_template";
	$strClassName = $arrClassName[1];
	
	// if $strClassName couldn't be exploded on "template" then die
	if (count($arrClassName) == 1)
	{
		// The class trying to be loaded is not a template class
		// This function does not currently handle any other kinds of class
		$strErrorMsg = 	"ERROR: The class '". $strClassName.
						"' is not a template class as it does not include the keyword 'Template'. ".
						"currently the autoloader only handles template classes.";
		Debug($strErrorMsg);
		throw new Exception($strErrorMsg);
		die;  // I don't think this will ever actually be called
	}
	
	// check if a directory listing for $strClassPath has already been created
	if (!isset($GLOBALS['*arrAvailableFiles'][$strClassPath]))
	{  
		// $strClassPath has not had its directory listing loaded before, so do it now
		foreach (glob(TEMPLATE_BASE_DIR . $strClassPath . "/*.php") as $strAbsoluteFilename)
		{
			//grab the filename part
			$arrFilename = explode("/", $strAbsoluteFilename);
			$strFilename = $arrFilename[count($arrFilename)-1];
			
			// $strClassName will have to be compared with each file in the directory, therefore
			// a modified version of the filename (all lowercase and underscores removed) should be stored
			// and the actual filename should be stored
			$GLOBALS['*arrAvailableFiles'][$strClassPath]['ActualFilename'][] = $strFilename;
			$GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename'][] = strtolower(str_replace("_", "", $strFilename));
		}
	}	

	// find the file that should contain the class which needs to be loaded
	$mixClassPointer = array_search($strClassName . ".php", $GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename']);

	// check if it could be found
	if ($mixClassPointer === FALSE)
	{
		// the file containing the class could not be found.  The program should die here
		$strErrorMsg = "ERROR: Could not find the class that was trying to autoload. The class '". 
							$arrClassName[1] . "' could not be found in the directory '$strClassPath'";
		Debug($strErrorMsg);
		throw new Exception($strErrorMsg);
		die;  // I don't think this will ever actually be called
	}

	// include the php file that defines the class
	include_once(TEMPLATE_BASE_DIR . $strClassPath . "/" . $GLOBALS['*arrAvailableFiles'][$strClassPath]['ActualFilename'][$mixClassPointer]);
}

//------------------------
// application_loader.php
//------------------------

class Application
{

	function Load($strTemplateName)
	{
		//split template name
		$arrTemplate 	= explode ('.', $strTemplateName);
		$strClass 		= 'AppTemplate'.$arrTemplate[0];
		$strMethod 		= $arrTemplate[1];
		
		//Get user details (inc Permissions)
		//$this->Dbo->Session->AuthenticatedEmployee->GetDetails();
		/*???can't this be done in the framework at the same time you build the Dbo object of 	variables
		--at this stage we ahavent defined this anywhere, needs to be somewhere\
		-- could be here or lower level*/
	
		
		
		//Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		
		//Run AppTemplate
		$this->objAppTemplate->{$strMethod}();
		
		//Add context menu
		//Add in JS & CSS
		//Add in documentation
	
		// Render Page
		$this->objAppTemplate->Page->Render();
		
		/*
		??? call the render function of the page object
		??? decide where context, doco and js/css go, and also include breadcrumbs
		*/
	}
	
}

class ApplicationTemplate extends BaseTemplate
{
	
	function LoadPage($strPageName)
	{
		// create new page object
		$this->Page = new Page;
		
		// load required page
		require_once(TEMPLATE_BASE_DIR."page_template/" . strtolower($strPageName) . ".php");
	}
}

class PageTemplate extends BaseTemplate
{

}

class HtmlTemplate extends BaseTemplate
{
	
	function LoadJavascript($strFilename)
	{
		// add $strFilename to global javascript function array
		$GLOBALS['*arrJavaScript'][$strFilename] = $strFilename;
	}
}

class LayoutTemplate extends BaseTemplate
{

}


class BaseTemplate
{

}


?>

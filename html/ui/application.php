<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * contains the Application class and the __autoload function
 *
 * contains the Application class and the __autoload function.
 * The __autoload function is used to dynamically include a php file
 * required to instantiate a class
 * 
 *
 * @file		application.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

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
 * @param	string	$strClassName	Class to load
 *									Note that there is a very specific format for this class name to be in.
 *									Class names must be like:
 *									ClassName					Location
 *									AppTemplateAccount			app_template/account.php
 *									HtmlTemplateAccountView		html_template/account_view.php
 *									OR HtmlTemplateAccountView	html_template/account/view.php 
 *									If the above 2 files exist then $strClassName = HtmlTemplateAccountView
 *									will load html_template/account_view.php
 *									The function explodes $strClassName on "template" to retrieve 
 *									the desired class name and its associated directory
 *									relative to TEMPLATE_BASE_DIR.  If the file cannot be found
 *									in this directory it then tries finding it in a subdirectory
 *									matching the first word in $strClassName after the "Template" token.
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

	// Retrieve the class name and its associated directory
	if (substr($strClassName, 0, 6) == "Module")
	{
		$strClassPath = MODULE_BASE_DIR . "module";
		$strClassName = substr($strClassName, 6);
	}
	else
	{
		$arrClassName = explode("Template", $strClassName, 2);
		$strClassPath = TEMPLATE_BASE_DIR . strtolower($arrClassName[0]) . "_template";
		$strClassName = $arrClassName[1];
	}		

	// If $strClassName couldn't be exploded on "template" or "module" then die
	if (!$strClassName)
	{
		// The class trying to be loaded is not a template class
		// This function does not currently handle any other kinds of class
		return FALSE;
	}
	
	// Load a directory listing for $strClassPath
	_LoadDirectoryListing($strClassPath);

	// Find the file that should contain the class which needs to be loaded
	$mixClassPointer = array_search(strtolower($strClassName) . ".php", $GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename']);
	
	if ($mixClassPointer === FALSE)
	{
		// The file could not be found so check for a subdirectory of $strClassPath matching the first word in $strClassName
		// The ereg function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 6.0.0.
		/*
		 * $strRegex = "^[A-Z][a-z]+[A-Z]";
		 * $mixLength = ereg($strRegex, $strClassName, $regs);
		 * if ($mixLength === FALSE)
		 */
		$aMatches	= array();
		$iMatches	= preg_match("/^([A-Z][a-z]+)([A-Z])/", $strClassName, $aMatches);
		if (!$iMatches)
		{
			// The class name is only one word long therefore it couldn't possibly be in a subdirectory
			// the class's file cannot be found
			return FALSE;
		}
		
		// Subtract 1 from $mixLength as it will have included the first letter of the second word
		$mixLength--;
		
		// Grab the first word (the sub directory)
		$strSubDir = substr($strClassName, 0, $mixLength);
		$strClassPath .= strtolower("/$strSubDir");
		
		// Grab the filename
		$strClassName = substr($strClassName, $mixLength);
		
		// Load a directory listing for $strClassPath
		_LoadDirectoryListing($strClassPath);
		
		// search again for the file that should contain the class which needs to be loaded
		$mixClassPointer = array_search(strtolower($strClassName) . ".php", $GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename']);
	}
	
	// include the php file that defines the class
	if ($mixClassPointer !== FALSE)
	{
		include_once($strClassPath . "/" . $GLOBALS['*arrAvailableFiles'][$strClassPath]['ActualFilename'][$mixClassPointer]);
	}
}

//------------------------------------------------------------------------//
// _LoadDirectoryListing
//------------------------------------------------------------------------//
/**
 * _LoadDirectoryListing()
 *
 * Finds all php files in the supplied directory and loads their names into $GLOBALS['*arrAvailableFiles'][$strPath]
 *
 * Finds all php files in the supplied directory and loads their names into $GLOBALS['*arrAvailableFiles'][$strPath]
 *
 * @param	string	$strPath	path to find all available php files
 *								ie "html_template" or "html_template/account"
 * @return	void
 *
 * @function
 */
function _LoadDirectoryListing($strPath)
{
	if (!isset($GLOBALS['*arrAvailableFiles'][$strPath]))
	{ 
		$GLOBALS['*arrAvailableFiles'][$strPath]['ActualFilename'] = Array();
		$GLOBALS['*arrAvailableFiles'][$strPath]['CorrectedFilename'] = Array();	
		
		// $strClassPath has not had its directory listing loaded before, so do it now
		foreach (glob($strPath . "/*.php") as $strAbsoluteFilename)
		{
			// Grab the filename part
			$arrFilename = explode("/", $strAbsoluteFilename);
			$strFilename = $arrFilename[count($arrFilename)-1];
			
			// $strClassName will have to be compared with each file in the directory, therefore
			// a modified version of the filename (all lowercase and underscores removed) should be stored
			// and the actual filename should be stored
			$GLOBALS['*arrAvailableFiles'][$strPath]['ActualFilename'][] = $strFilename;
			$GLOBALS['*arrAvailableFiles'][$strPath]['CorrectedFilename'][] = strtolower(str_replace("_", "", $strFilename));
		}
	}
}

$thisDir = dirname(__FILE__) . '/';
require_once($thisDir.'classes/Application.php');
require_once($thisDir.'classes/ApplicationTemplate.php');
require_once($thisDir.'classes/BaseTemplate.php');
require_once($thisDir.'classes/PageTemplate.php');
require_once($thisDir.'classes/HtmlTemplate.php');
require_once($thisDir.'classes/ModuleLoader.php');
require_once($thisDir.'classes/SubmittedData.php');

?>

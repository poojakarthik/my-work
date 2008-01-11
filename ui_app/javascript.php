<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// javascript
//----------------------------------------------------------------------------//
/**
 * javascript
 *
 * Retrieves the desired javascript file
 *
 * Retrieves the desired javascript file
 * It will first look in the Customer's instance of the application, (currently not implemented)
 * then in the shared directory of the application (LOCAL_BASE_DIR) 
 * then in the shared directory of the framework (FRAMEWORK_BASE_DIR)
 * This assumes the constants LOCAL_BASE_DIR and FRAMEWORK_BASE_DIR have been declared
 * This is part of the core ui_app framework
 *
 * @file		javascript.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//------------------------------------------------------------------------//
// HasJavascriptFile
//------------------------------------------------------------------------//
/**
 * HasJavascriptFile()
 *
 * Checks if the given directory contains the given javascript file
 *
 * Checks if the given directory contains the given javascript file
 * 
 * @param	string	$strJSFile		javascript file in the form <filename>.js
 * @param	string	$strPath		directory to search for the file in
 *									ie. "dir1/dir2/dir3"
 * @return	bool					TRUE if the file was found, else FALSE
 * @function
 */
function HasJavascriptFile($strJSFile, $strPath)
{
	foreach (glob($strPath . "/*.js") as $strAbsoluteFilename)
	{
		// grab the filename part
		$arrFilename = explode("/", $strAbsoluteFilename);
		$strFilename = $arrFilename[count($arrFilename)-1];
		
		if ($strFilename == $strJSFile)
		{
			// The file has been found
			return TRUE;
		}
	}
	
	// The file has not been found
	return FALSE;
}


//------------------------------------------------------------------------//
// VixenIncludeJavascriptFile (DEPRICATED)
//------------------------------------------------------------------------//
/**
 * VixenIncludeJavascriptFile()
 *
 * Includes the javascript file declared in $_GET['File']
 *
 * Includes the javascript file declared in $_GET['File']
 * It is a precondtion that LOCAL_BASE_DIR and FRAMEWORK_BASE_DIR have been set
 * and that $_GET['File'] is the name of a javascript file including the ".js" extension
 * 
 * @return	bool	TRUE if the javascript file was found and loaded
 *					FALSE if the file could not be found
 * @function
 */
function VixenIncludeJavascriptFile()
{
	// Clean the javascript filename
	$arrRequestedFile = explode('/', $_GET['File']);
	$strRequestedFile = $arrRequestedFile[count($arrRequestedFile)-1];
	
	// If nothing has been requested, return FALSE;
	if (trim($strRequestedFile) == "")
	{
		return FALSE;
	}
	
	// Try and find the javascript file
	if (HasJavascriptFile($strRequestedFile, LOCAL_BASE_DIR . "/javascript"))
	{
		// A local js file has been found.  Include it.
		$strAbsoluteFilename = LOCAL_BASE_DIR. "/javascript/$strRequestedFile";
	}
	elseif (HasJavascriptFile($strRequestedFile, FRAMEWORK_BASE_DIR . "/javascript"))
	{
		// The file has been found in the framework.  Include it.
		$strAbsoluteFilename = FRAMEWORK_BASE_DIR. "/javascript/$strRequestedFile";
	}
	else
	{
		// The file could not be found
		return FALSE;
	}

	// Include the file
	include($strAbsoluteFilename);
	return TRUE;
}

//------------------------------------------------------------------------//
// VixenIncludeJsFiles
//------------------------------------------------------------------------//
/**
 * VixenIncludeJsFiles()
 *
 * Includes each javascript file referenced in $arrFilenames
 *
 * Includes each javascript file referenced in $arrFilenames
 * It is a precondtion that LOCAL_BASE_DIR and FRAMEWORK_BASE_DIR have been set
 * The combined contents of all the javascript files, is echoed to standard output
 * 
 * @param	array	$arrFilename		names of the javascript files to retrieve (must include the .js extension)
 * @param	bool	$bolStripComments	optional, if set to TRUE then all comments will be stripped out of the javascript
 * 										files, before they are sent to standard output
 * 
 * @return	bool	TRUE if the javascript files were found and loaded
 *					FALSE if any of the files couldn't be found
 * @function
 */
function VixenIncludeJsFiles($arrFilenames, $bolStripComments = FALSE)
{
	$arrJsFilesToInclude = Array();
	// Find each file and append its location to the array of locations
	foreach ($arrFilenames as $strFilename)
	{
		// If nothing has been requested, return FALSE;
		if (trim($strFilename) == "")
		{
			return FALSE;
		}
		
		// Try and find the javascript file
		if (HasJavascriptFile($strFilename, LOCAL_BASE_DIR . "/javascript"))
		{
			// A local js file has been found.  Include it
			$arrJsFilesToInclude[] = LOCAL_BASE_DIR. "/javascript/$strFilename";
		}
		elseif (HasJavascriptFile($strFilename, FRAMEWORK_BASE_DIR . "/javascript"))
		{
			// The file has been found in the framework.  Include it
			$arrJsFilesToInclude[] = FRAMEWORK_BASE_DIR. "/javascript/$strFilename";
		}
		else
		{
			// The file could not be found
			return FALSE;
		}
	}
	
	$strJavascriptToSend = "";
	foreach ($arrJsFilesToInclude as $strAbsoluteFilename)
	{
		// Retrieve the file
		$strContents = file_get_contents($strAbsoluteFilename);

		if ($bolStripComments)
		{
			// This regex strips all the multi line and single line comments out of the js file
			$strJavascriptToSend .= preg_replace('((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\n\s*\/\/.*))', '', $strContents);
		}
		else
		{
			// Comments are not stripped from the js file
			$strJavascriptToSend .= $strContents;
		}
		
	}
	echo $strJavascriptToSend;
	
	return TRUE;
}

?>

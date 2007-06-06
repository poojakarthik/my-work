<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// functions.php
//----------------------------------------------------------------------------//
/**
 * functions
 *
 * general functions used within ui_app
 *
 * general functions used within ui_app
 *
 * @file		functions.php
 * @language	PHP
 * @package		ui_app
 * @author		Jared, Sean
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once(TEMPLATE_STYLE_DIR.'html_elements.php');


//------------------------------------------------------------------------//
// RenderHTMLTemplate
//------------------------------------------------------------------------//
/**
 * RenderHTMLTemplate()
 *
 * Render a HTML Element
 *
 * Render a HTML Element by calling the associated function of the
 * HTMLElements class and passing in the array of parameters to use.
 *
 * @param	Array	$arrParams	The parameters to use when building the element
 *
 * @function
 */
 //DEPRECIATED
function RenderHTMLTemplate($arrParams)
{
	// With overloading
	HTMLElements()->$arrParams['Template']($arrParams);
	
}



//------------------------------------------------------------------------//
// DBO
//------------------------------------------------------------------------//
/**
 * DBO()
 *
 * Returns an instance of the Database Object Framework
 *
 * Returns an instance of the Database Object Framework
 *
 * @return	DBOFramework
 *
 * @method
 */
function DBO()
{
	return Singleton::Instance('DBOFramework');
}

//------------------------------------------------------------------------//
// DBL
//------------------------------------------------------------------------//
/**
 * DBL()
 *
 * Returns an instance of the Database Object List Framework
 *
 * Returns an instance of the Database Object List Framework
 *
 * @return	DBLFramework
 *
 * @method
 */
function DBL()
{
	return Singleton::Instance('DBLFramework');
}


//------------------------------------------------------------------------//
// ImplodeTables
//------------------------------------------------------------------------//
/**
 * ImplodeTables()
 *
 * Takes a Database Definition of a list of tables, and implodes to a FROM string
 *
 * Takes a Database Definition of a list of tables, and implodes to a FROM string
 *
 * @param	array		$arrTables		Table Definition list
 * 
 * @return	string
 *
 * @method
 */
function ImplodeTables($arrTables)
{
	$arrReturn = Array();
	
	// Make sure we are sane
	if (!is_array($arrTables))
	{
		return FALSE;
	}
	
	// Implode
	foreach ($arrTables AS $strAlias=>$arrTable)
	{
		// Name & Alias
		if ($strAlias != $arrTable['Table'])
		{
			$strTable = "{$arrTable['Table']} AS $strAlias";
		}
		else
		{
			$strTable = $arrTable['Table'];
		}
		
		// Index
		if ($arrTable['Index'])
		{
			$strTable .= " USE INDEX ({$arrTable['Index']})";
		}
		
		// add to return array
		$arrReturn[] = $strTable;
	}
	
	return implode(', ', $arrReturn);
}


//------------------------------------------------------------------------//
// AjaxRecieve INCOMPLETE
//------------------------------------------------------------------------//
/**
 * AjaxRecieve()
 * 
 * Function to act as a reciever for AJAX data.  
 * 
 * Function to act as a reciever for AJAX data. Converts to and from JSON format.
 *
 * @return	str				
 *
 * @brokenreturn
 * @comments
 * 
 * @function
 */
function _AjaxRecieve()
{
	$objJson = Json();
	// get the JSON object and decode it into an object
	$strInput = file_get_contents('php://input', 1000000);
	$strInput = $objJson->decode($strInput);
	
	// expected to return an array of data if a connection was made
	// or false if not
	return $strInput;
}

//------------------------------------------------------------------------//
// AjaxReply
//------------------------------------------------------------------------//
/**
 * AjaxReply()
 * 
 * Send data via AJAX.
 * 
 * Send data via AJAX.
 *
 * @param	array	$arrReply				The array of data to send
 *
 * @return	void 
 *
 * @function
 */
function _AjaxReply($arrReply)
{
	$objJson = Json();
	echo $objJson->encode($arrReply);
}

//------------------------------------------------------------------------//
// Config
//------------------------------------------------------------------------//
/**
 * Config()
 *
 * Returns the singleton Config object
 *
 * Returns the singleton Config object
 * Note that this will return a new Config object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	Config object
 *
 * @function
 * 
 */
function Config()
{
	$objConfig = Singleton::Instance('Config');
	return $objConfig;
}

//------------------------------------------------------------------------//
// Json
//------------------------------------------------------------------------//
/**
 * Json()
 *
 * Returns the singleton Json object
 *
 * Returns the singleton Json object
 * Note that this will return a new Json object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	Json object
 *
 * @function
 * 
 */
function Json()
{
	$objJson = Singleton::Instance('Services_JSON');
	return $objJson;
}

//------------------------------------------------------------------------//
// HTMLElements
//------------------------------------------------------------------------//
/**
 * HTMLElements()
 *
 * Returns the singleton HTMLElements object
 *
 * Returns the singleton HTMLElements object
 * Note that this will return a new HTMLElements object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	HTMLElements object
 *
 * @function
 * 
 */
function HTMLElements()
{
	$objHTMLElements = Singleton::Instance('HTMLElements');
	return $objHTMLElements;
}


//------------------------------------------------------------------------//
// PropertyToken
//------------------------------------------------------------------------//
/**
 * PropertyToken()
 *
 * Returns the singleton PropertyToken object
 *
 * Returns the singleton PropertyToken object
 * Note that this will return a new PropertyToken object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	PropertyToken object
 *
 * @function
 * 
 */
function PropertyToken()
{
	$objPropertyToken = Singleton::Instance('PropertyToken');
	return $objPropertyToken;
}

//------------------------------------------------------------------------//
// Validate
//------------------------------------------------------------------------//
/**
 * Validate()
 *
 * Validates the given value using the specified validation rule or regular expression
 *
 * Validates the given value using the specified validation rule or regular expression
 *
 * @param	string	$strValidationRule	name of the validation rule's method which must
 *										be a method of the Validation class; 
 *										OR a regex to use for validation
 * @param	mixed	$mixValue			the value to validate
 *										
 * @return	bool
 *
 * @function
 * 
 */
function Validate($strValidationRule, $mixValue)
{
	$objValidation = Singleton::Instance('Validation');
	if (method_exists($objValidation, $strValidationRule))
	{
		// use validation method
		return $objValidation->$strValidationRule($mixValue);
	}
	else
	{
		// use regex validation
		return $objValidation->RegexValidate($strValidationRule, $mixValue);
	}
}


//------------------------------------------------------------------------//
// ContextMenu
//------------------------------------------------------------------------//
/**
 * ContextMenu()
 *
 * Returns the singleton ContextMenuFramework object
 *
 * Returns the singleton ContextMenuFramework object
 *
 * @return	ContextMenuFramework object
 *
 * @function
 * 
 */
function ContextMenu()
{
	return Singleton::Instance('ContextMenuFramework');
}

//------------------------------------------------------------------------//
// BreadCrumb
//------------------------------------------------------------------------//
/**
 * BreadCrumb()
 *
 * Returns the singleton BreadCrumbsFramework object
 *
 * Returns the singleton BreadCrumbsFramework object
 *
 * @return    BreadCrumbsFramework object
 *
 * @function
 * 
 */
function BreadCrumb()
{
	return Singleton::Instance('BreadCrumbFramework');
}



//------------------------------------------------------------------------//
// AuthenticatedUser
//------------------------------------------------------------------------//
/**
 * AuthenticatedUser()
 *
 * Returns the authenticated user object
 *
 * Returns the singleton PropertyToken object
 * Note that this will return a new PropertyToken object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	PropertyToken object
 *
 * @function
 * 
 */
function AuthenticatedUser()
{
	$objAuthenticatedUser = Singleton::Instance('Application');
	return $objAuthenticatedUser;
}


?>

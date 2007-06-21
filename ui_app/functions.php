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
// Table
//------------------------------------------------------------------------//
/**
 * Table()
 *
 * Returns an instance of the TableFramework
 *
 * Returns an instance of the TableFramework
 *
 * @return	TableFramework
 *
 * @method
 */
function Table()
{
	return Singleton::Instance('VixenTableFramework');
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
// AjaxReceive INCOMPLETE
//------------------------------------------------------------------------//
/**
 * AjaxReceive()
 * 
 * Function to act as a receiver for AJAX data.  
 * 
 * Function to act as a receiver for AJAX data. Converts to and from JSON format.
 *
 * @return	str				
 *
 * @brokenreturn
 * @comments
 * 
 * @function
 */
function AjaxReceive()
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
// Href
//------------------------------------------------------------------------//
/**
 * Href()
 *
 * Returns the singleton HrefFramework object
 *
 * Returns the singleton HrefFramework object
 *
 * @return	HrefFramework object
 *
 * @function
 * 
 */
function Href()
{
	$objHrefFramework = Singleton::Instance('HrefFramework');
	return $objHrefFramework;
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
 * Returns the singleton BreadCrumbFramework object
 *
 * Returns the singleton BreadCrumbFramework object
 *
 * @return    BreadCrumbFramework object
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
	// woah, do we really need to return the entire application, or just user?
	$objAuthenticatedUser = Singleton::Instance('Application');
	return $objAuthenticatedUser;
}

//------------------------------------------------------------------------//
// IsConditionTrue
//------------------------------------------------------------------------//
/**
 * IsConditionTrue()
 *
 * Tests the condition defined in the parameters
 *
 * Tests the condition defined in the parameters
 * it will effectively test the condition 	($mixLeftValue $strOperator $mixRightValue)
 * for example								(1 "<=" 6) will return TRUE
 *
 * @param	mix		$mixLeftValue	the value that will be left of the operator used in the condition
 * @param	string	$strOperator	the operator that will be used to compare the other to parameters
 *									current acceptable operators:
 *									<, >, <=, >=, ==, !=, IsEmpty
 * @param	mix		$mixRightValue	[optional]the value that will be right of the operator used in the condition
 *
 * @return	mix						returns NULL if the condition could not be tested, ELSE
 *									it returns the result of testing the condition
 *
 * @function
 */
function IsConditionTrue($mixLeftValue, $strOperator, $mixRightValue = NULL)
{
	switch ($strOperator)
	{
		case "==":
			$bolReturn = ($mixLeftValue == $mixRightValue);
			break;
		case "!=":
			$bolReturn = ($mixLeftValue != $mixRightValue);
			break;
		case "<":
			$bolReturn = ($mixLeftValue < $mixRightValue);
			break;
		case ">":
			$bolReturn = ($mixLeftValue > $mixRightValue);
			break;
		case "<=":
			$bolReturn = ($mixLeftValue <= $mixRightValue);
			break;
		case ">=":
			$bolReturn = ($mixLeftValue >= $mixRightValue);
			break;
		case "IsEmpty":
			$bolReturn = (strlen(trim($mixLeftValue)) == 0);
			break;
		case "IsNull":
			$bolReturn = ($mixLeftValue == NULL);
			break;
		default:
			$bolReturn = NULL;
			break;
	}
	return $bolReturn;
}


function GetEmployeeName($intEmployeeId)
{
	DBO()->EmployeeName->Id = $intEmployeeId;
	DBO()->EmployeeName->SetTable("Employee");
	DBO()->EmployeeName->Load();
	
	$strEmployeeName = DBO()->EmployeeName->FirstName->Value ." ". DBO()->EmployeeName->LastName->Value;
	
	return $strEmployeeName;
}

?>

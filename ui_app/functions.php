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
// Ajax
//------------------------------------------------------------------------//
/**
 * Ajax()
 *
 * Returns the singleton AjaxFramework object
 *
 * Returns the singleton AjaxFramework object
 *
 * @return	AjaxFramework object
 *
 * @function
 * 
 */
function Ajax()
{
	$objAjaxFramework = Singleton::Instance('AjaxFramework');
	return $objAjaxFramework;
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
// GetAuthenticatedUserDBObject
//------------------------------------------------------------------------//
/**
 * GetAuthenticatedUserDBObject()
 *
 * Returns a DBObject object with employee details of the currently authenticated user
 *
 * Returns a DBObject object with employee details of the currently authenticated user
 *
 * @return	DBObject object
 *
 * @function
 * 
 */
function GetAuthenticatedUserDBObject()
{
	$intEmployeeId = AuthenticatedUser()->_arrUser['Id'];
	
	$dboUser = new DBObject("Employee");
	$dboUser->Id = $intEmployeeId;
	$dboUser->Load();
	return $dboUser;
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
 *										OR a list of comma separted method names 
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
	
	// Check if the validation rule is a REGEX or list of methods
	if (substr($strValidationRule, 0, 6) == "REGEX:")
	{
		// We are dealing with a regex.  Remove the prefix
		$strValidationRule = substr($strValidationRule, 6, strlen($strValidationRule));
		
		// Use regex validation
		return $objValidation->RegexValidate($strValidationRule, $mixValue);
	}

	// Create a list of the validation rules
	$strValidationRule = str_replace(' ', '', $strValidationRule);
	$arrValidationMethods = explode(',', $strValidationRule);

	// The value is only valid if each validation rule returns TRUE
	$bolIsValid = TRUE;
	
	// Run each validation rule
	foreach ($arrValidationMethods as $strMethod)
	{
		if (method_exists($objValidation, $strMethod))
		{
			$bolIsValid = $bolIsValid && $objValidation->$strMethod($mixValue);
		}
	}

	return $bolIsValid;
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


//------------------------------------------------------------------------//
// GetEmployeeName
//------------------------------------------------------------------------//
/**
 * GetEmployeeName()
 *
 * Retrieves the name of an employee as "FirstName LastName"
 *
 * Retrieves the name of an employee as "FirstName LastName"
 *
 * @param	int		$intEmployeeId	The Id of the employee
 * @return	mix						returns "FirstName LastName" of the employee.
 *									If the employee could not be found then it returns NULL
 *
 * @function
 */
function GetEmployeeName($intEmployeeId)
{
	// check if we have a cache of employees
	if (!isset($GLOBALS['*arrEmployee']))
	{
		// retrieve all employees from the Employee table of the database and cache it in the global array
		$selFindEmployee = new StatementSelect("Employee", "FirstName, LastName, Id");
		$selFindEmployee->Execute(NULL);
		$arrEmployees = $selFindEmployee->FetchAll();

		foreach ($arrEmployees as $arrEmployee)
		{
			$arrName['FirstName'] = $arrEmployee['FirstName'];
			$arrName['LastName'] = $arrEmployee['LastName'];
			
			// add the employee to the global employee array
			$GLOBALS['*arrEmployee'][$arrEmployee['Id']] = $arrName;
		}
	}

	// check that an employee exists with id == $intEmployeeId
	if (isset($GLOBALS['*arrEmployee'][$intEmployeeId]))
	{
		// build the employee's name
		$strName = $GLOBALS['*arrEmployee'][$intEmployeeId]['FirstName'] ." ". $GLOBALS['*arrEmployee'][$intEmployeeId]['LastName'];
	}
	else
	{
		$strName = NULL;
	}

	return $strName;
}

function SubmittedForm($strFormId, $strButtonId=NULL)
{
	if ($strFormId == $GLOBALS['*SubmittedForm'])
	{
		if ($strButtonId && $strButtonId == $GLOBALS['*SubmittedButton'])
		{
			return TRUE;
		}
		elseif (!$strButtonId)
		{
			return TRUE;
		}
	}
	return FALSE;
}


//------------------------------------------------------------------------//
// GetCurrentDateForMySQL
//------------------------------------------------------------------------//
/**
 * GetCurrentDateForMySQL()
 *
 * Retrieves the current date in the format that MySql expects Date attributes to be in
 *
 * Retrieves the current date in the format that MySql expects Date attributes to be in
 *
 * @return	mix					current date as a string, properly formatted for MySql
 *								(YYYY-MM-DD)
 *
 * @function
 */
function GetCurrentDateForMySQL()
{
	return date("Y-m-d");
}

//------------------------------------------------------------------------//
// GetCurrentTimeForMySQL
//------------------------------------------------------------------------//
/**
 * GetCurrentTimeForMySQL()
 *
 * Retrieves the current time in the format that MySql expects time attributes to be in
 *
 * Retrieves the current time in the format that MySql expects time attributes to be in
 *
 * @return	mix					current time as a string, properly formatted for MySql
 *								(HH:MM:SS)
 *
 * @function
 */
function GetCurrentTimeForMySQL()
{
	return date("H:i:s");
}

//------------------------------------------------------------------------//
// GetCurrentDateAndTimeForMySQL
//------------------------------------------------------------------------//
/**
 * GetCurrentDateAndTimeForMySQL()
 *
 * Retrieves the current date and time in the format that MySql expects datetime attributes to be in
 *
 * Retrieves the current date and time in the format that MySql expects datetime attributes to be in
 *
 * @return	mix					current date and time as a string, properly formatted for MySql
 *								(YYYY-MM-DD HH:MM:SS)
 *
 * @function
 */
function GetCurrentDateAndTimeForMySQL()
{
	return GetCurrentDateForMySQL() ." ". GetCurrentTimeForMySQL();
}

//------------------------------------------------------------------------//
// FormatAsCurrency
//------------------------------------------------------------------------//
/**
 * FormatAsCurrency()
 *
 * Formats a float as a money value
 *
 * Formats a float as a money value
 *
 * @param	float	$fltValue					value to format as a money value
 * @param	int		$intDecPlaces				optional; number of decimal places to show
 * @param	bool	$bolIncludeDollarSign		optional; should a dollar sign be included
 * @param	bool	$bolUseBracketsForNegative	optional; should brackets be used to denote a negative value
 * @return	string								$fltValue formatted as a money value
 *
 * @function
 */
function FormatAsCurrency($fltValue, $intDecPlaces=2, $bolIncludeDollarSign=FALSE, $bolUseBracketsForNegative=FALSE)
{
	if (fltValue < 0)
	{
		$bolIsNegative = TRUE;
		// Change it to a positive
		$fltValue = fltValue * (-1.0);
	}
	else
	{
		$bolIsNegative = FALSE;
	}
	
	$strValue = number_format($fltValue, $intDecPlaces, ".", "");
	
	if ($bolIsNegative)
	{
		if ($bolUseBracketsForNegative)
		{
			$strValue = '($' . $strValue . ')';
		}
		else
		{
			$strValue = '$-' . $strValue;
		}
	}
	else
	{
		$strValue = '$' . $strValue;
	}
	
	return $strValue;	
}

?>

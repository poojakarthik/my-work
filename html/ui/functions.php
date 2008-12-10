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

//------------------------------------------------------------------------//
// Framework
//------------------------------------------------------------------------//
/**
 * Framework()
 *
 * Returns an instance of the Framework class (which is located in vixen/framework/framework.php)
 *
 * Returns an instance of the Framework class (which is located in vixen/framework/framework.php)
 *
 * @return	Framework
 *
 * @method
 */
function Framework()
{
	return $GLOBALS['fwkFramework'];
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
	return DBOFramework::instance();
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
	return DBLFramework::instance();
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
	return VixenTableFramework::instance();
}

//------------------------------------------------------------------------//
//  AddServiceChurnNote
//------------------------------------------------------------------------//
/**
 * AddServiceChurnNote()
 *
 * Adds a service churn note
 * 
 * Adds a service churn note
 * 
 * @param	int	$intAccountNumber		Account the churn will affect
 * @param	int	$intAccountGroup		AccountGroup
 * @param	str	$strFNN					Service affected
 * @param	str	$strCarrier				Carrier
 *
 * @return	null
 *
 * @method
 */
Function AddServiceChurnNote($intAccountNumber, $intAccountGroup, $strFNN, $strCarrier)
{
	$strSystemChangesNote = "Service churned away on " . GetCurrentDateForMySQL() ."\n";
	$strSystemChangesNote .= "FNN: $intFNN\n";
	$strSystemChangesNote .= "Carrier: $strCarrier";
	SaveSystemNote($strSystemChangesNote, $intAccountGroup, $intAccountNumber, NULL, NULL);		
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
	return Config::instance();
}


//------------------------------------------------------------------------//
// HtmlElements
//------------------------------------------------------------------------//
/**
 * HtmlElements()
 *
 * Returns the singleton HtmlElements object
 *
 * Returns the singleton HtmlElements object
 * Note that this will return a new HtmlElements object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	HtmlElements object
 *
 * @function
 * 
 */
function HtmlElements()
{
	return HtmlElements::instance();
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
	return HrefFramework::instance();
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
	return AjaxFramework::instance();
}

//------------------------------------------------------------------------//
// Browser
//------------------------------------------------------------------------//
/**
 * Browser()
 *
 * Returns the singleton BrowserInfo object
 *
 * Returns the singleton BrowserInfo object
 *
 * @return	BrowserInfo object
 *
 * @function
 * 
 */
function Browser()
{
	return BrowserInfo::instance();
}

//------------------------------------------------------------------------//
// PermCheck
//------------------------------------------------------------------------//
/**
 * PermCheck()
 *
 * Checks a set of permissions to the permission passed in
 * 
 * Checks a set of permissions to the permission passed in
 * 
 *
 * @param		int		$intPermSet	set of permissions to check against
 * @param		int		$intPerm	permission to check set against
 * @return		bool				
 * @method
 *
 */
function PermCheck($intPermSet, $intPerm)
{
	// Do a binary 'AND' between the user's privilages and the paramerter
	$intChecked = $intPermSet & $intPerm;
	
	// If the user has all the privileges defined in $intPerms, then $intChecked will equal $intPerms
	if ($intChecked == $intPerm)
	{
		return TRUE;
	}
	
	return FALSE;
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
	return PropertyToken::instance();
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
	$objValidation = Validation::instance();
	
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
	return ContextMenuFramework::instance();
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
	return BreadCrumbFramework::instance();
}



//------------------------------------------------------------------------//
// AuthenticatedUser
//------------------------------------------------------------------------//
/**
 * AuthenticatedUser()
 *
 * Returns the authenticated user object
 *
 * Returns the singleton Application object
 *
 * @return	Application object (the user's Employee record is located in obj->_arrUser)
 *
 * @function
 * 
 */
function AuthenticatedUser()
{
	// woah, do we really need to return the entire application, or just user?
	return Application::instance();
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
 *									If the employee could not be found then it returns an empty string
 *
 * @function
 */
function GetEmployeeName($intEmployeeId)
{
	if ($intEmployeeId == USER_ID)
	{
		// System employee id
		return "Automated System";
	}
	
	// check if we have a cache of employees
	if (!isset($GLOBALS['*arrEmployee']))
	{
		// retrieve all employees from the Employee table of the database and cache it in the global array
		$selFindEmployee = new StatementSelect("Employee", "FirstName, LastName, Id, UserName");
		$selFindEmployee->Execute(NULL);
		$arrEmployees = $selFindEmployee->FetchAll();

		foreach ($arrEmployees as $arrEmployee)
		{
			$arrName['FirstName']	= $arrEmployee['FirstName'];
			$arrName['LastName']	= $arrEmployee['LastName'];
			$arrName['UserName']	= $arrEmployee['UserName'];
			
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
		$strName = "";
	}

	return $strName;
}

//------------------------------------------------------------------------//
// GetEmployeeUserName
//------------------------------------------------------------------------//
/**
 * GetEmployeeUserName()
 *
 * Retrieves the username of an employee
 *
 * Retrieves the username of an employee
 *
 * @param	int		$intEmployeeId	The Id of the employee
 * @return	mix						returns the username of the employee.
 *									If the employee could not be found then it returns NULL
 *
 * @function
 */
function GetEmployeeUserName($intEmployeeId)
{
	// Load all the employee details if they have not already been loaded
	GetEmployeeName(0);
	
	if ($intEmployeeId === USER_ID)
	{
		// System employee record does not logically have a user name
		return NULL;
	}
	
	if (isset($GLOBALS['*arrEmployee'][$intEmployeeId]))
	{
		return $GLOBALS['*arrEmployee'][$intEmployeeId]['UserName'];
	}
	return NULL;
}

//------------------------------------------------------------------------//
// SubmittedForm
//------------------------------------------------------------------------//
/**
 * SubmittedForm()
 *
 * Returns TRUE if a form submission was made
 *
 * Returns TRUE if a form submission was made
 *
 * @param	string	$strFormId		Id of the form to test
 * @param	string	$strButtonId	optional, Id of the button used to submit the form
 * @return	bool					TRUE if the form identified by strFormId has been submitted
 * 									and the button identified by $strButtonId was used to submit the form,
 * 									else FALSE
 * @function
 */
function SubmittedForm($strFormId, $strButtonId=NULL)
{
	if (array_key_exists('*SubmittedForm', $GLOBALS) && $strFormId == $GLOBALS['*SubmittedForm'])
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
// ConvertUserDateToMySqlDate
//------------------------------------------------------------------------//
/**
 * ConvertUserDateToMySqlDate()
 *
 * Converts a date from the user format (DD/MM/YYYY) to MySql Date format (YYYY-MM-DD)
 *
 * Converts a date from the user format (DD/MM/YYYY) to MySql Date format (YYYY-MM-DD)
 *
 * @param	string	$strUserDate	date in format DD/MM/YYYY
 * @return	string					date in format YYYY-MM-DD
 *
 * @function
 */
function ConvertUserDateToMySqlDate($strUserDate)
{
	$arrDate = explode("/", $strUserDate);
	return "{$arrDate[2]}-{$arrDate[1]}-{$arrDate[0]}";
}
function ConvertDateToISODate($strDate)
{
	$arrDate = explode("/", $strDate);
	if (count($arrDate) != 3)
	{
		return FALSE;
	}
	return "{$arrDate[2]}-{$arrDate[1]}-{$arrDate[0]}";
}

//------------------------------------------------------------------------//
// ConvertMySQLDateToUnixTimeStamp
//------------------------------------------------------------------------//
/**
 * ConvertMySQLDateToUnixTimeStamp()
 *
 * Converts a date to Unix Date Time format
 *
 * Converts a date to Unix Date Time format
 *
 * @param	string	$strUserDate	date in format YYYY-MM-DD
 * @return	int						unix time stamp
 *
 * @function
 */
function ConvertMySQLDateToUnixTimeStamp($strUserDate)
{
	return strtotime($strUserDate);
}

//------------------------------------------------------------------------//
// ConvertUnixTimeToMySQLDateTime
//------------------------------------------------------------------------//
/**
 * ConvertUnixTimeToMySQLDateTime()
 *
 * Converts a Unix time variable into the MySQL datetime format
 *
 * Converts a Unix time variable into the MySQL datetime format
 *
 * @return	string				$intTime as a string, properly formatted for MySql
 *								(YYYY-MM-DD HH:MM:SS)
 *
 * @function
 */
function ConvertUnixTimeToMySQLDateTime($intTime)
{
	return date("Y-m-d H:i:s", $intTime);
}



//------------------------------------------------------------------------//
// OutputMask
//------------------------------------------------------------------------//
/**
 * OutputMask()
 *
 * Returns the singleton OutputMasks object
 *
 * Returns the singleton OutputMasks object
 *
 * @return	OutputMasks object
 *
 * @function
 * 
 */
function OutputMask()
{
	return OutputMasks::instance();
}

//------------------------------------------------------------------------//
// SaveSystemNote
//------------------------------------------------------------------------//
/**
 * SaveSystemNote()
 *
 * Saves a system note to the Note table
 *
 * Saves a system note to the Note table
 *
 * @param	string		$strNote			message to save
 * @param	int			$intAccountGroup	Account Group the note applies to
 * @param 	int			$intAccount			[optional] Account the note applies to
 * @param	int			$intContact			[optional] Contact the note applies to
 * @param	int			$intService			[optional] Service the note applies to
 *
 * @return	boolean							TRUE if the note could be saved, else FALSE
 *
 * @function
 * 
 */
function SaveSystemNote($strNote, $intAccountGroup, $intAccount=NULL, $intContact=NULL, $intService=NULL)
{
	$arrNote = Array(
		'Note'			=> $strNote,
		'AccountGroup'	=> $intAccountGroup,
		'Contact'		=> $intContact,
		'Account'		=> $intAccount,
		'Service'		=> $intService,
		'Employee'		=> AuthenticatedUser()->_arrUser['Id'],
		'Datetime'		=> GetCurrentISODateTime(),
		'NoteType'		=> SYSTEM_NOTE_TYPE
	);

	$insNote = new StatementInsert("Note", $arrNote);
	
	return (bool)$insNote->Execute($arrNote);
}

//------------------------------------------------------------------------//
// LoadNotes
//------------------------------------------------------------------------//
/**
 * LoadNotes()
 *
 * Loads Notes into the DBL()->Note DBList
 *
 * Loads Notes into the DBL()->Note DBList
 * This utilises the cookies (AccountNotesFilter, ServiceNotesFilter, ContactNotesFilter)
 * if $bolUpdateCookies is set to TRUE then the appropriate cookie will be updated with the value stored in DBO()->NoteDetails->FilterOption
 * Only 1 of $intAccountId, $intServiceId and $intContactId should be set to a proper Id.  The others should be NULL.
 * If DBO()->NoteDetails->FilterOption is not set then it will try and set it using the appropriate cookie.
 * If the cookie is also not set, then it will default to NOTE_FILTER_ALL which will retrieve all types of notes.
 * If DBO()->NoteDetails->MaxNotes is not set then it will default to DEFAULT_NOTES_LIMIT
 * If you want to update the cookies, this function must be run before anything else has been sent to the client (i think)
 * This function also sets DBO()->NoteDetails->AccountNotes || DBO()->NoteDetails->ServiceNotes || DBO()->NoteDetails->ContactNotes
 * depending on what kind of notes are being retrieved
 *
 * @param	int			$intAccountId		id of the account (set to NULL if you don't want to retrieve Account notes)
 * @param 	int			$intServiceId		[optional] id of the service 
 * @param	int			$intContactId		[optional] id of the contact
 * @param	boolean		$bolUpdateCookies	[optional] Set to TRUE if you want the cookies updated
 *
 * @return	boolean							returns FALSE if it failed, else TRUE
 *
 * @function
 * 
 */
function LoadNotes($intAccountId, $intServiceId=NULL, $intContactId=NULL, $bolUpdateCookies=FALSE)
{
	if ($intAccountId == NULL && $intServiceId == NULL && $intContactId == NULL)
	{
		// One of these three should have been specified
		return FALSE;
	}

	// Build Where clause
	if ($intAccountId)
	{
		// Load account notes (including all AccountGroup notes for the account group that the Account belongs to)
		// Note:	There shouldn't ever be notes associated with an AccountGroup, but if you delete a payment which has been applied
		// 			to an AccountGroup, then the system generated note can not specify a single Account
		
		// Find the AccountGroup for this account
		$selAccountGroup = new StatementSelect("Account", "AccountGroup", "Id = <AccountId>");
		$selAccountGroup->Execute(Array("AccountId" => $intAccountId));
		$arrAccountGroup = $selAccountGroup->Fetch();
		
		DBO()->NoteDetails->AccountNotes	= TRUE;
		$arrWhere							= Array("AccountId" => $intAccountId, "AccountGroupId" => $arrAccountGroup['AccountGroup']);
		
		// The Note table of Flex has an index on AccountGroup but not on Account, 
		// doing the "AccountGroup = <AccountGroupId> AND " bit should make it use the AccountGroup index
		//(old method)$strWhere	= "(Account = <AccountId> OR (AccountGroup = <AccountGroupId> AND Account IS NULL AND Service IS NULL AND Contact IS NULL))";
		$strWhere							= "AccountGroup = <AccountGroupId> AND (Account = <AccountId> OR (Account IS NULL AND Service IS NULL AND Contact IS NULL))";
		
		$strCookiePrefix					= "Account";
	}
	elseif ($intServiceId)
	{
		// Load service notes
		DBO()->NoteDetails->ServiceNotes	= TRUE;
		
		// Get all Service Ids relating to $intServiceId
		$arrServiceIds = AppTemplateService::GetAllServiceRecordIds($intServiceId);
		
		if (!is_array($arrServiceIds))
		{
			// A database error must have occurred
			return FALSE;
		}
		if (count($arrServiceIds) == 0)
		{
			// It couldn't even return $intService as an Id
			return FALSE; 
		}
		elseif (count($arrServiceIds) == 1)
		{
			// Just one service record models this service
			$strWhere = "Service = $intServiceId";
		}
		else
		{
			// Multiple service records have been required to model this service, for this account
			$strWhere = "Service IN (". implode(", ", $arrServiceIds) .")";
		}
		
		$arrWhere			= Array();
		$strCookiePrefix	= "Service";
	}
	elseif ($intContactId)
	{
		// Load contact notes
		DBO()->NoteDetails->ContactNotes	= TRUE;
		$arrWhere							= Array("ContactId" => $intContactId);
		$strWhere							= "Contact = <ContactId>";
		$strCookiePrefix					= "Contact";
	}
	
	// Set up the filter
	if (DBO()->NoteDetails->FilterOption->Value)
	{
		// The filter has already been set
		// Update the cookie, if it needs updating
		if (($bolUpdateCookies) && (!isset($_COOKIE["{$strCookiePrefix}NotesFilter"]) || ($_COOKIE["{$strCookiePrefix}NotesFilter"] != DBO()->NoteDetails->FilterOption->Value)))
		{
			// The cookie either isn't set, or needs updating
			
			// These cookies don't really ever need to expire
			$intExpiryDate = time()+(60*60*24*365*40);  //40 years from now
			setcookie("{$strCookiePrefix}NotesFilter", DBO()->NoteDetails->FilterOption->Value, $intExpiryDate, "/");
		}
	}
	elseif (isset($_COOKIE["{$strCookiePrefix}NotesFilter"]))
	{
		// The filter was not specified, but a cookie exists so use it
		DBO()->NoteDetails->FilterOption = $_COOKIE["{$strCookiePrefix}NotesFilter"];
	}
	else
	{
		// The filter was not specified and a cookie does not exist
		// Default to not use a filter
		DBO()->NoteDetails->FilterOption = NOTE_FILTER_ALL;
	}
	
	// Build the WHERE clause
	switch (DBO()->NoteDetails->FilterOption->Value)
	{
		case NOTE_FILTER_USER:
			$strFilterWhereClause = "AND NoteType != ". SYSTEM_NOTE_TYPE;
			break;
		case NOTE_FILTER_SYSTEM:
			$strFilterWhereClause = "AND NoteType = ". SYSTEM_NOTE_TYPE;
			break;
		case NOTE_FILTER_ALL:
		default:
			$strFilterWhereClause = "";
	}
	
	$strWhere = $strWhere ." ". $strFilterWhereClause;
	
	// Set the Max number of notes to return, if it already hasn't been specified
	if (!DBO()->NoteDetails->MaxNotes->Value)
	{
		DBO()->NoteDetails->MaxNotes = DEFAULT_NOTES_LIMIT;
	}
	$intMaxNotes = DBO()->NoteDetails->MaxNotes->Value;
	
	DBL()->Note->Where->Set($strWhere, $arrWhere);
	DBL()->Note->OrderBy("Datetime DESC");
	DBL()->Note->SetLimit($intMaxNotes);
	
	// Load the notes
	DBL()->Note->Load();
	
	return TRUE;
}

//------------------------------------------------------------------------//
// GetStartDateTimeForBillingPeriod
//------------------------------------------------------------------------//
/**
 * GetStartDateTimeForBillingPeriod()
 *
 * Returns the StartDatetime for the Billing Period which encompasses $mixNow
 *
 * Returns the StartDatetime for the Billing Period which encompasses $mixNow
 * Currently the StartDatetime of any billing period is midnight on the first of the month
 *
 * @param	mix			$mixNow		optional, can be expressed as a unix time (integer) or
 *									a MySQL date or datetime data type (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
 *									if not declared, then the current time on the MySQL server is used
 *
 * @return	int						StartDatetime of the billing period which encompasses $mixNow, expressed as in standard Unix format
 *
 * @function
 * 
 */
function GetStartDateTimeForBillingPeriod($mixNow=NULL)
{
	if (is_int($mixNow))
	{
		// $mixNow is in standard Unix format
		$intNow = $mixNow;
	}
	else
	{
		// $mixNow must be in the MySQL date or datetime format
		$intNow = strtotime($mixNow);
	}
	
	if ($intNow === FALSE)
	{
		// Converting $mixNow to the standard unix time format failed.  Use the current time of the MySQL server
		$intNow = strtotime(GetCurrentDateAndTimeForMySQL());
	}
	
	// Currently, the start of any billing period is midnight on the first of the month
	$strThisMonthAndYear = date("m Y", $intNow);
	$arrThisMonthAndYear = explode(" ", $strThisMonthAndYear);
	
	$intStartDatetime = mktime(0, 0, 0, $arrThisMonthAndYear[0], 1, $arrThisMonthAndYear[1]);
	
	return $intStartDatetime;
}

//------------------------------------------------------------------------//
// GetStartDateTimeForNextBillingPeriod
//------------------------------------------------------------------------//
/**
 * GetStartDateTimeForNextBillingPeriod()
 *
 * Returns the StartDatetime for the next Billing Period after the Billing Period which encompasses $mixNow
 *
 * Returns the StartDatetime for the next Billing Period after the Billing Period which encompasses $mixNow
 * Currently the StartDatetime of any billing period is midnight on the first of the month
 *
 * @param	mix			$mixNow		optional, can be expressed as a unix time (integer) or
 *									a MySQL date or datetime data type (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
 *									if not declared, then the current time on the MySQL server is used
 *
 * @return	int						StartDatetime of the billing period after the one which encompasses $mixNow, expressed as in standard Unix format
 *
 * @function
 * 
 */
function GetStartDateTimeForNextBillingPeriod($mixNow=NULL)
{
	$intStartDatetimeForBillingPeriod = GetStartDateTimeForBillingPeriod($mixNow);
	
	// Currently, the start of the next billing period is midnight on the first of the next month
	$intStartDatetimeForNextBillingPeriod = strtotime("+1 months ", $intStartDatetimeForBillingPeriod);
	
	return $intStartDatetimeForNextBillingPeriod;
}

//------------------------------------------------------------------------//
// GetNextBillDate
//------------------------------------------------------------------------//
/**
 * GetNextBillDate()
 *
 * Returns the Date (YYYY-MM-DD) that the Account should theoretically next be billed on
 *
 * Returns the Date (YYYY-MM-DD) that the Account should theoretically next be billed on
 * It only works for accounts that are billed once a month, and even then you can't
 * guarantee that it will be billed on this date
 * 
 *
 * @param	mix			$mixAccount		integer	: 	Account Id
 * 										array	: 	Account Record which must include the Array elements
 * 													LastBilled, BillingDate, BillingFreq, BillingFreqType
 * 
 * @return	mix			date as "YYYY-MM-DD" OR FALSE on failure
 *
 * @function
 */
function GetNextBillDate($mixAccount)
{
	if (!is_array($mixAccount))
	{
		// $mixAccount is a single AccountId.  Retrieve the billing period info from the database
		$selAccount = new StatementSelect("Account", "LastBilled, BillingDate, BillingFreq, BillingFreqType", "Id = <AcountId>");
		
		if ($selAccount->Execute(Array("AccountId"=>$mixAccount)) === FALSE)
		{
			return FALSE;
		}
		$arrAccount = $selAccount->Fetch();
	}
	else
	{
		$arrAccount = $mixAccount;
	}
	
	// Calculate when the Account will next be billed
	$strNowDate			= date("Y-m-d");
	$intBillingDate		= $arrAccount['BillingDate'];
	$intBillingFreq		= $arrAccount['BillingFreq'];
	$intBillingFreqType	= $arrAccount['BillingFreqType'];
	$strLastBilled		= $arrAccount['LastBilled'];
	
	if ($intBillingFreq != 1 || $intBillingFreqType != BILLING_FREQ_MONTH)
	{
		// Can only handle situations where an account is billed once a month
		return FALSE;
	}
	
	if ($strLastBilled == $strNowDate)
	{
		// The account was billed today, so return the scheduled billing date for next month
		return date("Y-m-d", strtotime("+1 month ". date("Y-m-") . $intBillingDate));
	}
	
	if ($intBillingDate >= date("d"))
	{
		// This Account should be billed on the {$intBillingDate}th day of this month
		return date("Y-m-") . str_pad($intBillingDate, 2, "0", STR_PAD_LEFT);
	}
	else
	{
		// The Account should have already been billed this month , so return the scheduled billing date for next month
		return date("Y-m-d", strtotime("+1 month ". date("Y-m-") . $intBillingDate));
	}
}

//------------------------------------------------------------------------//
// GetPlanScheduledForNextBillingPeriod
//------------------------------------------------------------------------//
/**
 * GetPlanScheduledForNextBillingPeriod()
 *
 * Returns the Id of the RatePlan which is scheduled to start at the begining of the next Billing Period after the Billing Period which encompasses $mixNow
 *
 * Returns the Id of the RatePlan which is scheduled to start at the begining of the next Billing Period after the Billing Period which encompasses $mixNow
 *
 * @param	int		$intServiceId	Id of the Service
 * @param	mix		$mixNow			optional, can be expressed as a unix time (integer) or
 *									a MySQL date or datetime data type (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
 *									if not declared, then the current time on the MySQL server is used
 *
 * @return	mix						Id of the RatePlan or FALSE if one isn't scheduled to start at the begining of the next Billing Period
 *
 * @function
 */
function GetPlanScheduledForNextBillingPeriod($intServiceId, $mixNow=NULL)
{
	$intStartDatetime = GetStartDateTimeForNextBillingPeriod($mixNow);
	
	$strStartDatetime = ConvertUnixTimeToMySQLDateTime($intStartDatetime);
	
	$selRatePlan = new StatementSelect("ServiceRatePlan", "RatePlan", "Service = <Service> AND StartDatetime = <StartOfNextBillingPeriod> AND StartDatetime < EndDatetime", "CreatedOn DESC", 1);
	$selRatePlan->Execute(Array('Service' => $intServiceId, "StartOfNextBillingPeriod" => $strStartDatetime));
	$arrRatePlan = $selRatePlan->Fetch();
	return ($arrRatePlan) ? $arrRatePlan['RatePlan'] : FALSE;
}

//------------------------------------------------------------------------//
// MakeCSVLine
//------------------------------------------------------------------------//
/**
 * MakeCSVLine()
 *
 * Returns a string which defines the passed array as a csv record
 *
 * Returns a string which defines the passed array as a csv record
 *
 * @param	array	$arrFields		The fields that you want to format as a CSV record (can be associative)
 * @param	array	$arrFieldOrder	optional, Array defining the order that the elements of $arrFields should be added to the CSV record
 *									Set it to NULL to use the default order of $arrFields (defaults to NULL)
 * @param	string	$strDelimiter	optional, character used to delimit fields (deaults to a comma) This must be 1 char long
 * @param	string	$strEnclosure	optional, character used to enclose string fields (defaults to a double-quote char) This must be 1 char long
 *									$strDelimiter != $strEnclosure
 *
 * @return	string					$arrField formatted as a CSV record complete with new line character at the end of it
 *
 * @function
 */
function MakeCSVLine($arrFields, $arrFieldOrder=NULL, $strDelimiter=',', $strEnclosure='"')
{
	if (($strDelimiter == $strEnclosure) || (strlen($strDelimiter) != 1) || (strlen($strEnclosure) != 1))
	{
		// The parameters have not been defined properly
		// I should probably remove this check, and include it in the docblock as a percondition
		return FALSE;
	}

	if ($arrFieldOrder == NULL)
	{
		$arrFieldOrder = array_keys($arrFields);
	}
	
	$strEscapedEnclosure = $strEnclosure . $strEnclosure;
	
	$strCSVLine = "";
	foreach ($arrFieldOrder as $mixKey)
	{
		$mixField = $arrFields[$mixKey];
		
		if ($mixField === TRUE)
		{
			// I think this should probably be stored as a 1
			$strCSVLine .= 'TRUE';
		}
		elseif ($mixField === FALSE)
		{
			// I think this should probably be stored as a 0
			$strCSVLine .= 'FALSE';
		}
		elseif ($mixField !== NULL)
		{
			if (is_numeric($mixField))
			{
				// numbers don't have to be enclosed (I'm assuming this is the case, even for numbers expressed in Scientific Notation)
				$strCSVLine .= $mixField;
			}
			else
			{
				// Escape all characters within the string, which match the enclosure character
				$mixField = str_replace($strEnclosure, $strEscapedEnclosure, $mixField);
			
				// Strings have to be properly enclosed
				$strCSVLine .= $strEnclosure . $mixField . $strEnclosure;
			}
		}
		else
		{
			// The value === NULL.  Don't do anything
		}
		
		// Add the field delimiter
		$strCSVLine .= $strDelimiter;
	}
	
	// Remove the very last delimiter
	$strCSVLine = rtrim($strCSVLine, $strDelimiter);
	
	// Add the new line character to the end of the line
	$strCSVLine .= "\n";
	
	return $strCSVLine;
}




/*
 *
 * GetDBConnection.
 *
 * Function to make a new database connection
 * Example Usage: $dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
 *
 */
function GetDBConnection($mixUsingSystem)
{
	$db_handler = NULL;
	switch ($mixUsingSystem) {
		
		case "mysqli":
		$dbConnection = new DB_MySQL_Database($GLOBALS['**arrDatabase']['flex']['URL'], $GLOBALS['**arrDatabase']['flex']['Database'], $GLOBALS['**arrDatabase']['flex']['User'], $GLOBALS['**arrDatabase']['flex']['Password'], $db_handler);
		break;
		
		case "mssql":
		$dbConnection = new DB_MSSQL_Database($GLOBALS['**arrDatabase']['flex']['URL'], $GLOBALS['**arrDatabase']['flex']['Database'], $GLOBALS['**arrDatabase']['flex']['User'], $GLOBALS['**arrDatabase']['flex']['Password'], $db_handler);
		break;

		case "postgres":
		$dbConnection = new DB_Postgres_Database($GLOBALS['**arrDatabase']['flex']['URL'], $GLOBALS['**arrDatabase']['flex']['Database'], $GLOBALS['**arrDatabase']['flex']['User'], $GLOBALS['**arrDatabase']['flex']['Password'], $db_handler);
		break;

	}
	return $dbConnection;
}

/*
 *
 * Error checking/form validation.
 *
 * $error_response = more technical information, only should be used for debugging purposes ..
 *
 * Format: InputValidation(string Name, Input to check, string Type, Field max Length);
 * String types : 
 * email, numbers, letters, mixed, multiword
 *
 *
 */
function InputValidation($strName,$mixInput,$strType,$intLength){
	$strFoundError=FALSE;
	$strErrorResponse="";
	switch ($strType) {
		case "email":
		preg_match('/^(.*\@)+(.*)$/', $mixInput, $mixMatch);
		$mixMailRecord = getmxrr($mixMatch['2'],$mxhosts=null,$weight=null);
		if(!$mixMailRecord)
		{	
			$strFoundError=TRUE;
			$strErrorResponse="The email address does not have a valid MX record, please check your email address and make sure it is valid. If you believe this is a temporary email issue please try again.<br><br>If problems persist, please try a different email address.";
		}
		if (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$", $mixInput)) {
			$strFoundError=TRUE;
			$strErrorResponse="Invalid input: $strName, max length: $intLength, characters accepted: $strType";
		}
		break;
		case "numbers":
		if(!eregi("^[0-9]{1,$intLength}$",$mixInput)){
			$strFoundError=TRUE;
			$strErrorResponse="Incorrect $strName entered. The $strName should be a maximum of $intLength characters long and contain only numbers. e.g. 0123456789";
		}
		break;
		case "letters":
		if(!eregi("^[a-zA-Z]{1,$intLength}$",$mixInput)){
			$strFoundError=TRUE;
			$strErrorResponse="Invalid input: $strName, max length: $intLength, characters accepted: $strType";
		}
		break;
		case "mixed":
		if(!eregi("^[a-zA-Z0-9]{1,$intLength}$",$mixInput)){
			$strFoundError=TRUE;
			$strErrorResponse="Invalid input: $strName, max length: $intLength, characters accepted: $strType";
		}
		break;
		case "multiword":
		if(!eregi("^[[:space:]a-zA-Z0-9_.-]{1,$intLength}$",$mixInput)){
			$strFoundError=TRUE;
			$strErrorResponse="Invalid input: $strName, max length: $intLength, characters accepted: $strType";
		}
		break;
	}
	return array ($strFoundError,$strErrorResponse);
}


/*
 *
 * RandomString()
 *
 * Function to generate a random string, can be used for anything, e.g. Random Securty codes, New passwords, etc.
 * Usage: $strNewString = RandomString("10"); // 10 = length of new string
 *
 */
function RandomString($len, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
{
    $string = '';
    for ($i = 0; $i < $len; $i++)
    {
        $pos = rand(0, strlen($chars)-1);
        $string .= $chars{$pos};
    }
    return $string;
}

/*
 *
 * pagination()
 *
 * Function to generate page links, i.e. when database reocrds need to be spread over multiple pages.
 * Example usage: list($intNext,$mixLinksDisplay) = pagination($intStart,$intResultsPerPage,$intTotalResults,"./flex.php/Console/FAQ/?all=1");
 *
 */
function MakePagination($intStart,$intResultsPerPage,$intTotalResults,$mixURL)
{

	$intPrevious = $intStart-$intResultsPerPage;
	$intNext = $intStart+$intResultsPerPage;
	$intCurrent = $intStart;
	$intPages = $intTotalResults/$intResultsPerPage;
	$mixLinksDisplay = NULL;

	if($intPages>1)
	{
		$mixLinksDisplay .= "Result Page: ";
		$cnt=1;
		if($intStart>=$intResultsPerPage)
		{

			$mixLinksDisplay .= "<a href='$mixURL$mixSearch&start=$intPrevious'>Previous</A>&nbsp;&nbsp; ";

		}
		for($i=0; $i<$intPages; $i++){

			$intCurrentPageNumber = $i*$intResultsPerPage;
			$intBackFourPages = $start-$intResultsPerPage*7;
			$intForwardFourPages = $start+$intResultsPerPage*7;

			if($intCurrentPageNumber<$intForwardFourPages&&$intCurrentPageNumber>$intBackFourPages){
				if($intCurrentPageNumber==$intStart){
					$mixLinksDisplay .= "$cnt \n";
				}
				else{
					$mixLinksDisplay .= "<a href='$mixURL$mixSearch&start=$intCurrentPageNumber'>$cnt</A></a> \n";
				}
			}

			$cnt++;
		
		}
		if($intNext+1<"$intTotalResults"){

			$mixLinksDisplay .= "&nbsp;&nbsp;<A href='$mixURL$mixSearch&start=$intNext'>Next</A>";
		
		}
	}
	return array ($intNext,$mixLinksDisplay);
}

/*
 *
 * CleanFormInput()
 *
 * Function to clean form input: POST, GET, PUT, etc
 * Example usage: CleanFormInput($_POST);
 *
 * Function should make data clean so it can be re-printed on a page or run in an SQL query.
 *
 */
function CleanFormInput($resMethod)
{
	foreach($resMethod as $key=>$val)
	{
		// remove any unwanted code/bad input.
		$key=htmlspecialchars(addslashes($key), ENT_QUOTES);
		if(!is_array($val)){
			$val=htmlspecialchars(addslashes($val), ENT_QUOTES);
		}
		else
		{
			foreach($val as $key2=>$val2)
			{
				$val[$key2]=htmlspecialchars(addslashes($val2), ENT_QUOTES);
			}
		}
		$resMethod[$key]=$val;
		//print "cleaned: $_POST[$key] = $val;<br>\n";
	}
	return($resMethod);
}

?>

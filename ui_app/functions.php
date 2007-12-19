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
//  AddServiceChurnNote
//------------------------------------------------------------------------//
/**
 * AddServiceChurnNote()
 *
 * Adds a service churn note
 * 
 * @param	int	$intAccountNumber		Account the churn will affect
 * @param	int	$intAccountGroup		AccountGroup
 * @param	str	$strFNN							Service affected
 * @param	str	$strCarrier					Carrier
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
	$objBrowserInfo = Singleton::Instance('BrowserInfo');
	return $objBrowserInfo;
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
	elseif ($intEmployeeId === SYSTEM_EMPLOYEE_ID)
	{
		$strName = SYSTEM_EMPLOYEE_NAME;
	}
	else
	{
		$strName = "";
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
	return Singleton::Instance('OutputMasks');
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
		'Datetime'		=> new MySQLFunction("NOW()"),
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
		$strWhere							= "(Account = <AccountId> OR (AccountGroup = <AccountGroupId> AND Account IS NULL AND Service IS NULL AND Contact IS NULL))";
		$strCookiePrefix					= "Account";
	}
	elseif (DBO()->Service->Id->Value)
	{
		// Load service notes
		DBO()->NoteDetails->ServiceNotes	= TRUE;
		$arrWhere							= Array("ServiceId" => $intServiceId);
		$strWhere							= "Service = <ServiceId>";
		$strCookiePrefix					= "Service";
	}
	elseif (DBO()->Contact->Id->Value)
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
	$intStartDatetimeForNextBillingPeriod = strtotime("+1months ", $intStartDatetimeForBillingPeriod);
	
	return $intStartDatetimeForNextBillingPeriod;
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
			$strCSVLine .= 'TRUE';
		}
		elseif ($mixField === FALSE)
		{
			$strCSVLine .= 'FALSE';
		}
		elseif ($mixField !== NULL)
		{
			if (is_string($mixField))
			{
				// Escape all characters within the string, which match the enclosure character
				$mixField = str_replace($strEnclosure, $strEscapedEnclosure, $mixField);
			
				// Strings have to be properly enclosed
				$strCSVLine .= $strEnclosure . $mixField . $strEnclosure;
			}
			else
			{
				// numbers don't have to be enclosed (I'm assuming this is the case, even for numbers expressed in Scientific Notation)
				$strCSVLine .= $mixField;
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

//------------------------------------------------------------------------//
// GenerateLatePaymentNotices
//------------------------------------------------------------------------//
/**
 * GenerateLatePaymentNotices()
 *
 * Generates the appropriate Late Payment Notices, if any need to be made that haven't already been made
 *
 * Generates the appropriate Late Payment Notices, if any need to be made that haven't already been made
 * A notice is generated for all accounts that satisfy ALL of the following conditions:
 * 		Account status is Active or Closed
 * 		AND 
 *		The total amount that is 1 to 29 days overdue is greater than $23
 *		AND
 *		The total amount that is 30+ days overdue is equal to 0
 *		AND
 *		The Account can have Late Notices generated for it (Account.DisableLateNotices == 0)
 * Disputed amounts and unbilled credit adjustments are not taken into consideration when calculating 
 * the total amounts invoiced within the 2 date ranges
 * This should be scheduled to run at least once a day
 *
 * @param	string	$strFilePath	optional, path where the generated notices will be placed
 *									
 * @return	mix						returns FALSE if it failed to work out if any notices should be generated today
 *									returns	Array[NoticeType]['Successful']	= number of successfully generated notices of the NoticeType
 *											Array[NoticeType]['Failed'] 	= number of notices that failed to generate, of the NoticeType
 * @function
 */
function GenerateLatePaymentNotices($strFilePath="./")
{
	//TODO! Currently this function only generates the notices, if they are supposed to be generated today
	// It should check the FileExport table to see if any notices have been skipped, and if so, create them
	
	//TODO! This uses hardcoded dates for scheduling the notices relative to the last invoice due date
	// These variables should probably be turned into constants, or stored in a config file
	$intDaysBeforeOverdueNotice		= 7;
	$intDaysBeforeSuspensionNotice	= 14;
	$intDaysBeforeFinalDemandNotice	= 21;
	
	// Accounts are allowed to have an outstanding balance of at most $23 without recieving any late notices
	//TODO! This value should be in the definitions.php file, or some sort of config file
	$fltAcceptableOverdueBalance = 23.00;

	// Append a backslash to the path, if it doesn't already end in one
	if (substr($strFilePath, -1) != "/")
	{
		$strFilePath .= "/";
	}

	// Notices shouldn't be generated for Archived Accounts, or those in debt collection, and probably even suspended ones.  Take this into account
	$arrApplicableAccountStatuses = Array(ACCOUNT_ACTIVE, ACCOUNT_CLOSED);
	
	// Find all Accounts that fit the requirements for Late Notice generation
	$strColumns = 	"Invoice.Account AS 'AccountId',
					Account.BusinessName AS 'BusinessName',
					Account.TradingName AS 'TradingName',
					Account.CustomerGroup AS 'CustomerGroup',
					Account.Archived As 'AccountStatus',
					Contact.FirstName AS 'FirstName', 
					Contact.LastName AS 'LastName',
					Account.Address1 AS 'AddressLine1',
					Account.Address2 AS 'AddressLine2',
					Account.Suburb AS 'Suburb',
					Account.Postcode AS 'Postcode',
					Account.State AS 'State',
					MAX(Invoice.DueOn) AS 'InvoiceDueDate',
					ADDDATE(MAX(Invoice.DueOn), INTERVAL $intDaysBeforeOverdueNotice DAY) AS 'OverdueNoticeDate',
					ADDDATE(MAX(Invoice.DueOn), INTERVAL $intDaysBeforeSuspensionNotice DAY) AS 'SuspensionNoticeDate',
					ADDDATE(MAX(Invoice.DueOn), INTERVAL $intDaysBeforeFinalDemandNotice DAY) AS 'FinalDemandNoticeDate',
					SUM(CASE WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 1 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 29 DAY) THEN 
						Invoice.Balance END) AS '1To29DaysOverdue',
					SUM(CASE WHEN CURDATE() >= ADDDATE(Invoice.DueOn, INTERVAL 30 DAY) THEN
						Invoice.Balance END) AS '30PlusDaysOverdue'";
					
	$strTables	= "Invoice JOIN Account ON Invoice.Account = Account.Id JOIN Contact ON Account.PrimaryContact = Contact.Id";
	$strWhere	= "Account.DisableLateNotices = 0 AND Account.Archived IN (". implode(", ", $arrApplicableAccountStatuses) .") AND Invoice.DueOn < CURDATE()";
	$strOrderBy	= "Invoice.Account ASC";
	$strGroupBy	= "Invoice.Account HAVING 1To29DaysOverdue > $fltAcceptableOverdueBalance AND 30PlusDaysOverdue IN (NULL, 0)";
	
	$selOverdue = new StatementSelect($strTables, $strColumns, $strWhere, $strOrderBy, "", $strGroupBy);
	$intNumOfNotices = $selOverdue->Execute();

	if ($intNumOfNotices === FALSE)
	{
		// Failed to retrieve the data from the database
		return FALSE;
	}
	
	// Store a running total of how many were successfully generated, and how many failed, for each notice type
	$arrCount = Array("Successful" => 0, "Failed" => 0);
	$arrGeneratedNotices = Array(LATE_PAYMENT_NOTICE_OVERDUE => $arrCount, LATE_PAYMENT_NOTICE_SUSPENSION=> $arrCount, LATE_PAYMENT_NOTICE_FINAL_DEMAND=> $arrCount);
	
	// For each account retrieved, generate the appropriate late payment notice
	//TODO! this part will have to be updated, to check the FileExport table to make sure previous notices were actually generated
	$arrAccounts = $selOverdue->FetchAll();
	$strToday = date("Y-m-d");
	foreach ($arrAccounts as $arrAccount)
	{
		switch ($strToday)
		{
			case $arrAccount['OverdueNoticeDate']:
				$bolSuccess		= BuildLatePaymentNotice(LATE_PAYMENT_NOTICE_OVERDUE, $arrAccount, $strPathFile);
				$intNoteType	= LATE_PAYMENT_NOTICE_OVERDUE;
				break;
				
			case $arrAccount['SuspensionNoticeDate']:
				$bolSuccess		= BuildLatePaymentNotice(LATE_PAYMENT_NOTICE_SUSPENSION, $arrAccount, $strPathFile);
				$intNoteType	= LATE_PAYMENT_NOTICE_SUSPENSION;
				break;
				
			case $arrAccount['FinalDemandNoticeDate']:
				$bolSuccess		= BuildLatePaymentNotice(LATE_PAYMENT_NOTICE_FINAL_DEMAND, $arrAccount, $strPathFile);
				$intNoteType	= LATE_PAYMENT_NOTICE_FINAL_DEMAND;
				break;
				
			default:
				continue;
		}
		if ($bolSuccess)
		{
			$arrGeneratedNotices[$intNoteType]['Successful'] += 1;
		}
		else
		{
			$arrGeneratedNotices[$intNoteType]['Failed'] += 1;
		}
	}
	
	return $arrGeneratedNotices;
}

// For each account that has "Send Late Notices" set to "Don't send late notices for current overdue amount", this will reset it to "Send Late Notices"
// if 30 days have transpired since the Account's most recent invoice was due
function UpdateDisableLateNoticesSetting()
{
	//TODO
}

//------------------------------------------------------------------------//
// BuildLatePaymentNotice
//------------------------------------------------------------------------//
/**
 * BuildLatePaymentNotice()
 *
 * Generates the chosen Late Payment Notice for an Account
 *
 * Generates the chosen Late Payment Notice for an Account
 *
 * @param	integer	$intNoticeType	Type of notice to generate (one of the LatePaymentNotice constants)
 * @param	array	$arrAccount		All Account, Contact and Invoice data required for the notice
 * @param	string	$strFilePath	optional, path where the generated notices will be placed. Must end with a '/'
 *									
 * @return	bool					TRUE if the notice was successfully generated, else FALSE
 *
 * @function
 */
function BuildLatePaymentNotice($intNoticeType, $arrAccount, $strFilePath="./")
{
	//TODO! Modify this so that it builds actual pdfs, instead of just text files representing the pdfs
	
	// Create the filename
	$strFilename = $strFilePath . date("Ymd") ."_". str_replace(" ", "_", strtolower(GetConstantDescription($intNoticeType, "LatePaymentNotice"))) ."_{$arrAccount['AccountId']}.txt";
	
	// Set up all values required of the notice, which have not been defined yet
	$strDateIssued					= date("d-m-Y");
	$strDueDateForAction			= date("d-F-Y", strtotime("+7 days", $strDateIssued));
	$arrAccount['CustomerGroup']	= GetConstantDescription($arrAccount['CustomerGroup'], "CustomerGroup");
	$arrAccount['AccountStatus']	= GetConstantDescription($arrAccount['AccountStatus'], "Account");
	if ($arrAccount['1To29DaysOverdue'] === NULL)
	{
		$arrAccount['1To29DaysOverdue'] = 0;
	}
	if ($arrAccount['30PlusDaysOverdue'] === NULL)
	{
		$arrAccount['30PlusDaysOverdue'] = 0;
	}
	
	// Open the file in text mode
	$ptrNoticeFile = fopen($strFilename, 'wt');
	if ($ptrNoticeFile === FALSE)
	{
		// The file could not be opened
		return FALSE;
	}
	
	// Include NoticeType specific stuff here
	switch ($intNoticeType)
	{
		case LATE_PAYMENT_NOTICE_OVERDUE:
			$strMessage =	"Our records indicate that your account for the amount of \${$arrAccount['1To29DaysOverdue']} remains unpaid.\n".
							"Please ensure payment is made by $strDueDateForAction to avoid any further recovery action and possible disruption to your services\n";
			break;
		case LATE_PAYMENT_NOTICE_SUSPENSION:
			$strMessage =	"Further to our recent reminder letter, our records indicate that your account remains unpaid.\n".
							"\tPrevious Amount Overdue: \${$arrAccount['1To29DaysOverdue']}\n".
							"\tCurrent Invoice: (Wouldn't this always be equal to the 'Previous Amount Overdue'?)\n".
							"\tTotal Amount Owing: (Again, wouldn't this always be equal to the 'Previous Amount Overdue'?)\n\n".
							"Please be advised that if we do not recieve payment by $strDueDateForAction your services will be suspended without further notice and we will commence appropriate collection action immediately.\n";
			break;
		case LATE_PAYMENT_NOTICE_FINAL_DEMAND:
			$strMessage =	"We note that dispite numerous reminders to pay this outstanding amount, the account still remains in arrears in the amount of \${$arrAccount['1To29DaysOverdue']}\n".
							"Your service is due to be temporarily disconnected because of your failure to pay your accounts.\n".
							"\tAmount Overdue: (how would this ever be different to the '1-29 days overdue' amount?)\n\n".
							"If you would like to avoid the impending actions, we request that you contact this office within 7 days with a view to payment of the outstanding account.\n";
			break;
	}
	
	$strMessage .= "Date Issued: $strDateIssued\n";
	
	// Output the contents of $arrAccount
	foreach ($arrAccount as $strProperty=>$mixValue)
	{
		$strMessage .= "$strProperty: $mixValue\n";
	}
	
	fwrite($ptrNoticeFile, $strMessage);
	fclose($ptrNoticeFile);
	
	return TRUE;
}



?>

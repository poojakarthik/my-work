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
 * The __autoload function is used to dynamically include a php file
 * required to instantiate a class
 * Also currently contains all constants required of ui_app
 * 
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
Define ('MODULE_BASE_DIR', "");
Define ('JAVASCRIPT_BASE_DIR', "");

Define ('COLUMN_ONE'	, 1);
Define ('COLUMN_TWO'	, 2);
Define ('COLUMN_THREE'	, 3);
Define ('COLUMN_FOUR'	, 4);

Define ('AJAX_MODE'		, 1);
Define ('HTML_MODE'		, 2);

Define ('USER_TIMEOUT'	, 1200);
Define ('GOD_TIMEOUT'	, 60*60*24*7);

Define("USER_PERMISSION_GOD"	, 0x7FFFFFFFFFFFFFFF);

define('DATABASE_URL', '10.11.12.13');
define('DATABASE_NAME', vixen);
define('DATABASE_USER', vixen);
define('DATABASE_PWORD', V1x3n);

// database documentation contexts
define('CONTEXT_DEFAULT', 0);

define('OUTPUT_TYPE_LABEL', 1);
define('OUTPUT_TYPE_RADIO', 2);

// CSS classes
define('CLASS_DEFAULT', 'Default');


// Object Status
define('STATUS_NEW',	 	100);
define('STATUS_CLEANED', 	101);
define('STATUS_LOADED', 	102);
define('STATUS_UPDATED', 	102);
define('STATUS_MERGED', 	102);
define('STATUS_SAVED', 		102);


require_once('../framework/functions.php');
LoadFramework();
require_once('functions.php');
require_once('framework.php');

require_once('db/db_access_ui.php');
require_once('db/db_object_base.php');
require_once('db/db_object.php');
require_once('db/db_list.php');
require_once('db/token.php');

require_once('json.php');
//require_once('db_access_old.php');

$Application = Singleton::Instance('Application');


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
 * @param	string	$strClassName	Class to load
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
	if (substr($strClassName, 0, 6) == "Module")
	{
		$strClassPath = MODULE_BASE_DIR . "module";
		$strClassName = substr($strClassName, 6);
	}
	else
	{
		$arrClassName = explode("template", strtolower($strClassName));
		$strClassPath = TEMPLATE_BASE_DIR . $arrClassName[0] . "_template";
		$strClassName = $arrClassName[1];
	}		
	// if $strClassName couldn't be exploded on "template" then die
	if (!$strClassName)
	{
		// The class trying to be loaded is not a template class
		// This function does not currently handle any other kinds of class
		return FALSE;
	}
	
	// check if a directory listing for $strClassPath has already been created
	if (!isset($GLOBALS['*arrAvailableFiles'][$strClassPath]))
	{ 
		$GLOBALS['*arrAvailableFiles'][$strClassPath]['ActualFilename'] = Array();
		$GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename'] = Array();	
		// $strClassPath has not had its directory listing loaded before, so do it now
		foreach (glob($strClassPath . "/*.php") as $strAbsoluteFilename)
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
	
	// include the php file that defines the class
	include_once($strClassPath . "/" . $GLOBALS['*arrAvailableFiles'][$strClassPath]['ActualFilename'][$mixClassPointer]);
}

//----------------------------------------------------------------------------//
// Application INCOMPLETE
//----------------------------------------------------------------------------//
/**
 * Application
 *
 * The Application class
 *
 * The Application class
 *
 *
 * @package	ui_app
 * @class	Application
 */
class Application
{

	//------------------------------------------------------------------------//
	// Load INCOMPLETE
	//------------------------------------------------------------------------//
	/**
	 * Load()
	 *
	 * Loads an extended ApplicationTemplate object which represents all the logic and layout of a single webpage of the application
	 *
	 * Loads an extended ApplicationTemplate object which represents all the logic and layout of a single webpage of the application
	 *
	 *
	 * @param		string	$strTemplateName	Name of the application template to load.
	 *											This template must be located in the "app_template"
	 *											directory and be named FileName.Method
	 *											For example: $strTemplateName = "Account.View"
	 *											This will instantiate an object of type AppTemplateAccount
	 *											which will be located in app_template/account.php
	 *											and run the View method of AppTemplateAccount
	 * @return		void
	 * @method
	 *
	 */
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
		
		// get submitted data
		$objSubmit = new SubmittedData();
		$objSubmit->Get();
		$objSubmit->Post();
	
		// validate all submitted objects
		// Note that while $objSubmit->Get() and ->POST set up the submitted objects, they have not actually 
		// been loaded from the database, so validating them at this stage should always return TRUE
		DBO()->Validate();

		//Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		$this->objAppTemplate->SetMode(HTML_MODE);
		
		//Run AppTemplate
		$this->objAppTemplate->{$strMethod}();
		
		// Render Page
		$this->objAppTemplate->Page->Render();
	}
	
	//------------------------------------------------------------------------//
	// AjaxLoad INCOMPLETE
	//------------------------------------------------------------------------//
	/**
	 * AjaxLoad()
	 *
	 * Loads an Ajax Template 
	 * 
	 * Loads an Ajax Template
	 *
	 * @return		void
	 * @method
	 *
	 */
	function AjaxLoad()
	{
		$objAjax = AjaxRecieve();
		
		// get submitted data
		$objSubmit = new submitted_data();
		$objSubmit->Ajax($objAjax);
	
		// YAY, this far is great!

		
		$strClassName = "AppTemplate" . $objAjax->Object;
		//Create AppTemplate Object
		// new AppTemplateAccount->View
		$this->objAppTemplate = new $strClassName;
		$this->objAppTemplate->SetMode(AJAX_MODE);
		
		//Run AppTemplate
		$this->objAppTemplate->View();
		
		$arrReply = Array();
		//var_dump($this->objAppTemplate->arrSend);
		/*
		if (is_array($this->arrSend['Dbo']))
		{
			foreach ($this->arrSend['Dbo'] as $strObject=>$mixValue)
			{
				if (is_array($mixValue))
				{
					foreach ($mixValue as $strProperty=>$bolValue)
					{
						// add just the property to the reply
						$arrReply['DBO'][$strObject][$strProperty] = DBO()->{$strObject}->{$strProperty}->Value;
					}
				}
				else
				{
					// add the whole object to the reply
					foreach (DBO()->{$strObject} as $strProperty=>$objProperty)
					{
						// add just the property to the reply
						$arrReply['DBO'][$strObject][$strProperty] = $objProperty->Value;
					}
				}
			}
		}
		if (is_array($this->arrSend['Dbl']))
		{
			foreach ($this->arrSend['Dbl'] as $strList=>$bolValue)
			{
				//TODO!Interface-kids!Add the Dbl object to the reply 
				foreach (DBL()->{$strList} as $intObject=>$objObject)
				{
					foreach ($objObject as $strProperty=>$objProperty)
					{
						// add just the property to the reply
						$arrReply['DBL'][$strList][$intObject][$strProperty] = $objProperty->Value;
					}
				}
			}
		}
		*/
		//AjaxReply($arrReply);
		AjaxReply(Array("yay","yayas"));
	}
	
	//------------------------------------------------------------------------//
	// CheckAuth
	//------------------------------------------------------------------------//
	/**
	 * CheckAuth()
	 *
	 * Checks user authentication
	 * 
	 * Checks user authentication
	 *
	 * @return		void
	 * @method
	 *
	 */
	function CheckAuth()
	{
		//var_dump($_COOKIE);die;
		if (isset($_COOKIE['Id']) && isset($_COOKIE['SessionId']))
		{
			//var_dump($_COOKIE);
			//print_r($cookie1);
			//print_r($cookie2);
			$selAuthenticated = new StatementSelect(
					"Employee",
					"*", 
					"Id = <Id> AND SessionId = <SessionId> AND SessionExpire > NOW() AND Archived = 0",
					null,
					1
				);
				
			$intRowsReturned = $selAuthenticated->Execute(Array("Id" => $_COOKIE['Id'], "SessionId" => $_COOKIE['SessionId']));
			$arrAuthentication = $selAuthenticated->Fetch();
			//echo "::::::";
			//var_dump($intRowsReturned);
			//echo "::::::";
			if ($intRowsReturned)
			{
				//Load user object from db
				$this->_arrUser = $arrAuthentication;

				//save new session details in db
				if ($arrAuthentication['Privileges'] == USER_PERMISSION_GOD)
				{
					$arrUpdate = Array("SessionExpire" => new MySQLFunction("ADDTIME(NOW(), SEC_TO_TIME(" . GOD_TIMEOUT . "))"));
					$intTime = time() + GOD_TIMEOUT;
				}
				else
				{
					$arrUpdate = Array("SessionExpire" => new MySQLFunction("ADDTIME(NOW(), SEC_TO_TIME(" . USER_TIMEOUT . "))"));
					$intTime = time() + USER_TIMEOUT;
				}
				$updUpdateStatement = new StatementUpdate("Employee", "Id = <Id>", $arrUpdate);
				$updUpdateStatement->Execute($arrUpdate, Array("Id" => $_COOKIE['Id']));

				
				//cookie setup
				$this->_arrCookie = Array();
				$this->_arrCookie["Id"]["Value"] = $_COOKIE['Id'];
				$this->_arrCookie["Id"]["ExpDate"] = $intTime;
				$this->_arrCookie["SessionId"]["Value"] = $_COOKIE['SessionId'];
				$this->_arrCookie["SessionId"]["ExpDate"] = $intTime;
				//print_r($this->_arrCookie);
			}
			else
			{
				$this->_arrUser = "";
			}
		}
		else
		{
			$this->_arrUser = "";
		}
	
		$bolLoggedIn = FALSE;
		if (isset($_POST['VixenUserName']) && isset($_POST['VixenPassword']))
		{
			// user has logged in
			// Get the Id of the Employee (Identified by UserName and PassWord combination)
			$selSelectStatement = new StatementSelect (
				"Employee", 
				"Id", 
				"UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", 
				null, 
				"1"
			);
			
			$selSelectStatement->Execute(Array("UserName"=>$_POST['VixenUserName'], "PassWord"=>$_POST['VixenPassword']));
			
			// If the employee could not be found, return false
			if ($selSelectStatement->Count () <> 1)
			{
				require_once("page_template/login.php");
			}
			
			// If we reach this part of the Method, the session is authenticated.
			// Therefore, we have to store the Authentication
			$arrFetch = $selSelectStatement->Fetch ();
			$intId = $arrFetch ['Id'];

			// Generate a new session ID
			$intSessionId = sha1(uniqid(rand(), true));
			
			// Updating information
			$Update = Array("SessionId" => $intSessionId, "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(),'00:20:00')"));
			
			// update the table
			$updUpdateStatement = new StatementUpdate("Employee", "UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", $Update);
			
			// If we successfully update the database table
			if ($updUpdateStatement->Execute($Update, Array("UserName"=>$_POST['VixenUserName'], "PassWord"=>$_POST['VixenPassword'])) == 1)
			{
				setCookie ("Id", $intId, time () + (60 * 20), "/");
				setCookie ("SessionId", $intSessionId, time () + (60 * 20), "/");
				$bolLoggedIn = TRUE;
			}
		}

		if ($bolLoggedIn)
		{
			//var_dump($_COOKIE);
			$selAuthenticated = new StatementSelect(
					"Employee",
					"*", 
					"Id = <Id> AND SessionId = <SessionId> AND SessionExpire > NOW() AND Archived = 0",
					null,
					1
				);
				
			$intRowsReturned = $selAuthenticated->Execute(Array("Id" => $intId, "SessionId" => $intSessionId));
			$arrAuthentication = $selAuthenticated->Fetch();

			if ($intRowsReturned)
			{
				//Load user object from db
				$this->_arrUser = $arrAuthentication;

				//save new session details in db
				if ($arrAuthentication['Privileges'] == USER_PERMISSION_GOD)
				{
					$arrUpdate = Array("SessionExpire" => new MySQLFunction("ADDTIME(NOW(), SEC_TO_TIME(" . GOD_TIMEOUT . "))"));
					$intTime = time() + GOD_TIMEOUT;
				}
				else
				{
					$arrUpdate = Array("SessionExpire" => new MySQLFunction("ADDTIME(NOW(), SEC_TO_TIME(" . USER_TIMEOUT . "))"));
					$intTime = time() + USER_TIMEOUT;
				}
				$updUpdateStatement = new StatementUpdate("Employee", "Id = <Id>", $arrUpdate);
				$updUpdateStatement->Execute($arrUpdate, Array("Id" => $intId));

				
				//cookie setup
				$this->_arrCookie = Array();
				$this->_arrCookie["Id"]["Value"] = $intId;
				$this->_arrCookie["Id"]["ExpDate"] = $intTime;
				$this->_arrCookie["SessionId"]["Value"] = $intSessionId;
				$this->_arrCookie["SessionId"]["ExpDate"] = $intTime;
				//print_r($this->_arrCookie);
			}
			else
			{
				$this->_arrUser = "";
			}
		}
	}
	
	
	//------------------------------------------------------------------------//
	// PermissionOrDie
	//------------------------------------------------------------------------//
	/**
	 * PermissionOrDie()
	 *
	 * Checks the user's permissions against the permissions required to view the current page
	 * 
	 * Checks the user's permissions against the permissions required to view the current page
	 * If the user does not have the required permissions then the login screen is loaded
	 *
	 * @param		int		$intPagePerms	permissions required to use the page
	 * @return		void
	 * @method
	 *
	 */
	function PermissionOrDie($intPagePerms)
	{
		// check the current user permission against permissions passed in
		if ($this->_arrUser['Privileges'] > $intPagePerms)
		{
			//var_dump ($this->_arrUser);
			return TRUE;
		}
		else
		{
			// ask user to login, then return to page
			//header ('Location: login.php');
			require_once("page_template/login.php");
			//header ('Referer: http://www.something.com/account_view.php');
			//var_dump($this->_arrUser->Permissions);
			exit;
		}

	}
	
	//------------------------------------------------------------------------//
	// UserHasPerm
	//------------------------------------------------------------------------//
	/**
	 * UserHasPerm()
	 *
	 * Checks the user's permissions against the permissions passed in
	 * 
	 * Checks the user's permissions against the permissions passed in
	 * 
	 *
	 * @param		int		$intPerms	permissions to check the user's permissions against
	 * @return		bool				
	 * @method
	 *
	 */
	function UserHasPerm($intPerms)
	{
		// check the permissions are greater/equal
		if ($this->_arrUser['Privileges'] >= $intPerms)
		{
			return TRUE;
		}
		// else return false
		return FALSE;
	}
}

//----------------------------------------------------------------------------//
// ApplicationTemplate
//----------------------------------------------------------------------------//
/**
 * ApplicationTemplate
 *
 * The ApplicationTemplate class
 *
 * The ApplicationTemplate class
 * 
 *
 * @package	ui_app
 * @class	ApplicationTemplate
 * @extends BaseTemplate
 */
class ApplicationTemplate extends BaseTemplate
{

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @return		void
	 * @method
	 */
	function __construct()
	{
		$this->Module = new ModuleLoader();
	}

	//------------------------------------------------------------------------//
	// LoadPage
	//------------------------------------------------------------------------//
	/**
	 * LoadPage()
	 *
	 * Loads a Page to the Application
	 * 
	 * Loads a Page to the Application, using any AJAX templates it finds
	 *
	 * @param		string	$strPageName	The name of the page to load
	 *
	 * @return		void
	 * @method
	 */
	function LoadPage($strPageName)
	{
		if ($this->_intTemplateMode == AJAX_MODE)
		{
			// load AJAX template
			require_once(TEMPLATE_BASE_DIR."ajax_template/" . strtolower($strPageName) . ".php");
		}
		else 
		{
			// create new page object
			$this->Page = new Page;
			
			// load required page
			require_once(TEMPLATE_BASE_DIR."page_template/" . strtolower($strPageName) . ".php");
		}
	}
	
	//------------------------------------------------------------------------//
	// SetMode
	//------------------------------------------------------------------------//
	/**
	 * SetMode()
	 *
	 * Sets the mode of the template
	 * 
	 * Sets the mode of the template
	 *
	 * @param		int	$intMode	The mode number to set
	 *								ie AJAX_MODE, HTML_MODE
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetMode($intMode)
	{
		$this->_intTemplateMode = $intMode;
	}
}

//----------------------------------------------------------------------------//
// PageTemplate
//----------------------------------------------------------------------------//
/**
 * PageTemplate
 *
 * The PageTemplate class
 *
 * The PageTemplate class
 *
 *
 * @package	ui_app
 * @class	PageTemplate
 * @extends BaseTemplate
 */
class PageTemplate extends BaseTemplate
{

}

//----------------------------------------------------------------------------//
// HtmlTemplate
//----------------------------------------------------------------------------//
/**
 * HtmlTemplate
 *
 * The HtmlTemplate class
 *
 * The HtmlTemplate class
 *
 *
 * @package	ui_app
 * @class	HtmlTemplate
 * @extends BaseTemplate
 */
class HtmlTemplate extends BaseTemplate
{
	
	//------------------------------------------------------------------------//
	// LoadJavascript
	//------------------------------------------------------------------------//
	/**
	 * LoadJavascript()
	 *
	 * Loads a js file to the internal array
	 * 
	 * Loads a js file to the internal array
	 *
	 * @param		string	$strFilename	The name of the js file to load
	 *
	 * @return		void
	 * @method
	 *
	 */
	function LoadJavascript($strFilename)
	{
		// add $strFilename to global javascript function array
		$GLOBALS['*arrJavaScript'][$strFilename] = $strFilename;
	}
}

//----------------------------------------------------------------------------//
// LayoutTemplate
//----------------------------------------------------------------------------//
/**
 * LayoutTemplate
 *
 * The LayoutTemplate class
 *
 * The LayoutTemplate class
 *
 *
 * @package	ui_app
 * @class	LayoutTemplate
 * @extends BaseTemplate
 */
class LayoutTemplate extends BaseTemplate
{

}

//----------------------------------------------------------------------------//
// BaseTemplate
//----------------------------------------------------------------------------//
/**
 * BaseTemplate
 *
 * The BaseTemplate class
 *
 * The BaseTemplate class
 *
 *
 * @package	ui_app
 * @class	BaseTemplate
 */
class BaseTemplate
{

}

//----------------------------------------------------------------------------//
// ModuleLoader
//----------------------------------------------------------------------------//
/**
 * ModuleLoader
 *
 * The ModuleLoader class - loads modules when requested
 *
 * The ModuleLoader class - loads modules when requested
 *
 *
 * @package	ui_app
 * @class	ModuleLoader
 */
class ModuleLoader
{
	//------------------------------------------------------------------------//
	// _arrModules
	//------------------------------------------------------------------------//
	/**
	 * _arrModules
	 *
	 * list of modules currently loaded
	 *
	 * list of modules currently loaded
	 *
	 * @type		array 
	 *
	 * @property
	 */
	private $_arrModules;
	
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * returns the requested module
	 *
	 * returns the requested module.
	 * 
	 * @param	string	$strPropertyName		Name of the module to load
	 *
	 * @method
	 */
	function __get($strPropertyName)
	{
		
		if (!is_object($this->_arrModules[$strPropertyName]))
		{
			// try to instantiate the object
			$strClassName = "Module" . $strPropertyName;
			$this->_arrModules[$strPropertyName] = new $strClassName;			
		}
		
		return $this->_arrModules[$strPropertyName];
	}
}


//----------------------------------------------------------------------------//
// SubmittedData
//----------------------------------------------------------------------------//
/**
 * SubmittedData
 *
 * Handles all GET and POST data that has been sent to the page
 *
 * Handles all GET and POST data that has been sent to the page
 *
 * @package	ui_app
 * @class	SubmittedData
 */
class SubmittedData
{

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor 
	 *
	 * Constructor
	 *
	 * @param	array	$arrDefine	[optional] This currently isn't actually used anywhere
	 * @return	void
	 *
	 * @method
	 */
	function __construct($arrDefine=NULL)
	{
		// save local copy of define
		$this->_arrDefine = $arrDefine;
	}

	//------------------------------------------------------------------------//
	// Request
	//------------------------------------------------------------------------//
	/**
	 * Request()
	 *
	 * Attempts to convert each variable from $_REQUEST into a DBObject in DBO()
	 *
	 * Attempts to convert each variable from $_REQUEST into a DBObject in DBO()
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	function Request()
	{
		// for each request variable
		if(is_array($_REQUEST))
		{
			foreach($_REQUEST AS $strName=>$strValue)
			{
				// parse variable
				$this->_ParseData($strName, $strValue);
			}
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// Get
	//------------------------------------------------------------------------//
	/**
	 * Get()
	 *
	 * Attempts to convert each variable from $_GET into a DBObject in DBO()
	 *
	 * Attempts to convert each variable from $_GET into a DBObject in DBO()
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 */	
	function Get()
	{
		// for each get variable
		if(is_array($_GET))
		{
			foreach($_GET AS $strName=>$strValue)
			{
				// parse variable
				$this->_ParseData($strName, $strValue);
			}
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// Post
	//------------------------------------------------------------------------//
	/**
	 * Post()
	 *
	 * Attempts to convert each variable from $_POST into a DBObject in DBO()
	 *
	 * Attempts to convert each variable from $_POST into a DBObject in DBO()
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	function Post()
	{
		// for each post variable
		if(is_array($_POST))
		{
			foreach($_POST AS $strName=>$strValue)
			{
				// parse variable
				$this->_ParseData($strName, $strValue);
			}
			return TRUE;
		}
		return FALSE;
	}

	//------------------------------------------------------------------------//
	// Cookie
	//------------------------------------------------------------------------//
	/**
	 * Cookie()
	 *
	 * Attempts to convert each variable from $_COOKIE into a DBObject in DBO()
	 *
	 * Attempts to convert each variable from $_COOKIE into a DBObject in DBO()
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	function Cookie()
	{
		// for each cookie variable
		if(is_array($_COOKIE))
		{
			foreach($_COOKIE AS $strName=>$strValue)
			{
				// parse variable
				$this->_ParseData($strName, $strValue);
			}
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// Ajax
	//------------------------------------------------------------------------//
	/**
	 * Ajax()
	 *
	 * Attempts to convert AJAX data into DBObjects in DBO()
	 *
	 * Attempts to convert AJAX data into DBObjects in DBO()
	 *
	 * @param   object	 $objAjax	The submitted data from AJAX
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	function Ajax($objAjax)
	{
		// for each post variable
		if(is_object($objAjax) && is_object($objAjax->Objects))
		{
			foreach($objAjax->Objects AS $strObject=>$objObject)
			{
				foreach($objObject AS $strProperty=>$mixValue)
				{
					// parse variable
					$this->_ParseData("$strObject_$strProperty", $mixValue);
				}
			}
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// _ParseData
	//------------------------------------------------------------------------//
	/**
	 * _ParseData()
	 *
	 * Attempts to create a DBO object using the passed data
	 *
	 * Attempts to create a DBO object using the passed data
	 *
	 * @param	string	$strName	the Get or Post variable name.  This must be
	 *								in the format ObjectName_PropertyName_Context
	 *								where Context is an integer
	 *								the Context is optional, ObjectName_PropertyName
	 *								will suffice
	 * @param	mixed	$mixValue	the value for the property
	 * @return	boolean
	 *
	 * @method
	 */
	function _ParseData($strName, $mixValue)
	{
		// split name into object, property & [optional] context
		$arrName = explode("_", $strName, 3);
		
		// fail if we don't have object and property
		if(!$arrName[0] || !$arrName[1])
		{
			return FALSE;
		}
		
		// make sure context is an int
		$intContext = (int)$arrName[2];
		if (!$intContext)
		{
			// if not set it to the default context
			$intContext = CONTEXT_DEFAULT;
		}
		
		// add property to object
		DBO()->{$arrName[0]}->AddProperty($arrName[1], $mixValue, $intContext);
		return TRUE;
	}
}

?>

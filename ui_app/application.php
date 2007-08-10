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
 * Also currently contains all constants required of ui_app
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
 *
 *
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
 *									in this directory it then trys finding it in a subdirectory
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

	// retrieve the class name and its associated directory
	if (substr($strClassName, 0, 6) == "Module")
	{
		$strClassPath = MODULE_BASE_DIR . "module";
		$strClassName = substr($strClassName, 6);
	}
	else
	{
		$arrClassName = explode("Template", $strClassName);
		$strClassPath = TEMPLATE_BASE_DIR . strtolower($arrClassName[0]) . "_template";
		$strClassName = $arrClassName[1];
	}		

	// if $strClassName couldn't be exploded on "template" or "module" then die
	if (!$strClassName)
	{
		// The class trying to be loaded is not a template class
		// This function does not currently handle any other kinds of class
		return FALSE;
	}
	
	// Load a directory listing for $strClassPath
	_LoadDirectoryListing($strClassPath);

	// find the file that should contain the class which needs to be loaded
	$mixClassPointer = array_search(strtolower($strClassName) . ".php", $GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename']);
	
	if ($mixClassPointer === FALSE)
	{
		// the file could not be found so check for a subdirectory of $strClassPath matching the first word in $strClassName
		$strRegex = "^[A-Z][a-z]+[A-Z]";
		$mixLength = ereg($strRegex, $strClassName, $regs);
		if ($mixLength === FALSE)
		{
			// The class name is only one word long therefore it couldn't possibly be in a subdirectory
			// the class's file cannot be found
			return FALSE;
		}
		
		// subtract 1 from $mixLength as it will have included the first letter of the second word
		$mixLength--;
		
		// grab the first word (the sub directory)
		$strSubDir = substr($strClassName, 0, $mixLength);
		$strClassPath .= strtolower("/$strSubDir");
		
		// grab the filename
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
			//grab the filename part
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
	public $_intMode;
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
		// split template name
		$arrTemplate 	= explode ('.', $strTemplateName);
		$strClass 		= 'AppTemplate'.$arrTemplate[0];
		$strMethod 		= $arrTemplate[1];
		
		// Get user details (inc Permissions)
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

		// Create AppTemplate Object
		
		
		$this->objAppTemplate = new $strClass;
		
		$this->objAppTemplate->SetMode(HTML_MODE);
	
		// Run AppTemplate
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
		$this->_intMode = AJAX_MODE;
		// get submitted data
		$objSubmit = new SubmittedData();
		$objAjax = $objSubmit->Ajax();

		$strClass 		= 'AppTemplate'.$objAjax->Class;
		$strMethod 		= $objAjax->Method;
		
		//Get user details (inc Permissions)
		//$this->Dbo->Session->AuthenticatedEmployee->GetDetails();
		/*???can't this be done in the framework at the same time you build the Dbo object of 	variables
		--at this stage we ahavent defined this anywhere, needs to be somewhere\
		-- could be here or lower level*/
		
		
	
		// validate all submitted objects
		// Note that while $objSubmit->Get() and ->POST set up the submitted objects, they have not actually 
		// been loaded from the database, so validating them at this stage should always return TRUE
		DBO()->Validate();

		//Create AppTemplate Object
		//echo $strClass;
		$this->objAppTemplate = new $strClass;
		
		$this->objAppTemplate->SetMode($objSubmit->Mode, $objAjax);

		//Run AppTemplate
		$this->objAppTemplate->{$strMethod}();
		
		// Render Page
		if (Ajax()->HasCommands())
		{
			// Send back AJAX data as JSON
			Ajax()->Reply();
		}
		elseif (isset($this->objAppTemplate->Page))
		{
			// Only do a page render if a Page has been declared
			// if you are just rendering a single div, then a Page wont have been declared
			$this->objAppTemplate->Page->SetMode($objSubmit->Mode, $objAjax);
			$this->objAppTemplate->Page->Render();
		}
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
		// If there is a cookie then use it to find the details of the user and check if their session is still valid
		if (isset($_COOKIE['Id']) && isset($_COOKIE['SessionId']))
		{
			// Find the employee information for the employee declared in the cookie
			$selAuthenticated = new StatementSelect(
					"Employee",
					"*", 
					"Id = <Id> AND SessionId = <SessionId> AND SessionExpire > NOW() AND Archived = 0",
					null,
					1
				);
				
			$intRowsReturned = $selAuthenticated->Execute(Array("Id" => $_COOKIE['Id'], "SessionId" => $_COOKIE['SessionId']));
			$arrAuthentication = $selAuthenticated->Fetch();

			// check if an employee could be found
			if ($intRowsReturned)
			{
				// Employee was found.
				$bolLoggedIn = TRUE;
				
				//Load user object from db
				$this->_arrUser = $arrAuthentication;
			}
			else
			{
				// the employee could not be found
				$this->_arrUser = NULL;
				$bolLoggedIn = FALSE;
			}
		}
		else
		{
			// There was no cookie found
			$this->_arrUser = NULL;
			$bolLoggedIn = FALSE;
		}
		
		// Check if the user has just logged in
		if (isset($_POST['VixenUserName']) && isset($_POST['VixenPassword']))
		{
			// user has just logged in. Get the Id of the Employee (Identified by UserName and PassWord combination)
			$selSelectStatement = new StatementSelect (
				"Employee", 
				"*", 
				"UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", 
				null, 
				"1"
			);
			
			$selSelectStatement->Execute(Array("UserName"=>$_POST['VixenUserName'], "PassWord"=>$_POST['VixenPassword']));
			
			// Check if an employee was found
			if ($selSelectStatement->Count() == 1)
			{
				// The session is authenticated.
				// Therefore, we have to store the Authentication
				$this->_arrUser = $selSelectStatement->Fetch();

				// We have to create a new session Id for the user
				$this->_arrUser['SessionId'] = sha1(uniqid(rand(), true));

				$bolLoggedIn = TRUE;
			}
			else
			{
				// Could not find the user.  Login failed.
				$bolLoggedIn = FALSE;
			}
		}

		if ($bolLoggedIn)
		{
			//Update the user's session details in the employee table of the database
			if ($arrAuthentication['Privileges'] == USER_PERMISSION_GOD)
			{
				$arrUpdate = Array("SessionId" => $this->_arrUser['SessionId'], "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(), SEC_TO_TIME(" . GOD_TIMEOUT . "))"));
				$intTime = time() + GOD_TIMEOUT;
			}
			else
			{
				$arrUpdate = Array("SessionId" => $this->_arrUser['SessionId'], "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(), SEC_TO_TIME(" . USER_TIMEOUT . "))"));
				$intTime = time() + USER_TIMEOUT;
			}

			// update the table
			$updUpdateStatement = new StatementUpdate("Employee", "Id = <Id> AND Archived = 0", $arrUpdate);
			if ($updUpdateStatement->Execute($arrUpdate, Array("Id"=>$this->_arrUser['Id'])) === FALSE)
			{
				// could not update the user's session details in the database.  Mark user as not logged in
				$bolLoggedIn = FALSE;
			}
		}
		
		if ($bolLoggedIn)
		{
			//set the cookie
			setCookie("Id", $this->_arrUser['Id'], $intTime, "/");
			setCookie("SessionId", $this->_arrUser['SessionId'], $intTime, "/");
		}
		else
		{
			//The user is not logged in.  Redirect them to the login page
			if ($this->_intMode == AJAX_MODE)
			{
				Ajax()->AddCommand("Reload");
				Ajax()->Reply();
				die;
			}
			else
			{				
				require_once(TEMPLATE_BASE_DIR . "page_template/login.php");
				die;
			}	
		}
		
		// by default set user as local
		$this->_arrUser['IsLocal'] = TRUE;
		
		// user is logged in at this point
		
		// check for a server forced login
		if ($_SERVER['PHP_AUTH_USER'])
		{
			$arrServerLogin = explode('@', $_SERVER['PHP_AUTH_USER']);
			
			// check for username match
			if (strtolower($arrServerLogin[0]) != strtolower($this->_arrUser['UserName']))
			{
				// send login headers and die
				header('WWW-Authenticate: Basic realm="Yellow Billing"');
				header('HTTP/1.0 401 Unauthorized');
				die;
			}
			
			// check for customer match
			/*
			//TODO!flame! Make this work
			if (strtolower($arrServerLogin[1]) != strtolower(*************))
			{
				header('WWW-Authenticate: Basic realm="Yellow Billing"');
				header('HTTP/1.0 401 Unauthorized');
				die;
			}
			*/
			
			//TODO!flame! Ban Users/IP Addresses that try to hack the system
			
			
			// Set user Privileges to public + operator
			$this->_arrUser['Privileges'] = PERMISSION_OPERATOR | PERMISSION_PUBLIC;
			
			// Set user as remote
			$this->_arrUser['IsLocal'] = FALSE;
			
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
	 * @param		int		$intPagePerms		permissions required to use the page
	 * @param		bool	$bolRequireLocal	require the user to be local
	 * @return		void
	 * @method
	 *
	 */
	function PermissionOrDie($intPagePerms, $bolRequireLocal=NULL)
	{
		// check the current user permission against permissions passed in
		if ($this->UserHasPerm($intPagePerms, $bolRequireLocal))
		{
			return TRUE;
		}
		else
		{
			// ask user to login, then return to page
			if ($this->_intMode == AJAX_MODE)
			{
				Ajax()->AddCommand("Reload");
				Ajax()->Reply();
				die;
			}
			else
			{				
				require_once(TEMPLATE_BASE_DIR . "page_template/login.php");
				die;
			}	
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
	 * @param		int		$intPerms			permissions to check the user's permissions against
	 * @param		bool	$bolRequireLocal	require the user to be local
	 * @return		bool				
	 * @method
	 *
	 */
	function UserHasPerm($intPerms, $bolRequireLocal=NULL)
	{
		// check for local user
		if ($bolRequireLocal == TRUE && $this->_arrUser['IsLocal'] !== TRUE)
		{
			return FALSE;
		}
		// Do a binary 'AND' between the user's privilages and the paramerter
		$intChecked = $this->_arrUser['Privileges'] & $intPerms;
		
		// If the user has all the privileges defined in $intPerms, then $intChecked will equal $intPerms
		if ($intChecked == $intPerms)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// CheckClientAuth
	//------------------------------------------------------------------------//
	/**
	 * CheckClientAuth()
	 *
	 * Checks user authentication for clients (used by web_app)
	 * 
	 * Checks user authentication for clients (used by web_app)
	 *
	 * @return		void
	 * @method
	 *
	 */
	function CheckClientAuth()
	{
		// If there is a cookie then use it to find the details of the user and check if their session is still valid
		if (isset($_COOKIE['ClientId']) && isset($_COOKIE['ClientSessionId']))
		{
			// Find the contact information for the client declared in the cookie
			$selAuthenticated = new StatementSelect(
					"Contact",
					"*", 
					"Id = <ClientId> AND SessionId = <ClientSessionId> AND SessionExpire > NOW() AND Archived = 0",
					null,
					1
				);
				
			$intRowsReturned = $selAuthenticated->Execute(Array("ClientId" => $_COOKIE['ClientId'], "ClientSessionId" => $_COOKIE['ClientSessionId']));
			$arrAuthentication = $selAuthenticated->Fetch();

			// check if the user could be found
			if ($intRowsReturned)
			{
				// user was found.
				$bolLoggedIn = TRUE;
				
				// Load user object from db
				$this->_arrUser = $arrAuthentication;
			}
			else
			{
				// the user could not be found
				$this->_arrUser = NULL;
				$bolLoggedIn = FALSE;
			}
		}
		else
		{
			// There was no cookie found
			$this->_arrUser = NULL;
			$bolLoggedIn = FALSE;
		}
		
		// Check if the user has just logged in
		if (isset($_POST['VixenUserName']) && isset($_POST['VixenPassword']))
		{
			// user has just logged in. Get the Id of the contact (Identified by UserName and PassWord combination)
			$selSelectStatement = new StatementSelect (
				"Contact", 
				"*", 
				"UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", 
				null, 
				"1"
			);
			
			$selSelectStatement->Execute(Array("UserName"=>$_POST['VixenUserName'], "PassWord"=>$_POST['VixenPassword']));
			
			// Check if the contact was found
			if ($selSelectStatement->Count() == 1)
			{
				// The session is authenticated.
				// Therefore, we have to store the Authentication
				$this->_arrUser = $selSelectStatement->Fetch();

				// We have to create a new session Id for the user
				$this->_arrUser['SessionId'] = sha1(uniqid(rand(), true));

				$bolLoggedIn = TRUE;
			}
			else
			{
				// Could not find the user.  Login failed.
				DBO()->Login->Failed = 1;
				$bolLoggedIn = FALSE;
			}
		}

		if ($bolLoggedIn)
		{
			//Update the user's session details in the contact table of the database
			$arrUpdate = Array("SessionId" => $this->_arrUser['SessionId'], "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(), SEC_TO_TIME(" . USER_TIMEOUT . "))"));
			$intTime = time() + USER_TIMEOUT;

			// update the table
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id> AND Archived = 0", $arrUpdate);
			if ($updUpdateStatement->Execute($arrUpdate, Array("Id"=>$this->_arrUser['Id'])) === FALSE)
			{
				// could not update the user's session details in the database.  Mark user as not logged in
				$bolLoggedIn = FALSE;
			}
		}
		
		if ($bolLoggedIn)
		{
			//set the cookie
			setCookie("ClientId", $this->_arrUser['Id'], $intTime, "/");
			setCookie("ClientSessionId", $this->_arrUser['SessionId'], $intTime, "/");
		}
		else
		{
			//The user is not logged in.  Redirect them to the login page
			if ($this->_intMode == AJAX_MODE)
			{
				Ajax()->AddCommand("Reload");
				Ajax()->Reply();
				die;
			}
			else
			{				
				require_once(TEMPLATE_BASE_DIR . "page_template/login.php");
				die;
			}	
		}
	}

	//------------------------------------------------------------------------//
	// LogoutClient
	//------------------------------------------------------------------------//
	/**
	 * LogoutClient()
	 *
	 * Logs out the current "client" user (used by web_app.  users are defined in the Contact table of Vixen)
	 * 
	 * Logs out the current "client" user (used by web_app.  users are defined in the Contact table of Vixen)
	 *
	 * @return		void
	 * @method
	 *
	 */
	function LogoutClient()
	{
		// We only need to log the client out, if they are currently logged in	
		if (isset($_COOKIE['ClientId']) && isset($_COOKIE['ClientSessionId']))
		{
			// Find the contact information for the client declared in the cookie
			// I'm doing this as a safety measure so as to only logout the user if they are the proper user
			$selAuthenticated = new StatementSelect(
					"Contact",
					"*", 
					"Id = <ClientId> AND SessionId = <ClientSessionId> AND SessionExpire > NOW() AND Archived = 0",
					null,
					1
				);
				
			$intRowsReturned = $selAuthenticated->Execute(Array("ClientId" => $_COOKIE['ClientId'], "ClientSessionId" => $_COOKIE['ClientSessionId']));
			$arrAuthentication = $selAuthenticated->Fetch();

			// check if the user could be found
			if ($intRowsReturned)
			{
				// user was found.
				$bolLoggedIn = TRUE;
				
				// Load user object from db
				$this->_arrUser = $arrAuthentication;
			}
			else
			{
				// the user could not be found
				$this->_arrUser = NULL;
				$bolLoggedIn = FALSE;
			}
		}
		else
		{
			// There was no cookie found
			$this->_arrUser = NULL;
			$bolLoggedIn = FALSE;
		}
		
		if ($bolLoggedIn)
		{
			// Update the user's session details in the contact table of the database, so that the SessionExpire time is in the past
			$arrUpdate = Array("SessionId" => $this->_arrUser['SessionId'], "SessionExpire" => new MySQLFunction ("SUBTIME(NOW(), SEC_TO_TIME(" . USER_TIMEOUT . "))"));
			
			// update the table
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id> AND Archived = 0", $arrUpdate);
			if ($updUpdateStatement->Execute($arrUpdate, Array("Id"=>$this->_arrUser['Id'])) === FALSE)
			{
				// could not update the user's session details in the database.  Mark user as not logged in
				$bolLoggedIn = FALSE;
			}
		}

		// If ($bolLoggedIn === TRUE) then loggin out the user has failed.  I don't know what to do in this situation.  It probably wont ever occur.
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
class ApplicationTemplate extends ApplicationBaseClass
{
	public $Module;
	protected $_objAjax;
	protected $_intTemplateMode;
	
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
		parent::__construct();
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
	 * @param		obj	$objAjax	optional Ajax object
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetMode($intMode, $objAjax=NULL)
	{
		$this->_intTemplateMode = $intMode;
		$this->_objAjax = $objAjax;
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
	protected $_strMethod;
	protected $_strForm;
	protected $_strTemplate;
	protected $_objAjax;
	protected $_intTemplateMode;

	//------------------------------------------------------------------------//
	// _strContainerDivId
	//------------------------------------------------------------------------//
	/**
	 * _strContainerDivId
	 *
	 * Stores the Id of the div element that contains the rendered contents of this HtmlTemplate
	 *
	 * Stores the Id of the div element that contains the rendered contents of this HtmlTemplate
	 * Currently this is only used for updating a div through an ajax call
	 * If required, it should be set in the constructor of the HtmlTemplate
	 *
	 * @type		string
	 *
	 * @property
	 */
	protected $_strContainerDivId;
	
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
	
	function LoadAjaxJavascript($strFilename)
	{
		echo "<script type='text/javascript' src='" . JAVASCRIPT_BASE_DIR . "javascript/$strFilename.js'></script>\n";
	}
	
	function FormStart($strId, $strTemplate, $strMethod, $arrParams=NULL)
	{
		$this->_strMethod = $strMethod;
		$this->_strForm = "VixenForm_$strId";
		$this->_strTemplate = $strTemplate;
		
		$strParams = "";
		if (is_array($arrParams))
		{
			foreach($arrParams AS $strKey=>$strValue)
			{
				$arrParams[$strKey] = "$strKey=$strValue";
			}
			$strParams = "?".implode('&', $arrParams);
		}
		
		echo "<form id='{$this->_strForm}' method='post' action='vixen.php/$strTemplate/$strMethod/$strParams'>\n";
		//echo "<form id='{$this->_strForm}' method='get' action='vixen.php/$strTemplate/$strMethod'>\n";
		echo "<input type='hidden' value='$strId' name='VixenFormId'>\n";
	}
	
	function FormEnd()
	{
		echo "</form>\n";
	}
	
	function Submit($strLabel, $strStyleClass="InputSubmit")
	{
		
		//echo "<submit name='VixenButtonId' class='$strStyleClass' value='$strLabel'></submit>\n";
		echo "<input type='submit' class='$strStyleClass' name='VixenButtonId' value='$strLabel'></input>\n";
	}
	
	function Button($strLabel, $strHref, $strStyleClass="InputSubmit")
	{
		$strName = "VixenButton_". str_replace(" ", "", $strLabel);
		//$strId = rand();
		//echo "<submit name='VixenButtonId' class='$strStyleClass' value='$strLabel'></submit>\n";
		echo "<input type='button' class='$strStyleClass' id='$strName' name='$strName' value='$strLabel' onClick='$strHref'></input>\n";
	}
	
	// You may wish to include $strTarget as a parameter as it is what is used to determine
	// whether you are working in HtmlMode or AjaxMode
	function AjaxSubmit($strLabel, $strTemplate=NULL, $strMethod=NULL, $strTargetType=NULL, $strStyleClass="InputSubmit")
	{
		if (!$strTemplate)
		{
			$strTemplate = $this->_strTemplate;
		}
		if (!$strMethod)
		{
			$strMethod = $this->_strMethod;
		}
		if (is_object($this->_objAjax))
		{
			//echo $this->_objAjax->TargetType;
			$strTarget = $this->_objAjax->TargetType;
			$strId = $this->_objAjax->strId;
			$strSize = $this->_objAjax->strSize;
		}
		
		if ($strTargetType !== NULL)
		{
			$strTarget = $strTargetType;
		}
		
		echo "<input type='button' value='$strLabel' class='$strStyleClass' name='VixenPopupButtonId' onclick=\"Vixen.Ajax.SendForm('{$this->_strForm}', '$strLabel','$strTemplate', '$strMethod', '$strTarget', '$strId', '$strSize', '{$this->_strContainerDivId}')\"></input>\n";
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
	 * @param		obj	$objAjax	optional Ajax object
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetMode($intMode, $objAjax=NULL)
	{
		$this->_intTemplateMode = $intMode;
		$this->_objAjax = $objAjax;
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
/*class LayoutTemplate extends BaseTemplate
{

}*/

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
	public $Mode;

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
		// get form and button Id
		if ($_REQUEST['VixenFormId'])
		{
			$GLOBALS['*SubmittedForm'] = $_REQUEST['VixenFormId'];	
		}
		if ($_REQUEST['VixenButtonId'])
		{
			$GLOBALS['*SubmittedButton'] = $_REQUEST['VixenButtonId'];	
		}
		
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
	 * Also handles the submitted form's Id and the submitted button's Id
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
	 * Also handles the submitted form's Id and the submitted button's Id
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
	 * Also handles the submitted form's Id and the submitted button's Id
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	function Ajax()
	{
		// Get Ajax Data
		$objAjax = AjaxRecieve();

		// get form Id and Button Id
		if ($objAjax->FormId)
		{
			$GLOBALS['*SubmittedForm'] = $objAjax->FormId;
		}
		if ($objAjax->ButtonId)
		{
			$GLOBALS['*SubmittedButton'] = $objAjax->ButtonId;
		}

		// for each post variable
		if(is_object($objAjax) && is_object($objAjax->Objects))
		{
			// Set output mode
			if ($objAjax->HtmlMode)
			{
				$this->Mode = HTML_MODE;
			}
			else
			{
				$this->Mode = AJAX_MODE;
			}

			foreach($objAjax->Objects AS $strObject=>$objObject)
			{
				foreach($objObject AS $strProperty=>$mixValue)
				{
					// parse variable
					$this->_ParseData("{$strObject}_{$strProperty}", $mixValue);
				}
			}
		}
		return $objAjax;
		//return FALSE;
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
	 * This will also load the global variables $GLOBALS['*SubmittedForm']
	 * and $GLOBALS['*SubmittedButtin'] if $strName equals "VixenFormId" or "VixenButtonId"
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
		// check if $strName is either "VixenFormId" or "VixenButtonId"
		if ($strName == "VixenFormId")
		{
			$GLOBALS['*SubmittedForm'] = $mixValue;
			return TRUE;
		}
		if ($strName == "VixenButtonId")
		{
			$GLOBALS['*SubmittedButton'] = $mixValue;
			return TRUE;
		}
	
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

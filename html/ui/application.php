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
		$strRegex = "^[A-Z][a-z]+[A-Z]";
		$mixLength = ereg($strRegex, $strClassName, $regs);
		if ($mixLength === FALSE)
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
	// Load
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
	function Load($strTemplateName, $bolModal=FALSE)
	{
		// Check that the user's browser is supported.  This will die if the user's browser is not supported
		$this->_CheckBrowser();
	
		// Split template name
		$arrTemplate 	= explode ('.', $strTemplateName);
		$strClass 		= 'AppTemplate'.$arrTemplate[0];
		$strMethod 		= $arrTemplate[1];
		
		// Get submitted data
		$objSubmit = new SubmittedData();
		$objSubmit->Get();
		$objSubmit->Post();
	
		// Validate all submitted objects
		// Note that while $objSubmit->Get() and ->POST set up the submitted objects,  
		// they have not actually been loaded from the database
		DBO()->Validate();

		// Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		
		$this->objAppTemplate->SetMode(HTML_MODE);
		$this->objAppTemplate->SetModal($bolModal);
	
		// Run AppTemplate
		$fltStart = microtime(TRUE);		
		$this->objAppTemplate->{$strMethod}();
		$fltAppTemplateTime = microtime(TRUE) - $fltStart;		
		
		// Append default options to the Context Menu
		ContextMenu()->Employee_Console();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			ContextMenu()->Add_Customer();
		}
		ContextMenu()->Find_Customer();
		ContextMenu()->View_Recent_Customers();
		ContextMenu()->Available_Plans();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			ContextMenu()->Admin_Console();
		}
		ContextMenu()->Logout();
		
		
		// Render Page
		//ob_start();
		$fltStart = microtime(TRUE);				
		$this->objAppTemplate->Page->Render();
		$fltRenderTime = microtime(TRUE) - $fltStart;		
		
		//ob_end_flush();
		
		// Check if this is being rendered in Debug mode
		if ($GLOBALS['bolDebugMode'])
		{
			echo "Time taken to run the AppTemplate method: ". number_format($fltAppTemplateTime, 4, ".", "") ." seconds<br />";
			echo "Time taken to do the page render: ". number_format($fltRenderTime, 4, ".", "") ." seconds<br />";
		}
	}
	
	function LoadModal($strTemplateName)
	{
		return $this->Load($strTemplateName, TRUE);
	}
	
	//------------------------------------------------------------------------//
	// AjaxLoad
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
		
		// Get submitted data
		$objSubmit		= new SubmittedData();
		$objAjax		= $objSubmit->Ajax();
		$strClass 		= 'AppTemplate' . $objAjax->Class;
		$strMethod 		= $objAjax->Method;
		
		// Validate all submitted objects
		// Note that while $objSubmit->Get() and ->POST set up the submitted objects, they have not actually 
		// been loaded from the database, so validating them at this stage should always return TRUE
		DBO()->Validate();

		// Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		
		$this->objAppTemplate->SetMode($objSubmit->Mode, $objAjax);

		// Run AppTemplate
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
	// WebLoad
	//------------------------------------------------------------------------//
	/**
	 * WebLoad()
	 *
	 * Loads an extended ApplicationTemplate object which represents all the logic and layout of a single webpage of the application
	 *
	 * Loads an extended ApplicationTemplate object which represents all the logic and layout of a single webpage of the application
	 * Specifically for the Web Application (which telco clients use)
	 * These "web" functions should be put in their own class and extend the Application class
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
	function WebLoad($strTemplateName)
	{
		//TODO! Work out what CustomerGroup is being referenced, from  $_SERVER['SERVER_NAME'];

		// Check that the user's browser is supported.  This will die if the user's browser is not supported
		// TODO! I don't know if we should bother doing this, because eventually we will want this to be compatable with
		// as many browsers as possible
		$this->_CheckBrowser();
		
		//TODO! Authenticate the user and redirect them to the appropriate login screen if they aren't currently logged in
		// Currently Authentication is done from within the requested AppTemplate method, but it should be done here
	
		// Split template name
		$arrTemplate 	= explode ('.', $strTemplateName);
		$strClass 		= 'AppTemplate'.$arrTemplate[0];
		$strMethod 		= $arrTemplate[1];
		
		// Get submitted data
		$objSubmit = new SubmittedData();
		$objSubmit->Get();
		$objSubmit->Post();
	
		// Validate all submitted objects
		DBO()->Validate();

		// Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		
		$this->objAppTemplate->SetMode(HTML_MODE);
	
		// Run AppTemplate
		$fltStart = microtime(TRUE);		
		$this->objAppTemplate->{$strMethod}();
		$fltAppTemplateTime = microtime(TRUE) - $fltStart;		
		
		// Render Page
		$fltStart = microtime(TRUE);				
		$this->objAppTemplate->Page->Render();
		$fltRenderTime = microtime(TRUE) - $fltStart;		
		
		// Check if this is being rendered in Debug mode
		if ($GLOBALS['bolDebugMode'])
		{
			echo "Time taken to run the AppTemplate method: ". number_format($fltAppTemplateTime, 4, ".", "") ." seconds<br />";
			echo "Time taken to do the page render: ". number_format($fltRenderTime, 4, ".", "") ." seconds<br />";
		}
	}
	
	//------------------------------------------------------------------------//
	// _CheckBrowser
	//------------------------------------------------------------------------//
	/**
	 * _CheckBrowser()
	 *
	 * Checks that the User's browser is supported, and dies if it is not
	 *
	 * Checks that the User's browser is supported, and dies if it is not
	 * When it dies it should output an appropriate error message
	 *
	 * @return		void
	 * @method
	 *
	 */
	private function _CheckBrowser()
	{
		if (!Browser()->IsSupported)
		{
			echo APP_NAME . " does not support your current browser<br />\n";
			echo "It only supports the following browsers: " . SUPPORTED_BROWSERS_DESCRIPTION . "\n";
			die;
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
	 * This function is also responsible for setting $GLOBALS['bolDebugMode']
	 *
	 * @return		void
	 * @method
	 *
	 */
	function CheckAuth()
	{
		// If there is nothing about login in the session, record that the user is not logged in
		if (!array_key_exists('LoggedIn', $_SESSION))
		{
			$_SESSION['LoggedIn'] = FALSE;
		}

		// If the user is logged in but the session has expired
		if ($_SESSION['LoggedIn'] && $_SESSION['SessionExpire'] < time())
		{
			$_SESSION['LoggedIn'] = FALSE;
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
				$currentUser = $selSelectStatement->Fetch();

				// If this is a new user, clean out the session to remove any info for a previous user
				if (!array_key_exists('User', $_SESSION) || $_SESSION['User']['Id'] != $currentUser['Id'])
				{
					$_SESSION = array();
				}

				// The session is authenticated.
				// Therefore, we have to store the Authentication
				$_SESSION['User'] = $currentUser;
				$_SESSION['LoggedIn'] = TRUE;
			}
			else
			{
				// Could not find the user. Login failed.
				$_SESSION['LoggedIn'] = FALSE;
			}
		}

		if ($_SESSION['LoggedIn'])
		{
			//Update the user's session details in the employee table of the database
			$_SESSION['SessionExpire'] = time() + ($_SESSION['User']['Privileges'] == USER_PERMISSION_GOD ? GOD_TIMEOUT : USER_TIMEOUT);
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
		$_SESSION['User']['IsLocal'] = TRUE;
		
		// user is logged in at this point
		
		// check for a server forced login
		if (array_key_exists('PHP_AUTH_USER', $_SERVER) && $_SERVER['PHP_AUTH_USER'])
		{
			$arrServerLogin = explode('@', $_SERVER['PHP_AUTH_USER']);
			
			// check for username match
			if (strtolower($arrServerLogin[0]) != strtolower($_SESSION['User']['UserName']))
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
			
			// Remove all the user's privileges except for PERMISSION_OPERATOR, PERMISSION_PUBLIC and PERMISSION_OPERATOR_VIEW
			$intAllowableRemotePerms = PERMISSION_OPERATOR_VIEW | PERMISSION_OPERATOR | PERMISSION_PUBLIC;
			$_SESSION['User']['Privileges'] = $_SESSION['User']['Privileges'] & ($intAllowableRemotePerms);
			
			// Set user as remote
			$_SESSION['User']['IsLocal'] = FALSE;
		}
		
		// Work out if we are in Debug Mode or not
		$bolDebugMode = (isset($_COOKIE['DebugMode']))? $_COOKIE['DebugMode'] : 0; 
		if (isset($_GET['Debug']))
		{
			// Change the value of the DebugMode cookie
			$bolDebugMode = ($bolDebugMode) ? 0 : 1;
			setcookie("DebugMode", $bolDebugMode, 0, "/");
		}
		$GLOBALS['bolDebugMode'] = ($bolDebugMode && $this->UserHasPerm(PERMISSION_DEBUG)) ? TRUE : FALSE;
		
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
				Ajax()->AddCommand("Alert", "You do not have the required user privileges to perform this action");
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
		if ($bolRequireLocal == TRUE && $_SESSION['User']['IsLocal'] !== TRUE)
		{
			return FALSE;
		}
		// Do a binary 'AND' between the user's privilages and the paramerter
		$intChecked = $_SESSION['User']['Privileges'] & $intPerms;
		
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
	function CheckClientAuth($bolLinkBackToConsole=FALSE)
	{
		// If there is nothing about login in the session, record that the user is not logged in
		if (!array_key_exists('LoggedIn', $_SESSION))
		{
			$_SESSION['LoggedIn'] = FALSE;
		}

		// If the user is logged in but the session has expired
		if ($_SESSION['LoggedIn'] && $_SESSION['SessionExpire'] < time())
		{
			$_SESSION['LoggedIn'] = FALSE;
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
				$currentUser = $selSelectStatement->Fetch();
				
				// If the user logging in is not the same user to which previous session data belongs, clear out the old stuff!
				if (!array_key_exists('User', $_SESSION) || $_SESSION['User']['Id'] != $currentUser['Id'])
				{
					$_SESSION = array();
				}
				
				// The session is authenticated.
				// Therefore, we have to store the Authentication
				$_SESSION['User'] = $currentUser;
				$_SESSION['LoggedIn'] = TRUE;
			}
			else
			{
				// Could not find the user.  Login failed.
				DBO()->Login->Failed = TRUE;
				$_SESSION['LoggedIn'] = FALSE;
			}
		}

		if ($_SESSION['LoggedIn'])
		{
			//Update the user's session details in the employee table of the database
			$_SESSION['SessionExpire'] = time() + USER_TIMEOUT;
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
				// If the location you are loading doesn't render a page, the user can get stuck on the login screen
				// This can happen if the user's session has timed out, and they try and download a pdf
				if ($bolLinkBackToConsole)
				{
					DBO()->Login->ShowLink = TRUE;
				}
				require_once(TEMPLATE_BASE_DIR . "page_template/login.php");
				die;
			}	
		}
	}

	//------------------------------------------------------------------------//
	// Logout
	//------------------------------------------------------------------------//
	/**
	 * Logout()
	 *
	 * Logs out the current flex intranet user
	 * 
	 * Logs out the current flex intranet user
	 *
	 * @return		bool		TRUE if the logging out process was successful, else FALSE
	 * @method
	 */
	function Logout()
	{
		// Blank the PHP session
		$_SESSION = array();
		$_SESSION['LoggedIn'] = FALSE;

		return TRUE;
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
		// Blank the PHP session
		$_SESSION = array();
		$_SESSION['LoggedIn'] = FALSE;

		return TRUE;
	}
	
	
	//----------------------------------------------------------------------------//
	// GetUserId
	//----------------------------------------------------------------------------//
	/**
	 * GetUserId()
	 * 
	 * @param	void
	 * 
	 * @return	int	Id of current user (from COOKIE[])
	 */
	function GetUserId()
	{
		$id = 0;
		if ($_SESSION['LoggedIn'])
		{
			$id = $_SESSION['User']['Id'];
		}
		return (int)$id;
	}

	
	//----------------------------------------------------------------------------//
	// __get
	//----------------------------------------------------------------------------//
	/**
	 * __get()
	 * 
	 * This function os here for backwards compatibility only!
	 * 
	 * @param	String $propName of property to be retreived. MUST BE '_arrUser'
	 * 
	 * @return	array $_SESSION['User'] if $propName == '_arrUser', otherwise NULL
	 */
	function __get($propName)
	{
		if ($propName == '_arrUser')
		{
			return $_SESSION['User'];
		}
		return NULL;
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
	protected $_bolModal = FALSE;
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
			
			// Pass on modality
			if ($this->IsModal())
			{
				$this->Page->SetModal(TRUE);
			}
			
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
	
	//------------------------------------------------------------------------//
	// SetModal
	//------------------------------------------------------------------------//
	/**
	 * SetModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		int	$bolModal	Whether the page is to be rendered as a modal (complete) page
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetModal($bolModal)
	{
		$this->_bolModal = $bolModal;
	}
	
	//------------------------------------------------------------------------//
	// IsModal
	//------------------------------------------------------------------------//
	/**
	 * IsModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		void
	 *
	 * @return		boolean	whether the page is to be rendered as modal (complete) or not
	 * @method
	 *
	 */
	function IsModal()
	{
		return $this->_bolModal;
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
	protected $_bolModal = FALSE;

	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	protected $_intContext;

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

	//------------------------------------------------------------------------//
	// FormStart
	//------------------------------------------------------------------------//
	/**
	 * FormStart()
	 *
	 * Echos the starting tag of an html form element, which will be handled by an AppTemplate method 
	 * 
	 * Echos the starting tag of an html form element, which will be handled by an AppTemplate method
	 *
	 * @param		string	$strId			Uniquely identifies the form
	 * @param		string	$strTemplate	AppTemplate class which will be called to process the form, on submittion
	 * 										(do not include the AppTemaplte prefix to the class name)
	 * @param		string	$strMethod		Method of the AppTemplate class, which will be executed when the form is submitted
	 * @param		string	$arrParams		Any parameters to pass to the AppTemplate Method as GET variables
	 * 										(ie $arrParams['Account.Id'] = 1000123456)
	 * @return		void
	 * @method
	 */
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
		
		echo "<form id='{$this->_strForm}' method='post' action='flex.php/$strTemplate/$strMethod/$strParams'>\n";
		echo "<input type='hidden' value='$strId' name='VixenFormId' />\n";
	}
	
	//------------------------------------------------------------------------//
	// FormEnd
	//------------------------------------------------------------------------//
	/**
	 * FormEnd()
	 *
	 * Echos the closing tag of an html form element 
	 * 
	 * Echos the closing tag of an html form element
	 *
	 * @return		void
	 * @method
	 */
	function FormEnd()
	{
		echo "</form>\n";
	}
	
	//------------------------------------------------------------------------//
	// Submit
	//------------------------------------------------------------------------//
	/**
	 * Submit()
	 *
	 * Echos html code to create an input submit button element 
	 * 
	 * Echos html code to create an input submit button element
	 * 
	 * @param	string	$strLabel		The value/label for the submit button
	 * @param	string	$strStyleClass	optional, CSS class for the "input submit" element
	 *
	 * @return		void
	 * @method
	 */
	function Submit($strLabel, $strStyleClass="InputSubmit")
	{
		echo "<input type='submit' class='$strStyleClass' name='VixenButtonId' value='$strLabel'></input>\n";
	}
	
	//------------------------------------------------------------------------//
	// Button
	//------------------------------------------------------------------------//
	/**
	 * Button()
	 *
	 * Echos html code to create an input button element 
	 * 
	 * Echos html code to create an input button element
	 * 
	 * @param	string	$strLabel		The value/label for the input button element
	 * @param	string	$strHref		value for the onclick property of the input button element
	 * @param	string	$strStyleClass	optional, CSS class for the input button element
	 *
	 * @return		void
	 * @method
	 */
	function Button($strLabel, $strHref, $strStyleClass="InputSubmit")
	{
		$strName = "VixenButton_". str_replace(" ", "", $strLabel);
		
		// Change all the single quotes in $strHref to their html safe versions, so that it doesn't escape
		// out of the onlick='...' prematurely (this also converts double quotes)
		$strHref = htmlspecialchars($strHref, ENT_QUOTES);
		echo "<input type='button' class='$strStyleClass' id='$strName' name='$strName' value='$strLabel' onclick='$strHref'></input>\n";
	}
	
	//------------------------------------------------------------------------//
	// AjaxSubmit
	//------------------------------------------------------------------------//
	/**
	 * AjaxSubmit()
	 *
	 * Echos html code to create an input button element which submits, via ajax, the most recently declared form
	 * 
	 * Echos html code to create an input button element which submits, via ajax, the most recently declared form
	 * 
	 * @param	string	$strLabel		The value/label for the input button element
	 * @param	string	$strTemplate	optional, Name of the AppTemplate which will contain the method which will be used
	 * 									to handle the submittion.  This defaults to whatever AppTemplate was specified
	 * 									in the most recent call to FormStart
	 * @param	string	$strMethod		optional, Method of the AppTemplate which will be executed by the ajax call
	 * 									This defaults to whatever Method was specified
	 * 									in the most recent call to FormStart
	 * @param	string	$strTargetType	?
	 * @param	string	$strStyleClass	optional, CSS class for the input button element
	 *
	 * @return		void
	 * @method
	 */
	function AjaxSubmit($strLabel, $strTemplate=NULL, $strMethod=NULL, $strTargetType=NULL, $strStyleClass="InputSubmit")
	{
		$strTarget = '';
		$strId = '';
		$strSize = '';
		
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
		
		echo "<input type='button' value='$strLabel' class='$strStyleClass' name='VixenButtonId' onclick=\"Vixen.Ajax.SendForm('{$this->_strForm}', '$strLabel','$strTemplate', '$strMethod', '$strTarget', '$strId', '$strSize', '{$this->_strContainerDivId}')\"></input>\n";
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
	
	
	//------------------------------------------------------------------------//
	// SetModal
	//------------------------------------------------------------------------//
	/**
	 * SetModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		int	$bolModal	Whether the page is to be rendered as a modal (complete) page
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetModal($bolModal)
	{
		$this->_bolModal = $bolModal;
	}
	
	//------------------------------------------------------------------------//
	// IsModal
	//------------------------------------------------------------------------//
	/**
	 * IsModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		void
	 *
	 * @return		boolean	whether the page is to be rendered as modal (complete) or not
	 * @method
	 *
	 */
	function IsModal()
	{
		return $this->_bolModal;
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
		if (array_key_exists('VixenFormId', $_REQUEST) && $_REQUEST['VixenFormId'])
		{
			$GLOBALS['*SubmittedForm'] = $_REQUEST['VixenFormId'];	
		}
		if (array_key_exists('VixenButtonId', $_REQUEST) && $_REQUEST['VixenButtonId'])
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
	 * @return	object
	 *
	 * @method
	 */
	function Ajax()
	{
		// Get Ajax Data
		$objAjax = AjaxRecieve();

		// get form Id and Button Id
		if (isset($objAjax->FormId) && $objAjax->FormId)
		{
			$GLOBALS['*SubmittedForm'] = $objAjax->FormId;
		}
		if (isset($objAjax->ButtonId) && $objAjax->ButtonId)
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
					$this->_ParseData("{$strObject}.{$strProperty}", $mixValue, ".");
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
	 * @param	string	$strName		the Get or Post variable name.  This must be
	 *									in the format ObjectName_PropertyName_Context
	 *									where Context is an integer
	 *									the Context is optional, ObjectName_PropertyName
	 *									will suffice
	 * @param	mixed	$mixValue		the value for the property
	 * @param	string	$strSeparator	optional, defaults to "_".  The delimeter that concatinates the ObjectName_PropertyName_Context together
	 * @return	boolean
	 *
	 * @method
	 */
	function _ParseData($strName, $mixValue, $strSeparator="_")
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
		$arrName = explode($strSeparator, $strName, 3);
		
		// fail if we don't have object and property
		if(!$arrName[0] || !$arrName[1])
		{
			return FALSE;
		}
		
		// make sure context is an int
		$intContext = array_key_exists(2, $arrName) ? (int)$arrName[2] : 0;
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

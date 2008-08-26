<?php

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
	// instance
	//------------------------------------------------------------------------//
	/**
	 * instance()
	 *
	 * Returns a singleton instance of this class
	 *
	 * Returns a singleton instance of this class
	 *
	 * @return	__CLASS__
	 *
	 * @method
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$instance = new self();
		}

		return $instance;
	}

	public function LoadJsonHandler($strHandlerName, $strHandlerMethod, $subPath=NULL)
	{
		$strClass = 'JSON_Handler_'.$strHandlerName;
		$objJSON = new JSON_Services();

		// Create JSON_Handler Object (autoloaded from /html/ui/classes/json/handler/)
		try
		{
			// Create the handler object, passing in the path info as parameter
			$this->objJsonHandler = new $strClass($subPath);

			// Get the JSON request arguments
			$arrArgs = array_key_exists('json', $_POST) ? $objJSON->decode($_POST['json']) : array();
			if (!is_array($arrArgs))
			{
				$arrArgs = array(0 => $arrArgs);
			}

			// Check that the function exists
			if (!method_exists($this->objJsonHandler, $strHandlerMethod))
			{
				throw new Exception ('The requested function is not supported: ' . $strHandlerName . '::' . $strHandlerMethod);
			}

			// Run the handler
			$response = call_user_func_array(array(0 => $this->objJsonHandler, 1 => $strHandlerMethod), $arrArgs);
		}
		catch(Exception $e)
		{
			// Send back an error so the JavaScript knows it failed
			$response = array('ERROR' => $e->getMessage());
		}

		echo $objJSON->encode($response);
	}

	public function LoadPageHandler($strHandlerName, $strHandlerMethod, $subPath=NULL, $bolModal=FALSE)
	{
		// Check that the user's browser is supported.  This will die if the user's browser is not supported
		$this->_CheckBrowser();
		
		// Split template name
		$strClass 		= 'Application_Handler_'.$strHandlerName;

		// Get submitted data
		$objSubmit = new SubmittedData();
		$objSubmit->Get();
		$objSubmit->Post();

		// Validate all submitted objects
		// Note that while $objSubmit->Get() and ->Post set up the submitted objects,  
		// they have not actually been loaded from the database
		DBO()->Validate();

		// Create AppTemplate Object (autoloaded from /html/ui/app_template/
		$this->objAppTemplate = new $strClass();
		
		$this->objAppTemplate->SetMode(HTML_MODE);
		$this->objAppTemplate->SetModal($bolModal);
	
		// Run AppTemplate
		$fltStart = microtime(TRUE);		
		$this->objAppTemplate->{$strHandlerMethod}($subPath);
		$fltAppTemplateTime = microtime(TRUE) - $fltStart;		
		
		// Append default options to the Context Menu
		ContextMenu()->Employee_Console();
		ContextMenu()->Customer->View_Recent_Customers();
		ContextMenu()->Customer->Find_Customer();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			ContextMenu()->Customer->Add_Customer();
		}
		ContextMenu()->Available_Plans();
		require_once dirname(__FILE__).'/../../../lib/ticketing/Ticketing_User.php';
		if (Ticketing_User::currentUserIsTicketingUser())
		{
			ContextMenu()->Ticketing->TicketingConsole();
			ContextMenu()->Ticketing->ViewUserTickets();
			ContextMenu()->Ticketing->AddTicket();
		}
		if (Ticketing_User::currentUserIsTicketingAdminUser())
		{
			ContextMenu()->Ticketing->Reports->TicketingSummaryReport();
			ContextMenu()->Ticketing->Administration->TicketingAdmin();
			ContextMenu()->Ticketing->Administration->TicketingAttachmentTypes();
		}
		
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			//ContextMenu()->Admin_Console();
			ContextMenu()->Admin->AdvancedAccountSearch();
			ContextMenu()->Admin->AdvancedContactSearch();
			ContextMenu()->Admin->AdvancedServiceSearch();
			
			ContextMenu()->Admin->Adjustments->ManageAdjustments();
			ContextMenu()->Admin->Adjustments->ManageSingleAdjustmentTypes();
			ContextMenu()->Admin->Adjustments->ManageRecurringAdjustmentTypes();
			
			ContextMenu()->Admin->PaymentDownload();
			ContextMenu()->Admin->MoveDelinquentCDRs();
			ContextMenu()->Admin->DataReports();
			
			ContextMenu()->Admin->ManageEmployees();
			ContextMenu()->Admin->ManageInvoiceRunEvents();
			
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
			{
				ContextMenu()->Admin->System_Settings->View_All_Constants();
				ContextMenu()->Admin->System_Settings->ManagePaymentTerms();
			}
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_CUSTOMER_GROUP_ADMIN))
			{
				ContextMenu()->Admin->System_Settings->ViewAllCustomerGroups();
			}
		}
		
		
		// Render Page
		//ob_start();
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

		// Create AppTemplate Object (autoloaded from /html/ui/app_template/
		$this->objAppTemplate = new $strClass;
		
		$this->objAppTemplate->SetMode(HTML_MODE);
		$this->objAppTemplate->SetModal($bolModal);
	
		ContextMenu()->Employee_Console();
		
		// Run AppTemplate
		$fltStart = microtime(TRUE);		
		$this->objAppTemplate->{$strMethod}();
		$fltAppTemplateTime = microtime(TRUE) - $fltStart;		
		
		// Append default options to the Context Menu
		ContextMenu()->Customer->View_Recent_Customers();
		ContextMenu()->Customer->Find_Customer();
		
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			ContextMenu()->Customer->Add_Customer();
		}
		
		ContextMenu()->Available_Plans();
		
		require_once dirname(__FILE__).'/../../../lib/ticketing/Ticketing_User.php';
		if (Ticketing_User::currentUserIsTicketingUser())
		{
			ContextMenu()->Ticketing->TicketingConsole();
			ContextMenu()->Ticketing->ViewUserTickets();
			ContextMenu()->Ticketing->AddTicket();
		}
		if (Ticketing_User::currentUserIsTicketingAdminUser())
		{
			ContextMenu()->Ticketing->Reports->TicketingSummaryReport();
			ContextMenu()->Ticketing->Administration->TicketingAdmin();
			ContextMenu()->Ticketing->Administration->TicketingAttachmentTypes();
		}
		
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			//ContextMenu()->Admin_Console();
			ContextMenu()->Admin->AdvancedAccountSearch();
			ContextMenu()->Admin->AdvancedContactSearch();
			ContextMenu()->Admin->AdvancedServiceSearch();
			
			ContextMenu()->Admin->Adjustments->ManageAdjustments();
			ContextMenu()->Admin->Adjustments->ManageSingleAdjustmentTypes();
			ContextMenu()->Admin->Adjustments->ManageRecurringAdjustmentTypes();
			
			ContextMenu()->Admin->PaymentDownload();
			ContextMenu()->Admin->MoveDelinquentCDRs();
			ContextMenu()->Admin->DataReports();
			
			ContextMenu()->Admin->ManageEmployees();
			ContextMenu()->Admin->ManageInvoiceRunEvents();
			
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
			{
				ContextMenu()->Admin->System_Settings->View_All_Constants();
				ContextMenu()->Admin->System_Settings->ManagePaymentTerms();
			}
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_CUSTOMER_GROUP_ADMIN))
			{
				ContextMenu()->Admin->System_Settings->ViewAllCustomerGroups();
			}
			
			
		}

		
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
	 * Specifically for the Web Application (/html/customer) (which telco clients use)
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
			/*
			echo APP_NAME . " does not support your current browser<br />\n";
			echo "It only supports the following browsers: " . SUPPORTED_BROWSERS_DESCRIPTION . "\n";
			die;
			*/
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

		$bolAttemptingLogIn = FALSE;
		if (isset($_POST['VixenUserName']) && isset($_POST['VixenPassword']))
		{
			// The user is loging in via the login page
			$bolAttemptingLogIn	= TRUE;
			$strUserName		= $_POST['VixenUserName'];
			$strPassword		= $_POST['VixenPassword'];
		}
		elseif ($this->_intMode == AJAX_MODE && DBO()->Login->UserName->IsSet && DBO()->Login->Password->IsSet)
		{
			// The user is loging in via ajax
			$bolAttemptingLogIn	= TRUE;
			$strUserName		= DBO()->Login->UserName->Value;
			$strPassword		= DBO()->Login->Password->Value;
		}

		// Check if the user has just logged in
		if ($bolAttemptingLogIn)
		{
			// user has just logged in. Get the Id of the Employee (Identified by UserName and PassWord combination)
			$selSelectStatement = new StatementSelect (
				"Employee", 
				"*", 
				"UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", 
				null, 
				"1"
			);

			$selSelectStatement->Execute(Array("UserName"=>$strUserName, "PassWord"=>$strPassword));

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
			$_SESSION['SessionDuration'] = ($_SESSION['User']['Privileges'] == USER_PERMISSION_GOD ? GOD_TIMEOUT : USER_TIMEOUT);
			$_SESSION['SessionExpire'] = time() + $_SESSION['SessionDuration'];
		}
		else
		{
			//The user is not logged in.  Redirect them to the login page
			if ($this->_intMode == AJAX_MODE)
			{
				//Ajax()->AddCommand("Reload");
				if ($bolAttemptingLogIn)
				{
					// The user has just attempted to log in via the login popup
					AjaxReply(array("Success" => FALSE));
					die;
				}
				
				// The user needs to log in.  Show the login popup
				Ajax()->AddCommand("VerifyUser");
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
		
		// Check if the user has just successfully logged in via Ajax
		if ($this->_intMode == AJAX_MODE && $bolAttemptingLogIn && $_SESSION['LoggedIn'])
		{
			// Reply to the Ajax request
			AjaxReply(array("Success" => TRUE));
			die;
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
			$this->InsufficientPrivilegeDie();
		}
	}
	
	function InsufficientPrivilegeDie()
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

				// Get the Account table.
				DBO()->Account->Id = $currentUser['Account'];
				DBO()->Account->Load();

				// Get the CustomerGroup table.
				DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
				DBO()->CustomerGroup->Load();
				
				// Save when the user last logged in.
				DBO()->Contact->LastLogin = time();
				DBO()->Contact->SetColumns("LastLogin");
				DBO()->Contact->Save();

				// Check if CustomersGroup in database matches the URL being used.
				if(!eregi($_SERVER['HTTP_HOST'],DBO()->CustomerGroup->flex_url->Value)){
					header("Location: " . DBO()->CustomerGroup->flex_url->Value);
				}

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

			// user is already logged in. Get the CustomerGroup
			$selSelectStatement = new StatementSelect (
				"Contact", 
				"*", 
				"UserName = <UserName> AND PassWord = <PassWord> AND Archived = 0", 
				null, 
				"1"
			);
			
			$selSelectStatement->Execute(Array("UserName"=>$_SESSION['User']['UserName'], "PassWord"=>$_SESSION['User']['PassWord']));
			$currentUser = $selSelectStatement->Fetch();

			// Get the Account table.
			DBO()->Account->Id = $currentUser['Account'];
			DBO()->Account->Load();

			// Get the CustomerGroup table.
			DBO()->CustomerGroup->Id = DBO()->Account->CustomerGroup->Value;
			DBO()->CustomerGroup->Load();

			// Check if CustomersGroup in database matches the URL being used.
			if(!eregi($_SERVER['HTTP_HOST'],DBO()->CustomerGroup->flex_url->Value)){
				header("Location: " . DBO()->CustomerGroup->flex_url->Value);
			}

			//Update the user's session details in the employee table of the database
			$_SESSION['SessionDuration'] = USER_TIMEOUT;
			$_SESSION['SessionExpire'] = time() + $_SESSION['SessionDuration'];
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

?>

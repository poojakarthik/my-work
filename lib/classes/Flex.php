<?php

final class Flex
{
	// Define the session cookie names used by flex
	const FLEX_ADMIN_SESSION = 'flex_admin_sess_id';
	const FLEX_CUSTOMER_SESSION = 'flex_cust_sess_id';

	// This is a static library - prevent initialisation!
	private function __construct(){}
	private function Flex(){}

	public static function startSession($username, $password)
	{
		// TODO :: Login to application
	}

	function continueSession($sessionName=self::FLEX_CUSTOMER_SESSION)
	{
		// Start the session
		session_cache_limiter('private');
		session_name($sessionName);
		session_start();

		if (!self::loggedIn() 
		 // or the user's session has expired due to inactivity
		 || $_SESSION['SessionExpire'] < time())
		{
			return FALSE;
		}

		// The session is valid, so extend it
		$_SESSION['SessionExpire'] = time() + $_SESSION['SessionDuration'];

		return TRUE;
	}

	public static function loggedIn()
	{
		return array_key_exists('LoggedIn', $_SESSION) && $_SESSION['LoggedIn'];
	}

	public static function getUsername()
	{
		if (self::loggedIn())
		{
			return $_SESSION['User']['UserName'];
		}
		return NULL;
	}

	public static function getUserId()
	{
		if (self::loggedIn())
		{
			return $_SESSION['User']['Id'];
		}
		return NULL;
	}

	public static function getDisplayName()
	{
		if (self::loggedIn())
		{
			$name = $_SESSION['User']['FirstName'];
			if ($_SESSION['User']['LastName'])
			{
				if ($name) $name .= ' ';
				$name .= $_SESSION['User']['LastName'];
			}
			return $name ? $name : $_SESSION['User']['UserName'];
		}
		return NULL;
	}

	public static function endSession($sessionName=self::FLEX_CUSTOMER_SESSION)
	{
		// Logout of application, clearing session contents
		self::continueSession($sessionName);
		$_SESSION = array();
	}

	public static function framework($loadDbConstants=TRUE)
	{
		static $framework;
		if (!isset($framework))
		{
			$framework = LoadFramework(NULL, TRUE, $loadDbConstants);
		}
		return $framework;
	}

	public static function frameworkUrlBase()
	{
		return self::getUrlBase() . '../ui/';
	}

	// Returns the relative base path of the Framework for the applications
	public static function relativeFrameworkBase()
	{
		return 'html'.DIRECTORY_SEPARATOR.'ui'.DIRECTORY_SEPARATOR;
	}

	// Returns the absolute base path of the Framework for the applications
	public static function frameworkBase()
	{
		static $frameworkBase;
		if (!isset($frameworkBase))
		{
			$frameworkBase = self::getBase() . self::relativeFrameworkBase();
		}
		return $frameworkBase;
	}

	public static function isAdminSession()
	{
		return session_name() == self::FLEX_ADMIN_SESSION;
	}

	public static function isCustomerSession()
	{
		return session_name() == self::FLEX_CUSTOMER_SESSION;
	}

	public static function applicationUrlBase()
	{
		static $applicationUrlBase;
		if (!isset($applicationUrlBase))
		{
			switch(session_name())
			{
				case self::FLEX_ADMIN_SESSION:
					$applicationUrlBase = self::getUrlBase() . '../admin/';
					break;
				case self::FLEX_CUSTOMER_SESSION:
					$applicationUrlBase = self::getUrlBase() . '../customer/';
					break;
				default:
					$applicationUrlBase = FALSE;
			}
		}
		return $applicationUrlBase;
	}

	// Returns the relative base path of the web application
	public static function relativeApplicationBase()
	{
		static $relativeApplicationBase;
		if (!isset($relativeApplicationBase))
		{
			switch(session_name())
			{
				case self::FLEX_ADMIN_SESSION:
					$relativeApplicationBase = 'html' . DIRECTORY_SEPARATOR . 'admin'.DIRECTORY_SEPARATOR;
					break;
				case self::FLEX_CUSTOMER_SESSION:
					$relativeApplicationBase = 'html' . DIRECTORY_SEPARATOR . 'customer'.DIRECTORY_SEPARATOR;
					break;
				default:
					$relativeApplicationBase = FALSE;
			}
		}
		return $relativeApplicationBase;
	}
	
	// Returns the absolute base path of the web application
	public static function applicationBase()
	{
		static $applicationBase;
		if (!isset($applicationBase))
		{
			$applicationBase = self::getBase() . self::relativeApplicationBase();
		}
		return $applicationBase;
	}

	public static function load($loadDbConstants=TRUE)
	{
		// Only load once or we'll have problems with autoloading...
		static $loaded;
		if (isset($loaded))
		{
			return;
		}
		$loaded = TRUE;

		// Load the AutoloadException class before registering the autoload function
		self::requireOnce('lib/classes/AutoloadException.php');

		// spl_autoload_register is available by default from PHP 5.3.0
		if (function_exists('spl_autoload_register'))
		{
			spl_autoload_register(array('Flex', 'autoload'));
		}
		else
		{
			// Ugly, but until PHP 5.3.0 this should do the job
			eval("
				function __autoload(\$strClassName)
				{
					return Flex::autoload(\$strClassName);
				}
			");
		}

		self::requireOnce(
			'flex.cfg.php',
			'lib/framework/functions.php');

		self::framework($loadDbConstants);

		// Include files from the Application (either admin or customer app)
		$relativeApplicationBase = self::relativeApplicationBase();
		if ($relativeApplicationBase)
		{
			self::requireOnce($relativeApplicationBase . 'definitions.php');
		}

		// Include files from the Framework
		$relativeFrameworkBase = self::relativeFrameworkBase();
		if ($relativeFrameworkBase)
		{
			self::requireOnce(
				$relativeFrameworkBase . 'functions.php',
				$relativeFrameworkBase . 'style_template/html_elements.php'
			);
		}
	}

	public static function autoload($strClassName)
	{
		$subDirs = explode('_', strtolower($strClassName));
		if ($subDirs[0] == 'flex')
		{
			array_shift($subDirs);
		}
		array_unshift($subDirs, '');

		$accumulatedPath = '';
		//TODO! Instead of having 1 loop which tests all 4 possible locations, It should be as 4 separate loops, because there is a precedence to the locations
		foreach ($subDirs as $subDir)
		{
			$accumulatedPath .= $subDir . DIRECTORY_SEPARATOR;

			// Check the specific application for the class
			// Classes specific to the web application (admin or customer) will be located here 
			if (self::applicationBase() && file_exists(self::applicationBase().'classes'.$accumulatedPath.$strClassName.'.php'))
			{
				require_once self::applicationBase().'classes'.$accumulatedPath.$strClassName.'.php';
				if (class_exists($strClassName, FALSE))
				{
					return TRUE;
				}
			}
			
			// Check the applications framework for the class
			// Classes that are used by all of the web applications will be located here
			if (self::frameworkBase() && file_exists(self::frameworkBase().'classes'.$accumulatedPath.$strClassName.'.php'))
			{
				require_once self::frameworkBase().'classes'.$accumulatedPath.$strClassName.'.php';
				if (class_exists($strClassName, FALSE))
				{
					return TRUE;
				}
			}

			// Check the lib/classes directory for the class (all classes should probably be ket here)
			// All model classes are kept here
			if (file_exists(self::getBase().'lib/classes'.$accumulatedPath.$strClassName.'.php'))
			{
				require_once self::getBase().'lib/classes'.$accumulatedPath.$strClassName.'.php';
				if (class_exists($strClassName, FALSE))
				{
					return TRUE;
				}
			}

			// Check the lib directory for the class the autoload function should not really be used for loading these classes.
			// Libraries should really be included explicitly and each should load it's own classes 
			// (otherwise it isn't much use as a stand-alone library!)
			if (file_exists(self::getBase().'lib'.$accumulatedPath.$strClassName.'.php'))
			{
				require_once self::getBase().'lib'.$accumulatedPath.$strClassName.'.php';
				if (class_exists($strClassName, FALSE))
				{
					return TRUE;
				}
			}
		}

		// Try to load the class using the old method (taken from html/ui/application.php)
		self::oldAutoload($strClassName);
		if (class_exists($strClassName, FALSE))
		{
			return TRUE;
		}

		// Last ditch attempt, see if the file exists in the include path
		@include_once($strClassName.'.php');
		if (class_exists($strClassName, FALSE))
		{
			return TRUE;
		}

		// ... and again, but in lowercase ...
		@include_once(strtolower($strClassName).'.php');
		if (class_exists($strClassName, FALSE))
		{
			return TRUE;
		}

		// Create an error class so that we can handle this failure gracefully
		// (This will only work as of PHP 5.3.0. Until then this would just complicate matters!)
		if (version_compare(PHP_VERSION, '5.3.0', '>='))
		{
			self::autoloadError($strClassName);
		}
		return TRUE;
	}

	private static function autoloadError($strClassName)
	{
		eval("class $strClassName {
			public function __construct() {
				throw new AutoloadException('Class $strClassName not found');
			}

			// As of PHP 5.3.0 ...
			public static function __callStatic(\$m, \$args) {
				throw new AutoloadException('Class $strClassName not found');
			}
		}");
	}

	public static function getUrlBase()
	{
		static $strBaseDir;
		if (!isset($strBaseDir))
		{
			$strBaseDir = dirname($_SERVER['SCRIPT_NAME']) . "/";
			if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])
			{
				$strBaseDir = "https://{$_SERVER['SERVER_NAME']}$strBaseDir";
			}
			else
			{
				$strBaseDir = "http://{$_SERVER['SERVER_NAME']}$strBaseDir";
			}
		}
		return $strBaseDir;
	}

	public static function getPathInfo()
	{
		return explode('/', trim($_SERVER['PATH_INFO'] , ' /'));
	}

	public static function getBase()
	{
		static $base;
		if (!isset($base))
		{
			$base = realpath(dirname(__FILE__) . '/../../').DIRECTORY_SEPARATOR;
		}
		return $base;
	}

	public static function getRelativeBase()
	{
		return "..". DIRECTORY_SEPARATOR ."..". DIRECTORY_SEPARATOR;
	}

	public static function requireOnce()
	{
		$args = func_get_args();
		foreach ($args as $arg)
		{
			if (is_array($arg))
			{
				self::requireOnce($arg);
			}
			else
			{if  (!file_exists(self::getBase().$arg)) throw new Exception('Required file not found.');
				require_once self::getBase().$arg;
			}
		}
	}



	private static function oldAutoload($strClassName)
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
		self::oldLoadDirectoryListing($strClassPath);
	
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
			self::oldLoadDirectoryListing($strClassPath);
			
			// search again for the file that should contain the class which needs to be loaded
			$mixClassPointer = array_search(strtolower($strClassName) . ".php", $GLOBALS['*arrAvailableFiles'][$strClassPath]['CorrectedFilename']);
		}
		
		// include the php file that defines the class
		if ($mixClassPointer !== FALSE)
		{
			include_once($strClassPath . "/" . $GLOBALS['*arrAvailableFiles'][$strClassPath]['ActualFilename'][$mixClassPointer]);
			return TRUE;
		}
		return FALSE;
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
	private static function oldLoadDirectoryListing($strPath)
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
}

?>

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
 * The __autoload function is used to dynamically include the php file
 * required to instantiate a class
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

require_once('functions.php');
require_once('framework.php');
$myApplication = new Application;


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
 * @param	string	$strClassName	The class to load
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
		$strErrorMsg = 	"ERROR: The class '". $strClassName.
						"' is not a template class as it does not include the keyword 'Template'. ".
						"currently the autoloader only handles template classes.";
		Debug($strErrorMsg);
		throw new Exception($strErrorMsg);
		die;  // I don't think this will ever actually be called
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
	 * @param		string	$strTemplateName	The name of the application template to load.
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
		$objSubmit = new submitted_data();
		$objSubmit->Get();
		$objSubmit->POST();
	
		
		
		//Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		$this->objAppTemplate->SetMode(HTML_MODE);
		
		//Run AppTemplate
		$this->objAppTemplate->{$strMethod}();
		
		//Add context menu
		//Add in JS & CSS
		//Add in documentation
	
		// Render Page
		$this->objAppTemplate->Page->Render();
		
		/*
		??? call the render function of the page object
		??? decide where context, doco and js/css go, and also include breadcrumbs
		*/
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
	
		
		
		//Create AppTemplate Object
		$this->objAppTemplate = new $objAjax->strClass;
		$this->objAppTemplate->SetMode(AJAX_MODE);
		
		//Run AppTemplate
		$this->objAppTemplate->{$objAjax->strMethod}();
		
		$arrReply = Array();
		
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
		
		AjaxReply($arrReply);
		
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
	 *
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

class ModuleLoader
{
	private $_arrModules;
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
// submitted_data
//----------------------------------------------------------------------------//
/**
 * submitted_data
 *
 * <short description>
 *
 * <long description>
 *
 *
 * @prefix	<prefix>
 *
 * @package	<package_name>
 * @parent	<full.parent.path>
 * @class	<ClassName||InstanceName>
 * @extends	<ClassName>
 */
class submitted_data
{

	//------------------------------------------------------------------------//
	// __Construct
	//------------------------------------------------------------------------//
	/**
	 * __Construct()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	array	$arrDefine	[optional] <description>
	 * @return	void
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function __Construct($arrDefine=NULL)
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
	 * <short description>
	 *
	 * <long description>
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	 * <short description>
	 *
	 * <long description>
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	 * <short description>
	 *
	 * <long description>
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	 * <short description>
	 *
	 * <long description>
	 *
	 *
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param   object	 $objAjax	The submitted data from AJAX
	 *
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
					$this->_ParseData("$strObject.$strProperty", $mixValue);
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
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strName	<description>
	 * @param	mixed	$mixValue	<description>
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function _ParseData($strName, $mixValue)
	{
		// print_r($strName);
		// split name into object & property
		$arrName = explode("_", $strName, 2);
		if(!$arrName[0] || !$arrName[1])
		{
			return FALSE;
		}
		
		// add property to object
		Dbo()->{$arrName[0]}->AddProperty($arrName[1], $mixValue);
		return TRUE;
	}
}

?>

<?php

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
		$arrName = explode($strSeparator, $strName);
		
		// fail if we don't have object and property
		if(!$arrName[0] || !$arrName[1])
		{
			return FALSE;
		}
		
		// make sure context is an int
		$nrParts = count($arrName);
		$lastPart = $arrName[$nrParts - 1];
		$hasContext = $nrParts > 2 && preg_match("/^[0-9]+\$/", $lastPart);
		$intContext = $hasContext ? array_pop($arrName) : 0;
		if (!$intContext)
		{
			// if not set it to the default context
			$intContext = CONTEXT_DEFAULT;
		}
		
		$strObject = array_shift($arrName);
		$strProerty = implode($strSeparator, $arrName);
		
		// add property to object
		DBO()->{$strObject}->AddProperty($strProerty, $mixValue, $intContext);
		return TRUE;
	}

}

?>

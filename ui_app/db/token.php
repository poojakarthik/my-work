<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// token
//----------------------------------------------------------------------------//
/**
 * token
 *
 * Contains all of the Database Token classes
 *
 * Contains all of the Database Token classes
 *
 * @file		token.php
 * @language	PHP
 * @package		ui_app
 * @author		Rich 'Waste Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// PropertyToken
//----------------------------------------------------------------------------//
/**
 * PropertyToken
 *
 * Token Property Object for Database Objects
 *
 * Token Property Object for Database Objects
 *
 *
 * @prefix	tok
 *
 * @package	framework_ui
 * @class	PropertyToken
 */
class PropertyToken
{
	//------------------------------------------------------------------------//
	// Properties
	//------------------------------------------------------------------------//
	private $_dboOwner;
	private $_strProperty;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Token constructor
	 *
	 * Token constructor
	 *
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_dboOwner	= NULL;
		$this->_strProperty	= NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// Property
	//------------------------------------------------------------------------//
	/**
	 * Property()
	 *
	 * Token Object takes form of the passed Property and returns itself
	 *
	 * Token Object takes form of the passed Property and returns itself
	 *
	 * @param	DBObject		$dboOwner	The owner object
	 * @param	string			
	 *
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function _Property($dboOwner, $strProperty)
	{
		$this->_dboOwner	= $dboOwner;
		$this->_strProperty	= $strProperty;
		//Debug("Token = {$dboOwner->_strName}->$strProperty");
		return $this;
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Accessor for Token Property's... Properties? 
	 *
	 * Accessor for Token Property's... Properties?
	 *
	 * @param	string	$strName	Property's Property
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __get($strName)
	{
		//Debug("Get: {$this->_dboOwner->_strName}->{$this->_strProperty}->$strName");

		// Are we after one of our "magic" variables?
		switch (strtolower($strName))
		{
			// The property's value
			case "value":
				return $this->_dboOwner->_arrProperties[$this->_strProperty];
			// The properties validity
			case "valid":
				return $this->_dboOwner->_arrValid[$this->_strProperty];
		}
		
		// Do we have a Define property by this name?
		$intContext = (int)$this->_dboOwner->_intContext;
		if (isset($this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName]))
		{
			return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName];
		}
		
		return NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Modifier for Token Property's... Properties? 
	 *
	 * Modifier for Token Property's... Properties?
	 *
	 * @param	string	$strName	Property's Property
	 * @param	mixed	$mixValue	Value to assign
	 * 
	 * @return	boolean				Pass/Fail
	 *
	 * @method
	 */
	function __set($strName, $mixValue)
	{		
		// Validate
		// TODO
		
		// Set the value & return
		switch (strtolower($strName))
		{
			// The property's value
			case "value":
				return (bool)($this->_dboOwner->_arrProperties[$this->_strProperty] = $mixValue);
		}
		
		// Do we have a define property by this name?
		$intContext = (int)$this->_dboOwner->_intContext;
		return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName] = $mixValue;
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * 
	 *
	 * 
	 *
	 * @param	dbo		$dboObject		The owner object
	 * @param	string	$strMethod		Method to run
	 * @param	array	$arrArguments	Passed Arguments
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	/*function __call($strMethod, $arrArguments)
	{
	
	}*/

	//------------------------------------------------------------------------//
	// RenderInput
	//------------------------------------------------------------------------//
	/**
	 * RenderInput()
	 *
	 * Renders the property in it's HTML input form
	 *
	 * Renders the property in it's HTML input form
	 *
	 * @param	bool	$bolRequired	Whether the field should be mandatory
	 * @param	string	$strContext		The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property or FALSE if it failed to render
	 *
	 * @method
	 */
	function RenderInput($bolRequired=NULL, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_RenderIO("Input", $bolRequired, $intContext);
	}

	//------------------------------------------------------------------------//
	// RenderOutput
	//------------------------------------------------------------------------//
	/**
	 * RenderOutput()
	 *
	 * Renders the property in it's standard label form
	 *
	 * Renders the property in it's standard label form
	 *
	 * @param	bool	$bolRequired	Whether the field should be mandatory
	 * @param	string	$strContext		The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property or FALSE if it failed to render
	 *
	 * @method
	 */
	function RenderOutput($bolRequired=NULL, $intContext=CONTEXT_DEFAULT)
	{
		return $this->_RenderIO("Output", $bolRequired, $intContext);
	}

	//------------------------------------------------------------------------//
	// _RenderIO
	//------------------------------------------------------------------------//
	/**
	 * _RenderIO()
	 *
	 * Renders the property in its specified template
	 *
	 * Renders the property in its specified template
	 *
	 * @param	string	$strType		either "Output" or "Input"
	 * @param	bool	$bolRequired	Whether the field should be mandatory
	 * @param	string	$strContext		The context in which the property will be displayed
	 * 
	 * @return	mixed	PropertyValue	returns the value of the property or FALSE if it failed to render
	 *
	 * @method
	 */
	private function _RenderIO($strType, $bolRequired=NULL, $intContext=CONTEXT_DEFAULT)
	{
		//TODO!Rich!Why does the contect array start at 1 (when CONTEXT_DEFAULT = 0)
		//$intContext = 1;
		
		// require a definition
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext])
		{
			//var_dump($this->_dboOwner->_arrDefine[$this->_strProperty][$intContext]);
			//echo "<br />" . $intContext . "=" . CONTEXT_DEFAULT;
			return FALSE;
		}
		
		// Build up parameters for RenderHTMLTemplate()
		$arrParams = Array();
		$arrParams['Object'] 		= $this->_dboOwner->_strName;
		$arrParams['Property'] 		= $this->_strProperty;
		$arrParams['Context'] 		= $intContext;
		$arrParams['Value'] 		= $this->_dboOwner->_arrProperties[$this->_strProperty];
		
		$arrParams['Valid'] 		= $this->_dboOwner->_arrValid[$this->_strProperty];
		$arrParams['Required'] 		= $bolRequired;
		$arrParams['Definition'] 	= $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext];
		
		// work out the class to use
		if (!$arrParams['Definition']['Class'])
		{
			$arrParams['Definition']['FullClass'] = CLASS_DEFAULT; // Default
		}
		else
		{
			$arrParams['Definition']['FullClass'] = $arrParams['Definition']['Class'];
		}
		$arrParams['Definition']['FullClass'] .= $strType; // DefaultInput or DefaultOutput
		if ($arrParams['Valid'] === FALSE)
		{
			$arrParams['Definition']['FullClass'] .= "Invalid"; // DefaultInputInvalid or DefaultOutput
		}
		
		HTMLElements()->$arrParams['Definition'][$strType.'Type']($arrParams);
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the property in it's standard label form
	 *
	 * Renders the property in it's standard label form
	 *
	 * @param	string	$strOutputMask	optional output mask 
	 * 
	 * @return	mixed PropertyValue
	 *
	 * @method
	 */
	function Render($strOutputMask=NULL)
	{
		echo $this->_dboOwner->_arrProperties[$this->_strProperty];
		return $this->_dboOwner->_arrProperties[$this->_strProperty];		
	}
	
	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate the property
	 *
	 * Validate the property
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Valid()
	{
		return $this->_dboOwner->ValidateProperty($this->_strProperty);
	}
	
}



//----------------------------------------------------------------------------//
// MenuToken
//----------------------------------------------------------------------------//
/**
 * MenuToken
 *
 * Token Menu Object for Inteface Context Menu
 *
 * Token Menu Object for Inteface Context Menu
 *
 * @prefix	tok
 *
 * @package	framework_ui
 * @class	MenuToken
 */
class MenuToken
{
	//------------------------------------------------------------------------//
	// Properties
	//------------------------------------------------------------------------//
	private $_objOwner;
	private $_strProperty;
	private $_arrPath;
	private $_strMenu;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Token constructor
	 *
	 * Token constructor
	 *
	 * @return	MenuToken
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_objOwner	= NULL;
		$this->_strMenu		= NULL;
		$this->_arrPath		= NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// NewPath
	//------------------------------------------------------------------------//
	/**
	 * NewPath()
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * @param	DBObject		$objOwner		The owner object
	 * @param	string			$strName		The name of the first level in the path
	 *
	 * @return	MenuToken
	 *
	 * @method
	 */
	function NewPath($objOwner, $strName)
	{
		$this->_objOwner	= $objOwner;
		$this->_strMenu		= $strName;
		$this->_arrPath		= Array($strName);
		return $this;
	}
	
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * Token Object takes form of the passed Menu and returns itself
	 *
	 * @param	string	$strName	Menu Option name
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __get($strName)
	{
		$this->_strMenu		= $strName;
		$this->_arrPath[]	= $strName;
		return $this;
	}
	
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Creates a new Menu item with this name
	 *
	 * Creates a new Menu item with this name
	 *
	 * @param	string	$strItem		Item to create
	 * @param	array	$arrArguments	Passed Arguments where first and only member should be the value
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __call($strItem, $arrArguments)
	{
		// Dereference the Item
		$arrMenu = &$this->_objOwner->arrProperties;
		foreach ($this->_arrPath as $strPathItem)
		{
			if (!isset($arrMenu[$strPathItem]))
			{
				$arrMenu[$strPathItem] = Array();
			}
			$arrMenu = &$arrMenu[$strPathItem];
		}
		
		// Set item value
		$arrMenu[$strItem]	= $arrArguments;
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * Returns all private data attributes
	 *
	 * Returns all private data attributes
	 *
	 * @return	array
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn['Owner'] = $this->_objOwner;
		$arrReturn['Property'] = $this->_strProperty;
		$arrReturn['Path'] = $this->_arrPath;
		$arrReturn['Menu'] = $this->_strMenu;
	
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a string representing where the menu item is located in the context menu
	 *
	 * Formats a string representing where the menu item is located in the context menu
	 *
	 * @params	string		$strTabs	[optional] used to indent the formatted string.
	 *									if not inlcuded then the string is output.
	 * @return	string
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		// recursively printout the names from the array, until it gets to the final
		// one where it will printout value
		$arrMenu = $this->_objOwner->arrProperties;
		foreach ($this->_arrPath as $strPathItem)
		{
			$strOutput .=  $strExtraTabs . $strTabs.$strPathItem . "\n";
			$strExtraTabs .= "\t";
			
			$arrMenu = $arrMenu[$strPathItem];
		}
		
		// remove the last new line char from $strOutput
		$strOutput = substr($strOutput, 0, strlen($strOutput)-1);
		
		// add the parameters associated with the menu token
		$strOutput .= "(";
		foreach ($arrMenu as $strValue)
		{
			$strParams .=  "$strValue, ";
		}
		$strParams = substr($strParams, 0, strlen($strParams)-2);
		$strOutput .= $strParams . ")\n";

		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}
}
?>

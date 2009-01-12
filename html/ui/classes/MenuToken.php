<?php

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
		static $objMenuItems;
		if (!isset($objMenuItems))
		{
			$objMenuItems = new MenuItems();
		}
		
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
		$objMenuItems->strContextMenuLabel = "";
		$strMethod		= str_replace("_", "", $strItem);
		$strAction		= call_user_func_array(Array($objMenuItems, $strMethod), $arrArguments);
		$strActionLabel	= (strlen($objMenuItems->strContextMenuLabel) != 0)? $objMenuItems->strContextMenuLabel : str_replace("_", " ", $strItem);
		
		$arrMenu[$strActionLabel] = $strAction;
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
		$strExtraTabs = '';
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

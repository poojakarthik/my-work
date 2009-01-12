<?php

//----------------------------------------------------------------------------//
// ContextMenuFramework
//----------------------------------------------------------------------------//
/**
 * ContextMenuFramework
 *
 * Context Menu container
 *
 * Context Menu container.  Manages a context menu.
 *
 * @prefix	cmf
 *
 * @package	ui_app
 * @class	ContextMenuFramework
 */
class ContextMenuFramework 
{
	//------------------------------------------------------------------------//
	// _arrProperties
	//------------------------------------------------------------------------//
	/**
	 * _arrProperties
	 *
	 * Multi-dimensional array storing all submenus and menu items
	 *
	 * Multi-dimensional array storing all submenus and menu items
	 *
	 * @type		array
	 *
	 * @property
	 */
	public	$arrProperties	= Array();
	
	//------------------------------------------------------------------------//
	// _objMenuToken
	//------------------------------------------------------------------------//
	/**
	 * _objMenuToken
	 *
	 * Token object used to represent a single menu item that is stored in $arrProperties
	 *
	 * Token object used to represent a single menu item that is stored in $arrProperties
	 *
	 * @type		MenuToken
	 *
	 * @property
	 */
	private	$_objMenuToken	= NULL;
	
	//------------------------------------------------------------------------//
	// _objMenuItems
	//------------------------------------------------------------------------//
	/**
	 * _objMenuItems
	 *
	 * MenuItems object, used to compile Hrefs for the menu items
	 *
	 * MenuItems object, used to compile Hrefs for the menu items
	 *
	 * @type		MenuItems
	 *
	 * @property
	 */
	private $_objMenuItems;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for a ContextMenuFramework object
	 *
	 * Constructor for a ContextMenuFramework object
	 *
	 * @return	void
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_objMenuToken = new MenuToken();
		
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
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Creates a new context menu path and returns a reference to it
	 *
	 * Creates a new context menu path and returns a reference to it 
	 *
	 * @param	string	$strName	Name of the new menu path to create
	 * 
	 * @return	MenuToken
	 *
	 * @method
	 */
	function __get($strName)
	{
		$this->_objMenuToken->NewPath($this, $strName);

		// Return the MenuToken
		return $this->_objMenuToken;
	}
	
	//------------------------------------------------------------------------//
	// Reset
	//------------------------------------------------------------------------//
	/**
	 * Reset()
	 *
	 * Resets the context menu (empties it)
	 *
	 * Resets the context menu (empties it)
	 * 
	 * @return	void
	 * @method
	 */
	function Reset()
	{
		$this->arrProperties = Array();
	}
	
	//------------------------------------------------------------------------//
	// _BuildArray
	//------------------------------------------------------------------------//
	/**
	 * _BuildArray()
	 *
	 * Used recursively by the method BuildArray() to build the Context Menu array
	 *
	 * Used recursively by the method BuildArray() to build the Context Menu array
	 *
	 * @param	array	$arrMenu	the menu to build the Context Menu array from
	 * 
	 * @return	array				the built Context Menu array
	 * @method
	 */
	function _BuildArray($arrMenu)
	{
		$arrReturn = Array();

		foreach ($arrMenu as $strMenuItemLabel=>$mixDetails)
		{
			if (is_array($mixDetails))
			{
				// mixDetails is a submenu
				$arrReturn[str_replace("_", " ", $strMenuItemLabel)] = $this->_BuildArray($mixDetails);
			}
			else
			{
				// Assume mixDetails is an action string (href or javascript)
				$arrReturn[$strMenuItemLabel] = $mixDetails;
			}
		}
		
		return $arrReturn;
	}

	//------------------------------------------------------------------------//
	// BuildArray
	//------------------------------------------------------------------------//
	/**
	 * BuildArray()
	 *
	 * Builds the Context Menu Array
	 *
	 * Builds the Context Menu Array
	 * 
	 * @return	array
	 * @method
	 */
	function BuildArray()
	{
		return $this->_BuildArray($this->arrProperties);
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Creates a new root Menu item with this name (Yes I know this is ugly, considering the code is repeated in the MenuToken class)
	 *
	 * Creates a new root Menu item with this name (Yes I know this is ugly, considering the code is repeated in the MenuToken class)
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
		
		// Set item value
		$objMenuItems->strContextMenuLabel = "";
		$strMethod		= str_replace("_", "", $strItem);
		$strAction		= call_user_func_array(Array($objMenuItems, $strMethod), $arrArguments);
		$strActionLabel	= (strlen($objMenuItems->strContextMenuLabel) != 0)? $objMenuItems->strContextMenuLabel : str_replace("_", " ", $strItem);
		
		// Set item value
		$this->arrProperties[$strActionLabel] = $strAction;
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * returns a multi-dimensional array representing the contents of the ContextMenu
	 *
	 * returns a multi-dimensional array representing the contents of the ContextMenu
	 * 
	 * @return	array
	 *
	 * @method
	 */
	function Info()
	{
		return $this->_BuildArray($this->arrProperties);
	}

	//------------------------------------------------------------------------//
	// _ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * _ShowInfo()
	 *
	 * Formats a string representing the layout of the Context Menu (used recursively)
	 *
	 * Formats a string representing the layout of the Context Menu (used recursively)
	 * 
	 * @param	array		$arrMenu				multi-dimensional menu structure to process
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the menu structure should be tabbed.
	 * @return	string								returns the menu as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 * @method
	 */
	private function _ShowInfo($arrMenu, $strTabs='')
	{
		// Output each element of the array $arrMenu
		if (!is_array($arrMenu))
		{
			// This should never actually happen
			return "";
		}
		foreach ($arrMenu as $strMenu=>$mixSubMenu)
		{
			if (!is_array($mixSubMenu))
			{
				// this is a command
				$strOutput .= $strTabs . $strMenu . " => " . $mixSubMenu . "\n";
			}
			else
			{
				// this is a menu
				$strOutput .= $strTabs . $strMenu . "\n";
				$strOutput .= $this->_ShowInfo($mixSubMenu, $strTabs . "\t");
			}
		}
	
		return $strOutput;
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a string representing the layout of the Context Menu
	 *
	 * Formats a string representing the layout of the Context Menu
	 * 
	 * @param	string		$strTabs	[optional]	string containing tab chars '\t'
	 *												used to define how far the menu structure should be tabbed.
	 * @return	string								returns the menu as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		$arrMenu = $this->Info();
		
		$strOutput = $this->_ShowInfo($arrMenu, $strTabs);
		
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}	

}

?>

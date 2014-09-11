<?php

//----------------------------------------------------------------------------//
// HrefFramework
//----------------------------------------------------------------------------//
/**
 * HrefFramework
 *
 * Wrapper for the MenuItems class.  Used to return the resultant Href for a given menu item.
 *
 * Wrapper for the MenuItems class.  Used to return the resultant Href for a given menu item.
 *
 * @prefix	hrf
 *
 * @package	ui_app
 * @class	HrefFramework
 */
class HrefFramework
{
	// Local objMenuItems object
	public $objMenuItems = NULL;

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
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Should only call the names of methods belonging to the MenuItems class
	 *
	 * Should only call the names of methods belonging to the MenuItems class
	 *
	 * @param	string	$strMethod		name of MenuItem method to use to produce a Href
	 * @param	array	$arrArguments	arguments required of $strMethod
	 * @return	string					resultant href
	 *
	 * @method
	 */
	function __call($strMethod, $arrArguments)
	{
		$this->objMenuItems = new MenuItems();
		
		$strHref = call_user_func_array(Array($this->objMenuItems, $strMethod), $arrArguments);

		return $strHref;
	}
	
	//------------------------------------------------------------------------//
	// GetLastMenuItemLabel
	//------------------------------------------------------------------------//
	/**
	 * GetLastMenuItemLabel()
	 *
	 * Returns the label associated with the last menu item
	 *
	 * Returns the label associated with the last menu item
	 *
	 * @return	string	the label associated with the last menu item
	 *
	 * @method
	 */
	function GetLastMenuItemLabel()
	{
		if ($this->objMenuItems === NULL)
		{
			return NULL;
		}
		return $this->objMenuItems->GetLabel();
	}
}

?>

<?php

//----------------------------------------------------------------------------//
// BreadCrumbFramework
//----------------------------------------------------------------------------//
/**
 * BreadCrumbFramework
 *
 * Manages the bread crumb menu
 *
 * Manages the bread crumb menu
 *
 * @prefix	bcf
 *
 * @package	ui_app
 * @class	BreadCrumbFramework
 */
class BreadCrumbFramework
{
	//------------------------------------------------------------------------//
	// _strCurrentPage
	//------------------------------------------------------------------------//
	/**
	 * _strCurrentPage
	 *
	 * The current page (not a link)
	 *
	 * The current page (not a link)
	 *
	 * @type		string
	 *
	 * @property
	 */
	private $_strCurrentPage = NULL;

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
	 *
	 * @return	void
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_mitMenuItems = new MenuItems();
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
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Adds a breadcrumb to the menu so long as $strName is a valid menu item that can be expressed as a breadcrumb
	 *
	 * Adds a breadcrumb to the menu so long as $strName is a valid menu item that can be expressed as a breadcrumb
	 * Menu items are defined in the MenuItems class
	 * 
	 * @param	string		$strName		Name of the menu item to be used as a bread crumb
	 * @param	array		$arrParams		Any parameters required by the menu item
	 *
	 * @return	array				['Href'] 	= Href to be executed when the breadcrumb is clicked
	 *								['Label'] 	= breadcrumb's label
	 *
	 * @method
	 */
	function __call($strName, $arrParams)
	{
		$arrBreadCrumb = $this->_mitMenuItems->BreadCrumb($strName, $arrParams);
		if (is_array($arrBreadCrumb))
		{
			DBO()->BreadCrumb->$strName 		= $arrBreadCrumb['Href'];
			DBO()->BreadCrumb->$strName->Label 	= $arrBreadCrumb['Label'];
		}
		return $arrBreadCrumb;
	}
	
	
	//------------------------------------------------------------------------//
	// HasBreadCrumbs
	//------------------------------------------------------------------------//
	/**
	 * HasBreadCrumbs
	 *
	 * Used to check if breadcrumbs have been defined
	 *
	 * Used to check if breadcrumbs have been defined
	 * 
	 * @return	array				TRUE if breadcrumbs have been defined, else FALSE
	 *
	 * @method
	 */
	function HasBreadCrumbs()
	{
		return ($this->_strCurrentPage !== NULL || count(DBO()->BreadCrumb->_arrProperties) > 0);
	}
	
	//------------------------------------------------------------------------//
	// SetCurrentPage
	//------------------------------------------------------------------------//
	/**
	 * SetCurrentPage()
	 *
	 * Set the name of the current page, which will be displayed as the last breadcrumb and not be a link
	 *
	 * Set the name of the current page, which will be displayed as the last breadcrumb and not be a link
	 * 
	 * @param	string		$strName		Name of the current page
	 *
	 * @return	void
	 *
	 * @method
	 */
	function SetCurrentPage($strName)
	{
		$this->_strCurrentPage = $strName;
	}
	
	//------------------------------------------------------------------------//
	// GetCurrentPage
	//------------------------------------------------------------------------//
	/**
	 * GetCurrentPage()
	 *
	 * Accessor method for the name of the current page, which will be displayed as the last breadcrumb
	 *
	 * Accessor method for the name of the current page, which will be displayed as the last breadcrumb
	 * 
	 * @return	mix				If the current page breadcrumb has been set then it is returned; else returns FALSE
	 *
	 * @method
	 */
	function GetCurrentPage()
	{
		if ($this->_strCurrentPage === NULL)
		{
			return FALSE;
		}
		return $this->_strCurrentPage;
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * returns an array representing the contents of the Bread Crumb Menu
	 *
	 * returns an array representing the contents of the Bread Crumb Menu
	 * 
	 * @return	array
	 *
	 * @method
	 */
	function Info()
	{
		return DBO()->BreadCrumb->Info();
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a string representing the layout of the Bread Crumb Menu
	 *
	 * Formats a string representing the layout of the Bread Crumb Menu
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the menu structure should be tabbed.
	 * @return	string								returns the menu as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		return DBO()->BreadCrumb->ShowInfo($strTabs);
	}
}

?>

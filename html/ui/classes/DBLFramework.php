<?php

//----------------------------------------------------------------------------//
// DBLFramework
//----------------------------------------------------------------------------//
/**
 * DBLFramework
 *
 * Database Object List Framework container
 *
 * Database Object List Framework container
 *
 * @prefix	dbl
 *
 * @package	ui_app
 * @class	DBLFramework
 */
class DBLFramework
{
	// this member variable is not currently used for anything
	public	$_arrOptions	= Array();

	//------------------------------------------------------------------------//
	// _arrProperty
	//------------------------------------------------------------------------//
	/**
	 * _arrProperty
	 *
	 * Stores all DBList objects in the DBLFramework
	 *
	 * Stores all DBList objects in the DBLFramework
	 *
	 * @type		array
	 *
	 * @property
	 */
	private	$_arrProperty	= Array();

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
	 * Generic GET function for returning Database Object Lists
	 *
	 * Generic GET function for returning Database Object Lists
	 *
	 * @param	string	$strName	Name of the Database Object List
	 * 
	 * @return	DBList
	 *
	 * @method
	 */
	function __get($strName)
	{
		// Instanciate the DBList if we can't find an instance
		if (!array_key_exists($strName, $this->_arrProperty) || !$this->_arrProperty[$strName])
		{
			$this->_arrProperty[$strName] = new DBList($strName);
		}
		
		// Return the DBList
		return $this->_arrProperty[$strName];
	}

	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * return info about all DBL objects
	 *
	 * return info about all DBL objects
	 * 
	 * @return	array		[DBListName=>DBListInfo]
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn = Array();
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$arrReturn[$strObject] = $objObject->Info();
		}
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a list containing information regarding each DBList object, so that it can be displayed
	 *
	 * Formats a list containing information regarding each DBList object, so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the list should be tabbed.
	 * @return	string								returns the list as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$strOutput .= $strTabs."$strObject\n";
			$strOutput .= $objObject->ShowInfo($strTabs."\t");
		}
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}
}

?>

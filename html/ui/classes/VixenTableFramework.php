<?php

//----------------------------------------------------------------------------//
// VixenTableFramework
//----------------------------------------------------------------------------//
/**
 * VixenTableFramework
 *
 * VixenTable Object Framework container
 *
 * VixenTable Object Framework container
 *
 * @prefix	tblfwk
 *
 * @package	ui_app
 * @class	DBOFramework
 */
class VixenTableFramework
{
	//------------------------------------------------------------------------//
	// _arrTable
	//------------------------------------------------------------------------//
	/**
	 * _arrTable
	 *
	 * Stores all VixenTable objects in the DBOFramework
	 *
	 * Stores all VixenTable objects in the DBOFramework
	 *
	 * @type	array
	 *
	 * @property
	 */
	private	$_arrTable = Array();
	
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
	 * Generic GET function for returning VixenTable objects
	 *
	 * Generic GET function for returning VixenTable objects
	 * If the VixenTable object requested doesn't exist, it is created and returned.
	 *
	 * @param	string	$strName	Name of the Table Object
	 * 
	 * @return	VixenTable
	 *
	 * @method
	 */
	function __get($strName)
	{
		// Instanciate the VixenTable if we can't find an instance
		if (!array_key_exists($strName, $this->_arrTable) || !$this->_arrTable[$strName])
		{
			$this->_arrTable[$strName] = new VixenTable($strName);
		}
		
		// Return the Table
		return $this->_arrTable[$strName];
	}
	
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * returns info about each VixenTable object contained in the framework
	 *
	 * returns info about each VixenTable object contained in the framework
	 * 
	 * @return	array		
	 *
	 * @method
	 */
	function Info()
	{
		foreach ($this->_arrTable as $objTable)
		{
			$arrReturn[] = $objTable->Info();
		}
		
		return $arrReturn;
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a list containing information regarding each VixenTable object, so that it can be displayed
	 *
	 * Formats a list containing information regarding each VixenTable object, so that it can be displayed
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
		$strOutput = "Vixen Tables:\n";
		foreach ($this->_arrTable as $objTable)
		{
			$strOutput .= $objTable->ShowInfo("\t");
		}
		
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}	
}

?>

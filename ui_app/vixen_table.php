<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// vixen_table.php
//----------------------------------------------------------------------------//
/**
 * vixen_table
 *
 * contains the VixenTable class which represents a table that can be displayed in a HtmlTemplate
 *
 * contains the VixenTable class which represents a table that can be displayed in a HtmlTemplate
 *
 * @file		vixen_table.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenTable
//----------------------------------------------------------------------------//
/**
 * VixenTable
 *
 * Represents a table that can be displayed in a HtmlTemplate
 *
 * Represents a table that can be displayed in a HtmlTemplate
 *
 *
 * @prefix	vxntbl
 *
 * @package	ui_app
 * @class	VixenTable
 */
class VixenTable
{
	//------------------------------------------------------------------------//
	// _arrRow
	//------------------------------------------------------------------------//
	/**
	 * _arrRow
	 *
	 * Stores row data and information relating to the row (for each row)
	 *
	 * Stores row data and information relating to the row (for each row)
	 * $this->_arrRow[]['Detail'] 	= $strDetail (HTML -> detial div)
	 *                 ['Columns'] 	= $arrColumns (indexed array of HTML output)
	 *                 ['ToolTip']	= $strToolTip (HTML -> tooltip div)
	 *                 ['index']	= [name][] = value
	 *
	 * @type	array
	 *
	 * @property
	 */
	public $_arrRow				= Array();
	
	public $_arrHeader			= Array();
	public $_arrWidths			= Array(); //as % or px
	public $_arrAlignment		= Array();
	public $_bolRowHighlighting = FALSE;
	public $_strName			= '';
	public $_arrlinkedTables	= Array();
		//$this->_arrLinkedTables[] = Array($strTableName => $strIndexName)
	public $_intCurrentRow		= 0;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Construct a new VixenTable Object
	 *
	 * construct a new VixenTable Object
	 *
	 * @param	string	$strName					Name of the object to create
	 * @param	string	$strTable		optional	Database table to connect the data object to 
	 * @param	mixed	$mixColumns		optional	Columns to include in the data object
	 * 
	 * @return	DBObject
	 *
	 * @method
	 */
	function __construct($strName)
	{
		$this->_strName = $strName;
		$this->_intCurrentRow = NULL;
	}
	
	
	//------------------------------------------------------------------------//
	// AddHeader
	//------------------------------------------------------------------------//
	/**
	 * AddHeader()
	 *
	 * Adds the header to the table
	 *
	 * Adds the header to the table
	 *
	 * @param	string		$strProperty	new property's name
	 * @param	mix			$mixValue		new property's value
	 * @param	string		$intContext		new property's context which is 
	 *										used to select the specific validation rule
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function AddHeader()
	{
		// for each string passed, add it to $this->_arrHeader
		
		//check out the GetFuncArgs function of php (or something to that effect)
		
	}
	
	// this should probably return the row number of the added row, or NULL if it failed to add the row
	function AddRow()
	{
		if (!func_num_args())
		{
			// no parameters were passed
			return FALSE;
		}
		
		// increment the row pointer
		if (!isset($this->_intCurrentRow))
		{
			$this->_intCurrentRow = 0;
		}
		else
		{
			$this->_intCurrentRow++;
		}
		
		// build the array of column values		
		$arrColumns = func_get_args();
		$this->_arrRows[] = Array('Columns'=>$arrColumns);
	}
	 
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * return info about the DBO object
	 *
	 * return info about the DBO object
	 * 
	 * @return	array		stores all properties ['Properties'] and all valid properties ['Valid']
	 *
	 * @method
	 */
	function Info()
	{
		return $this->_arrRows;
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats info about the DBO object so that it can be displayed
	 *
	 * Formats info about the DBO object so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	string containing tab chars '\t'
	 *												used to define how far the object's 
	 *												info should be tabbed.
	 * @return	string								returns the object's info as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
	}
	
	//------------------------------------------------------------------------//
	// _ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * _ShowInfo()
	 *
	 * Recursively formats data which may or may not be a multi-dimensional array
	 *
	 * Recursively formats data which may or may not be a multi-dimensional array
	 * This is used by the ShowInfo method
	 *
	 * @param	mix			$mixData				Data to format
	 *												this can be a single value, array
	 *												or multi-dimensional array
	 * @param	string		$strTabs	[optional]	string containing tab chars '\t'
	 *												used to define how far the object's 
	 *												info should be tabbed.
	 * @return	string								returns the object's info as a formatted string.
	 *
	 * @method
	 */
	private function _ShowInfo($mixData, $strTabs='')
	{
	}
	

}


?>

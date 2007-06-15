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
	public $_arrRows			= Array();
	
	public $_arrHeader			= Array();
	public $_arrWidths			= Array();
	public $_arrAlignments		= Array();
	public $_arrlinkedTables	= Array();
	public $_strName			= '';
	public $_bolRowHighlighting = FALSE;
	public $_bolDetails			= FALSE;
	public $_bolToolTips		= FALSE;
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
	// SetHeader
	//------------------------------------------------------------------------//
	/**
	 * SetHeader()
	 *
	 * Sets the header to the table
	 *
	 * Sets the header to the table.
	 * 
	 *
	 * @param	string		$strColTitle, [$strColTitle]	Specify any number of column titles as separate parameters
	 * 
	 * @return	mixed										Indexed	array of column titles.
	 *														If nothing was passed to the method, then it returns NULL
	 *
	 * @method
	 */
	function SetHeader()
	{
		if (!func_num_args())
		{
			// no parameters were passed
			return NULL;
		}
		
		// retrieve the header values
		$this->_arrHeader = func_get_args();

		return $this->_arrHeader;
	}
	
	//------------------------------------------------------------------------//
	// SetWidth
	//------------------------------------------------------------------------//
	/**
	 * SetWidth()
	 *
	 * Sets the Width of each column of the table
	 *
	 * Sets the Width of each column of the table
	 * 
	 *
	 * @param	string		$strColWidth, [$strColWidth]	Specify a width for each column as a separate parameter
	 *														For example ("20%", "30%", "50%") or ("40px","30px","10px")
	 * 
	 * @return	mixed										Indexed	array of column widths.
	 *														If nothing was passed to the method, then it returns NULL
	 *
	 * @method
	 */
	function SetWidth()
	{
		if (!func_num_args())
		{
			// no parameters were passed
			return NULL;
		}
		
		// retrieve the width values
		$this->_arrWidths = func_get_args();

		return $this->_arrWidths;
	}

	//------------------------------------------------------------------------//
	// SetAlignment
	//------------------------------------------------------------------------//
	/**
	 * SetAlignment()
	 *
	 * Sets the alignment of each column of the table
	 *
	 * Sets the alignment of each column of the table
	 * 
	 *
	 * @param	string		$strColAlignment, [$strColAlignment]	Specify an alignment for each column as a separate parameter
	 *																For example ("Left", NULL, "Right")
	 * 
	 * @return	mixed												Indexed	array of column alignments.
	 *																If nothing was passed to the method, then it returns NULL
	 *
	 * @method
	 */
	function SetAlignment()
	{
		if (!func_num_args())
		{
			// no parameters were passed
			return NULL;
		}
		
		// retrieve the alignment values
		$this->_arrAlignments = func_get_args();

		return $this->_arrAlignments;
	}

	// this should probably return the row number of the added row, or NULL if it failed to add the row
	function AddRow()
	{
		if (!func_num_args())
		{
			// no parameters were passed
			return NULL;
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
		
		return $this->_intCurrentRow;
	}
	
	function SetDetail($strHtmlContent)
	{
		if (!isset($this->_intCurrentRow))
		{
			// a row has not yet been added yet
			return NULL;
		}
		
		// Flag this table as having detail
		$this->_bolDetails = TRUE;
		
		$this->_arrRows[$this->_intCurrentRow]['Detail'] = $strHtmlContent;
		return $this->_intCurrentRow;
	}
	
	function SetToolTip($strHtmlContent)
	{
		if (!isset($this->_intCurrentRow))
		{
			// a row has not yet been added yet
			return NULL;
		}
		
		$this->_bolToolTips = TRUE;
		
		$this->_arrRows[$this->_intCurrentRow]['ToolTip'] = $strHtmlContent;
		return $this->_intCurrentRow;
	}
	
	function AddIndex($strName, $mixValue)
	{
		if (!isset($this->_intCurrentRow))
		{
			// a row has not yet been added yet
			return NULL;
		}

		$this->_arrRows[$this->_intCurrentRow]['Index'][$strName][] = $mixValue;

		return $this->_intCurrentRow;
	}
	
	function LinkTable($strTableName, $strIndexName)
	{
		$this->_arrlinkedTables[$strTableName][] = $strIndexName;
	}
	
	
	function __get($strMagicVar)
	{
		switch ($strMagicVar)
		{
			case "RowHighlighting":
				return $this->_bolRowHighlighting;
		}
		
		return NULL;
	}
	
	function __set($strMagicVar, $mixValue)
	{
		switch ($strMagicVar)
		{
			case "RowHighlighting":
				return (bool)($this->_bolRowHighlighting = $mixValue);
		}
		
		return NULL;
	}
	
	function Render()
	{
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
		if ($this->_bolRowHighlighting)
		{
			$arrReturn['RowHighlighting'] = "True";
		}
		else
		{
			$arrReturn['RowHighlighting'] = "False";
		}
		
		if ($this->_bolDetail)
		{
			$arrReturn['ShowDetail'] = "True";
		}
		else
		{
			$arrReturn['ShowDetail'] = "False";
		}
		
		
		$arrReturn['Header']		= $this->_arrHeader;
		$arrReturn['Widths']		= $this->_arrWidths;
		$arrReturn['Alignments']	= $this->_arrAlignments;
		$arrReturn['LinkedTables']	= $this->_arrlinkedTables;
		$arrReturn['Rows']			= $this->_arrRows;
		
		
		return $arrReturn;
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

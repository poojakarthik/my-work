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
	 *                 ['Index']	= [name][] = value
	 *
	 * @type	array
	 *
	 * @property
	 */
	public $_arrRows			= Array();
	
	public $_arrHeader			= Array();
	public $_arrWidths			= Array();
	public $_arrAlignments		= Array();
	public $_arrLinkedTables	= Array();
	public $_strName			= '';
	public $_bolRowHighlighting = FALSE;
	public $_bolDetails			= FALSE;
	public $_bolToolTips		= FALSE;
	public $_bolLinked			= FALSE;
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


	//------------------------------------------------------------------------//
	// AddRow
	//------------------------------------------------------------------------//
	/**
	 * AddRow()
	 *
	 * Adds a row of values to the table
	 *
	 * Adds a row of values to the table
	 * 
	 *
	 * @param	string		$strColValue, [$strColValue]	HTML code for each column as a separate parameter
	 *														For example ("value1", "$100.00", "<span class='BlahClass'>Blah blah</span>")
	 * 
	 * @return	mixed										row number of the added row
	 *														If nothing was passed to the method, then it returns NULL
	 *
	 * @method
	 */
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

		// Flag this table as having a link
		$this->_bolLinked = TRUE;

		$this->_arrRows[$this->_intCurrentRow]['Index'][$strName][] = $mixValue;

		return $this->_intCurrentRow;
	}
	
	function LinkTable($strTableName, $strIndexName)
	{
		$this->_arrLinkedTables[$strTableName][] = $strIndexName;
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
		// render header
	/*
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

		$strTableName = $this->_strName;

		if ($this->_bolDetails || $this->_bolRowHighlighting || $this->_bolToolTips || $this->_bolLinked)
		{
			echo "<script type='text/javascript'>\n";
			
		$strVixenTable = "Vixen.table." . $strTableName;
		echo $strVixenTable . " = Object(); \n";
		echo $strVixenTable . ".collapseAll = TRUE;\n";
		echo $strVixenTable . ".linked = TRUE;\n";
		echo $strVixenTable . ".totalRows = 0;\n";
		echo $strVixenTable . ".row = Array(); \n";
		echo "</script>\n";
		}

		echo "<table border='0' cellpadding='3' cellspacing='0' class='Listing' width='100%' id='$strTableName'>\n";
		
		// Build headers
		echo "<tr class='First'>\n";
		foreach ($this->_arrHeader AS $objField)
		{
			echo " <th>". $objField ."</th>\n";
		}
		echo "</tr>\n";
		
		// Build rows
		$intRowCount = -1;
		foreach ($this->_arrRows AS $objRow)
		{
			$intRowCount++;
			$strClass = ($intRowCount % 2) ? 'Even' : 'Odd' ;
			echo "<tr id='" . $strTableName . "_" . $intRowCount . "' class='$strClass'>\n";
			// Build fields
			foreach ($objRow['Columns'] as $objField)
			{
				echo "<td>";
				echo $objField;
				echo "</td>\n";			
			}
			// Build detail
			if ($this->_bolDetails)
			{
				echo "</tr>";
				echo "<tr>";
				echo "<td colspan=4 style='padding-top: 0px; padding-bottom: 0px'>";
				echo "<div id='" . $strTableName . "_" . $intRowCount . "DIV-DETAIL' style='display: block; overflow:hidden;'>";
				echo $objRow['Detail'];
				echo "</div>";
				echo "</td>\n";
			}
			
			// Build tooltip
			if ($this->_bolToolTips)
			{
				echo "</tr>";
				echo "<tr>";
				echo "<td colspan=4 style='padding-top: 0px; padding-bottom: 0px'>";
				echo "<div id='" . $strTableName . "_" . $intRowCount . "DIV-TOOLTIP' style='display: none;'>";
				echo "Tooltip goes here";
				echo "</div>\n";
				echo "</td>";
			}
			
			echo "\n<script type='text/javascript'>";
						/*
						{
				'selected' : FALSE,
				'up' : TRUE,
				'index' : 
				{
					'Invoice' :'3000308781',
					'Service' :'6123'
				}
			},*/
			
			echo "objRow = Object();\n";
			
			echo "objRow.selected = FALSE;\n";
			echo "objRow.up = TRUE;\n";			

			if ($this->_bolLinked)
			{
				if (is_array($objRow['Index']))
				{
					// add Indexes to objRow
					echo "objIndex = Object();";
					
					foreach ($objRow['Index'] as $strIndexName=>$arrValues)
					{
						echo "objIndex. " .$strIndexName. " = Array();";
						foreach ($arrValues as $strValue)
						{
							echo "objIndex. " .$strIndexName. ".push('" .$strValue. "');";
						}
					}
					echo "objRow.index = objIndex;";
				}
			}
			
			echo $strVixenTable . ".row.push(objRow);\n";
			echo "</script>\n";
			
			echo "</tr>\n";
		}
		
		echo "</table>\n";
		
		echo "<script>" . $strVixenTable . ".totalRows = " . $intRowCount . ";</script>\n";	
		
		if ($this->_bolRowHighlighting)
		{
			echo "<script type='text/javascript'>Vixen.AddCommand('Vixen.Highlight.Attach','\'$strTableName\'', $intRowCount);</script>";
		}
		
		if ($this->_bolToolTips)
		{
			echo "<script type='text/javascript'>Vixen.Tooltip.Attach('$strTableName', $intRowCount);</script>";
		}
		
		if ($this->_bolDetails)
		{
			echo "<script type='text/javascript'>Vixen.Slide.Attach('$strTableName', $intRowCount, TRUE);</script>\n";
		}
		
		if ($this->_bolLinked)
		{
			echo "<script type='text/javascript'>";
			echo $strVixenTable . ".linked = TRUE;";
			
			echo "objLink = Object();\n";
			
			foreach ($this->_arrLinkedTables AS $strTableName=>$arrIndexes)
			{
				echo "objLink." . $strTableName . " = Array();\n";
				foreach ($arrIndexes AS $strIndex)
				{
					echo "objLink. " . $strTableName . ".push('" . $strIndex . "');\n";
				}
			}
			echo $strVixenTable . ".link = objLink;\n";
			
			echo "</script>\n";
				/*'link':
			{
				'AccountInvoices' :
				[
					'Invoice'
				]
			},*/
		}
		
		echo "<div class='seperator'></div>";
	
	
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

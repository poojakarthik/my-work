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
 * Contains the VixenTable class which represents a table that can be displayed in a HtmlTemplate
 *
 * Contains the VixenTable class which represents a table that can be displayed in a HtmlTemplate
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
	// _arrRows
	//------------------------------------------------------------------------//
	/**
	 * _arrRows
	 *
	 * Stores row data and information relating to the row (for each row)
	 *
	 * Stores row data and information relating to the row (for each row)
	 * $this->_arrRows[]['Detail'] 			= $strDetail (HTML -> detial div)
	 *                  ['Columns'] 		= $arrColumns (indexed array of HTML output)
	 *                  ['ToolTip']			= $strToolTip (HTML -> tooltip div)
	 *                  ['OnClick']			= $strOnClick (if set, this will be executed when the row is clicked)
	 *                  ['Index']			= [name][] = value
	 *                  ['Widths'] 			= $arrWidths (indexed array of widths for each column specific to this row, specified as px or %)
	 *										  This can be used to override the column widths defined for the table [currently doesn't work]
	 *                  ['Alignments'] 		= $arrAlignments (indexed array of alignments for each column specific to this row)
	 *										  This can be used to override the column alignments defined for the table
	 *                  ['ColSpans']		= $arrColSpans (indexed array of integers denoting how many columns, each of the current row's column's span)
	 *
	 * @type	array
	 *
	 * @property
	 */
	private $_arrRows				= Array();
	
	//------------------------------------------------------------------------//
	// _arrHeader
	//------------------------------------------------------------------------//
	/**
	 * _arrHeader
	 *
	 * Stores the title of each column
	 *
	 * Stores the title of each column
	 *
	 * @type	array
	 *
	 * @property
	 */
	private $_arrHeader				= Array();

	//------------------------------------------------------------------------//
	// _arrWidths
	//------------------------------------------------------------------------//
	/**
	 * _arrWidths
	 *
	 * Stores the width of each column, specified as px or %
	 *
	 * Stores the width of each column, specified as px or %
	 *
	 * @type	array
	 *
	 * @property
	 */
	private $_arrWidths				= Array();

	//------------------------------------------------------------------------//
	// _arrAlignments
	//------------------------------------------------------------------------//
	/**
	 * _arrAlignments
	 *
	 * Stores the alignment of each column
	 *
	 * Stores the alignment of each column
	 *
	 * @type	array
	 *
	 * @property
	 */
	private $_arrAlignments			= Array();

	//------------------------------------------------------------------------//
	// _arrLinkedTables
	//------------------------------------------------------------------------//
	/**
	 * _arrLinkedTables
	 *
	 * Stores the name of each VixenTable object that is linked to this one and the name of the index on which they are linked
	 *
	 * Stores the name of each VixenTable object that is linked to this one and the name of the index on which they are linked
	 *
	 * @type	array
	 *
	 * @property
	 */
	private $_arrLinkedTables		= Array();

	//------------------------------------------------------------------------//
	// _strName
	//------------------------------------------------------------------------//
	/**
	 * _strName
	 *
	 * The name of the table
	 *
	 * The name of the table
	 *
	 * @type	string
	 *
	 * @property
	 */
	private $_strName				= '';
	
	//------------------------------------------------------------------------//
	// _bolRowHighlighting
	//------------------------------------------------------------------------//
	/**
	 * _bolRowHighlighting
	 *
	 * Flag for enabling/disabling row highlighting
	 *
	 * Flag for enabling/disabling row highlighting
	 *
	 * @type	bool
	 *
	 * @property
	 */
	private $_bolRowHighlighting 	= FALSE;
	
	//------------------------------------------------------------------------//
	// _bolDetails
	//------------------------------------------------------------------------//
	/**
	 * _bolDetails
	 *
	 * Flag for enabling/disabling the showing of details when a row is selected
	 *
	 * Flag for enabling/disabling the showing of details when a row is selected
	 *
	 * @type	bool
	 *
	 * @property
	 */
	private $_bolDetails			= FALSE;
	
	//------------------------------------------------------------------------//
	// _bolToolTips
	//------------------------------------------------------------------------//
	/**
	 * _bolToolTips
	 *
	 * Flag for enabling/disabling the showing of tool tips when the mouse is over a row
	 *
	 * Flag for enabling/disabling the showing of tool tips when the mouse is over a row
	 *
	 * @type	bool
	 *
	 * @property
	 */
	private $_bolToolTips			= FALSE;
	
	//------------------------------------------------------------------------//
	// _bolLinked
	//------------------------------------------------------------------------//
	/**
	 * _bolLinked
	 *
	 * Flag for enabling/disabling the linking of tables to this one
	 *
	 * Flag for enabling/disabling the linking of tables to this one
	 *
	 * @type	bool
	 *
	 * @property
	 */
	private $_bolLinked				= FALSE;
	
	//------------------------------------------------------------------------//
	// _intCurrentRow
	//------------------------------------------------------------------------//
	/**
	 * _intCurrentRow
	 *
	 * Pointer to the current row.
	 *
	 * Pointer to the current row.
	 *
	 * @type	int
	 *
	 * @property
	 */
	private $_intCurrentRow;
	
	//------------------------------------------------------------------------//
	// _bolSortable
	//------------------------------------------------------------------------//
	/**
	 * _bolSortable
	 *
	 * Flag for enabling/disabling the sortability of the table
	 *
	 * Flag for enabling/disabling the sortability of the table
	 *
	 * @type	bool
	 *
	 * @property
	 */
	private $_bolSortable			= FALSE;
	
	//------------------------------------------------------------------------//
	// _arrSortFields
	//------------------------------------------------------------------------//
	/**
	 * _arrSortFields
	 *
	 * Names of fields to be used for sorting (null name if not to be sorted)
	 *
	 * Names of fields to be used for sorting (null name if not to be sorted)
	 *
	 * @type	array
	 *
	 * @property
	 */
	private $_arrSortFields			= null;
	
	//------------------------------------------------------------------------//
	// _intPageSize
	//------------------------------------------------------------------------//
	/**
	 * _intPageSize
	 *
	 * Number of items to be displayed per page in a paginated table
	 *
	 * Number of items to be displayed per page in a paginated table (0 if not to be paginated)
	 *
	 * @type	Integer
	 *
	 * @property
	 */
	private $_intPageSize			= 0;
	
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
	 * 
	 * @return	VixenTable
	 *
	 * @method
	 */
	function __construct($strName)
	{
		$this->_strName = $strName;
		$this->_intCurrentRow = NULL;
	}
	
	//------------------------------------------------------------------------//
	// Clean
	//------------------------------------------------------------------------//
	/**
	 * Clean()
	 *
	 * Deletes all details/info pertaining to the table
	 *
	 * Deletes all details/info pertaining to the table
	 *
	 * @return	void
	 *
	 * @method
	 */
	function Clean()
	{
		$this->_arrRows				= Array();
		$this->_arrHeader			= Array();
		$this->_arrWidths			= Array();
		$this->_arrAlignments		= Array();
		$this->_arrLinkedTables		= Array();
		$this->_bolRowHighlighting	= FALSE;
		$this->_bolDetails			= FALSE;
		$this->_bolToolTips			= FALSE;
		$this->_bolLinked			= FALSE;
		$this->_intCurrentRow		= NULL;
	}
	
	//------------------------------------------------------------------------//
	// SetHeader
	//------------------------------------------------------------------------//
	/**
	 * SetHeader()
	 *
	 * Sets the header to the table
	 *
	 * Sets the header to the table
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
			// No parameters were passed
			return NULL;
		}
		
		// Store the header values
		$this->_arrHeader = func_get_args();

		return $this->_arrHeader;
	}
	
	function SetSortFields()
	{
		$this->_arrSortFields = func_get_args();
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
			// No parameters were passed
			return NULL;
		}
		
		// Store the width values
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
			// No parameters were passed
			return NULL;
		}
		
		// Store the alignment values
		$this->_arrAlignments = func_get_args();

		return $this->_arrAlignments;
	}

	//------------------------------------------------------------------------//
	// SetSortable
	//------------------------------------------------------------------------//
	/**
	 * SetSortable()
	 *
	 * Sets the sortability of the table
	 *
	 * Sets the sortability of the table
	 * 
	 *
	 * @param	boolean		$bolSortable	Specify if the table should be sortable
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function SetSortable($bolSortable)
	{
		$this->_bolSortable = $bolSortable;
	}
	


	//------------------------------------------------------------------------//
	// SetPageSize
	//------------------------------------------------------------------------//
	/**
	 * SetPageSize()
	 *
	 * Sets the page size to use when paginating the table
	 *
	 * Sets the page size to use when paginating the table
	 * 
	 *
	 * @param	integer		$intPageSize	page size of table or 0 if not 
	 * 										paginated (default = 0 (zero))
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function SetPageSize($intPageSize=0)
	{
		$this->_intPageSize = $intPageSize;
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
	
	//------------------------------------------------------------------------//
	// SetRowColumnSpan
	//------------------------------------------------------------------------//
	/**
	 * SetRowColumnSpan()
	 *
	 * Sets the column spans for each column for the current row
	 *
	 * Sets the column spans for each column for the current row
	 *
	 * @param	string		$intColSpan, [$intColSpan]	Specify a colspan for each column as a separate parameter
	 *													For example (3, 1, 2)
	 * 
	 * @return	mixed									Row number of the current row
	 *													If nothing was passed to the method, or there is no current row then it returns NULL
	 *
	 * @method
	 */
	function SetRowColumnSpan()
	{
		if ((!func_num_args()) || (!isset($this->_intCurrentRow)))
		{
			// no parameters were passed or there is no current row
			return NULL;
		}
		
		// retrieve the width values
		$arrColSpans = func_get_args();
		
		$this->_arrRows[$this->_intCurrentRow]['ColSpans'] = $arrColSpans;

		return $this->_intCurrentRow;
	}
	
	
	//------------------------------------------------------------------------//
	// SetRowWidth [CURRENTLY DOESN'T WORK]
	//------------------------------------------------------------------------//
	/**
	 * SetRowWidth()
	 *
	 * Sets the Width of each column for the current row
	 *
	 * Sets the Width of each column for the current row
	 * This will override the widths defined for the entire table
	 * 
	 *
	 * @param	string		$strRowWidth, [$strRowWidth]	Specify a width for each column as a separate parameter
	 *														For example ("20%", "30%", "50%") or ("40px","30px","10px")
	 * 
	 * @return	mixed										Row number of the current row
	 *														If nothing was passed to the method, or there is no current row then it returns NULL
	 *
	 * @method
	 */
	function SetRowWidth()
	{
		// FIX IT!
		// Defining column widths to be applied to a single row, doesn't currently work.
		// For some annoying reason you can specify the alignment of columns at the <td></td> level, but if you
		// specify column widths at this level, they get applied to all the other columns
		// For now just use the SetRowColumnSpan method, although this means you can't add a row that has more columns than previous rows have had
		
		if ((!func_num_args()) || (!isset($this->_intCurrentRow)))
		{
			// no parameters were passed or there is no current row
			return NULL;
		}
		
		// retrieve the width values
		$arrRowWidths = func_get_args();
		
		$this->_arrRows[$this->_intCurrentRow]['Widths'] = $arrRowWidths;

		return $this->_intCurrentRow;
	}

	//------------------------------------------------------------------------//
	// SetRowAlignment
	//------------------------------------------------------------------------//
	/**
	 * SetRowAlignment()
	 *
	 * Sets the Alignment of each column for the current row
	 *
	 * Sets the Alignment of each column for the current row
	 * This will override the alignments defined for the entire table
	 *
	 * @param	string		$strAlignment, [$strAlignment]	Specify an alignment for each column as a separate parameter
	 *														For example ("left", "center", "right")
	 * 
	 * @return	mixed										Row number of the current row
	 *														If nothing was passed to the method, or there is no current row then it returns NULL
	 *
	 * @method
	 */
	function SetRowAlignment()
	{
		if ((!func_num_args()) || (!isset($this->_intCurrentRow)))
		{
			// no parameters were passed or there is no current row
			return NULL;
		}
		
		// retrieve the alignment values
		$arrRowAlignments = func_get_args();
		
		$this->_arrRows[$this->_intCurrentRow]['Alignments'] = $arrRowAlignments;

		return $this->_intCurrentRow;
	}


	//------------------------------------------------------------------------//
	// SetDetail
	//------------------------------------------------------------------------//
	/**
	 * SetDetail()
	 *
	 * Sets the detail for the current row, which is displayed when the row is selected
	 *
	 * Sets the detail for the current row, which is displayed when the row is selected
	 * 
	 *
	 * @param	string		$strHtmlContent		HTML code defining the detail and its layout
	 * 
	 * @return	mixed							row number of the current row
	 *											If there is no current row, then it returns NULL
	 *
	 * @method
	 */
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
	
	//------------------------------------------------------------------------//
	// SetToolTip
	//------------------------------------------------------------------------//
	/**
	 * SetToolTip()
	 *
	 * Sets the ToolTip for the current row, which is displayed when the mouse is over the row
	 *
	 * Sets the ToolTip for the current row, which is displayed when the mouse is over the row
	 * 
	 *
	 * @param	string		$strHtmlContent		HTML code defining the ToolTip and its layout
	 * 
	 * @return	mixed							row number of the current row
	 *											If there is no current row, then it returns NULL
	 *
	 * @method
	 */
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
	
	//------------------------------------------------------------------------//
	// SetOnClick
	//------------------------------------------------------------------------//
	/**
	 * SetOnClick()
	 *
	 * Sets the OnClick code for the current row, which is executed when the row is clicked
	 *
	 * Sets the OnClick code for the current row, which is executed when the row is clicked
	 * 
	 *
	 * @param	string		$strCodeToExecute	javascript code or Href to execute when the row is clicked
	 * 
	 * @return	mixed							row number of the current row
	 *											If there is no current row, then it returns NULL
	 *
	 * @method
	 */
	function SetOnClick($strCodeToExecute)
	{
		if (!isset($this->_intCurrentRow))
		{
			// a row has not yet been added yet
			return NULL;
		}
		
		//$this->_bolToolTips = TRUE;
		
		$this->_arrRows[$this->_intCurrentRow]['OnClick'] = $strCodeToExecute;
		return $this->_intCurrentRow;
	}
	
	
	//------------------------------------------------------------------------//
	// AddIndex
	//------------------------------------------------------------------------//
	/**
	 * AddIndex()
	 *
	 * Adds and index for the current row
	 *
	 * Adds and index for the current row
	 * If a row in a linked table has the same index (and same value), then it will be highlighted when this row is selected
	 * 
	 *
	 * @param	string		$strName	The name of the index 
	 * @param	mixed		$mixValue	The value of the index
	 * 
	 * @return	mixed							row number of the current row
	 *											If there is no current row, then it returns NULL
	 *
	 * @method
	 */
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
	
	//------------------------------------------------------------------------//
	// RowCount
	//------------------------------------------------------------------------//
	/**
	 * RowCount()
	 *
	 * Returns the number of rows currently in the table
	 *
	 * Returns the number of rows currently in the table
	 *
	 * 
	 * @return	int				number of rows in the table
	 * @method
	 */
	function RowCount()
	{
		if (!isset($this->_intCurrentRow))
		{
			return 0;
		}
		else
		{
			return ($this->_intCurrentRow + 1);
		}
	}
	
	//------------------------------------------------------------------------//
	// LinkTable
	//------------------------------------------------------------------------//
	/**
	 * LinkTable()
	 *
	 * Links a VixenTable to this one so that corresponding rows can be highlighted
	 *
	 * Links a VixenTable to this one so that corresponding rows can be highlighted
	 * 
	 *
	 * @param	string		$strTableName		The name of the VixenTable to link to this one
	 * @param	string		$strIndexName		The name of the index, which both tables should have in common
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function LinkTable($strTableName, $strIndexName)
	{
		$this->_arrLinkedTables[$strTableName][] = $strIndexName;
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Accessor method for magic variables
	 *
	 * Accessor method for magic variables
	 * 
	 *
	 * @param	string		$strMagicVar		The name of the Magic Variable to retrieve
	 * 
	 * @return	mixed							the value of the Magic Variable
	 *											NULL if the variable could not be found
	 *
	 * @method
	 */
	function __get($strMagicVar)
	{
		switch ($strMagicVar)
		{
			case "RowHighlighting":
				return $this->_bolRowHighlighting;
		}
		
		return NULL;
	}
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Mutator method for magic variables
	 *
	 * Mutator method for magic variables
	 * 
	 *
	 * @param	string		$strMagicVar		The name of the Magic Variable
	 * @param	mixed		$mixValue			The value to set the Magic Variable to
	 * 
	 * @return	mixed							TRUE if the variable could be changed
	 *											NULL if the variable was not found
	 *
	 * @method
	 */
	function __set($strMagicVar, $mixValue)
	{
		switch ($strMagicVar)
		{
			case "RowHighlighting":
				return (bool)($this->_bolRowHighlighting = $mixValue);
		}
		
		return NULL;
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the table along with all required javascript
	 *
	 * Renders the table along with all required javascript
	 * 
	 * @return	void
	 *
	 * @method
	 */	 
	function Render()
	{
		$strTableName = $this->_strName;
		$strVixenTable = "Vixen.table.{$strTableName}";

		echo "
<script type='text/javascript'>
	{$strVixenTable} = Object();
	{$strVixenTable}.collapseAll = TRUE;
	{$strVixenTable}.linked = TRUE;
	{$strVixenTable}.totalRows = 0;
	{$strVixenTable}.row = Array();
</script>";
			
		
		$strPageSize = $this->_intPageSize > 0 ?  " page_size='{$this->_intPageSize}' " : "";

		echo "<table border='0' cellpadding='3' cellspacing='0' class='Listing' width='100%' id='$strTableName'$strPageSize>\n";
		
		// Build headers
		echo "<tr class='First'>\n";
		$intHeaderCount = 0;
		$intSortLimit = ($this->_bolSortable && is_array($this->_arrSortFields)) ? count ($this->_arrSortFields) : -1;
		foreach ($this->_arrHeader AS $objField)
		{
			$strAlign = $this->_arrAlignments[$intHeaderCount];
			$strSortLabel = "";
			if ($intHeaderCount <= $intSortLimit)
			{
				if ($this->_arrSortFields[$intHeaderCount] !== NULL)
				{
					$strSortLabel = " TABLE_SORT='" . $this->_arrSortFields[$intHeaderCount] . "' ";
				}
				else
				{
					$strSortLabel = " NO_TABLE_SORT='1' ";
				}
			}
			echo " <th width='{$this->_arrWidths[$intHeaderCount]}' align='$strAlign'$strSortLabel>". $objField ."</th>\n";
			$intHeaderCount++;
		}
		echo "</tr>\n";
		
		// Build rows
		$intRow = -1;
		foreach ($this->_arrRows AS $objRow)
		{
			$intRow++;
			$strClass = ($intRow % 2) ? 'Odd' : 'Even';
			$strStyle = "";
			
			if (isset($objRow['OnClick']))
			{
				// Escape special chars
				$strOnClick = "onclick='". htmlspecialchars($objRow['OnClick'], ENT_QUOTES) ."'";
				$strStyle .= "cursor:pointer;";
			}
			else
			{
				$strOnClick = "";
			}
			
			echo "<tr id='" . $strTableName . "_" . $intRow . "' class='$strClass' $strOnClick style='$strStyle'>\n";
			
			$intColCount = 0;
			// Build fields
			foreach ($objRow['Columns'] as $objField)
			{
				$strWidth = '';
				// Work out which width to use
				//TODO! After setting the widths once in the header, you shouldn't have to set them again, but we are anyway.
				//This could cut down the size of the html file generated
				/*if (isset($objRow['Widths']))
				{
					// Use the width specific to this row and column
					$strWidth = "width='". $objRow['Widths'][$intColCount] ."'";
				}
				else
				{
					// Use the general width of this column
					$strWidth = "width='". $this->_arrWidths[$intColCount] ."'";
				}
				*/

				// Work out which alignment to use
				if (isset($objRow['Alignments']))
				{
					// Use the alignment specific to this row and column
					$strAlignment = "align='". $objRow['Alignments'][$intColCount] ."'";
				}
				else
				{
					// Use the general alignment of this column
					$strAlignment = "align='". $this->_arrAlignments[$intColCount] ."'";
				}
				
				// Work out how many columns, this column spans
				$strColSpan = "";
				if (isset($objRow['ColSpans']))
				{
					// colspan values have been declared for this row
					$strColSpan = "colspan='". $objRow['ColSpans'][$intColCount] ."'";
					
					// If using ColSpans do not use row widths
					$strWidth = "";
				}
				
				echo "<td $strWidth $strAlignment $strColSpan>";
				echo "$objField";
				echo "</td>\n";
				$intColCount++;				
			}
			
			// Build detail
			if ($this->_bolDetails)
			{
				echo "</tr>";
				echo "<tr>";
				echo "<td colspan=". count($this->_arrHeader) ." style='padding: 0px 1px 1px 1px;'>";
				echo "<div id='" . $strTableName . "_" . $intRow . "DIV-DETAIL' style='display: block; overflow:hidden;'>";
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
				echo "<div id='" . $strTableName . "_" . $intRow . "DIV-TOOLTIP' style='display: none;'>";
				echo $objRow['ToolTip'];
				echo "</div>\n";
				echo "</td>";
			}
		
			echo "\n<script type='text/javascript'>";
			echo "objRow = Object();\n";
			
			echo "objRow.selected = false;\n";
			echo "objRow.up = true;\n";

			if ($this->_bolLinked)
			{
				if (is_array($objRow['Index']))
				{
					// add Indexes to objRow
					echo "objIndex = Object();";
					
					foreach ($objRow['Index'] as $strIndexName=>$arrValues)
					{
						echo "objIndex.{$strIndexName} = Array();";
						foreach ($arrValues as $strValue)
						{
							echo "objIndex.{$strIndexName}.push('$strValue');";
						}
					}
					echo "objRow.index = objIndex;";
				}
			}
			
			echo "{$strVixenTable}.row.push(objRow);\n";
			echo "</script>\n";
			echo "</tr>\n";
		}
		$intRowCount = $intRow + 1;
		echo "</table>\n";
		
		echo "<script type='text/javascript'>{$strVixenTable}.totalRows = $intRowCount;</script>\n";	
		
		if ($this->_bolRowHighlighting)
		{
			// The following "Vixen.AddCommand" method breaks down when you try dynamicly inserting a VixenTable into
			// the DOM, because AddCommand only triggers the command when the body.onload event is triggered
			//echo "<script type='text/javascript'>Vixen.AddCommand('Vixen.Highlight.Attach','\'$strTableName\'', $intRowCount);</script>";
			echo "<script type='text/javascript'>Vixen.Highlight.Attach('$strTableName');</script>";
		}
		
		if ($this->_bolToolTips)
		{
			echo "<script type='text/javascript'>Vixen.Tooltip.Attach('$strTableName');</script>";
		}
		
		if ($this->_bolDetails)
		{
			echo "<script type='text/javascript'>Vixen.Slide.Attach('$strTableName', TRUE);</script>\n";
		}
		
		if ($this->_bolLinked)
		{
			echo "<script type='text/javascript'>";
			echo "{$strVixenTable}.linked = TRUE;";
			
			echo "objLink = Object();\n";
			
			foreach ($this->_arrLinkedTables AS $strTableName=>$arrIndexes)
			{
				echo "objLink.{$strTableName} = Array();\n";
				foreach ($arrIndexes AS $strIndex)
				{
					echo "objLink.{$strTableName}.push('$strIndex');\n";
				}
			}
			echo "{$strVixenTable}.link = objLink;\n";
			
			echo "</script>\n";
				/*'link':
			{
				'AccountInvoices' :
				[
					'Invoice'
				]
			},*/
		}
		
		if ($this->_bolSortable)
		{
			echo "<script type='text/javascript'>Vixen.TableSort.prepare('$strTableName');</script>\n";
		}
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * Return info about the Vixen table
	 *
	 * Return info about the Vixen table
	 * 
	 * @return	array		
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn['TableName'] = $this->_strName;
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

		if ($this->_bolToolTips)
		{
			$arrReturn['ToolTips'] = "True";
		}
		else
		{
			$arrReturn['ToolTips'] = "False";
		}
		
		if ($this->_bolLinked)
		{
			$arrReturn['Linked'] = "True";
		}
		else
		{
			$arrReturn['Linked'] = "False";
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
	 * Formats info about the VixenTable so that it can be displayed
	 *
	 * Formats info about the VixenTable so that it can be displayed
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
		$arrInfo = $this->Info();
		$strOutput = $this->_ShowInfo($arrInfo, $strTabs);

		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
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
		if (is_array($mixData))
		{
			foreach ($mixData as $mixKey=>$mixValue)
			{
				if (!is_array($mixValue))
				{
					// $mixValue is not an array
					$strOutput .= $strTabs . $mixKey . " : " . $mixValue . "\n";
				}
				else
				{
					// $mixValue is an array so output its contents
					$strOutput .= $strTabs . $mixKey . "\n";
					$strOutput .= $this->_ShowInfo($mixValue, $strTabs."\t");
				}
			}
		} 
		else
		{
			$strOutput = $mixData . "\n";
		}
		return $strOutput;
	}
	

}


?>

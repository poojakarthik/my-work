
if (Vixen.TableSort == undefined)
{
	Vixen.TableSort = {
	
		// Number of rows per table page before bottom navigation controls are displayed
		BOTTOM_NAV_CONTROL_ROW_LIMIT: 20, 
		
		// Maximum number of navigation controls to displayed, including the current page number
		// (An odd number works best)
		NUMBER_OF_NAV_LINKS_TO_DISPLAY: 11,


		// Regular expression for HTML tags
		HTML_TAGS_REG: /<\/?[^>]+>/gi,

		// Regular expression for dates
		DATE_REG: /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/,

		// Constants used for sorting and as style class names
		SORT_ASC: " VixenTableSortColumnAsc ",
		SORT_DESC: " VixenTableSortColumnDesc ",

		// Regular expression for matching the above constants
		SORT_DIRECTION_REG: / *VixenTableSortColumn(Asc|Desc) */,

		// Constants used as style class names for table rows
		ROW_ODD: "Odd",
		ROW_EVEN: "Even",

		// Regular expression for matching the above constants
		ODD_EVEN_REG: /(Odd|Even)/,

		// prepare()
		/**
		 * Prepare a table for sorting.
		 *
		 * @param tableId String value of ID attribute of the table to be sorted
		 *
		 * @return void
		 *
		 * @public
		 */
		prepare: function(tableId)
		{
			// Get the table and initialise some attributes 
			var table = document.getElementById(tableId);
			table.sortedColumnIndex = -1;
			table.sortDirection = "None";
			table.style.layout = "fixed";
			
			// Prepare the columns for sorting
			var nrColumns = table.rows[0].cells.length;
			for (var columnIndex = 0; columnIndex < nrColumns; columnIndex++)
			{
				Vixen.TableSort._makeColumnSortable(table, columnIndex);
			}
			
			Vixen.TableSort.showTablePage(tableId);
		},

		// _makeColumnSortable()
		/**
		 * Make a column sortable.
		 *
		 * @param table DOMTable The table containing the column
		 * @param columnIndex int The index of the column to be manipulated
		 *
		 * @return void
		 *
		 * @private
		 */
		_makeColumnSortable: function(table, columnIndex)
		{
			// Get the header cell of the column
			var cell = table.rows[0].cells[columnIndex];
			
			// If the cell is not to be sorted then quit
			if (cell.getAttribute('NO_TABLE_SORT') == "1") return;
			
			// Add the style class for all sortable columns
			cell.className += " VixenTableSortColumn ";
			// Put the cell contents in a div (allowing us to display sorting images as background images)
			var div = document.createElement('DIV');
			div.className = 'VixenTableSortColumnWrapper';
			for (var i = 0, l = cell.childNodes.length; i < l; i++)
			{
				div.appendChild(cell.childNodes[0]);
			}
			// Append a &nbsp; for padding (... not sure if this is needed)
			div.appendChild(document.createTextNode('\u00A0'));
			// Resize the column to make space for the sorting images
			cell.style.width = (cell.clientWidth + 20) + "px";
			// Put the new div in the table cell
			cell.appendChild(div);
			// Add an onclick event handler to the cell
			// NOTE:: This will over-write any existing onclick event!
			// TODO:: This should be changed to use addEevntHandler W3C standard event handling
			cell.setAttribute("onclick", 'Vixen.TableSort.sortTable("'+table.id+'", '+columnIndex+')');
		},
		
		// getSortedField()
		/**
		 * Get the sorted field name (database column name).
		 *
		 * @param tableId String value of ID attribute of the table to get the sorted field of
		 *
		 * @return String TABLE_SORT attribute of the sorted column which, by default, 
		 * 				  maps to the database column name of the data in the column
		 *
		 * @public
		 */
		getSortedField: function(tableId)
		{
			var table = document.getElementById(tableId);
			if (table.sortedColumnIndex == -1) return null;
			var cell = table.rows[0].cells[table.sortedColumnIndex];
			return cell.getAttribute("TABLE_SORT");
		},

		// getSortedAscending()
		/**
		 * Get the sorted field name (database column name).
		 *
		 * @param tableId String value of ID attribute of the table to get the sorted direction of
		 *
		 * @return Boolean TRUE if table is sorted ascending
		 *
		 * @public
		 */
		getSortedAscending: function(tableId)
		{
			var table = document.getElementById(tableId);
			if (table.sortedColumnIndex == -1) return false;
			return table.sortDirection == Vixen.TableSort.SORT_ASC;;
		},

		// sortTable()
		/**
		 * Sort the contents of a table on the given column index.
		 *
		 * @param tableId String value of ID attribute of the table to sort
		 * @param columnIndex Integer column index of column to be sorted
		 *
		 * @return void
		 *
		 * @public
		 */
		sortTable: function(tableId, columnIndex)
		{
			// Get the table and header cell of the column to be sorted
			var table = document.getElementById(tableId);
			var cell = table.rows[0].cells[columnIndex];
			
			// Get the inverse sort direction of the column - default to ascending if not already sorted 
			var sortAscending = table.sortDirection != Vixen.TableSort.SORT_ASC || table.sortedColumnIndex != columnIndex;

			// Apply the sorting...
			Vixen.TableSort._applySort(table, columnIndex, sortAscending);

			// If the table had been sorted already...
			if (table.sortedColumnIndex >= 0)
			{
				// Get the previously sorted header cell and remove the sort style class name
				var oldSortedCell = table.rows[0].cells[table.sortedColumnIndex];
				oldSortedCell.className = oldSortedCell.className.replace(Vixen.TableSort.SORT_DIRECTION_REG, "");
			}

			// Update the table with the latest sort attributes
			table.sortDirection = sortAscending ? Vixen.TableSort.SORT_ASC : Vixen.TableSort.SORT_DESC;
			table.sortedColumnIndex = columnIndex;

			// Add the sort style class name to the sorted column
			var newSortedCell = table.rows[0].cells[columnIndex];
			newSortedCell.className += " " + table.sortDirection;
		},

		// resortTable()
		/**
		 * Re-sort the contents of a table using the already applied sort criteria.
		 * This is useful if you have just appended new items or reloaded the table contents.
		 *
		 * @param tableId String value of ID attribute of the table to re-sort
		 *
		 * @return void
		 *
		 * @public
		 */
		resortTable: function(tableId)
		{
			var table = document.getElementById(tableId);
			var sortAscending = table.sortDirection == Vixen.TableSort.SORT_ASC;
			Vixen.TableSort._applySort(table, table.sortedColumnIndex, sortAscending);
		},

		// setTableRows()
		/**
		 * Change the table contents.
		 *
		 * @param tableId String value of ID attribute of the table to change contents of
		 * @param rows DOMRows of another table to be placed in this one (eg: table.rows) 
		 *
		 * @return void
		 *
		 * @public
		 */
		setTableRows: function(tableId, rows)
		{
			Vixen.TableSort.emptyTable(tableId);
			Vixen.TableSort.appendTableRows(tableId, rows);
		},

		// setRows()
		/**
		 * Change the table contents.
		 *
		 * @param tableId String value of ID attribute of the table to change contents of
		 * @param rows array[DOMRow] rows to be placed in this one 
		 *
		 * @return void
		 *
		 * @public
		 */
		setRows: function(tableId, rows)
		{
			Vixen.TableSort.emptyTable(tableId);
			Vixen.TableSort.appendRows(tableId, rows);
		},

		// emptyTable()
		/**
		 * Remove the item rows from a table.
		 *
		 * @param tableId String value of ID attribute of the table to be emptied
		 *
		 * @return void
		 *
		 * @public
		 */
		emptyTable: function(tableId)
		{
			var table = document.getElementById(tableId);
			for (var i = table.rows.length - 1; i > 0; i--)
			{
				table.deleteRow(i);
			}
		},

		// appendTableRows()
		/**
		 * Append rows to a table.
		 *
		 * @param tableId String value of ID attribute of the table to append rows to
		 * @param rows DOMRows of another table to be appended to this one (eg: table.rows) 
		 *
		 * @return void
		 *
		 * @public
		 */
		appendTableRows: function(tableId, tableRows)
		{
			var table = document.getElementById(tableId);
			var pageRange = Vixen.TableSort._getPageRange(table);
			var newRowIndex = table.rows.length;
			for (var i = 0, l = tableRows.length; i < l; i++, newRowIndex++)
			{
				tableRows[0].style.display = (newRowIndex >= pageRange.from && pageRange <= pageRange.to) ? "table-row" : "none";
				table.tBodies[0].appendChild(tableRows[0]);
			}
			Vixen.TableSort.resortTable(tableId);
		},

		// appendTableRows()
		/**
		 * Append rows to a table.
		 *
		 * @param tableId String value of ID attribute of the table to append rows to
		 * @param rows array[DOMRow] rows to be appended to this one
		 *
		 * @return void
		 *
		 * @public
		 */
		appendRows: function(tableId, rows)
		{
			var table = document.getElementById(tableId);
			var pageRange = Vixen.TableSort._getPageRange(table);
			for (var i = 0, l = rows.length; i < l; i++, newRowIndex++)
			{
				rows[i].style.display = (newRowIndex >= pageRange.from && pageRange <= pageRange.to) ? "table-row" : "none";
				table.tBodies[0].appendChild(rows[i]);
			}
			Vixen.TableSort.resortTable(tableId);
		},

		// _applySort()
		/**
		 * Apply sorting to a table.
		 *
		 * @param table DOMTable table element to be sorted
		 * @param columnIndex Integer column index of column to be sorted
		 * @param sortAscending Boolean whether to sort ascending (TRUE) or descending (FALSE)
		 *
		 * @return void
		 *
		 * @private
		 */
		_applySort: function(table, columnIndex, sortAscending)
		{
			if (columnIndex == -1) 
			{
				Vixen.TableSort.showTablePage(table.id);
				return;
			}
			var values = Vixen.TableSort._getColumnValues(table, columnIndex);
			var compFunc = Vixen.TableSort._getComparisonFunction(values, table, columnIndex);
			var anyChanges = Vixen.TableSort._sortValues(values, compFunc, sortAscending);
			if (anyChanges) 
			{
				Vixen.TableSort._reArrangeRows(table, values);
			}
			Vixen.TableSort.showTablePage(table.id);
		},

		// _reArrangeRows()
		/**
		 * Re-arrange rows of a table.
		 *
		 * @param table DOMTable table element to be sorted
		 * @param sortedValues array[String => array[ 0=>Value, 1=>DOMRow ] ] Return value of _getColumnValues()
		 * @param sortAscending Boolean whether to sort ascending (TRUE) or descending (FALSE)
		 *
		 * @return void
		 *
		 * @private
		 */
		_reArrangeRows: function(table, sortedValues)
		{
			for (var i = 0, l = sortedValues.length; i < l; i++)
			{
				// Apply the appropriate style class to the row (odd/even) 
				var strClass = (i % 2) ? Vixen.TableSort.ROW_ODD : Vixen.TableSort.ROW_EVEN;
				sortedValues[i][1].className = sortedValues[i][1].className.replace(Vixen.TableSort.ODD_EVEN_REG, strClass);
				// Append this row to the end of the table 
				table.tBodies[0].appendChild(sortedValues[i][1]);
			}
		},

		// _sortValues()
		/**
		 * Sort a list values.
		 *
		 * @param sortedValues array[String => array[ 0=>Value, 1=>DOMRow ] ] Return value of _getColumnValues()
		 * @param compFunc Function to be used for comparing the values
		 * @param sortAscending Boolean whether to sort ascending (TRUE) or descending (FALSE)
		 *
		 * @return Boolean TRUE if any sort-order changes were required
		 *
		 * @see http://en.wikipedia.org/wiki/Cocktail_sort
		 *
		 * @private
		 */
		_sortValues: function(values, compFunc, sortAscending)
		{
			// 
			var b = 0;
			var t = values.length - 1;
			var swap = true;
			var sortSwitch = sortAscending ? 1 : -1;
			var anySwaps = false;

			while(swap) {
				swap = false;
				for(var i = b; i < t; ++i) {
					if ( (compFunc(values[i][0], values[i+1][0]) * sortSwitch) > 0 ) {
						var q = values[i]; values[i] = values[i+1]; values[i+1] = q;
						swap = true;
						anySwaps = true;
					}
				} // for
				t--;

				if (!swap) break;

				for(var i = t; i > b; --i) {
					if ( (compFunc(values[i][0], values[i-1][0]) * sortSwitch) < 0 ) {
						var q = values[i]; values[i] = values[i-1]; values[i-1] = q;
						swap = true;
						anySwaps = true;
					}
				} // for
				b++;

			} // while(swap)
			return anySwaps;
		},

		// _innerText()
		/**
		 * Get the textual content of a DOMNode 
		 *
		 * @param node DOMNode to get the textual contents of
		 *
		 * @return String contents of passed DOMNode 
		 *
		 * @private
		 */
		_innerText: function(node)
		{
			// Return the innerHTML of the node with HTML tags stripped out
			return node.innerHTML.replace(Vixen.TableSort.HTML_TAGS_REG, "");
		},

		// _getColumnValues()
		/**
		 * Get the values from a column in a table.
		 *
		 * @param table DOMTable table element to get values from
		 * @param columnIndex Integer column index of column to get values from
		 *
		 * @return array[integer => array[ 0=>strValue, 1=>DOMRow ] ] Textual values and rows for column
		 *
		 * @private
		 */
		_getColumnValues: function(table, columnIndex)
		{
			// Itterate through the cells of the column, skipping the first (hearer) cell 
			var values = new Array();
			var maxRowIndex = table.rows.length - 1;
			for (var rowIndex = 1; rowIndex <= maxRowIndex; rowIndex++)
			{
				// Add the row to the values array in the form of [ 0 => value, 1 => DOMRow ]
				values[rowIndex - 1] = [ Vixen.TableSort._innerText(table.rows[rowIndex].cells[columnIndex]), table.rows[rowIndex] ];
			}
			return values;
		},

		// _getComparisonFunction()
		/**
		 * Determine and return an appropriate comparison function to be used for a list of values, based on a guess of the value type.
		 *
		 * @param values array[String => array[ 0=>Value, 1=>DOMRow ] ] Return value of _getColumnValues()
		 * @param table DOMTable table element to get function for
		 * @param columnIndex Integer column index of column to function for
		 *
		 * @return Function to be used for comparing values of the guessed type in the passed array 
		 *
		 * @private
		 */
		_getComparisonFunction: function(values, table, columnIndex) 
		{
			// If the column head has a SORT_TYPE attribute, we can use it to determine the 
			// most appropriate sort function to use.
			if (table.rows[0].cells[columnIndex].hasAttribute("SORT_TYPE"))
			{
				switch(table.rows[0].cells[columnIndex].getAttribute("SORT_TYPE").toUpperCase())
				{
					case "DDMM":
						return Vixen.TableSort._compareDDMM;
					case "MMDD":
						return Vixen.TableSort._compareMMDD;
					case "NUMERIC":
						return Vixen.TableSort._compareNumeric;
					case "ALPHA":
						return Vixen.TableSort._compareAlphaCaseInsensitive;
				}
			}
		
			// Apply a default sort function for case-insensitive alphabetical sorting
			var sortfn = Vixen.TableSort._compareAlphaCaseInsensitive;
			var softfnName = "ALPHA";
			for (var i=0; i<values.length; i++) 
			{
				var text = values[i][0];
				if (text != '') 
				{
					// Check to see if it is numeric
					if (text.match(/^-?[£$¤]?[\d,.]+%?$/)) 
					{
						// Set the SORT_TYPE attribute on the header cell so we don't have to do this check next time
						table.rows[0].cells[columnIndex].setAttribute("SORT_TYPE", "NUMERIC");
						return Vixen.TableSort._compareNumeric;
					}
					// Check for a date: dd/mm/yyyy or dd/mm/yy 
					// can have / or . or - as separator
					// can be mm/dd as well
					var possdate = text.match(Vixen.TableSort.DATE_REG)
					if (possdate) 
					{
						// looks like a date
						first = parseInt(possdate[1]);
						second = parseInt(possdate[2]);
						if (first > 12) 
						{
							// definitely dd/mm
							// Set the SORT_TYPE attribute on the header cell so we don't have to do this check next time
							table.rows[0].cells[columnIndex].setAttribute("SORT_TYPE", "DDMM");
							return Vixen.TableSort._compareDDMM;
						} 
						else if (second > 12) 
						{
							// definitely mm/dd
							// Set the SORT_TYPE attribute on the header cell so we don't have to do this check next time
							table.rows[0].cells[columnIndex].setAttribute("SORT_TYPE", "MMDD");
							return Vixen.TableSort._compareMMDD;
						} 
						else 
						{
							// looks like a date, but we can't tell which, so assume
							// that it's dd/mm (English imperialism!) and keep looking
							softfnName = "DDMM";
							sortfn = Vixen.TableSort._compareDDMM;
							continue;
						}
					}
					// OK, so it's not numeric or a date.
					// Must be an alphanumeric
					// Set the SORT_TYPE attribute on the header cell so we don't have to do this check next time
					table.rows[0].cells[columnIndex].setAttribute("SORT_TYPE", "ALPHA");
					return Vixen.TableSort._compareAlphaCaseInsensitive;
				}
			}
			// We've looked at them all. They are either all dates with days <= 12 or they are all empty
			// Set the SORT_TYPE attribute on the header cell so we don't have to do this check next time
			table.rows[0].cells[columnIndex].setAttribute("SORT_TYPE", softfnName);
			return sortfn;
		},

		// _compareAlphaCaseInsensitive()
		/**
		 * Compare two values as case-insensitive alphanumerics
		 *
		 * @param a String first value
		 * @param b String second value
		 *
		 * @return Integer 	> 0 if a > b
		 *					< 0 if a < b
		 *					  0 if a == b  
		 *
		 * @private
		 */
		_compareAlphaCaseInsensitive: function(a, b) 
		{
			aUpper = a.toUpperCase();
			bUpper = b.toUpperCase();
			if (aUpper == bUpper) return 0;
			if (aUpper < bUpper) return -1;
			return 1;
		},

		// _compareAlpha()
		/**
		 * Compare two values as alphanumerics
		 *
		 * @param a String first value
		 * @param b String second value
		 *
		 * @return Integer 	> 0 if a > b
		 *					< 0 if a < b
		 *					  0 if a == b  
		 *
		 * @private
		 */
		_compareAlpha: function(a, b) 
		{
			if (a == b) return 0;
			if (a < b) return -1;
			return 1;
		},

		// _compareNumeric()
		/**
		 * Compare two values as numeric values
		 *
		 * @param a String first value
		 * @param b String second value
		 *
		 * @return Integer 	> 0 if a > b
		 *					< 0 if a < b
		 *					  0 if a == b  
		 *
		 * @private
		 */
		_compareNumeric: function(a, b) 
		{
			aa = parseFloat(a.replace(/[^0-9.-]/g,''));
			if (isNaN(aa)) aa = 0;
			bb = parseFloat(b.replace(/[^0-9.-]/g,'')); 
			if (isNaN(bb)) bb = 0;
			return aa - bb;
		},

		// _compareDDDMM()
		/**
		 * Compare two values as dates in the format DDMM[YY]YY
		 *
		 * @param a String first value
		 * @param b String second value
		 *
		 * @return Integer 	> 0 if a > b
		 *					< 0 if a < b
		 *					  0 if a == b  
		 *
		 * @private
		 */
		_compareDDDMM: function(a, b) 
		{
			mtch = a.match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; m = mtch[2]; d = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt1 = y+m+d;
			mtch = b.match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; m = mtch[2]; d = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt2 = y+m+d;
			if (dt1==dt2) return 0;
			if (dt1<dt2) return -1;
			return 1;
		},

		// _compareMMDD()
		/**
		 * Compare two values as dates in the format MMDD[YY]YY
		 *
		 * @param a String first value
		 * @param b String second value
		 *
		 * @return Integer 	> 0 if a > b
		 *					< 0 if a < b
		 *					  0 if a == b  
		 *
		 * @private
		 */
		_compareMMDD: function(a, b) 
		{
			mtch = a.match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; d = mtch[2]; m = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt1 = y+m+d;
			mtch = b.match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; d = mtch[2]; m = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt2 = y+m+d;
			if (dt1==dt2) return 0;
			if (dt1<dt2) return -1;
			return 1;
		},
		
		// showTablePage()
		/**
		 * Shows a page of a table, making it paginated if not already
		 *
		 * @param tableId String value of ID attribute of the paginated table
		 * @param page Integer of page to be displayed
		 *
		 * @return void
		 *
		 * @public
		 */
		showTablePage: function(tableId, page)
		{
			var table = document.getElementById(tableId);
			if (!table.hasAttribute("page_size"))
			{
				// Table does not want paginating!!
				return;
			}
			// Check to see if pagination has not been applied to this table
			if (!table.paginationInitialized)
			{
				table.pageSize = parseInt(table.getAttribute("page_size"));

				// Apply pagination even if there are not enough rows to warrant it.
				// The number of rows in the table could increase later!!
				
				// Create a wrapper to hold the table & navigation controls
				var wrapper = document.createElement("div");
				wrapper.className = "paginated-table-wrapper";
				//wrapper.style.width = table.clientWidth + "px";
				wrapper.style.width = "100%";
				
				// Create a container for the navigation controls
				var nav = document.createElement("div");
				wrapper.appendChild(nav);
				nav.className = "paginated-table-navigation";
				nav.id = tableId + "_page_nav_top";
				
				// Place the wrapper in the tables location in the DOM					
				table.parentNode.replaceChild(wrapper, table);
				// And slap the table into the wrapper
				wrapper.appendChild(table);
				
				// Clone nav and append it here to give controls below the table too.
				nav = nav.cloneNode(true);
				nav.id = tableId + "_page_nav_bottom";
				wrapper.appendChild(nav);
				
				// Set the table as paginated
				table.rows[0].style.display = "table-row";
				table.className += " paginated ";
				table.pageDisplay = 1;

				// Record the fact that the pagination has been set up
				table.paginationInitialized = true;
			}
			if (page == undefined)
			{
				page = table.pageDisplay;
			}
			var nrPages = Math.ceil((table.rows.length - 1) / table.pageSize);
			if (page > nrPages)
			{
				page = nrPages;
			}
			if (page < 1)
			{
				page = 1;
			}
			
			// Find the last row index before the displayed page range
			var hideTo = ((page - 1) * table.pageSize);
			// Find the first row index after the displayed page range
			var hideFrom = (page * table.pageSize) + 1;
			// Itterate through all table rows except the first (header) row
			for (var i = 1, nrRows = table.rows.length; i < nrRows; i++)
			{
				// If a row is out of range then hide it, else show it
				table.rows[i].style.display = (i <= hideTo || i >= hideFrom) ? "none" : "table-row";
			}
			// Update the pageDisplay property of the table
			table.pageDisplay = page;
					
			// Prepare some appropriate nav controls
			Vixen.TableSort._doNavControls(table);
		},
		
		// _doNavControls()
		/**
		 * Prepares appropriate navigation controls for a paginated table.
		 * Assumes that the navigation control divs already exist in the document.
		 *
		 * @param table DOMTable to prepare navigation controls for
		 *
		 * @return void
		 *
		 * @private
		 */
		_doNavControls: function(table)
		{
			// Get the nav control bar
			var navTopId = table.id + "_page_nav_top";
			var nav = document.getElementById(navTopId);
			
			// Attempt to retrieve the bottom nav controls
			var navBottomId = table.id + "_page_nav_bottom";
			var navBottom = document.getElementById(navBottomId);

			// If pagination isn't needed (there is only one page!)
			if ((table.rows.length - 1) <= table.pageSize)
			{
				// Hide the navigation control divs
				nav.style.display = "none";
				if (navBottom != undefined && navBottom != null)
				{
					navBottom.style.display = "none";
				}
				return;
			}
			
			// Navigation is required so ensure controls are visible
			nav.style.display = "block";
			
			// Remove the old controls
			for (var i = nav.childNodes.length - 1; i >= 0; i--)
			{
				nav.removeChild(nav.childNodes[i]);
			}
			
			// Define the number of links to be displayed including the current page
			// (done like this to allow making this configurable easier)
			var nrLinks = Vixen.TableSort.NUMBER_OF_NAV_LINKS_TO_DISPLAY;

			// Figure out what pagination controls are wanted
			var nrPages = Math.ceil((table.rows.length - 1) / table.pageSize);
			var pagesBefore = table.pageDisplay - 1;
			var pagesAfter = nrPages - table.pageDisplay;
			
			// If there are more pages than desired number of links, add quick nav links for first/previous page
			if (nrLinks < nrPages)
			{
				// Determine if the current page is the first page.
				// If it is then we should make the controls invisible to act as 'space savers'
				var firstPage = table.pageDisplay <= 1; 
				// \u00AB is HTML entity &laqou; and \u2039 is HTML entity &lsaquo;
				nav.appendChild(Vixen.TableSort._navSpan(table.id, "\u00AB", 1, 					firstPage, !firstPage));
				nav.appendChild(Vixen.TableSort._navSpan(table.id, "\u2039", table.pageDisplay - 1, firstPage, !firstPage));
			}
			
			// Default all pages to be displayed
			var from = 1;
			var to = nrPages;

			// Work out the desired minimum number of links allowed before or after the current page
			var halfLinksBefore = Math.ceil((nrLinks - 1) / 2);
			var halfLinksAfter = Math.floor((nrLinks - 1) / 2);
			
			// If links can't be displayed for all pages
			if (nrPages > nrLinks)
			{
				// If there are more pages both before and after the current page than can be linked to
				if (pagesAfter >= halfLinksAfter && pagesBefore >= halfLinksBefore)
				{
					// Spread the link range evenly around the current page
					from = table.pageDisplay - halfLinksBefore;
					to = table.pageDisplay + halfLinksAfter;
				}
				// If there are only more pages after than can be linked to 
				else if (pagesAfter >= halfLinksAfter)
				{
					// Allow links up to the maximum number of links allowed
					to = nrLinks;
				}
				// There are only more links before the current page than can be linked to
				else
				{
					// Allow as many links from the last page as we can
					from = nrPages - nrLinks + 1;
				}
			}
			
			// Add links to each page in the calculated range
			for (var i = from; i <= to; i++)
			{
				nav.appendChild(Vixen.TableSort._navSpan(table.id, i, i, table.pageDisplay == i));
			}

			// If there are more pages than links, add quick nav links for next/last page
			if (nrLinks < nrPages)
			{
				// Determine if the current page is the last page
				// If it is then we should make the controls invisible to act as 'space savers'
				var lastPage = table.pageDisplay >= nrPages; 
				// \u203A is HTML entity &rsaquo; and \u00BB is HTML entity &raquo;
				nav.appendChild(Vixen.TableSort._navSpan(table.id, "\u203A", table.pageDisplay + 1, lastPage, !lastPage));
				nav.appendChild(Vixen.TableSort._navSpan(table.id, "\u00BB", nrPages, 				lastPage, !lastPage));
			}
			
			// Duplicate controls at bottom if they exist there already
			if (navBottom != undefined && navBottom != null)
			{
				// If there are more than 20 items per page, show navigation controls at the bottom too
				if (table.pageSize >= Vixen.TableSort.BOTTOM_NAV_CONTROL_ROW_LIMIT)
				{
					nav = nav.cloneNode(true);
					nav.id = navBottomId;
					navBottom.parentNode.replaceChild(nav, navBottom);
				}
				// Otherwise, these are a bit excessive, so hide them
				else
				{
					navBottom.style.display = "none";
				}
			}
		},
		
		// _navSpan()
		/**
		 * Returns a span element to be used as a navigation control
		 *
		 * @param tableId String value of ID attribute of the paginated table
		 * @param label String to be displayed as the navigation control
		 * @param page Integer of page to be displayed when the control is clicked
		 * @param selected Boolen TRUE if the target page is the currently displayed page, else false
		 * @param visible Boolean FALSE if the element should be a space-holder only i.e. invisible (default=TRUE) 
		 *
		 * @return DOMSpan node to be used as a navigation control
		 *
		 * @private
		 */
		_navSpan: function(tableId, label, page, selected, visible)
		{
			// Create the DOM node
			var span = document.createElement("span");
			
			// Append the label as a text node 
			span.appendChild(document.createTextNode(label));
			
			// If selected then add a suitable class name for styling
			if (selected)
			{
				span.className = "paginated-table-navigation-control-current";
			}
			// If not active, add a click event listener to pagingate to the desired page
			else
			{
				span.setAttribute("onclick", "Vixen.TableSort.showTablePage('" + tableId + "', " + page + ");");
			}

			// Unless specified, assume it should be visible
			if (visible == undefined) visible = true;
			if (!visible) span.style.visibility = "hidden";

			return span;
		},
		
		// _getPageRange()
		/**
		 * Returns an inclusive range of rows that should be displayed for the current page
		 *
		 * @param table DOMTable to get the displayed range of
		 *
		 * @return Object with integer properties 'from' and 'to' defining the index of rows in the range
		 *
		 * @private
		 */
		_getPageRange: function(table)
		{
			// Set a default range that includes everything after the header.
			// If there are more rows in your than I am allowing here, you deserve it to go wrong!!
			var range = { from: 1, to: 9999999999 };
			
			// If the table has pagination initialized on it, take the actual values from the table
			if (table.paginationInitialized)
			{
				range.from = (table.pageSize * (table.pageDisplay - 1)) + 1;
				range.to   = (table.pageSize * table.pageDisplay);
			}
			
			// Return the range
			return range;
		}

	}
}

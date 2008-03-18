if ($get == undefined)
{
	var $get = function(id)
	{
		if (typeof id != 'string') return id;
		return document.getElementById(id);
	}
}

if (Vixen.TableSort == undefined)
{
	Vixen.TableSort = {

		HTML_TAGS_REG: /<\/?[^>]+>/gi,

		DATE_REG: /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/,

		SORT_ASC: " VixenTableSortColumnAsc ",
		SORT_DESC: " VixenTableSortColumnDesc ",
		SORT_DIRECTION_REG: / *VixenTableSortColumn(Asc|Desc) */,

		ROW_ODD: "Odd",
		ROW_EVEN: "Even",
		ODD_EVEN_REG: /(Odd|Even)/,

		prepare: function(tableId)
		{
			var table = $get(tableId);
			table.sortedColumnIndex = -1;
			table.sortDirection = "None";
			table.style.layout = "fixed";
			var nrColumns = table.rows[0].cells.length;
			for (var columnIndex = 0; columnIndex < nrColumns; columnIndex++)
			{
				Vixen.TableSort._makeColumnSortable(table, columnIndex);
			}
		},

		_makeColumnSortable: function(table, columnIndex)
		{
			var cell = table.rows[0].cells[columnIndex];
			if (cell.getAttribute('NO_TABLE_SORT') == "1") return;
			cell.className += " VixenTableSortColumn ";
			var div = document.createElement('DIV');
			div.className = 'VixenTableSortColumnWrapper';
			for (var i = 0, l = cell.childNodes.length; i < l; i++)
			{
				div.appendChild(cell.childNodes[0]);
			}
			div.appendChild(document.createTextNode('\u00A0'));
			cell.style.width = (cell.clientWidth + 20) + "px";
			cell.appendChild(div);
			cell.setAttribute("onclick", 'Vixen.TableSort.sortTable("'+table.id+'", '+columnIndex+')');
		},
		
		getSortedField: function(tableId)
		{
			var table = $get(tableId);
			if (table.sortedColumnIndex == -1) return null;
			var cell = table.rows[0].cells[table.sortedColumnIndex];
			return cell.getAttribute("TABLE_SORT");
		},

		sortTable: function(tableId, columnIndex)
		{
			var table = $get(tableId);
			var cell = table.rows[0].cells[columnIndex];
			
			var sortAscending = table.sortDirection != Vixen.TableSort.SORT_ASC || table.sortedColumnIndex != columnIndex;

			Vixen.TableSort._applySort(table, columnIndex, sortAscending);

			if (table.sortedColumnIndex >= 0)
			{
				var oldSortedCell = table.rows[0].cells[table.sortedColumnIndex];
				oldSortedCell.className = oldSortedCell.className.replace(Vixen.TableSort.SORT_DIRECTION_REG, "");
			}

			table.sortDirection = sortAscending ? Vixen.TableSort.SORT_ASC : Vixen.TableSort.SORT_DESC;
			table.sortedColumnIndex = columnIndex;

			var newSortedCell = table.rows[0].cells[columnIndex];
			newSortedCell.className += " " + table.sortDirection;
		},

		resortTable: function(tableId)
		{
			var table = $get(tableId);
			var sortAscending = table.sortDirection == Vixen.TableSort.SORT_ASC;
			Vixen.TableSort._applySort(table, table.sortedColumnIndex, sortAscending);
		},

		setTableRows: function(tableId, rows)
		{
			Vixen.TableSort.emptyTable(tableId);
			Vixen.TableSort.appendTableRows(tableId, rows);
		},

		setRows: function(tableId, rows)
		{
			Vixen.TableSort.emptyTable(tableId);
			Vixen.TableSort.appendRows(tableId, rows);
		},

		emptyTable: function(tableId)
		{
			var table = $get(tableId);
			for (var i = table.rows.length - 1; i > 0; i--)
			{
				table.deleteRow(i);
			}
		},

		appendTableRows: function(tableId, tableRows)
		{
			var table = $get(tableId);
			for (var i = 0, l = tableRows.length; i < l; i++)
			{
				table.tBodies[0].appendChild(tableRows[0]);
			}
			Vixen.TableSort.resortTable(tableId);
		},

		appendRows: function(tableId, rows)
		{
			var table = $get(tableId);
			for (var i = 0, l = rows.length; i < l; i++)
			{
				table.tBodies[0].appendChild(rows[i]);
			}
			Vixen.TableSort.resortTable(tableId);
		},

		_applySort: function(table, columnIndex, sortAscending)
		{
			if (columnIndex == -1) return;
			var values = Vixen.TableSort._getColumnValues(table, columnIndex);
			var compFunc = Vixen.TableSort._getComparisonFunction(values);
			var anyChanges = Vixen.TableSort._sortValues(values, compFunc, sortAscending);
			if (anyChanges) Vixen.TableSort._reArrangeRows(table, values);
		},

		_reArrangeRows: function(table, sortedValues)
		{
			var l = sortedValues.length;
			for (var i = 0; i < l; i++)
			{
				var strClass = (i % 2) ? Vixen.TableSort.ROW_EVEN : Vixen.TableSort.ROW_ODD;
				sortedValues[i][1].className = sortedValues[i][1].className.replace(Vixen.TableSort.ODD_EVEN_REG, strClass);
				table.tBodies[0].appendChild(sortedValues[i][1]);
			}
		},

		_sortValues: function(values, compFunc, sortAscending)
		{
			// see: http://en.wikipedia.org/wiki/Cocktail_sort
			var b = 0;
			var t = values.length - 1;
			var swap = true;
			var sortSwitch = sortAscending ? 1 : -1;
			var anySwaps = false;

			while(swap) {
				swap = false;
				for(var i = b; i < t; ++i) {
					if ( (compFunc(values[i], values[i+1]) * sortSwitch) > 0 ) {
						var q = values[i]; values[i] = values[i+1]; values[i+1] = q;
						swap = true;
						anySwaps = true;
					}
				} // for
				t--;

				if (!swap) break;

				for(var i = t; i > b; --i) {
					if ( (compFunc(values[i], values[i-1]) * sortSwitch) < 0 ) {
						var q = values[i]; values[i] = values[i-1]; values[i-1] = q;
						swap = true;
						anySwaps = true;
					}
				} // for
				b++;

			} // while(swap)
			return anySwaps;
		},

		_getComparisonFunction: function(values)
		{
			return Vixen.TableSort._compareAlphaCaseInsensitive;
		},

		_guessType: function(table, column) 
		{
			// guess the type of a column based on its first non-blank row
			var sortfn = Vixen.TableSort._compareAlpha;
			for (var i=0; i<table.rows.length; i++) 
			{
				var text = Vixen.TableSort._innerText(table.rows[i].cells[column]);
				if (text != '') 
				{
					if (text.match(/^-?[£$¤]?[\d,.]+%?$/)) 
					{
						return Vixen.TableSort._compareNumeric;
					}
					// check for a date: dd/mm/yyyy or dd/mm/yy 
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
							return Vixen.TableSort._compareDDMM;
						} 
						else if (second > 12) 
						{
							return Vixen.TableSort._compareMMDD;
						} 
						else 
						{
							// looks like a date, but we can't tell which, so assume
							// that it's dd/mm (English imperialism!) and keep looking
							sortfn = Vixen.TableSort._compareDDMM;
						}
					}
				}
			}
			return sortfn;
		},

		_compareNumeric: function(a,b) 
		{
			aa = parseFloat(a[0].replace(/[^0-9.-]/g,''));
			if (isNaN(aa)) aa = 0;
			bb = parseFloat(b[0].replace(/[^0-9.-]/g,'')); 
			if (isNaN(bb)) bb = 0;
			return aa-bb;
		},

		_compareAlpha: function(a,b) 
		{
			if (a[0]==b[0]) return 0;
			if (a[0]<b[0]) return -1;
			return 1;
		},

		_compareAlphaCaseInsensitive: function(a,b) 
		{
			aUpper = a[0].toUpperCase();
			bUpper = b[0].toUpperCase();
			if (aUpper==bUpper) return 0;
			if (aUpper<bUpper) return -1;
			return 1;
		},

		_compareDDDMM: function(a,b) 
		{
			mtch = a[0].match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; m = mtch[2]; d = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt1 = y+m+d;
			mtch = b[0].match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; m = mtch[2]; d = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt2 = y+m+d;
			if (dt1==dt2) return 0;
			if (dt1<dt2) return -1;
			return 1;
		},

		_compareMMDD: function(a,b) 
		{
			mtch = a[0].match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; d = mtch[2]; m = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt1 = y+m+d;
			mtch = b[0].match(Vixen.TableSort.DATE_REG);
			y = mtch[3]; d = mtch[2]; m = mtch[1];
			if (m.length == 1) m = '0'+m;
			if (d.length == 1) d = '0'+d;
			dt2 = y+m+d;
			if (dt1==dt2) return 0;
			if (dt1<dt2) return -1;
			return 1;
		},

		_innerText: function(node)
		{
			return node.innerHTML.replace(Vixen.TableSort.HTML_TAGS_REG, "");
		},

		_getColumnValues: function(table, columnIndex)
		{
			var values = new Array();
			var maxRowIndex = table.rows.length - 1;
			for (var rowIndex = 1; rowIndex <= maxRowIndex; rowIndex++)
			{
				values[rowIndex - 1] = [ Vixen.TableSort._innerText(table.rows[rowIndex].cells[columnIndex]), table.rows[rowIndex] ];
			}
			return values;
		}

	}
}
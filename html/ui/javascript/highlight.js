//----------------------------------------------------------------------------//
// VixenHighlightClass
//----------------------------------------------------------------------------//
/**
 * VixenHighlightClass
 *
 * Vixen highlight class
 *
 * Vixen highlight class
 *
 *
 *
 * @package	framework_ui
 * @class	Vixen.Highlight
 */
function VixenHighlightClass()
{
	this.Unselect = function(strTableId)
	{
		// Remove all highlighting/selection from a table
		for (var i=0; i < Vixen.table[strTableId].totalRows; i++)
		{
			var elmRowUnselect = $ID(strTableId + '_' + i);
			
			// Change the class back to even/odd
			if (elmRowUnselect.intRowIndex % 2)
			{
				elmRowUnselect.className = "Even";
			}
			else
			{
				elmRowUnselect.className = "Odd";
			}
			// Deselect the row
			Vixen.table[strTableId].row[i].selected = false;
			Vixen.table[strTableId].row[i].primarySelected = false;
		}
	}
	
	this.ToggleSelect = function(elmRow)
	{
		// Grab the table and row objects
		var strTable	= elmRow.parentNode.parentNode.id;
		var objRow		= Vixen.table[strTable].row[elmRow.intRowIndex];
		
		// Clear all selections from this table
		this.Unselect(strTable);
		
		elmRow.className		= "PrimarySelected";
		objRow.selected			= true;
		objRow.primarySelected	= true;
	}
	
	this.LightsUp = function(elmRow)
	{
		// MouseOver on row, highlight the row, unless it is selected
		if (!Vixen.table[elmRow.parentNode.parentNode.id].row[elmRow.intRowIndex].selected)
		{
			elmRow.className = "Hover";
		}
	}
	
	this.LightsDown = function(elmRow)
	{
		// MouseOut on row, remove highlight
		var intRow = elmRow.intRowIndex;
		var objRow = Vixen.table[elmRow.parentNode.parentNode.id].row[intRow];
		
		if (objRow.selected)
		{
			// Row is selected
			elmRow.className = (objRow.primarySelected)? "PrimarySelected" : "Selected";
		}
		else
		{
			// Row is not selected. Change the class back to even/odd
			elmRow.className = (intRow % 2)? "Even" : "Odd";
		}
	}
	
	this.Attach = function(strTableId)
	{
		// Add behaviour to the table
		for (var i=0; i < Vixen.table[strTableId].totalRows; i++)
		{
			var elmRow = $ID(strTableId + '_' + i);
			elmRow.intRowIndex = i;
			
			elmRow.addEventListener('mousedown', MouseDownHandler, TRUE);
			elmRow.addEventListener('mouseover', MouseOverHandler, TRUE);
			elmRow.addEventListener('mouseout', MouseOutHandler, TRUE);
		}
	}
	
	function MouseDownHandler()
	{
		// MouseDown on row, toggle row and propagate selection
		objTable = Vixen.table[this.parentNode.parentNode.id];
		
		//NOTE: the this pointer points to the row element
		
		// Propagate selection to linked tables
		if (objTable.linked)
		{
			// get row number from elmRow
			//var intRow = this.id.lastIndexOf('_');
			//intRow = this.id.substr(intRow + 1);
			
			// update table links
			// TODO! I don't know why the 2nd and 3rd parameters are in []s
			Vixen.Highlight.UpdateLink(objTable.link, [objTable.row[this.intRowIndex].index],[this.parentNode.parentNode.id]);
		}
		
		// Toggle row
		Vixen.Highlight.ToggleSelect(this);
	}
	
	function MouseOverHandler()
	{
		// MouseOver on row, highlight it
		Vixen.Highlight.LightsUp(this);
	}

	function MouseOutHandler()
	{
		// MouseOut on row, remove highlight
		Vixen.Highlight.LightsDown(this);
	}
	
	//TODO! this functionality has to be fixed up.  It is conceptually wrong
	this.UpdateLink = function(arrTables, arrIndexes, arrSkipTables)
	{
		// Propagate selection from one table to next
		//TODO! Only tables rows that directly relate to the selected row should be highlighted
		//That is to say, don't highlight rows that relate to other rows that relate to the initially highlighted row

		// declare variables
		var intTable;
		var strTable;
		var tblTarget;
		var bolSkip = FALSE;
		var objRow;
		var intRow;
		var intIndex;
		var intRowIndex;
		var strIndex;
		var arrSubIndexes = Array();
		var intIndexEntry;
		
		// for each linked table
		for (strTable in arrTables)
		{	
			tblTarget 	= Vixen.table[strTable];
			// bolSkip = FALSE;
			// check for skip table
			for (intTable=0; intTable < arrSkipTables.length; intTable++)
			{
				if (arrSkipTables[intTable] == strTable)
				{
					bolSkip = TRUE;
					break;
				}
				else
				{
					bolSkip = FALSE;
				}
			}
		
			// skip this table if it has already been done
			if (bolSkip == TRUE)
			{
				continue;
			}
			
			// Unselect all on target (& collapse?)
			Vixen.Highlight.Unselect(strTable);
			Vixen.Slide.CollapseAll(strTable);
		
			// for each row
			for (intRow=0; intRow < tblTarget.row.length; intRow++)
			{
				objRow = tblTarget.row[intRow];
				
				// for each index
				for (intIndex=0; intIndex < arrIndexes.length; intIndex++)
				{
					for (strIndex in arrIndexes[intIndex])
					{
						// check if we are linked on this index
						if (arrTables[strTable] != strIndex)
						{
							// not linked
							continue;							
						}
					
						// check if the row has an index
						if (!objRow.index)
						{
							// no index
						}
						else
						{
							// for each row index that matches index
							if (objRow.index[strIndex])
							{					
								for (intRowIndex in objRow.index[strIndex])
								{
									for (intIndexEntry=0; intIndexEntry < arrIndexes[intIndex][strIndex].length; intIndexEntry++)
									{
										if (objRow.index[strIndex][intRowIndex] == arrIndexes[intIndex][strIndex][intIndexEntry])
										{
											// Highlight if index matches
											strRowId = strTable + "_" + intRow;
											Vixen.table[strTable].row[intRow].selected = TRUE;
											Vixen.Highlight.LightsDown(document.getElementById(strRowId));
											
											// Add row indexes to arrSubIndexes
											arrSubIndexes.push(objRow.index);
										}
									}
								}
							}
						}
					}
				}
				
			}
			
			// if we are linked to other tables
			if (tblTarget.linked)
			{				
				// add table to skip tables
				arrSkipTables.push(strTable);
				
				// update table links
				Vixen.Highlight.UpdateLink(tblTarget.link, arrSubIndexes, arrSkipTables);
			}
		}
	}
}

// Create an instance of the Vixen highlight class
if (Vixen.Highlight == undefined)
{
	Vixen.Highlight = new VixenHighlightClass();
}

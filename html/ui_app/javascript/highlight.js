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
	this.Unselect =function(strTableId)
	{
		// Remove all highlighting/selection from a table
		for (var i=0; i <= Vixen.table[strTableId].totalRows; i++)
		{
			var elmRowUnselect = document.getElementById(strTableId + '_' + i);
			
			// Change the class back to even/odd
			if (elmRowUnselect.id.substr(elmRowUnselect.id.indexOf('_') + 1) % 2)
			{
				elmRowUnselect.className = "Even";
			}
			else
			{
				elmRowUnselect.className = "Odd";
			}
			// Deselect the row
			Vixen.table[strTableId].row[i].selected = FALSE;
			
		}
	}
	this.ToggleSelect =function (elmRow)
	{
		// get row number from elmRow
		intPos = elmRow.id.lastIndexOf('_');
		intRow = elmRow.id.substr(intPos + 1);
		
		// Grab the javascript row object
		objRow = Vixen.table[elmRow.parentNode.parentNode.id].row[intRow];
		
		// We are about to clear all selections, so have to save current status
		bolSelected = objRow.selected;
		
		// Clear all selections from this table
		this.Unselect(elmRow.parentNode.parentNode.id);
		
		// Toggle the row
		if (bolSelected && (!objRow.Up))
		{
			// if the row is selected and down, unselect it
			elmRow.className = "Hover";
			objRow.selected = FALSE;
		}
		else
		{
			// otherwise select it
			elmRow.className = "Selected";
			objRow.selected = TRUE;
		}
	}
	
	this.LightsUp =function (elmRow)
	{
		// MouseOver on row, highlight the row
		elmRow.className = "Hover";
	}
	
	this.LightsDown =function (elmRow)
	{
		// MouseOut on row, remove highlight
		
		// get row number from elmRow
		intPos = elmRow.id.lastIndexOf('_');
		intRow = elmRow.id.substr(intPos + 1);
		
		if (Vixen.table[elmRow.parentNode.parentNode.id].row[intRow].selected)
		{
			// Row is selected
			elmRow.className = "Selected";
		}
		else
		{
			// Change the class back to even/odd
			if (elmRow.id.substr(elmRow.id.indexOf('_') + 1) % 2)
			{
				elmRow.className = "Even";
			}
			else
			{
				elmRow.className = "Odd";
			}
		}
	}
	
	this.Attach =function (strTableId, totalRows)
	{
		// Add behaviour to the table
		for (var i=0; i <=totalRows; i++)
		{
			var elmRow = document.getElementById(strTableId + '_' + i);
			elmRow.addEventListener('mousedown', MouseDownHandler, TRUE);
			elmRow.addEventListener('mouseover', MouseOverHandler, TRUE);
			elmRow.addEventListener('mouseout', MouseOutHandler, TRUE);
		}
	}
	
	function MouseDownHandler ()
	{
		// MouseDown on row, toggle row and propagate selection
		objTable = Vixen.table[this.parentNode.parentNode.id];
		
		// Propagate selection to linked tables
		if (objTable.linked)
		{
			// get row number from elmRow
			var intRow = this.id.lastIndexOf('_');
			intRow = this.id.substr(intRow + 1);
			
			// update table links
			Vixen.Highlight.UpdateLink(objTable.link, [objTable.row[intRow].index],[this.parentNode.parentNode.id]);
		}
		
		// Toggle row
		Vixen.Highlight.ToggleSelect(this);
	}
	
	function MouseOverHandler ()
	{
		// MouseOver on row, highlight it
		Vixen.Highlight.LightsUp(this);
	}

	function MouseOutHandler ()
	{
		// MouseOut on row, remove highlight
		Vixen.Highlight.LightsDown(this);
	}
	
	this.UpdateLink = function(arrTables, arrIndexes, arrSkipTables)
	{
		// Propagate selection from one table to next

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
			for (intTable in arrSkipTables)
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
			for (intRow in tblTarget.row)
			{
				objRow = tblTarget.row[intRow];
				
				// for each index
				for (intIndex in arrIndexes)
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
									for (intIndexEntry in arrIndexes[intIndex][strIndex])
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

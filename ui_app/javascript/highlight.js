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
		//debug (strTableId);
		for (var i=0; i <= Vixen.table[strTableId].totalRows; i++)
		{
			var elmRowUnselect = document.getElementById(strTableId + '_' + i);
			if (elmRowUnselect.id.substr(elmRowUnselect.id.indexOf('_') + 1) % 2)
			{
				elmRowUnselect.className = "Even";
			}
			else
			{
				elmRowUnselect.className = "Odd";
			}
			
			Vixen.table[strTableId].row[i].selected = FALSE;
			
		}
	}
	this.ToggleSelect =function (elmRow)
	{
		//debug (elmRow.id);
		// get row number from elmRow
		intRow = elmRow.id.lastIndexOf('_');
		intRow = elmRow.id.substr(intRow + 1);
		
		objRow = Vixen.table[elmRow.parentNode.parentNode.id].row[intRow];
		
		bolSelected = objRow.selected;
		
		this.Unselect(elmRow.parentNode.parentNode.id);
		
		if (bolSelected && (!objRow.Up))
		{
			elmRow.className = "Hover";
			objRow.selected = FALSE;
		}
		else
		{
			elmRow.className = "Selected";
			objRow.selected = TRUE;
		}
	}
	
	this.LightsUp =function (elmRow)
	{
		elmRow.className = "Hover";
	}
	
	this.LightsDown =function (elmRow)
	{
		// get row number from elmRow
		intRow = elmRow.id.lastIndexOf('_');
		intRow = elmRow.id.substr(intRow + 1);
		
		//debug (Vixen.table[elmRow.parentNode.parentNode.id].row[intRow]);
		
		if (Vixen.table[elmRow.parentNode.parentNode.id].row[intRow].selected)
		{
			//debug (elmRow.id);
			elmRow.className = "Selected";
		}
		else
		{
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
		objTable = Vixen.table[this.parentNode.parentNode.id];
		
		if (objTable.linked)
		{
			// get row number from elmRow
			var intRow = this.id.lastIndexOf('_');
			intRow = this.id.substr(intRow + 1);
			
			// update table links
			Vixen.Highlight.UpdateLink(objTable.link, [objTable.row[intRow].index],[this.parentNode.parentNode.id]);
		}
		Vixen.Highlight.ToggleSelect(this);
	}
	
	function MouseOverHandler ()
	{
		Vixen.Highlight.LightsUp(this);
	}

	function MouseOutHandler ()
	{
		Vixen.Highlight.LightsDown(this);
	}
	
	this.UpdateLink =function(arrTables, arrIndexes, arrSkipTables)
	{
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
		
		// for each linked table
		for (strTable in arrTables)
		{	
			tblTarget 	= Vixen.table[strTable];
		
			// check for skip table
			for (intTable in arrSkipTables)
			{
				if (arrSkipTables[intTable] == strTable)
				{
					bolSkip = TRUE;
					break;
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
									if (objRow.index[strIndex][intRowIndex] == arrIndexes[intIndex][strIndex])
									{
										// Highlight if index matches
										//.selected = TRUE lightsdown();
										//debug ('asfasdfsad');
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
Vixen.Highlight = new VixenHighlightClass();

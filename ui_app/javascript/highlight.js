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
			intRow = this.id.lastIndexOf('_');
			intRow = this.id.substr(intRow + 1);
			
			// call updatelink
			arrTables = [];
			arrIndexes = [];
			for (var objLink in objTable.link)
			{
				arrTables.push(objLink);
				
				for (var j=0; j<objTable.link[objLink].length; j++)
				{
					strKey = objTable.link[objLink][j];
					//debug (strKey);
					if (!objTable.row[intRow].index)
					{
						//debug ('no index');
					}
					else
					{
						//debug (objTable.row[intRow].index[strKey] + "aeeeeeeeee");
						for (var k=0; k<objTable.row[intRow].index[strKey].length; k++)
						{
							arrIndexes.push({'name' : strKey, 'value': objTable.row[intRow].index[strKey][k]});
						}
					}
				}
			}
			Vixen.Highlight.UpdateLink(arrTables,arrIndexes,[]);
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
		//debug (arrTables);
		for (var i=0; i<arrTables.length; i++)
		{
			strTargetId = arrTables[i];
			tblTarget = Vixen.table[strTargetId];
			
			// Unselect all on target (& collapse?)
			Vixen.Highlight.Unselect(strTargetId);
			Vixen.Slide.CollapseAll(strTargetId);
			
			for (var j=0; j<tblTarget.row.length; j++)
			{
				objRow = tblTarget.row[j];
				for (var k=0; k<arrIndexes.length; k++)
				{
					//debug (objRow.index[arrIndexes[k].name],1);
					if (!objRow.index)
					{
						//debug ('again, no index');
					}
					else
					{
						//debug (objRow.index[arrIndexes[k].name]);
						//debug (arrIndexes[k].value);
						//debug ('-----------');
						// .value is a value, but objrow.index[]is an array
						for (var l=0; l<objRow.index[arrIndexes[k].name].length; l++)
						{
							if (objRow.index[arrIndexes[k].name][l] == arrIndexes[k].value)
							{
								// Highlight if index matches
								//.selected = TRUE lightsdown();
								//debug ('asfasdfsad');
								strRowId = strTargetId + "_" + j;
								Vixen.table[strTargetId].row[j].selected = TRUE;
								Vixen.Highlight.LightsDown(document.getElementById(strRowId));
								
								// Add row indexes to arrTargetIndexes
							}
						}
					}
				}
				
			}
			// if we are linked to talkbes
			//	call update link (arrtargettables, arrtargetindeces, arrskiptables)
		}
	}
}	

// Create an instance of the Vixen highlight class
Vixen.Highlight = new VixenHighlightClass();

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
	this.tables = 
	{ 
		'Example': 
		{
			'totalRows': 10,
			'selected': 50
		},
	};
		
	this.ToggleSelect =function (elmRow)
	{
		for (i = 1; i <= this.tables[elmRow.parentNode.parentNode.id].totalRows; i++)
		{
			var elmRowUnselect = document.getElementById(elmRow.parentNode.parentNode.id + '_' + i);
			if (elmRowUnselect.id.substr(elmRowUnselect.id.indexOf('_') + 1) % 2)
			{
				elmRowUnselect.className = "Odd";
			}
			else
			{
				elmRowUnselect.className = "Even";
			}
		}
		if (elmRow.id == this.tables[elmRow.parentNode.parentNode.id].selected)
		{
			elmRow.className = "Hover";
			this.tables[elmRow.parentNode.parentNode.id].selected = "NULL";
		}
		else
		{
			elmRow.className = "Selected";
			this.tables[elmRow.parentNode.parentNode.id].selected = elmRow.id;
		}
	}
	
	this.LightsUp =function (elmRow)
	{
		elmRow.className = "Hover";
	}
	
	this.LightsDown =function (elmRow)
	{
		if (elmRow.id == this.tables[elmRow.parentNode.parentNode.id].selected)
		{
			elmRow.className = "Selected";
		}
		else
		{
			if (elmRow.id.substr(elmRow.id.indexOf('_') + 1) % 2)
			{
				elmRow.className = "Odd";
			}
			else
			{
				elmRow.className = "Even";
			}
		}
	}
	
	this.Attach =function (strTableId, totalRows)
	{
		this.tables[strTableId] = new Object();
		this.tables[strTableId] = { 'totalRows' : totalRows,
									'selected' : ''};
		for (i = 1; i <=totalRows; i++)
		{
			var elmRow = document.getElementById(strTableId + '_' + i);
			elmRow.addEventListener('mousedown', MouseDownHandler, TRUE);
			elmRow.addEventListener('mouseover', MouseOverHandler, TRUE);
			elmRow.addEventListener('mouseout', MouseOutHandler, TRUE);
		}

	}
	
	function MouseDownHandler ()
	{
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
}	

// Create an instance of the Vixen highlight class
Vixen.Highlight = new VixenHighlightClass();

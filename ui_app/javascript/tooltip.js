//----------------------------------------------------------------------------//
// VixenTooltipClass
//----------------------------------------------------------------------------//
/**
 * VixenTooltipClass
 *
 * Vixen tooltip class
 *
 * Vixen tooltip class
 *
 *
 *
 * @package	framework_ui
 * @class	Vixen.Tooltip
 */
function VixenTooltipClass()
{
	this.target = "";
	this.timer = [];
	
	this.Create = function(strRowId, evtHover)
	{
		// try to find a previous object
		elmExists = document.getElementById('VixenTooltip');
		if (elmExists)
		{
			// destroy it
			elmTooltip = elmExists;
		}
		else
		{
			return FALSE;
		}
		
		
		
		// assign to current object
		this.target = strRowId;
		
		strContent = document.getElementById(strRowId + 'DIV-TOOLTIP').innerHTML;
		// Set the content of the popup box		
		if (!strContent)
		{
			strContent = "No data<br />";
		}
		
		//elmPopup.style.visibility = 'visible';			
		elmTooltip.innerHTML = strContent;

		// Set the behaviour autohide
		document.addEventListener('mousedown', CloseHandler, TRUE);
	
		strSize = "small";
		// Set the size of the popup box
		switch (strSize)
		{
			case "small":
				{	//small
					elmTooltip.style.width = '200px';
					break;
				}
			case "medium":
				{	//medium
					elmTooltip.style.width = '450px';
					break;
				}
			case "large":
				{	//large
					elmTooltip.style.width = '700px';
					break;
				}
			default:
				{
					//default
					elmTooltip.style.width = '450px';
					break;
				}
		}
		
		// Set the position (centre/pointer/target)
		if (evtHover == "[object MouseEvent]")
		{
			// set the popup to the cursor
			elmTooltip.style.position = 'absolute';
			elmTooltip.style.left = evtHover.clientX + 25;
			elmTooltip.style.top = evtHover.clientY + 25;
			elmTooltip.style.display = 'block';
		}
		
		
		function CloseHandler(event)
		{
			//debug (event.target.id);
			if (event.target.id.indexOf('VixenTooltip') >= 0)
			{
				//debug (event.target.id.substr(18));
				// Top bar, looking to drag
			}			
			else
			{
				Vixen.Tooltip.Close();
				document.removeEventListener('mousedown', CloseHandler, TRUE);
			}
		}
	}
	
	this.Close = function()
	{
		var objClose = document.getElementById('VixenTooltip');
		if (objClose)
		{
			this.timer.push( window.setTimeout('document.getElementById("VixenTooltip").style.display = "none";', 500));
			this.timer.push( window.setTimeout('Vixen.Tooltip.target = "";', 500));
		}
	}

	this.Attach =function(strTableId, intTotalRows)
	{
		for (var i=0; i <=intTotalRows; i++)
		{
			var elmRow = document.getElementById(strTableId + '_' + i);
			elmRow.addEventListener('mouseover', MouseOverHandler, TRUE);
			elmRow.addEventListener('mouseout', MouseOutHandler, TRUE);
		}
	}
	
	function MouseOverHandler (evtHover)
	{
		if (Vixen.Tooltip.target != this.id)
		{
			Vixen.Tooltip.Create(this.id, evtHover);
		}
		else
		{
			// destroy previous destroyer
			for (var i=0; i<Vixen.Tooltip.timer.length; i++)
			{
				window.clearTimeout(Vixen.Tooltip.timer[i]);
			}
		}
	}
	function MouseOutHandler (evtHover)
	{
		if (evtHover.relatedTarget.id != 'VixenTooltip')
		{
			Vixen.Tooltip.Close();
		}
	}
}

// Create an instance of the Vixen menu class
Vixen.Tooltip = new VixenTooltipClass();

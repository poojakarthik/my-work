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
	this.bolExists = FALSE;
	
	this.Create = function(strRowId, evtHover)
	{
		if (this.bolExists)
		{
			// Grab tooltip
			elmTooltip = document.getElementById('VixenTooltip');
		}
		else
		{
			// Add event listeners only ONCE
			elmTooltip = document.getElementById('VixenTooltip');
			
			// Set the behaviour of the tooltip
			elmTooltip.addEventListener('mouseover', HoverHandler, TRUE);
			elmTooltip.addEventListener('mouseout', LeaveHandler, TRUE);
			document.addEventListener('mousedown', CloseHandler, TRUE);
			
			this.bolExists = TRUE;
		}
		
		// Used to identify which row the current tooltip is for
		this.target = strRowId;
		
		
		// Get the content of the tooltip
		strContent = document.getElementById(strRowId + 'DIV-TOOLTIP').innerHTML;
		if (!strContent)
		{
			strContent = "No data<br />";
		}
		
		// Set the content of the tooltip			
		elmTooltip.innerHTML = strContent;


		
		strSize = "medium";
		// Set the size of the tooltip (leftover from popup code)
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
		
		// Set the position (centre/pointer/target) (leftover from popup code)

		var objTarget = evtHover.target;
		//while ((objTarget = objTarget.parentNode).id.indexOf('T') != "[object HTMLTableRowElement]")
		while ((objTarget = objTarget.parentNode).id != strRowId)
		{
			// Find me that damn table!
			//if (objTarget == "[object HTMLTableElement]") {debug ("a: " + objTarget.id); break;}
		}
		
		arrPos = findPos(objTarget);
		
		// set the popup to beside the table
		elmTooltip.style.position = 'absolute';
		elmTooltip.style.display = 'block';
		elmTooltip.style.top = arrPos[1];
		elmTooltip.style.left = arrPos[0] - elmTooltip.clientWidth - 5;
		
		/*
		// set the popup to the cursor
		elmTooltip.style.position = 'absolute';
		elmTooltip.style.left = evtHover.clientX + 25;
		elmTooltip.style.top = evtHover.clientY + 25;
		elmTooltip.style.display = 'block';
		*/


		// www.quirksmode.org == awesome
		function findPos(obj)
		{
			var curleft = curtop = 0;
			if (obj.offsetParent) {
				curleft = obj.offsetLeft
				curtop = obj.offsetTop
				while (obj = obj.offsetParent) {
					curleft += obj.offsetLeft
					curtop += obj.offsetTop
				}
			}
			return [curleft,curtop];
		}
		
		function CloseHandler(evt)
		{
			var objTarget = evt.target;
			
			while (((objTarget = objTarget.parentNode).id.indexOf('Table') == -1) && (objTarget.id.indexOf('VixenTooltip') == -1))
			{
				// Find me that damn table!
				if (objTarget == "[object HTMLHtmlElement]") {break;}
			}
			
			if (objTarget.id.indexOf('Table') >= 0 || objTarget.id.indexOf('VixenTooltip') >= 0)
			{
				return;
			}
			else
			{
				// MouseDown on other element, close tooltip
				Vixen.Tooltip.Close();
			}
		}
		function HoverHandler(evt)
		{
			// destroy previous destroyer
			for (var i=0; i<Vixen.Tooltip.timer.length; i++)
			{
				window.clearTimeout(Vixen.Tooltip.timer[i]);
			}
		}
		
		function LeaveHandler(evt)
		{
			if (evtHover.relatedTarget.id != 'VixenTooltip')
			{
				// MouseOut on Tooltip, close tooltip
				Vixen.Tooltip.Close();
			}
		}
	}
	
	this.Close = function()
	{
		// Get the tooltip
		var objClose = document.getElementById('VixenTooltip');
		if (objClose)
		{
			// Close the tooltip
			this.timer.push( window.setTimeout('document.getElementById("VixenTooltip").style.display = "none";', 500));
			this.timer.push( window.setTimeout('Vixen.Tooltip.target = "";', 500));
		}
	}

	this.Attach =function(strTableId, intTotalRows)
	{
		for (var i=0; i <=intTotalRows; i++)
		{
			// Add some behaviour to the row
			var elmRow = document.getElementById(strTableId + '_' + i);
			elmRow.addEventListener('mouseover', MouseOverHandler, TRUE);
			elmRow.addEventListener('mouseout', MouseOutHandler, TRUE);
		}
	}
	
	function MouseOverHandler (evtHover)
	{
		if (Vixen.Tooltip.target != this.id)
		{
			// If the tooltip is not already on this row, create it on this row
			Vixen.Tooltip.Create(this.id, evtHover);
			// Stop any previous destroyers
			for (var i=0; i<Vixen.Tooltip.timer.length; i++)
			{
				window.clearTimeout(Vixen.Tooltip.timer[i]);
			}
		}
		else
		{
			// Stop any previous destroyers
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
			// MouseOut on row, close tooltip
			Vixen.Tooltip.Close();
		}
	}
}

// Create an instance of the Vixen menu class
Vixen.Tooltip = new VixenTooltipClass();

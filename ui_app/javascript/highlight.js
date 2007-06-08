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
	this.Highlight =function (target, table, maxRows)
	{
		//alert(table);
		var selected="";
		for (i = 0; i <= maxRows; i++)
		{
			var myElement = document.getElementById(table + i);
			if (myElement.className == "Selected")
			{
				selected = "myrow" + i;
				
			}			
			if (i % 2 == 0)
			{
				myElement.className = "Even";
			}
			else
			{
				myElement.className = "Odd";
			}
			
		}
		document.getElementById(target).className = "Hover";
		if (selected != "" )
		{
			document.getElementById(target).className = "Selected";
		}
	}
	
	this.ToggleSelection =function (target, table, maxRows)
	{
		if (document.getElementById(target).className == "Selected")
		{
			var alreadyselected = true;
		}
		for (i = 1; i <= maxRows; i++)
		{
			var myElement = document.getElementById(table + i);
			myElement.className = "";
		}
		if (!alreadyselected)
		{
			document.getElementById(target).className = "Selected";
		}
		highlight(target, table, maxRows);
	}
}	

// Create an instance of the Vixen highlight class
Vixen.Highlight = new VixenHighlightClass();

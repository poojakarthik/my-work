
Developer_Ticker	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(80);
		
		this.oTicker	= new Reflex.Control.Ticker('left', true);
		
		// Add messages
		this.oTicker.addMessage("Alert: Preselection through Optus will not work from 22/1/2010 to 25/1/2010");
		this.oTicker.addMessage("Remember to close the damn front door when you leave.  Were you bought in a goddamn tent?");
		
		var sContent	= "<div style='font-weight: bold;'>Attach Ticker to Element (using CSS Selector): </div><div><select><option value='child'>as Child of</option><option value='before'>before</option><option value='after'>after</option></select><input type='text' style='width: 50em;' /><button>Attach!</button></div>";
		
		this.setContent(sContent);
		this.addCloseButton();
		this.setTitle("Ticker");
		
		this.contentPane.select('button').first().observe('click', this.moveTicker.bind(this));
	},
	
	moveTicker	: function()
	{
		var sCSSSelector	= this.contentPane.select('input').first().value;
		
		// Valid CSS Selector?
		var oResult	= $$(sCSSSelector).first();
		var oElement;
		if (oResult.appendChild)
		{
			// Single Element
			oElement	= oResult;
		}
		else if (oResult.push)
		{
			// Array
			oElement	= oResult[0];
		}
		else
		{
			alert("Invalid CSS Selector");
			return false;
		}
		
		var oSelect	= this.contentPane.select('select').first();
		if (oSelect.selectedIndex > -1)
		{
			switch (oSelect[oSelect.selectedIndex].value.charAt(0).toLowerCase())
			{
				case 'b':
					// Before
					oElement.parentNode.insertBefore(this.oTicker.getElement(), oElement);
					break;
					
				case 'a':
					// After
					oElement.parentNode.insertBefore(this.oTicker.getElement(), oElement.nextSibling);
					break;
					
				case 'c':
				default:
					// Child
					oElement.appendChild(this.oTicker.getElement());
					break;
			}
		}
	}
});

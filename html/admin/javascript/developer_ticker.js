
Developer_Ticker	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(80);
		
		this.oTicker	= new Reflex.Control.Ticker('left', true);
		
		var sContent	= "<div style='font-weight: bold;'>Attach Ticker to Element (using CSS Selector): </div><div><select><option value='child'>as Child of</option><option value='before'>before</option><option value='after'>after</option></select><input type='text' style='width: 50em;' /><button>Attach!</button></div>";
		
		this.setContent(sContent);
		this.addCloseButton();
		this.setTitle("Ticker");
		
		this.contentPane.$$('button').observe('click', this.moveTicker.bind(this));
	},
	
	moveTicker	: function()
	{
		var sCSSSelector	= this.contentPane.$$('input').value;
		
		// Valid CSS Selector?
		var oResult	= $$(sCSSSelector);
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
		
		var oSelect	= this.contentPane.$$('select');
		switch (oSelect[oSelect.selectedIndex].charAt(0).toLowerCase())
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
});

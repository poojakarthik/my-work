	
	window.addEventListener (
		"load",
		function ()
		{
			var tabIndex = 0;
			
			for (var i=0; i < document.forms.length; ++i)
			{
				form = document.forms [i];
				
				for (var j=0; j < form.elements.length; ++j)
				{
					
					element = form.elements [j];
					element.tabIndex = ++tabIndex;
				}
			}
			
			if (document.forms[0])
			{
				if (document.forms[0].elements[0])
				{
					document.forms[0].elements[0].focus ();
				}
			}
		},
		true
	);
	
	function openPopup (address, width, height)
	{
		if (width == undefined)
		{
			width = 800;
		}
		
		if (height == undefined)
		{
			height = 400;
		}
		
		window.open (
			address,
			"",
			"width=" + width + ", " +
			"height=" + height + ", " +
			"scrollbars=yes, " +
			"resize=yes, " +
			"channelmode=no, " +
			"directories=no, " +
			"location=no, " +
			"menubar=no, " +
			"titlebar=no "
		);
		
		return false;
	}

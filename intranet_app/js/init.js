	
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
		},
		true
	);
	

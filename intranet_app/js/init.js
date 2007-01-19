	
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
	
	function ModalExternal (element, address)
	{
		$('#Modal-Popup-Content').load (
			address, 
			{}, 
			function ()
			{
				ModalDisplay ('#modalContent-Popup');
			}
		);
				
		$('#Modal-Popup-Title').empty ().append (element.getAttribute ('title'));
		$('#Modal-Popup-Summary').empty ().append (element.getAttribute ('alt'));
		
		return false;
	}
	
	function ModalDisplay (object)
	{
		$(object).modalContent (
			null, 
			'slideDown', 
			600
		);
		
		return false;
	}
	
	function BugSubmit (form)
	{
		var PageDetails = document.createElement ("INPUT");
		PageDetails.setAttribute ("type", "hidden");
		PageDetails.setAttribute ("name", "PageDetails");
		PageDetails.setAttribute ("value", document.getElementsByTagName ("HTML").item (0).innerHTML);
		form.appendChild (PageDetails);
		
		return true;
	}
	

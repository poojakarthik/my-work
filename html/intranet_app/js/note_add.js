	
	// Usage:
	// <script language="javascript" src="js/note_add.js"></script>
	// <form onsubmit="return noteAdd (this)">
	
	function noteAdd (form)
	{
		if (form.elements ["Note"].value.replace (/\s/g, "") == "")
		{
//			alert ("You cannot enter a Blank Note into the system.");
			return false;
		}
		
		return true;
	}
	
	function noteType (elm)
	{
		var allowSubmit = (elm.value.replace (/^\s*|\s*$/g,"") != "");
		for (j=0; j < elm.form.elements.length; ++j)
		{
			element = elm.form.elements [j];
			
			if (element.type == "submit")
			{
				element.className = ((allowSubmit) ? "input-submit" : "input-submit-disabled");
				element.disabled = !allowSubmit;
			}
		}
	}
	
	window.addEventListener (
		"load",
		function ()
		{
			for (i=0; i < document.forms.length; ++i)
			{
				var form = document.forms [i];
				
				if (form.name == "NoteAdd")
				{
					form.elements ["Note"].addEventListener (
						"keyup",
						function ()
						{
							noteType (this);
						},
						true
					);
					
					noteType (form.elements ["Note"])
				}
			}
		},
		true
	);

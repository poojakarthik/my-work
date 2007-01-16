	
	// Usage:
	// <script language="javascript" src="js/note_add.js"></script>
	// <form onsubmit="return noteAdd (this)">
	
	function noteAdd (form)
	{
		if (form.elements ["Note"].value.replace (/\s/g, "") == "")
		{
			alert ("You cannot enter a Blank Note into the system.");
			return false;
		}
		
		return true;
	}
	

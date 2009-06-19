var Control_Tab	= Class.create
({
	initialize	: function(strName, objContentDIV, strIconURL)
	{
		this.strName	= strName;
		
		if (objContentDIV)
		{
			this.setContent(objContentDIV);
		}
		
		if (strIconURL)
		{
			this.setIcon(strIconURL);
		}
	},
	
	getName	: function()
	{
		return this.strName;
	},
	
	getContent	: function()
	{
		return this.objContentDIV;
	},
	
	setContent	: function(objContentDIV)
	{
		this.objContentDIV	= objContentDIV;
	},
	
	setIcon	: function(strIconURL)
	{
		this.strIconURL	= strIconURL;
	},
	
	getIcon	: function(strIconURL)
	{
		return this.strIconURL;
	}
})
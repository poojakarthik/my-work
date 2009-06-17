var Control_Tab	= Class.create
({
	initialize	: function(strName, objContentDIV)
	{
		this.strName	= strName;
		this.setContent(objContentDIV);
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
	}
})
var Control_Tab	= Class.create
({
	initialize	: function(strName, objContentDIV)
	{
		this.setName(strName);
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
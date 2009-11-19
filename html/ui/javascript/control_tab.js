var Control_Tab	= Class.create
({
	initialize	: function(strName, objContentDIV, strIconURL)
	{
		this.oControlTabGroup	= null;
		
		this.oTabPage				= document.createElement('div');
		this.oTabPage.className		= 'tab-page';
		this.oTabPage.hide();
		
		this.oTabButton				= document.createElement('div');
		this.oTabButton.className	= 'tab';
		
		this.setName(strName);
		this.setIcon(strIconURL);
		this.setContent(objContentDIV);
	},
	
	setName	: function(strName)
	{
		this.strName	= strName;
		this._paintTabButton();
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
		this.oTabPage.removeChild(this.objContentDIV);
		this.objContentDIV	= objContentDIV ? objContentDIV : null;
		
		if (this.objContentDIV)
		{
			this.oTabPage.appendChild(this.objContentDIV);
		}
	},
	
	setIcon	: function(strIconURL)
	{
		this.strIconURL	= strIconURL ? strIconURL : null;
		this._paintTabButton();
	},
	
	getIcon	: function(strIconURL)
	{
		return this.strIconURL;
	},
	
	_paintTabButton	: function()
	{
		// Update Tab Button
		var strTabButtonHTML	= "<span>" + this.getName().replace(/&/gmi, '&amp;').replace(/"/gmi, '&quot;').replace(/>/gmi, '&gt;').replace(/</gmi, '&lt;') + "</span>";
		if (this.getIcon())
		{
			strTabButtonHTML	= "<img class='icon' alt='' title='" + this.getName() + "' src='" + this.getIcon() + "' />" + strTabButtonHTML;
		}
		this.oTabButton.innerHTML	= strTabButtonHTML;
	},
	
	setParentGroup	: function(oControlTabGroup)
	{
		this.oControlTabGroup	= oControlTabGroup ? oControlTabGroup : null;
		
		if (this.oControlTabGroup)
		{
			this.oTabButton.addEventListener('click', this.oControlTabGroup.switchToTab.bind(this.oControlTabGroup, strAlias), false);
		}
	}
});
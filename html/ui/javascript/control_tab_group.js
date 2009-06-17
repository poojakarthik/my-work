var Control_Tab_Group	= Class.create
({
	initialize	: function(objDIVContainer)
	{
		this._arrTabs		= [];
		
		// Container
		this.objContainer						= {};
		this.objContainer.domElement			= objDIVContainer;
		this.objContainer.domElement.innerHTML	= '';
		
		// Tab Row
		this.objContainer.objTabRow							= {};
		this.objContainer.objTabRow.domElement				= document.createElement('div');
		this.objContainer.objTabRow.domElement.className	= '';
		this.objContainer.domElement.appendChild(this.objContainer.objTabRow.domElement);
		
		// Page Container
		this.objContainer.objPageContainer						= {};
		this.objContainer.objPageContainer.domElement			= document.createElement('div');
		this.objContainer.objPageContainer.domElement.className	= '';
		this.objContainer.domElement.appendChild(this.objContainer.objPageContainer.domElement);
	},
	
	addTab		: function(strAlias, objControlTab)
	{
		// Check the Alias is valid and unique
		if (!strAlias.match("/^[a-z\_][\w]*$/i"))
		{
			throw "Tab alias '" + strAlias + "' is invalid!";
		}
		if (this.tabExists(strAlias))
		{
			throw "A tab with the alias '" + strAlias + "' already exists!";
		}
		
		// Add the Tab to this Group
		var objPage				= document.createElement('div');
		objPage.className		= 'page';
		objPage.style.display	= 'none';
		this.objContainer.objPageContainer.domElement.appendChild(objPage);
		
		var domTabButton		= document.createElement('div');
		domTabButton.className	= 'tab';
		domTabButton.innerHTML	= objControlTab.getName().replace("/&/gmi", '&amp;').replace('/"/gmi', '&quot;').replace("/>/gmi", '&gt;').replace("/</gmi", '&lt;');
		
		this._arrTabs.push({strAlias: strAlias, domTabButton: domTabButton, objPage: objTabPage, objControlTab: objControlTab});
		
		// If this is the first tab, then select it
		this.switchToTab(strAlias);
	},
	
	switchToTab	: function(mixTab)
	{
		var objControlTab	= this.getTab(mixTab);
		if (objControlTab)
		{
			// Set Visibility
			for (var i = 0; i < this._arrTabs.length; i++)
			{
				if (this._arrTabs[i].objControlTab == objControlTab)
				{
					// Selected Tab
					this._arrTabs[i].objPage.style.display	= 'block';
				}
				else
				{
					// Non-selected Tab
					this._arrTabs[i].objPage.style.display	= 'none';
				}
			}
		}
	},
	
	getTab	: function(mixTab)
	{
		if (mixTab instanceof Control_Tab)
		{
			// Control_Tab object
			return mixTab;
		}
		
		// Is mixTab an alias?
		for (var i = 0; i < this._arrTabs.length; i++)
		{
			if (this._arrTabs[i].strAlias == mixTab)
			{
				// Yes -- return the Control_Tab object
				return this._arrTabs[i].objControlTab;
			}
		}
		
		// Is mixTab an Index?
		var intTabIndex	= Number(mixTab);
		if (intTabIndex >= 0 && intTabIndex < this._arrTabs.length)
		{
			// Yes -- return the Control_Tab object
			return this._arrTabs[intTabIndex].objControlTab;
		}
		
		alert("Unable to find tab '" + mixTab + "'");
		return false;
	}
})
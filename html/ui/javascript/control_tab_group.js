/**
 *	Control_Tab_Group
 */
var Control_Tab_Group	= Class.create
({
	/**
	 *	initialize()
	 *
	 *	Constructor
	 *
	 *	@param	DOMNode	objDIVContainer				DIV that will be used at the Tab Group container
	 *	@param	boolean	bolEmbedded					TRUE	: Embedded in page
	 *												FALSE	: Sole element in a Popup
	 */
	initialize	: function(objDIVContainer, bolEmbedded)
	{
		// Parameter defaults 
		bolEmbedded			= (bolEmbedded || bolEmbedded == undefined || bolEmbedded == null) ? true : false;
		this.oSelectedTab	= null;
		
		this._oTabs			= {};
		this._aTabOrder		= [];
		
		// Container
		this.objContainer						= {};
		this.objContainer.domElement			= objDIVContainer;
		this.objContainer.domElement.className	= 'tab-group' + (bolEmbedded ? ' embedded' : '');
		this.objContainer.domElement.innerHTML	= '';
		
		// Tab Row
		this.objContainer.objTabRow							= {};
		this.objContainer.objTabRow.domElement				= document.createElement('div');
		this.objContainer.objTabRow.domElement.className	= 'tab-row';
		this.objContainer.domElement.appendChild(this.objContainer.objTabRow.domElement);
		
		// Page Container
		this.objContainer.objPageContainer						= {};
		this.objContainer.objPageContainer.domElement			= document.createElement('div');
		this.objContainer.objPageContainer.domElement.className	= 'tab-page-container';
		this.objContainer.domElement.appendChild(this.objContainer.objPageContainer.domElement);
	},
	
	addTab		: function(strAlias, objControlTab)
	{
		var bolFirstTab	= (this._aTabOrder.length == 0) ? true : false;
		
		// Check the Alias is valid and unique
		if (strAlias.search(/^[a-z\_][\w]*$/i) == -1)
		{
			throw "Tab alias '" + strAlias + "' is invalid!";
		}
		if (this.getTab(strAlias))
		{
			throw "A tab with the alias '" + strAlias + "' already exists!";
		}
		
		// Add the Tab to this Group
		objControlTab.setParentGroup(this);
		
		// Add to list of tabs
		this._oTabs[strAlias]	= objControlTab;
		this._aTabOrder.push(strAlias);
		
		// Render
		this._paint();
	},
	
	removeTab	: function(mixTab)
	{
		var objControlTab	= this.getTab(mixTab);
		if (objControlTab)
		{
			objControlTab.setParentGroup(null);
			
			for (sAlias in this._oTabs)
			{
				if (this._oTabs[sAlias] === objControlTab)
				{
					delete this._oTabs[sAlias];
					this._aTabOrder.splice(this._aTabOrder.indexOf(sAlias), 1);
					break;
				}
			}
			
			this._paint();
		}
	},
	
	_paint	: function()
	{
		// Purge existing Tabs
		this.objContainer.objTabRow.domElement.childElements().each(this.objContainer.objTabRow.domElement.removeChild, this.objContainer.objTabRow.domElement);
		this.objContainer.objPageContainer.domElement.childElements().each(this.objContainer.objPageContainer.domElement.removeChild, this.objContainer.objPageContainer.domElement);
		
		// Render Tabs
		for (var i = 0; i < this._aTabOrder.length; i++)
		{
			this.objContainer.objPageContainer.domElement.appendChild(this._oTabs[this._aTabOrder[i]].getPage());
			this.objContainer.objTabRow.domElement.appendChild(this._oTabs[this._aTabOrder[i]].getButton());
		}
		
		// Switch to the last selected tab (or first tab available)
		if (!this.switchToTab(this.oSelectedTab))
		{
			this.switchToTab(this._aTabOrder.first());
		}
	},
	
	switchToTab	: function(mixTab)
	{
		var bSwitched		= false;
		var objControlTab	= this.getTab(mixTab);
		if (objControlTab)
		{
			// Set Visibility
			for (var i = 0; i < this._aTabOrder.length; i++)
			{
				if (this._oTabs[this._aTabOrder[i]] == objControlTab)
				{
					// Selected Tab
					objControlTab.getPage().show();
					objControlTab.getButton().addClassName('selected');
					
					this.oSelectedTab	= objControlTab;
					bSwitched			= true;
				}
				else
				{
					// Non-selected Tab
					this._oTabs[this._aTabOrder[i]].getPage().hide();
					this._oTabs[this._aTabOrder[i]].getButton().removeClassName('selected');
				}
			}
		}
		
		return bSwitched;
	},
	/*
	setTabEnabled	: function(mTab, bEnabled)
	{
		// Ensure that the Tab is visible and selectable
		var objControlTab	= this.getTab(mixTab);
		if (objControlTab)
		{
			if (bEnabled)
			{
				objControlTab.getButton().show();
			}
			else
			{
				objControlTab.getButton().hide();
				
				// Make sure we switch away from this tab as it's no longer selectable
				if (this.oSelectedTab === objControlTab)
				{
					this.switchToTab(0);
				}
			}
		}
	},
	
	isTabEnabled	: function(mTab)
	{
		// Ensure that the Tab is invisible and not selectable
		var objControlTab	= this.getTab(mixTab);
		return (objControlTab && objControlTab.getButton().visible()) ? true : false;
	},
	*/
	getTab	: function(mixTab)
	{
		if (mixTab instanceof Control_Tab)
		{
			// Control_Tab object
			for (var i = 0; i < this._aTabOrder.length; i++)
			{
				if (this._oTabs[this._aTabOrder[i]] == mixTab)
				{
					// Yes -- return the Control_Tab object
					return this._oTabs[this._aTabOrder[i]];
				}
			}
		}
		
		// Is mixTab an alias?
		for (var i = 0; i < this._aTabOrder.length; i++)
		{
			if (this._aTabOrder[i] == mixTab)
			{
				// Yes -- return the Control_Tab object
				return this._oTabs[this._aTabOrder[i]];
			}
		}
		
		// Is mixTab an Index?
		var intTabIndex	= Number(mixTab);
		if (intTabIndex >= 0 && intTabIndex < this._aTabOrder.length)
		{
			// Yes -- return the Control_Tab object
			return this._oTabs[this._aTabOrder[intTabIndex]];
		}
		
		//alert("Unable to find tab '" + mixTab + "'");
		return false;
	}
});
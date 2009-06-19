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
	initialize	: function(objDIVContainer, bolEmbedded, bolFadeFX)
	{
		// Parameter defaults 
		bolEmbedded		= (bolEmbedded == undefined || bolEmbedded == null) ? true : false;
		//this.bolFadeFX	= (bolFadeFX) ? true : false;
		this.bolFadeFX	= false;
		
		this._arrTabs		= [];
		
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
		
		// Tab Row Clearing
		this.objContainer.objTabRow.objClearing							= {};
		this.objContainer.objTabRow.objClearing.domElement				= document.createElement('div');
		this.objContainer.objTabRow.objClearing.domElement.className	= 'clearing';
		this.objContainer.objTabRow.domElement.appendChild(this.objContainer.objTabRow.objClearing.domElement);
		
		// Page Container
		this.objContainer.objPageContainer						= {};
		this.objContainer.objPageContainer.domElement			= document.createElement('div');
		this.objContainer.objPageContainer.domElement.className	= 'tab-page-container';
		this.objContainer.domElement.appendChild(this.objContainer.objPageContainer.domElement);
	},
	
	addTab		: function(strAlias, objControlTab)
	{
		var bolFirstTab	= (this._arrTabs.length == 0) ? true : false;
		
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
		var objPage				= document.createElement('div');
		objPage.style.opacity	= 0;
		objPage.className		= 'tab-page';
		objPage.appendChild(objControlTab.getContent());
		this.objContainer.objPageContainer.domElement.appendChild(objPage);
		
		var strTabButtonHTML	= "<span>" + objControlTab.getName().replace(/&/gmi, '&amp;').replace(/"/gmi, '&quot;').replace(/>/gmi, '&gt;').replace(/</gmi, '&lt;') + "</span>";
		if (objControlTab.getIcon())
		{
			strTabButtonHTML	= "<img class='icon' alt='' title='" + objControlTab.getName() + "' src='" + objControlTab.getIcon() + "' />" + strTabButtonHTML;
		}
		
		var domTabButton		= document.createElement('div');
		domTabButton.className	= 'tab';
		domTabButton.innerHTML	= strTabButtonHTML;
		domTabButton.addEventListener('click', this.switchToTab.bind(this, strAlias), false);
		
		this.objContainer.objTabRow.domElement.insertBefore(domTabButton, this.objContainer.objTabRow.objClearing.domElement);
		
		var objTab	= {strAlias: strAlias, domTabButton: domTabButton, objPage: objPage, objControlTab: objControlTab};
		
		// Fade FX
		if (this.bolFadeFX)
		{
			objTab.objFXFade	= new FX_Fade(this.setPageOpacity.bind(this, strAlias), bolFirstTab, 10, 1);
		}
		
		// Add to list of tabs
		this._arrTabs.push(objTab);
		
		// If this is the first tab, then select it
		if (bolFirstTab)
		{
			this.switchToTab(strAlias);
		}
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
					if (this.bolFadeFX)
					{
						this._arrTabs[i].objFXFade.show();
					}
					else
					{
						this._arrTabs[i].objPage.style.opacity	= 1;
					}
					this._arrTabs[i].domTabButton.className	= 'tab selected';
				}
				else
				{
					// Non-selected Tab
					if (this.bolFadeFX)
					{
						this._arrTabs[i].objFXFade.hide();
					}
					else
					{
						this._arrTabs[i].objPage.style.opacity	= 0;
					}
					this._arrTabs[i].domTabButton.className	= 'tab';
				}
			}
		}
	},
	
	getTab	: function(mixTab, bolAsControlTab)
	{
		bolAsControlTab	= (bolAsControlTab == undefined || bolAsControlTab == null) ? true : false;
		
		if (mixTab instanceof Control_Tab)
		{
			// Control_Tab object
			for (var i = 0; i < this._arrTabs.length; i++)
			{
				if (this._arrTabs[i].objControlTab == objControlTab)
				{
					// Yes -- return the Control_Tab object
					return (bolAsControlTab) ? this._arrTabs[i].objControlTab : this._arrTabs[i];
				}
			}
		}
		
		// Is mixTab an alias?
		for (var i = 0; i < this._arrTabs.length; i++)
		{
			if (this._arrTabs[i].strAlias == mixTab)
			{
				// Yes -- return the Control_Tab object
				return (bolAsControlTab) ? this._arrTabs[i].objControlTab : this._arrTabs[i];
			}
		}
		
		// Is mixTab an Index?
		var intTabIndex	= Number(mixTab);
		if (intTabIndex >= 0 && intTabIndex < this._arrTabs.length)
		{
			// Yes -- return the Control_Tab object
			return (bolAsControlTab) ? this._arrTabs[intTabIndex].objControlTab : this._arrTabs[intTabIndex];
		}
		
		//alert("Unable to find tab '" + mixTab + "'");
		return false;
	},
	
	setPageOpacity	: function(strAlias, fltOpacity)
	{
		var objTab	= this.getTab(strAlias, false);
		if (objTab)
		{
			objTab.objPage.style.opacity	= fltOpacity;
		}
	}
});
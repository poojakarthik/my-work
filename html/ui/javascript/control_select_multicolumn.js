var Control_Select_Multicolumn	= Class.create
({
	initialize	: function(bSelectMode)
	{
		// DOM Elements
		this.oTable				= {};
		this.oTable.domElement	= document.createElement('table');
		this.oTable.domElement.addClassName('select');
		
		// Properties
		this.aOptions		= [];
		this.iSelectedIndex	= null;
		this.oColumns		= {};
	},
	
	getElement	: function()
	{
		return this.oTable.domElement;
	},
	
	add	: function(oOption)
	{
		// Validate parameters
		if (!(oOption instanceof Control_Select_Multicolumn_Option))
		{
			throw "oOption is not a 'Control_Select_Multicolumn_Option' object!";
		}
		else
		{
			// Add to array and return the index
			return this.aOptions.push(oOption) - 1;
		}
	},
	
	remove	: function(oOption)
	{
		// Validate parameters
		var iIndex	= null;
		if (!(oOption instanceof Control_Select_Multicolumn_Option))
		{
			throw "oOption is not a 'Control_Select_Multicolumn_Option' object!";
		}
		else if ((iIndex = this.aOptions.indexOf(oOption)) > -1)
		{
			if (iIndex === this.iSelectedIndex)
			{
				this.iSelectedIndex	= null;
			}
			
			this.aOptions.splice(iIndex, 1);
			return this.aOptions.length;
		}
		else
		{
			return false;
		}
	},
	
	getSelectedOption	: function()
	{
		if (this.iSelectedIndex === null)
		{
			return null;
		}
		else
		{
			return this.aOptions[this.iSelectedIndex];
		}
	},
	
	setColumns	: function(oDefinition)
	{
		this.oColumns	= {};
		for (sColumnAlias in oDefinition)
		{
			this.oColumns[sColumnAlias]	= {};
			this.oColumns[sColumnAlias].sWidth	= (oDefinition[sColumnAlias].sWidth) ? oDefinition[sColumnAlias].sWidth : 
		}
		this._render();
	},
	
	_render	: function()
	{
		// Render the Children
		for (i in this.oColumns)
		{
			this.oColumns[i].render();
		}
	}
});

// Static Methods
Control_Select_Multicolumn.factory	= function()
{
	return new Control_Select_Multicolumn();
};

// Class Constants
Control_Select_Multicolumn.SELECT_MODE_SINGLE	= false;
Control_Select_Multicolumn.SELECT_MODE_MULTIPLE	= true;
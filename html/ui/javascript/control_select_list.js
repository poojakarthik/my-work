var Control_Select_List	= Class.create
({
	initialize	: function(bSelectMode)
	{
		// DOM Elements
		this.oContainer				= {};
		this.oContainer.domElement	= document.createElement('div');
		this.oContainer.domElement.addClassName('select-list');
		
		this.oContainer.oTable				= {};
		this.oContainer.oTable.domElement	= document.createElement('table');
		this.oContainer.oTable.domElement.addClassName('select-list');
		this.oContainer.domElement.appendChild(this.oContainer.oTable.domElement);
		
		// Properties
		this.aOptions		= [];
		this.iSelectedIndex	= null;
		this.oColumns		= {};
	},
	
	getElement	: function()
	{
		return this.oContainer.domElement;
	},
	
	getTable	: function()
	{
		return this.oContainer.oTable.domElement;
	},
	
	add	: function(oOption)
	{
		// Validate parameters
		if (!(oOption instanceof Control_Select_List_Option))
		{
			throw "oOption is not a 'Control_Select_List_Option' object!";
		}
		else
		{
			// Add to array and return the index
			oOption.attachTo(this);
			var iIndex	= this.aOptions.push(oOption) - 1;
			this.render();
			return iIndex;
		}
	},
	
	remove	: function(oOption)
	{
		// Validate parameters
		var iIndex	= null;
		if (!(oOption instanceof Control_Select_List_Option))
		{
			throw "oOption is not a 'Control_Select_List_Option' object!";
		}
		else if ((iIndex = this.aOptions.indexOf(oOption)) > -1)
		{
			if (iIndex === this.iSelectedIndex)
			{
				this.iSelectedIndex	= null;
			}
			
			this.aOptions[iIndex].detach();
			this.aOptions.splice(iIndex, 1);
			this.render();
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
		this.oColumns	= oDefinition;
		/* TODO: Only add data we care about
		this.oColumns	= {};
		for (sColumnAlias in oDefinition)
		{
			this.oColumns[sColumnAlias]	= {};
		}*/
		this.render();
	},
	
	render	: function()
	{
		// Render the Children
		for (var i = 0; i < this.aOptions.length; i++)
		{
			this.aOptions[i].render(this.oColumns);
		}
	}
});

// Static Methods
Control_Select_List.factory	= function()
{
	return new Control_Select_List();
};

// Class Constants
Control_Select_List.SELECT_MODE_SINGLE	= false;
Control_Select_List.SELECT_MODE_MULTIPLE	= true;

Control_Select_List.COLUMN_LABEL	= 'label';
Control_Select_List.COLUMN_VALUE	= 'value';
Control_Select_List.COLUMN_SEND		= 'send';
Control_Select_List.COLUMN_EXPAND	= 'expand';

Control_Select_List.COLUMN_TYPE_TEXT	= 'text';
Control_Select_List.COLUMN_TYPE_SEND	= 'send';
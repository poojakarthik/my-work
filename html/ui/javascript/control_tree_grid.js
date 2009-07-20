var Control_Tree_Grid	= Class.create
({
	initialize	: function(bSelectMode)
	{
		// DOM Elements
		this.oContainer				= {};
		this.oContainer.domElement	= document.createElement('div');
		this.oContainer.domElement.addClassName('tree-grid');
		
		this.oContainer.oTable				= {};
		this.oContainer.oTable.domElement	= document.createElement('table');
		this.oContainer.oTable.domElement.addClassName('tree-grid');
		this.oContainer.domElement.appendChild(this.oContainer.oTable.domElement);
		
		// Properties
		this.oRootNode	= new Control_Tree_Grid_Node_Root();
		this.oRootNode.attachTo(this);
		
		this.oColumns	= {};
		
		this.oDataTypes	= {};
	},
	
	getElement	: function()
	{
		return this.oContainer.domElement;
	},
	
	getTable	: function()
	{
		return this.oContainer.oTable.domElement;
	},
	
	// Wrap around Root Node::appendChild()
	appendChild	: function(oTreeGridNode)
	{
		this.oRootNode.appendChild(oTreeGridNode);
	},
	
	// Wrap around Root Node::removeChild()
	removeChild	: function(oTreeGridNode)
	{
		this.oRootNode.removeChild(oTreeGridNode);
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
	
	addDataType	: function(sName, sDescription, sIconSource, fOnSelectCallback)
	{
		this.oDataTypes[sName]						= {};
		this.oDataTypes[sName].sName				= sName;
		this.oDataTypes[sName].sDescription			= sDescription ? sDescription : sName;
		this.oDataTypes[sName].sIconSource			= sIconSource ? sIconSource : null;
		this.oDataTypes[sName].fOnSelectCallback	= fOnSelectCallback ? fOnSelectCallback : null;
	},
	
	removeDataType	: function(sName)
	{
		if (this.oDataTypes[sName])
		{
			delete this.oDataTypes[sName];
		}
	},
	
	render	: function()
	{
		//alert('Rendering Tree...');
		
		// Render Column Titles
		// TODO
		
		// Render the Children with the Visible Columns
		//alert("Rendering Tree Grid");
		var oVisibleColumns	= Object.clone(this.oColumns);
		if (oVisibleColumns[Control_Tree_Grid.COLUMN_CHECK] && !oVisibleColumns[Control_Tree_Grid.COLUMN_CHECK].bShowWhenReadOnly)
		{
			delete oVisibleColumns[Control_Tree_Grid.COLUMN_CHECK];
		}
		this.oRootNode.render(oVisibleColumns);
	},
	
	setEditable	: function(bEditable)
	{
		this._bEditable	= bEditable ? true : false;
		this.render();
	},
	
	isEditable	: function()
	{
		return this._bEditable;
	}
});

// Static Methods
Control_Tree_Grid.factory	= function()
{
	return new Control_Tree_Grid();
};

// Class Constants
Control_Tree_Grid.SELECT_MODE_SINGLE	= false;
Control_Tree_Grid.SELECT_MODE_MULTIPLE	= true;

Control_Tree_Grid.COLUMN_LABEL	= 'label';
Control_Tree_Grid.COLUMN_VALUE	= 'value';
Control_Tree_Grid.COLUMN_CHECK	= 'check';
Control_Tree_Grid.COLUMN_EXPAND	= 'expand';
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
	
	render	: function()
	{
		alert('Rendering Tree...');
		
		// Render Column Titles
		// TODO
		
		// Render the Children with the Visible Columns
		//alert("Rendering Tree Grid");
		this.oRootNode.render(this.oColumns);
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
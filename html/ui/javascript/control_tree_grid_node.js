var Control_Tree_Grid_Node	= Class.create(
{
	initialize	: function()
	{
		// DOM Elements
		this._oElement				= {}
		
		// Properties
		this._aChildren			= [];
		this._oParent			= null;
		this._oVisibleColumns	= null;
	},
	
	getElement	: function()
	{
		return this._oElement.domElement;
	},
	
	getLastChild	: function()
	{
		return this._aChildren[this._aChildren.length-1];
	},
	
	getChildAfter	: function(oChild)
	{
		var iIndex	= this._aChildren.indexOf(oChild);
		if (iIndex > -1)
		{
			return (this._aChildren[iIndex + 1]) ? this._aChildren[iIndex + 1] : null;
		}
		else
		{
			return null;
		}
	},
	
	getChildBefore	: function(oChild)
	{
		var iIndex	= this._aChildren.indexOf(oChild);
		if (iIndex > 0)
		{
			return this._aChildren[iIndex - 1];
		}
		else
		{
			return null;
		}
	},
	
	appendChild	: function(oTreeGridNode)
	{
		if (oTreeGridNode instanceof Control_Tree_Grid_Node_Data)
		{
			// Attach Child
			oTreeGridNode.attachTo(this);
			
			// Add to Children array
			this._aChildren.push(oTreeGridNode);
			
			// Render
			this.render();
		}
		else
		{
			throw "oTreeGridNode is not a Data node!";
		}
	},
	
	removeChild	: function(oTreeGridNode)
	{
		if (oTreeGridNode instanceof Control_Tree_Grid_Node_Data)
		{
			// Detach Child
			oTreeGridNode.detach();
			
			// Remove from Children array
			var iIndex	= this._aChildren.indexOf(oChild);
			if (iIndex > -1)
			{
				this._aChildren.splice(iIndex, 1);
			}
			
			// Render
			this.render();
		}
		else
		{
			throw "oTreeGridNode is not a Data node!";
		}
	},
	
	getParent	: function()
	{
		return this._oParent;
	},
	
	getTreeGrid	: function()
	{
		throw "Virtual Method Control_Tree_Grid_Node::getTreeGrid() cannot be invoked directly!";
	},
	
	attachTo	: function(oTreeGridElement)
	{
		throw "Virtual Method Control_Tree_Grid_Node::attachTo() cannot be invoked directly!";
	},
	
	detach	: function()
	{
		if (this._oParent)
		{
			this.getTreeGrid().getTable().removeChild(this._oElement.domElement);
			this._oParent	= null;
		}
	},
	
	isExpanded	: function()
	{
		throw "Virtual Method Control_Tree_Grid_Node::isExpanded() cannot be invoked directly!";
	},
	
	isVisible	: function()
	{
		throw "Virtual Method Control_Tree_Grid_Node::isVisible() cannot be invoked directly!";
	},
	
	render	: function(oVisibleColumns, bForceRender)
	{
		// Set the internal cache of visible columns
		this._oVisibleColumns	= oVisibleColumns;
		
		// Render the Children
		for (var i = 0; i < this._aChildren.length; i++)
		{
			//alert("Rendering Child "+i);
			this._aChildren[i].render(this._oVisibleColumns);
		}
	}
});
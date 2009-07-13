var Control_Tree_Grid_Node_Root	= Class.create(/* extends */ Control_Tree_Grid_Node, 
{
	initialize	: function($super, oContent, bSelected)
	{
		$super({}, false);
		
		// DOM Elements
		this._oElement.domElement	= document.createElement('tbody');
	},
	
	attachTo	: function(oTreeGridElement)
	{
		if (oTreeGridElement instanceof Control_Tree_Grid)
		{
			// Valid Element
			this._oParent	= oTreeGridElement;
			
			// Attach this and any children to the new parent
			this.getTreeGrid().getTable().appendChild(this.getElement());
			for (var i = 0; i < this._aChildren.length; i++)
			{
				this._aChildren[i].attachTo(this);
			}
		}
		else
		{
			throw "Can only attach Control_Tree_Grid";
		}
	},
	
	getTreeGrid	: function()
	{
		return this._oParent ? this._oParent : null;
	},
	
	getRootNode	: function()
	{
		return this;
	},
	
	isExpanded	: function()
	{
		return true;
	},
	
	isVisible	: function()
	{
		if (this.getParent() instanceof Control_Tree_Grid)
		{
			return true;
		}
	},
	
	getDepth	: function()
	{
		return (this.getParent()) ? -1 : null;
	}
});
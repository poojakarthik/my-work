var Control_Tree_Grid_Node_Root	= Class.create(/* extends */ Control_Tree_Grid_Node, 
{
	initialize	: function($super, oContent, bSelected)
	{
		$super({}, false);
		
		// DOM Elements
		this._oElement.domElement	= document.createElement('tbody');
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
	}
});
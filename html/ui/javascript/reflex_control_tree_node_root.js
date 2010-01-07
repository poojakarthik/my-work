
Reflex.Control.Tree.Node.Root	= Class.create
({
	setParent	: function(oControlTree)
	{
		if (oControlTree instanceof Reflex.Control.Tree)
		{
			// Decouple from existing Parent
			if (this.oParent)
			{
				throw "Cannot set the Tree for a Root Node more than once!";
			}
			
			this.oParent	= oControlTreeNode;
		}
	},
	
	getTree	: function()
	{
		return this.oParent ? this.oParent : null;
	},
	
	getNodeDepth	: function()
	{
		return 0;
	}
});

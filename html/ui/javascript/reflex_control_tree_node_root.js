
Reflex.Control.Tree.Node.Root	= Class.create(/* extends */Reflex.Control.Tree.Node,
{
	setParent	: function(oControlTree)
	{
		if (oControlTree instanceof Reflex.Control.Tree)
		{
			// Decouple from existing Parent
			if (this.oParent)
			{
				throw "Cannot set the Tree for a Root Node more than once!";
			}
			
			this.oParent	= oControlTree;
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

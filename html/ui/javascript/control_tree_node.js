
Control_Tree_Node	= Class.create
({
	initialize	: function(oData)
	{
		// DOM Elements
		this.oElement	= document.createElement('li');
		this.oElement.addClassName('reflex-tree-node');
		
		this.oColumnsList	= document.createElement('ul');
		this.oElement.addClassName('reflex-tree-node-columns');
		this.oElement.appendChild(this.oColumnsList);
		
		this.oChildrenListContainer	= document.createElement('div');
		this.oElement.appendChild(this.oChildrenListContainer);
		
		this.oChildrenList	= document.createElement('ul');
		this.oElement.addClassName('reflex-tree-node-children');
		this.oElement.appendChild(this.oChildrenListContainer);
		
		// Set Data
		this.setData(oData);
		
		// Defaults
		this.oColumns	= {};
		this.aChildren	= [];
	},
	
	setData	: function(oData)
	{
		this.oData	= {};
		for (sField in oData)
		{
			this.oData	= sField;
		}
		
		this._paint();
	},
	
	getElement	: function()
	{
		return this.oElement;
	},
	
	addChild	: function(oControlTreeNode)
	{
		if (oControlTreeNode instanceof Control_Tree_Node && oControlTreeNode !== this)
		{
			oControlTreeNode.setParent(this);
			this.aChildren.push(oControlTreeNode);
			this.oChildrenList.appendChild(oControlTreeNode.getElement());
		}
	},
	
	removeChild	: function(oControlTreeNode)
	{
		if (oControlTreeNode instanceof Control_Tree_Node)
		{
			for (var i = 0, j = this.aChildren.length; i < j; i++)
			{
				if (this.aChildren[i] === oControlTreeNode)
				{
					delete oControlTreeNode.oParent;
					this.aChildren.splice(i, 1);
					this.oChildrenList.removeChild(oControlTreeNode.getElement());
					return true;
				}
			}
		}
	},
	
	setParent	: function(oControlTreeNode)
	{
		if (oControlTreeNode instanceof Control_Tree_Node && oControlTreeNode !== this)
		{
			// Decouple from existing Parent
			if (this.oParent)
			{
				this.oParent.removeChild(this);
			}
			
			this.oParent	= oControlTree;
		}
	},
	
	setExpanded	: function(bExpanded)
	{
		this.bExpanded	= (bExpanded) ? true : false;
		
		// Animate
		var oPercentComplete	= 1;
		if (this.oSlideFX)
		{
			oPercentComplete	= this.oSlideFX.cancel();
		}
		
		if (this.bExpanded)
		{
			// Open
			this.oSlideFX	= new Reflex_FX_Shift(this.oChildrenListContainer, null, null, {height: this.oChildrenList.getHeight()}, 1.0, Control_Tree_Node.SLIDE_ANIMATION_DURATION * oPercentComplete, 'ease', (function(){this.oChildrenList.setStyle({position: 'static'});}).bind(this));
		}
		else
		{
			// Close
			this.oSlideFX	= new Reflex_FX_Shift(this.oChildrenListContainer, null, null, {height: '0px'}, 0.0, Control_Tree_Node.SLIDE_ANIMATION_DURATION * oPercentComplete, 'ease', (function(){this.oChildrenList.setStyle({position: 'static'});}).bind(this));
		}
		this.oChildrenList.setStyle({position: 'absolute'});
		this.oSlideFX.start();
	},
	
	isExpanded	: function()
	{
		return this.bExpanded;
	},
	
	toggleExpanded	: function()
	{
		this.setExpanded(!this.isExpanded());
	},
	
	_paint	: function(oColumns)
	{
		var oVisibleColumns	= oColumns ? oColumns : this.oColumns;
		
		// TODO
	}
});

Control_Tree_Node.SLIDE_ANIMATION_DURATION	= 0.5;

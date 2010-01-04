
Control_Tree_Node	= Class.create
({
	initialize	: function(oData)
	{
		// DOM Elements
		this.oElement	= document.createElement('li');
		this.oElement.addClassName('reflex-tree-node');
		
		this.oColumnsList	= document.createElement('ul');
		this.oColumnsList.addClassName('reflex-tree-node-columns');
		this.oElement.appendChild(this.oColumnsList);
		
		this.oChildrenListContainer	= document.createElement('div');
		this.oChildrenListContainer.addClassName('reflex-tree-node-children');
		this.oElement.appendChild(this.oChildrenListContainer);
		
		this.oChildrenList	= document.createElement('ul');
		this.oChildrenListContainer.appendChild(this.oChildrenList);
		
		this.oIconElement	= document.createElement('img');
		
		// Defaults
		this.oVisibleColumns	= {};
		this.aChildren			= []; 
		
		// Set Data
		this.setData(oData);
		this.setExpanded(false, false);
	},
	
	setData	: function(oData)
	{
		this.oData	= {};
		for (sField in oData)
		{
			this.oData	= sField;
		}
		
		this.paint();
	},
	
	setIcon	: function(sIcon, sTitle)
	{
		if (sIcon)
		{
			this.oIconElement.src	= String(sIcon);
			this.oIconElement.title	= sTitle ? String(sTitle) : '';
			this.oIconElement.show();
		}
		else
		{
			this.oIconElement.hide();
			this.oIconElement.src	= '';
			this.oIconElement.title	= '';
		}
	},
	
	getElement	: function()
	{
		return this.oElement;
	},
	
	addChild	: function(oControlTreeNode)
	{
		if (oControlTreeNode instanceof Control_Tree_Node && oControlTreeNode !== this)
		{
			alert("Adding Child '"+oControlTreeNode.oData.label+"' to '"+this.oData.label+"'");
			
			oControlTreeNode.setParent(this);
			this.aChildren.push(oControlTreeNode);
			this.oChildrenList.appendChild(oControlTreeNode.getElement());
		}
		this.paint();
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
			
			this.oParent	= oControlTreeNode;
		}
	},
	
	setExpanded	: function(bExpanded, bAnimate)
	{
		this.bExpanded	= (bExpanded) ? true : false;
		bAnimate		= (bAnimate || bAnimate === undefined || bAnimate === null) ? true : false;
		
		// Animate
		var oPercentComplete	= 1;
		if (this.oSlideFX)
		{
			oPercentComplete	= this.oSlideFX.cancel();
		}
		
		this.oChildrenList.setStyle({position: 'absolute'});
		if (this.bExpanded)
		{
			this.oElement.addClassName('reflex-tree-node-expanded');
			
			// Open
			alert(this.oChildrenList.getDimensions().toSource());
			//this.oSlideFX	= new Reflex_FX_Shift(this.oChildrenListContainer, null, null, {height: this.oChildrenList.getHeight()+'px'}, 1.0, Control_Tree_Node.SLIDE_ANIMATION_DURATION * oPercentComplete, 'ease', (function(){this.oChildrenList.setStyle({position: 'static'});}).bind(this));
		}
		else
		{
			this.oElement.removeClassName('reflex-tree-node-expanded');
			
			// Close
			//this.oSlideFX	= new Reflex_FX_Shift(this.oChildrenListContainer, null, null, {height: '0px'}, 0.0, Control_Tree_Node.SLIDE_ANIMATION_DURATION * oPercentComplete, 'ease', (function(){this.oChildrenList.setStyle({position: 'static'});}).bind(this));
		}
		//this.oSlideFX.start();
		if (!bAnimate)
		{
			//this.oSlideFX.end();
		}
	},
	
	isExpanded	: function()
	{
		return this.bExpanded;
	},
	
	toggleExpanded	: function()
	{
		this.setExpanded(!this.isExpanded());
	},
	
	getNodeDepth	: function()
	{
		return this.oParent ? this.oParent.getNodeDepth() + 1 : 0;
	},
	
	paint	: function(oColumns)
	{
		this.oVisibleColumns	= oColumns ? oColumns : this.oVisibleColumns;
		
		// Purge existing Columns
		this.oColumnsList.childElements().each(Element.remove, Element);
		
		// Add Columns
		for (sName in this.oVisibleColumns)
		{
			var	oColumnElement	= document.createElement('li');
			
			if (sName === 'label')
			{
				// Label Column
				oColumnElement.setStyle({paddingLeft: (this.getNodeDepth() * Control_Tree_Node.NODE_INDENT_STEPPING_EM) + 'em'});
				
				var	oExpandContainer	= document.createElement('div'),
					oIconContainer		= document.createElement('div'),
					oTextElement		= document.createElement('div');
				
				oExpandContainer.addClassName('reflex-tree-node-expand');
				oIconContainer.addClassName('reflex-tree-node-icon');
				
				oIconContainer.appendChild(this.oIconElement);
				
				oExpandContainer.observe('click', this.toggleExpanded.bind(this));
				if (this.aChildren.length > 0)
				{
					oExpandContainer.addClassName('reflex-tree-node-expand');
					if (this.bExpanded)
					{
						oExpandContainer.addClassName('reflex-tree-node-expanded');
					}
				}
				
				oTextElement.innerHTML	= this.oData['label'] ? this.oData['label'].escapeHTML() : '[ No Label ]';
				
				oColumnElement.appendChild(oExpandContainer);
				oColumnElement.appendChild(oIconContainer);
				oColumnElement.appendChild(oTextElement);
				
				// Must go first (doesn't make sense anywhere else)
				this.oColumnsList.insertBefore(oColumnElement, this.oColumnsList.firstDescendant());
			}
			else
			{
				// Data Column
				oColumnElement.innerHTML	= this.oData[sName] ? this.oData[sName].escapeHTML() : '';
			}
		}
		
		// Paint Children
		for (var i = 0, j = this.aChildren.length; i < j; i++)
		{
			this.aChildren[i].paint(this.oVisibleColumns);
		}
	}
});

Control_Tree_Node.SLIDE_ANIMATION_DURATION	= 0.5;
Control_Tree_Node.NODE_INDENT_STEPPING_EM	= 2;


Reflex.Control.Tree.Node	= Class.create
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
		this.setIcon();
	},
	
	setData	: function(oData)
	{
		this.oData	= {};
		for (sField in oData)
		{
			this.oData[sField]	= oData[sField];
		}
		
		//alert(this.oData.toSource());
		this.paint();
	},
	
	setIcon	: function(sIcon, sTitle)
	{
		if (sIcon)
		{
			//alert("Icon: "+sIcon);
			this.oIconElement.src			= String(sIcon);
			this.oIconElement.title			= sTitle ? String(sTitle) : '';
			this.oIconElement.show();
		}
		else
		{
			//alert("No Icon!");
			this.oIconElement.hide();
			this.oIconElement.src	= '';
			this.oIconElement.title	= '';
		}
		//alert("Icon updated to: "+this.oIconElement.src);
	},
	
	getElement	: function()
	{
		return this.oElement;
	},
	
	addChild	: function(oControlTreeNode)
	{
		if (oControlTreeNode instanceof Reflex.Control.Tree.Node && oControlTreeNode !== this)
		{
			//alert("Adding Child '"+oControlTreeNode.oData.label+"' to '"+this.oData.label+"'");
			
			oControlTreeNode.setParent(this);
			this.aChildren.push(oControlTreeNode);
			this.oChildrenList.appendChild(oControlTreeNode.getElement());
			
			this.paint();
		}
	},
	
	removeChild	: function(oControlTreeNode)
	{
		if (oControlTreeNode instanceof Reflex.Control.Tree.Node)
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
		if (oControlTreeNode instanceof Reflex.Control.Tree.Node && oControlTreeNode !== this)
		{
			// Decouple from existing Parent
			if (this.oParent)
			{
				this.oParent.removeChild(this);
			}
			
			this.oParent	= oControlTreeNode;
		}
	},
	
	getTree	: function()
	{
		var oParent	= this.getParent();
		if (oParent)
		{
			return (oParent instanceof Control_Tree) ? oParent : oParent.getTree();
		}
		else
		{
			return null;
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
		else
		{
			this.oSlideFX	= new Reflex_FX_Reveal(this.oChildrenList, 'top', 'slide', true, true, Reflex.Control.Tree.Node.SLIDE_ANIMATION_DURATION, 'ease');
		}
		
		if (this.bExpanded)
		{
			// Open
			this.oElement.addClassName('reflex-tree-node-expanded');
			this.oSlideFX.resume(false);
		}
		else
		{
			// Close
			this.oElement.removeClassName('reflex-tree-node-expanded');
			this.oSlideFX.resume(true);
		}
		if (!bAnimate)
		{
			this.oSlideFX.end();
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
	
	expandAll	: function()
	{
		this.setExpandedAll(true);
	},
	
	collapseAll	: function()
	{
		this.setExpandedAll(false);
	},
	
	setExpandedAll	: function(bExpanded)
	{
		// Expand/Contract Children
		for (var i = 0, j = this.aChildren.length; i < j; i++)
		{
			this.aChildren[i].setExpandAll(bExpanded);
		}
		
		// Expand/Contract Self
		this.setExpanded(bExpanded);
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
			var oColumnElement	= $T.li({class: 'reflex-tree-node-column ' + this.oVisibleColumns[sName].sClassName});
			
			if (sName === 'label')
			{
				// Attach the label dom
				this.paintLabel(oColumnElement, sName);
				oColumnElement.addClassName('reflex-tree-node-label');
				
				// Must go first (doesn't make sense anywhere else)
				this.oColumnsList.insertBefore(oColumnElement, this.oColumnsList.firstDescendant());
			}
			else 
			{
				if((typeof this.oData[sName] == 'object') && (typeof this.oData[sName].appendChild == 'function'))
				{
					// Data column (dom element)
					oColumnElement.appendChild(this.oData[sName]);
					this.oColumnsList.appendChild(oColumnElement);
				}
				else
				{
					// Data Column (text)
					oColumnElement.innerHTML	= "<span>"+(this.oData[sName] ? this.oData[sName].escapeHTML() : '')+"</span>";
					this.oColumnsList.appendChild(oColumnElement);
				}
			}
		}
		
		// Paint Children
		for (var i = 0, j = this.aChildren.length; i < j; i++)
		{
			this.aChildren[i].paint(this.oVisibleColumns);
		}
	},
	
	paintLabel	: function(oColumnElement, sName)
	{
		// Attach each label element
		oColumnElement.appendChild(this.getLabelInsetElement());
		oColumnElement.appendChild(this.getLabelExpandContainer());
		oColumnElement.appendChild(this.getLabelIconContainer());
		oColumnElement.appendChild(this.getLabelTextElement());
	},
	
	getLabelInsetElement	: function()
	{
		return $T.span({style: 'padding-left: ' + (Math.max(0, this.getNodeDepth() - 1) * Reflex.Control.Tree.Node.NODE_INDENT_STEPPING_EM) + 'em;'});
	},
	
	getLabelExpandContainer	: function()
	{
		var oExpandContainer	= $T.span({class: 'reflex-tree-node-expand'});
		
		if (this.aChildren.length > 0)
		{
			oExpandContainer.observe('click', this.toggleExpanded.bind(this));
			oExpandContainer.addClassName('reflex-tree-node-expandable');
			
			if (this.bExpanded)
			{
				oExpandContainer.addClassName('reflex-tree-node-expanded');
			}
		}
		
		return oExpandContainer;
	},
	
	getLabelIconContainer	: function()
	{
		return	$T.span({class: 'reflex-tree-node-icon'},
					this.oIconElement
				);
	},
	
	getLabelTextElement	: function()
	{
		return $T.span(this.oData['label'] ? this.oData['label'].escapeHTML() : '[ No Label ]');
	}
});

Reflex.Control.Tree.Node.SLIDE_ANIMATION_DURATION	= 0.1;
//Reflex.Control.Tree.Node.SLIDE_ANIMATION_DURATION	= 1.0;
Reflex.Control.Tree.Node.NODE_INDENT_STEPPING_EM	= 0.75;


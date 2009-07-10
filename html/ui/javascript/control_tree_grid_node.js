var Control_Tree_Grid_Node	= Class.create
({
	initialize	: function(oContent, bSelected)
	{
		// DOM Elements
		this._oTR				= {}
		this._oTR.domElement	= document.createElement('tr');
		
		this._oTR.oCheckBox					= {}
		this._oTR.oCheckBox.domElement		= document.createElement('input');
		this._oTR.oCheckBox.domElement.type	= 'checkbox';
		this._oTR.oCheckBox.domElement.addClassName('row-select');
		
		// Properties
		this.setContent(oContent);
		
		this._oParentNode	= null;
		
		this._oVisibleColumns	= null;
		
		// Defaults
		this._bExpanded	= false;
		this._bSelected	= (bSelected) ? true : false;
	},
	
	appendChild	: function(oTreeGridNode)
	{
		oTreeGridNode.attachTo(this);
		this.render();
	},
	
	removeChild	: function(oTreeGridNode)
	{
		oTreeGridNode.detach();
		this.render();
	},
	
	getParent	: function()
	{
		return this._oParentNode;
	},
	
	getTreeGrid	: function()
	{
		if (this._oParentNode === null)
		{
			// No parent, therefore no Tree Grid
			return null;
		}
		else if (this._oParentNode instanceof Control_Tree_Grid)
		{
			// Parent is a Tree Grid
			return this._oParentNode;
		}
		else
		{
			// Parent is a Node, check grandparent
			return this._oParentNode.getTreeGrid();
		}
	},
	
	attachTo	: function(oTreeGridElement)
	{
		if (oTreeGridElement instanceof Control_Tree_Grid || oTreeGridElement instanceof Control_Tree_Grid_Node)
		{
			// Valid Element
			this._oParentNode	= oTreeGridElement;
		}
		else
		{
			throw "Can only attach Control_Tree_Grid or Control_Tree_Grid_Node elements";
		}
	},
	
	detach	: function()
	{
		if (this._oParentNode)
		{
			this.getTreeGrid().getTable().removeChild(this._oTR.domElement);
			this._oParentNode	= null;
		}
	},
	
	setContent	: function(oContent)
	{
		this._oContent	= oContent;
	},
	
	toggleSelected	: function()
	{
		this.setSelected(!this.isSelected());
	},
	
	setSelected	: function(bSelected)
	{
		this._bSelected	= (bSelected) ? true : false;
	},
	
	isSelected	: function()
	{
		return this._bSelected;
	},
	
	toggleExpanded	: function()
	{
		this.setExpanded(!this.isExpanded());
	},
	
	setExpanded	: function(bExpanded)
	{
		this._bExpanded	= (bExpanded) ? true : false;
		this.render(this._oVisibleColumns, true);
	},
	
	isExpanded	: function()
	{
		return this._bSelected;
	},
	
	isVisible	: function()
	{
		if (this.getParent() instanceof Control_Tree_Grid_Node && this.getParent().isExpanded())
		{
			return true;
		}
		else if (this.getParent() instanceof Control_Tree_Grid)
		{
			return true;
		}
		else
		{
			return false;
		}
	},
	
	render	: function(oVisibleColumns, bForceRender)
	{
		// Remove all existing columns
		this._oTR.domElement.innerHTML	= '';
		
		// Add all visible columns
		for (sField in oVisibleColumns)
		{
			var domTD		= document.createElement('td');
			
			switch (oVisibleColumns[sField].sType)
			{
				case Control_Tree_Grid.COLUMN_EXPAND:
					var domExpandIcon	= document.createElement('img');
					domTD.addClassName('icon');
					
					if (this.aChildren.length)
					{
						domExpandIcon.src	= '../admin/img/template/' + (this.isExpanded() ? 'order_desc.png' : 'menu_open_right.png');
						domExpandIcon.addClassName('clickable');
						domExpandIcon.addEventListener('click', this.toggleExpanded.bind(this), false);
					}
					else
					{
						domExpandIcon.src	= '../admin/img/template/transparent-bg';
					}
					domTD.appendChild(domExpandIcon);
					break;
				
				default:
					domTD.innerHTML	= (this._oContent && this._oContent[sField]) ? this._oContent[sField] : '';
			}
			
			this._oTR.domElement.appendChild(domTD);
		}
		
		if (this.isVisible())
		{
			// Show
			this.getTreeGrid().getTable().insertBefore(this._oTR.domElement, this.getParent().getChildAfter(this).getElement());
		}
		else
		{
			// Hide
			this.getTreeGrid().getTable().removeChild(this._oTR.domElement);
		}
		
		// Set the internal cache of visible columns
		this._oVisibleColumns	= oVisibleColumns;
	}
});
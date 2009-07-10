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
		
		this._aChildren	= [];
		
		this._oParentNode	= null;
		
		this._oVisibleColumns	= null;
		
		// Defaults
		this._bExpanded	= false;
		this._bSelected	= (bSelected) ? true : false;
	},
	
	getElement	: function()
	{
		return this._oTR.domElement;
	},
	
	getChildAfter	: function(oChild)
	{
		var iIndex	= this._aChildren.indexOf(oChild);
		if (iIndex > -1)
		{
			return this._aChildren[iIndex + 1];
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
		// Attach Child
		oTreeGridNode.attachTo(this);
		
		// Add to Children array
		this._aChildren.push(oTreeGridNode);
		
		// Render
		this.render();
	},
	
	removeChild	: function(oTreeGridNode)
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
		this.render(this._oVisibleColumns, true);
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
		return this._bExpanded;
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
			
			switch (sField)
			{
				case Control_Tree_Grid.COLUMN_EXPAND:
					var domExpandIcon	= document.createElement('img');
					domTD.addClassName('icon');
					
					if (this._aChildren.length)
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
		
		if (this.getTreeGrid())
		{
			if (this.isVisible())
			{
				alert("Showing");
				// Show
				var oParent	= this.getParent();
				if (oParent instanceof Control_Tree_Grid_Node)
				{
					alert("Normal Node");
					// This is a normal node
					if (oParent.getChildBefore(this))
					{
						alert("Siblings");
						this.getTreeGrid().getTable().insertBefore(this.getElement(), oParent.getChildBefore(this).getElement().nextSibling);
					}
					else
					{
						alert("No Siblings");
						this.getTreeGrid().getTable().insertBefore(this.getElement(), oParent.getElement().nextSibling);
					}
				}
				else
				{
					alert("Root Node");
					// This is the Root node
					this.getTreeGrid().getTable().appendChild(this._oTR.domElement);
				}
			}
			else
			{
				// Hide
				alert("Hiding");
				try
				{
					this.getTreeGrid().getTable().removeChild(this._oTR.domElement);
				}
				catch (eException)
				{
					// Do nothing -- permitted error
				}
			}
		}
		
		// Set the internal cache of visible columns
		this._oVisibleColumns	= oVisibleColumns;
		
		// Render the Children
		for (var i = 0; i < this._aChildren.length; i++)
		{
			//alert("Rendering Child "+i);
			this._aChildren[i].render();
		}
	}
});
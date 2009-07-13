var Control_Tree_Grid_Node_Data	= Class.create(/* extends */ Control_Tree_Grid_Node, 
{
	initialize	: function($super, oContent, bSelected)
	{
		// Parent constructor	
		$super();
		
		// DOM Elements
		this._oElement.domElement	= document.createElement('tr');
		
		this._oElement.oCheckBox					= {}
		this._oElement.oCheckBox.domElement			= document.createElement('input');
		this._oElement.oCheckBox.domElement.type	= 'checkbox';
		this._oElement.oCheckBox.domElement.addClassName('row-select');
		
		this._oElement.oExpandIcon				= {};
		this._oElement.oExpandIcon.domElement	= document.createElement('img');
		this._oElement.oExpandIcon.onClick		= this.toggleExpanded.bind(this);
		
		this._oElement.oRowIcon				= {};
		this._oElement.oRowIcon.domElement	= document.createElement('img');
		
		// Properties
		this.setContent(oContent);
		
		// Defaults
		this._bExpanded	= false;
		this._bSelected	= (bSelected) ? true : false;
	},
	
	getTreeGrid	: function()
	{
		return (this._oParent === null) ? null : this._oParent.getTreeGrid();
	},
	
	attachTo	: function(oTreeGridElement)
	{
		if (oTreeGridElement instanceof Control_Tree_Grid_Node_Root || oTreeGridElement instanceof Control_Tree_Grid_Node_Data)
		{
			// Valid Element
			this._oParent	= oTreeGridElement;
		}
		else
		{
			throw "Can only attach Control_Tree_Grid_Node_Root or Control_Tree_Grid_Node_Data elements";
		}
	},
	
	setContent	: function(oContent)
	{
		this._oContent	= oContent ? oContent : {};
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
		return (this.getParent() && this.getParent().isVisible() && this.getParent().isExpanded()) ? true : false;
	},
	
	getDepth	: function()
	{
		return (this.getParent()) ? this.getParent().getDepth() + 1 : null;
	},
	
	render	: function(oVisibleColumns, bForceRender)
	{
		// Remove all existing columns
		this._oElement.domElement.innerHTML	= '';
		
		// Set the internal cache of visible columns
		this._oVisibleColumns	= oVisibleColumns;
		
		// Add all visible columns to the TR
		for (sField in oVisibleColumns)
		{
			var domTD		= document.createElement('td');
			
			//alert("Table Field: "+sField);
			
			switch (sField)
			{
				//case Control_Tree_Grid.COLUMN_EXPAND:
				case Control_Tree_Grid.COLUMN_LABEL:
					// Complex label
					domTD.addClassName('tree');
					
					// Calculate Depth
					var iDepth	= this.getDepth();
					iDepth	= (iDepth === null) ? 0 : iDepth;
					
					// Add Expand icon
					if (this._aChildren.length)
					{
						this._oElement.oExpandIcon.domElement.src	= '../admin/img/template/' + (this.isExpanded() ? 'tree_open.png' : 'tree_closed.png');
						this._oElement.oExpandIcon.domElement.addClassName('clickable');
						this._oElement.oExpandIcon.domElement.addEventListener('click', this._oElement.oExpandIcon.onClick, false);
					}
					else
					{
						this._oElement.oExpandIcon.domElement.src	= '../admin/img/template/1px-transparent.png';
						this._oElement.oExpandIcon.domElement.removeClassName('clickable');
						this._oElement.oExpandIcon.domElement.removeEventListener('click', this._oElement.oExpandIcon.onClick, false);
					}
					this._oElement.oExpandIcon.domElement.style.marginLeft	= (iDepth * Control_Tree_Grid_Node_Data.TREE_DEPTH_SCALE)+'px';
					domTD.appendChild(this._oElement.oExpandIcon.domElement);
					
					// Add Row Icon
					if (this._oContent[sField].sIconSource)
					{
						this._oElement.oRowIcon.domElement.style.display	= 'inline';
						this._oElement.oRowIcon.domElement.src	= this._oContent[sField].sIconSource;
					}
					else
					{
						this._oElement.oRowIcon.domElement.style.display	= 'none';
					}
					this._oElement.oExpandIcon.domElement.style.marginLeft	= (iDepth * Control_Tree_Grid_Node_Data.TREE_DEPTH_SCALE)+'px';
					domTD.appendChild(this._oElement.oExpandIcon.domElement);
					
					// Add Label
					var domLabel		= document.createElement('span');
					domLabel.innerHTML	= this._oContent[sField].sLabel;
					domTD.appendChild(domLabel);
					break;
				
				default:
					//alert("oContent: "+this._oContent);
					//alert("oContent["+sField+"]: "+this._oContent[sField]);
					domTD.innerHTML	= (this._oContent && this._oContent[sField]) ? this._oContent[sField] : '';
					//alert(domTD.innerHTML);
			}
			
			this._oElement.domElement.appendChild(domTD);
		}
		
		// Insert TR into the DOM
		if (this.getTreeGrid())
		{
			if (this.isVisible())
			{
				//alert("Showing");
				// Show
				var oParent	= this.getParent();
				if (oParent instanceof Control_Tree_Grid_Node)
				{
					//alert("Normal Node");
					// This is a normal node
					if (oParent.getChildBefore(this))
					{
						//alert("Siblings");
						this.getTreeGrid().getTable().insertBefore(this.getElement(), oParent.getChildBefore(this).getElement().nextSibling);
					}
					else
					{
						//alert("No Siblings");
						this.getTreeGrid().getTable().insertBefore(this.getElement(), oParent.getElement().nextSibling);
					}
				}
				else
				{
					//alert("Root Node");
					// This is the Root node
					this.getTreeGrid().getTable().appendChild(this._oElement.domElement);
				}
			}
			else
			{
				// Hide
				//alert("Hiding");
				try
				{
					this.getTreeGrid().getTable().removeChild(this._oElement.domElement);
				}
				catch (eException)
				{
					// Do nothing -- permitted error
				}
			}
		}
		
		// Render the Children
		for (var i = 0; i < this._aChildren.length; i++)
		{
			//alert("Rendering Child "+i);
			this._aChildren[i].render(this._oVisibleColumns);
		}
	}
});

Control_Tree_Grid_Node_Data.TREE_DEPTH_SCALE	= 8;
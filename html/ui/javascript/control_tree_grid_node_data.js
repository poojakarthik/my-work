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
		this.setExpanded(false);
		this.setSelected(bSelected);
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
			
			// Attach this and any children to the new parent
			if (this._oParent.getLastChild())
			{
				this.getTreeGrid().insertBefore(this.getElement(), this._oParent.getLastChild().getElement().nextSibling);
			}
			else
			{
				this.getTreeGrid().insertBefore(this.getElement(), this._oParent.getElement().nextSibling);
			}
			
			// Reattach children
			for (var i = 0; i < this._aChildren.length; i++)
			{
				this._aChildren[i].attachTo(this);
			}
		}
		else
		{
			throw "Can only attach Control_Tree_Grid_Node_Root or Control_Tree_Grid_Node_Data elements";
		}
	},
	
	appendChild	: function($super, oTreeGridNode)
	{
		$super(oTreeGridNode);
		this._updateExpandIcon();
	},
	
	removeChild	: function($super, oTreeGridNode)
	{
		$super(oTreeGridNode);
		this._updateExpandIcon();
	},
	
	setContent	: function(oContent)
	{
		this._oContent	= {};
		
		// Build TDs
		//--------------------------------------------------------------------//
		// Add all visible columns to the TR
		for (sField in oContent)
		{
			var domTD		= document.createElement('td');
			
			//alert("Table Field: "+sField);
			
			switch (sField)
			{
				case Control_Tree_Grid.COLUMN_LABEL:
					alert("Label: "+oContent[sField].sLabel);
					
					// Complex label
					domTD.addClassName('tree');
					
					// Add Expand icon
					domTD.appendChild(this._oElement.oExpandIcon.domElement);
					
					// Add Row Icon
					if (oContent[sField].sIconSource)
					{
						this._oElement.oRowIcon.domElement.style.display	= 'inline';
						this._oElement.oRowIcon.domElement.src				= oContent[sField].sIconSource;
					}
					else
					{
						this._oElement.oRowIcon.domElement.style.display	= 'none';
					}
					domTD.appendChild(this._oElement.oRowIcon.domElement);
					
					// Add Label
					var domLabel		= document.createElement('span');
					domLabel.innerHTML	= oContent[sField].sLabel;
					domLabel.addClassName('label');
					domTD.appendChild(domLabel);
					break;
				
				default:
					domTD.innerHTML	= (oContent && oContent[sField]) ? oContent[sField] : '';
					break;
			}
			
			// Set Column TD
			this._oContent[sField]				= {};
			this._oContent[sField].oData		= oContent[sField];
			this._oContent[sField].domElement	= domTD;
		}
		//--------------------------------------------------------------------//
		
		if (this._oVisibleColumns)
		{
			this.render(this._oVisibleColumns);
		}
	},
	
	toggleSelected	: function()
	{
		this.setSelected(!this.isSelected());
	},
	
	setSelected	: function(bSelected)
	{
		this._bSelected	= (bSelected) ? true : false;
		
		if (this._oVisibleColumns)
		{
			this.render(this._oVisibleColumns);
		}
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
		this._updateExpandIcon();
		
		if (this._oVisibleColumns)
		{
			this.render(this._oVisibleColumns);
		}
	},
	
	isExpanded	: function()
	{
		return this._bExpanded;
	},
	
	_updateExpandIcon	: function()
	{
		// Calculate Depth
		var iDepth	= this.getDepth();
		iDepth	= (iDepth === null) ? 0 : iDepth;
		
		// Update the icon
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
	},
	
	isVisible	: function()
	{
		return (this.getParent() && this.getParent().isVisible() && this.getParent().isExpanded()) ? true : false;
	},
	
	getDepth	: function()
	{
		return (this.getParent()) ? this.getParent().getDepth() + 1 : null;
	},
	
	render	: function(oVisibleColumns)
	{
		this._oVisibleColumns	= oVisibleColumns;
		
		// Show/Hide Row
		this.getElement().style.display	= (this.isVisible()) ? 'table-row' : 'none';
		alert(this._oContent['label'].oData.sLabel+" is "+(this.isVisible() ? '' : 'not ')+'visible');
		
		// Remove all existing columns
		this._oElement.domElement.innerHTML	= '';
		
		// Show/Hide Columns
		for (sField in this._oVisibleColumns)
		{
			this._oElement.domElement.appendChild((this._oContent && this._oContent[sField]) ? this._oContent[sField].domElement : document.createElement('td'));
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
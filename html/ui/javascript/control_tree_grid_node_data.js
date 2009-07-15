var Control_Tree_Grid_Node_Data	= Class.create(/* extends */ Control_Tree_Grid_Node, 
{
	initialize	: function($super, oContent, sDataType)
	{
		// Parent constructor	
		$super();
		
		// DOM Elements
		this._oElement.domElement	= document.createElement('tr');
		
		this._oElement.oCheckBox					= {}
		this._oElement.oCheckBox.domElement			= document.createElement('input');
		this._oElement.oCheckBox.domElement.type	= 'checkbox';
		
		this._oElement.oExpandIcon				= {};
		this._oElement.oExpandIcon.domElement	= document.createElement('img');
		this._oElement.oExpandIcon.onClick		= this.toggleExpanded.bind(this);
		
		this._oElement.oSelectIcon				= {};
		this._oElement.oSelectIcon.domElement	= document.createElement('img');
		this._oElement.oSelectIcon.onClick		= this.toggleSelected.bind(this);
		this._oElement.oSelectIcon.domElement.addEventListener('click', this._oElement.oSelectIcon.onClick, false);
		
		this._oElement.oRowIcon				= {};
		this._oElement.oRowIcon.domElement	= document.createElement('img');
		
		// Properties
		this.setContent(oContent);
		
		this._sDataType	= sDataType ? sDataType : null;
		
		// Defaults
		this.setExpanded(false);
		this.setSelected(false);
	},
	
	getTreeGrid	: function()
	{
		return (this._oParent === null) ? null : this._oParent.getTreeGrid();
	},
	
	getRootNode	: function()
	{
		return this.getParent() ? this.getParent().getRootNode() : null;
	},
	
	attachTo	: function(oTreeGridElement)
	{
		if (oTreeGridElement instanceof Control_Tree_Grid_Node_Root || oTreeGridElement instanceof Control_Tree_Grid_Node_Data)
		{
			// Valid Element
			this._oParent	= oTreeGridElement;
			
			// Update the Node Icon
			this._updateNodeIcon();
			
			// Attach this and any children to the new parent
			if (this.getRootNode())
			{
				var sLabel	= ((oTreeGridElement instanceof Control_Tree_Grid_Node_Data) ? oTreeGridElement._oContent['label'].oData.sLabel+'->' : '') + this._oContent['label'].oData.sLabel; 
				if (this._oParent.getLastChild())
				{
					//alert(sLabel + " has siblings!");
					//alert(this._oParent.getLastChild()._oContent['label'].oData.sLabel + " is the last sibling");
					//this.getRootNode().getElement().insertBefore(this.getElement(), this._oParent.getLastChild().getElement().nextSibling);
					this.getRootNode().getElement().insertBefore(this.getElement(), this._oParent.getLastElement().nextSibling);
				}
				else
				{
					//alert(sLabel + " has no siblings!");
					this.getRootNode().getElement().insertBefore(this.getElement(), this._oParent.getElement().nextSibling);
				}
				
				// Reattach children
				for (var i = 0; i < this._aChildren.length; i++)
				{
					this._aChildren[i].attachTo(this);
				}
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
	
	setDataType	: function(sDataType)
	{
		this._sDataType	= sDataType ? sDataType : null;
	},
	
	getDataType	: function()
	{
		return this._sDataType;
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
			
			switch (sField)
			{
				case Control_Tree_Grid.COLUMN_LABEL:
					//alert("Label: "+oContent[sField].sLabel);
					
					// Complex label
					domTD.addClassName('tree');
					
					// Add Expand icon
					domTD.appendChild(this._oElement.oExpandIcon.domElement);
					
					// Add Row Icon
					domTD.appendChild(this._oElement.oRowIcon.domElement);
					
					// Add Label
					var domLabel		= document.createElement('span');
					domLabel.innerHTML	= oContent[sField].sLabel;
					domLabel.addClassName('label');
					domTD.appendChild(domLabel);
					break;
				
				case Control_Tree_Grid.COLUMN_CHECK:
					this.setSelected(oContent[sField].bChecked);
					
					this._oElement.oCheckBox.domElement.name	= oContent[sField].sName ? oContent[sField].sName : '';
					this._oElement.oCheckBox.domElement.value	= oContent[sField].mValue ? oContent[sField].mValue : 1;
					
					domTD.addClassName('row-select');
					domTD.appendChild(this._oElement.oCheckBox.domElement);
					domTD.appendChild(this._oElement.oSelectIcon.domElement);
					
					this.getElement().addClassName('selectable');
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
	
	getContent	: function()
	{
		var oRawContent	= {};
		for (sField in this._oContent)
		{
			oRawContent[sField]	= this._oContent[sField].oData;
		}
		return oRawContent;
	},
	
	toggleSelected	: function()
	{
		this.setSelected(!this.isSelected());
	},
	
	setSelected	: function(bSelected)
	{
		this._bSelected								= (bSelected) ? true : false;
		this._oElement.oCheckBox.domElement.checked	= this._bSelected;
		
		// Update Icon
		this._oElement.oSelectIcon.domElement.src	= '../admin/img/template/checkbox' + (this.isSelected() ? '-checked' : '') + '.png';
		
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
	
	_updateNodeIcon	: function()
	{
		if (this.getDataType() && this.getTreeGrid() && this.getTreeGrid().aDataTypes[this.getDataType()] && this.getTreeGrid().aDataTypes[this.getDataType()].sIconSource)
		{
			this._oElement.oRowIcon.domElement.style.display	= 'inline';
			this._oElement.oRowIcon.domElement.src				= this.getTreeGrid().aDataTypes[this.getDataType()].sIconSource;
		}
		else
		{
			this._oElement.oRowIcon.domElement.style.display	= 'none';
		}
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
		this._updateExpandIcon();
		
		// Show/Hide Row
		this.getElement().style.display	= (this.isVisible()) ? 'table-row' : 'none';
		
		// Remove all existing columns
		this._oElement.domElement.innerHTML	= '';
		
		// Show/Hide Columns
		for (sField in this._oVisibleColumns)
		{
			this._oElement.domElement.appendChild((this._oContent && this._oContent[sField]) ? this._oContent[sField].domElement : document.createElement('td'));
			
			//alert("Field '"+sField+"' does "+((this._oContent && this._oContent[sField]) ? '' : 'not ')+"exist");
		}
		
		// Render the Children
		for (var i = 0; i < this._aChildren.length; i++)
		{
			//alert("Rendering Child "+i);
			this._aChildren[i].render(this._oVisibleColumns);
		}
	}
});

Control_Tree_Grid_Node_Data.TREE_DEPTH_SCALE	= 16;
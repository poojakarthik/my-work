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
		
		// Properties
		this.setContent(oContent);
		
		// Defaults
		this._bExpanded	= false;
		this._bSelected	= (bSelected) ? true : false;
	},
	
	getParent	: function()
	{
		return this._oParent;
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
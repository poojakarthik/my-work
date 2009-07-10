var Control_Select_List_Option	= Class.create
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
		
		this._oParentControl	= null;
		
		this._oVisibleColumns	= null;
		
		// Defaults
		this._bExpanded	= false;
		this._bSelected	= (bSelected) ? true : false;
	},
	
	attachTo	: function(oControlSelectList)
	{
		if (oControlSelectList instanceof Control_Select_List)
		{
			// Detach from old parent
			if (this._oParentControl)
			{
				this._oParentControl.remove(this);
			}
			
			// Attach to new parent
			this._oParentControl	= oControlSelectList;
			oControlSelectList.getTable().appendChild(this._oTR.domElement);
		}
	},
	
	detach	: function()
	{
		if (this._oParentControl)
		{
			this._oParentControl.getTable().removeChild(this._oTR.domElement);
			this._oParentControl	= null;
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
	
	setExpanded	: function(bSelected)
	{
		this._bSelected	= (bSelected) ? true : false;
		this.render(this._oVisibleColumns, true);
	},
	
	isExpanded	: function()
	{
		return this._bSelected;
	},
	
	render	: function(oVisibleColumns, bForceRender)
	{
		// Do we really need to re-render?
		if (oVisibleColumns != this._oVisibleColumns || bForceRender)
		{
			// Remove all existing columns
			this._oTR.domElement.innerHTML	= '';
			
			// Add all visible columns
			for (sField in oVisibleColumns)
			{
				var domTD		= document.createElement('td');
				
				switch (oVisibleColumns[sField].sType)
				{
					case Control_Select_List.COLUMN_TYPE_SEND:
						var domSendIcon	= document.createElement('img');
						domSendIcon.src	= oVisibleColumns[sField].sIconSource;
						domTD.addClassName('icon');
						domTD.appendChild(domSendIcon);
						
						// Add 'Send' Click Listener
						domSendIcon.addEventListener('click', oVisibleColumns[sField].oSendDestination.add.bind(oVisibleColumns[sField].oSendDestination, this), false);
						break;
					
					default:
						domTD.innerHTML	= (this._oContent && this._oContent[sField]) ? this._oContent[sField] : '';
				}
				
				this._oTR.domElement.appendChild(domTD);
			}
			
			// Set the internal cache of visible columns
			this._oVisibleColumns	= oVisibleColumns;
		}
		else
		{
			//alert('Skipping rendering...');
		}
	}
});
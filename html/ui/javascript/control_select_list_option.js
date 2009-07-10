var Control_Select_List_Option	= Class.create
({
	initialize	: function(oContent, mValue)
	{
		// DOM Elements
		this._oTR				= {}
		this._oTR.domElement	= document.createElement('tr');
		
		// Properties
		this.setContent(oContent);
		this.setValue(mValue);
		this.setSelected(false);
		
		this._oParentControl	= null;
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
	
	setValue	: function(mValue)
	{
		this._mValue	= mValue;
	},
	
	getValue	: function()
	{
		return this._mValue;
	},
	
	setSelected	: function(bSelected)
	{
		this._bSelected	= (bSelected) ? true : false;
	},
	
	isSelected	: function()
	{
		return this._bSelected;
	},
	
	render	: function(oVisibleColumns)
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
	}
});
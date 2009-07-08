var Control_Select_Multicolumn_Option	= Class.create
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
	},
	
	attachTo	: function(oControlSelectMulticolumn)
	{
		oControlSelectMulticolumn.domElement.appendChild(this._oTR.domElement);
	},
	
	detachFrom	: function(oControlSelectMulticolumn)
	{
		oControlSelectMulticolumn.domElement.removeChild(this._oTR.domElement);
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
			domTD.innerHTML	= (this._oContent && this._oContent[sField]) ? this._oContent[sField] : '';
			this._oTR.domElement.appendChild(domTD);
		}
	}
});
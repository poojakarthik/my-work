var Control_Field_RadioButton	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		this._aOnChangeCallbacks	= [];
		
		// Create the DOM Elements
		this.oControlOutput.oEdit		= document.createElement('input');
		this.oControlOutput.oEdit.type	= 'radio';
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		this.oControlOutput.oElement.addClassName('control-field-radiobutton');
		
		this.validate();
		
		this.addEventListeners();
	},
	
	setFieldName	: function(sName)
	{
		this.oControlOutput.oEdit.setAttribute('name', sName);
	},
	
	getElementValue	: function()
	{
		return this.oControlOutput.oEdit.checked;
	},
	
	setElementValue	: function(mValue)
	{	
		this.oControlOutput.oEdit.checked	= (Number(mValue)) ? true : false;
	},
	
	updateElementValue	: function()
	{
		var	mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= (Number(mValue)) ? 'Yes' : 'No';
	},
	
	addOnChangeCallback	: function(fnCallback)
	{
		this._aOnChangeCallbacks.push(fnCallback);
	},
	
	removeOnChangeCallback	: function(fnCallback)
	{
		var i	= this._aOnChangeCallbacks.indexOf(fnCallback);
		if (i !== -1)
		{
			this._aOnChangeCallbacks.splice(i, 1);
		}
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers				= {};
		this.aEventHandlers.fnOnChange	= this._valueChanged.bind(this);
		
		this.oControlOutput.oEdit.addEventListener('click'	, this.aEventHandlers.fnOnChange, false);
		this.oControlOutput.oEdit.addEventListener('change'	, this.aEventHandlers.fnOnChange, false);
		this.oControlOutput.oEdit.addEventListener('keyup'	, this.aEventHandlers.fnOnChange, false);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oEdit.removeEventListener('click'	, this.aEventHandlers.fnOnChange, false);
		this.oControlOutput.oEdit.removeEventListener('change'	, this.aEventHandlers.fnOnChange, false);
		this.oControlOutput.oEdit.removeEventListener('keyup'	, this.aEventHandlers.fnOnChange, false);
	},
	
	_valueChanged	: function()
	{
		this.validate();
		for (var i = 0; i < this._aOnChangeCallbacks.length; i++)
		{
			this._aOnChangeCallbacks[i](this);
		}
	}
});
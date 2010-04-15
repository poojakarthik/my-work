var Control_Field_Checkbox	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Create the DOM Elements
		this.oControlOutput.oEdit			= document.createElement('input');
		this.oControlOutput.oEdit.type	= 'checkbox';
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		this.validate();
		
		this.addEventListeners();
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
		mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= (Number(mValue)) ? 'Yes' : 'No';
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers				= {};
		this.aEventHandlers.fnValidate	= this.validate.bind(this);
		
		this.oControlOutput.oEdit.addEventListener('click'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.addEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.addEventListener('keyup'	, this.aEventHandlers.fnValidate, false);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oEdit.removeEventListener('click'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.removeEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.removeEventListener('keyup'	, this.aEventHandlers.fnValidate, false);
	}
});
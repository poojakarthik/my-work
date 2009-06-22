var Control_Field_Text	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, strLabel, strLabelSeparator)
	{
		// Parent
		$super(strLabel, strLabelSeparator);
		
		// Create the DOM Elements
		this.objControlOutput.domEdit		= document.createElement('input');
		this.objControlOutput.domEdit.type	= 'text';
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
		
		this.objControlOutput.domView		= document.createElement('span');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domView);
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return this.objControlOutput.domEdit.value;
	},
	
	updateElementValue	: function(bolUseInternalValue)
	{
		mixValue	= (bolUseInternalValue) ? this.mixValue : this.getValue();

		this.objControlOutput.domEdit.value		= mixValue;
		this.objControlOutput.domView.innerHTML	= mixValue;
	},
	
	getElement	: function()
	{
		//this.update();
		return this.objControlOutput.domElement;
	},
	
	addEventListeners	: function()
	{
		this.arrEventHandlers				= {};
		this.arrEventHandlers.fncValidate	= this.validate.bind(this);
		
		this.objControlOutput.domEdit.addEventListener('click'		, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('change'		, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('mouseup'	, this.arrEventHandlers.fncValidate, false);
	},
	
	removeEventListeners	: function()
	{
		this.objControlOutput.domEdit.removeEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('mouseup'	, this.arrEventHandlers.fncValidate, false);
	}
});
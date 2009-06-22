var Control_Field_Checkbox	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, strLabel, strLabelSeparator)
	{
		// Parent
		$super(strLabel, strLabelSeparator);
		
		// Create the DOM Elements
		this.objControlOutput.domEdit		= document.createElement('input');
		this.objControlOutput.domEdit.type	= 'checkbox';
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
	
	setElementValue	: function(mixValue)
	{	
		this.objControlOutput.domEdit.checked	= (Number(mixValue)) ? true : false;
	},
	
	updateElementValue	: function()
	{
		mixValue	= this.getValue();
		
		this.setElementValue(mixValue);
		this.objControlOutput.domView.innerHTML	= (Number(mixValue)) ? 'Yes' : 'No';
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
		
		this.objControlOutput.domEdit.addEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('keyup'	, this.arrEventHandlers.fncValidate, false);
	},
	
	removeEventListeners	: function()
	{
		this.objControlOutput.domEdit.removeEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('keyup'	, this.arrEventHandlers.fncValidate, false);
	}
});
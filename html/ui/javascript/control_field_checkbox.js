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
		
		this.update();
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return this.objControlOutput.domEdit.value;
	},
	
	getElement	: function()
	{
		this.update();
		return this.objControlOutput.domElement;
	},
	
	update	: function()
	{
		// Update value
		var strValue	= this.getValue();
		
		this.objControlOutput.domEdit.value		= strValue;
		this.objControlOutput.domView.innerHTML	= (strValue) ? 'Yes' : 'No';
		
		// Update Render Method
		if (this.isEditable())
		{
			this.objControlOutput.domEdit.removeClassName('hide');
			this.objControlOutput.domView.addClassName('hide');
		}
		else
		{
			this.objControlOutput.domEdit.addClassName('hide');
			this.objControlOutput.domView.removeClassName('hide');
		}
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
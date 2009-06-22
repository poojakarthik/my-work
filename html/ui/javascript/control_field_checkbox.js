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
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
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
	}
});
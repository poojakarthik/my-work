var Control_Field_Text	= Class.create
({
	initialize	: function($super, strLabel, strLabelSeparator)
	{
		// Parent
		$super(strLabel, strLabelSeparator);
		
		// Create the DOM Elements
		this.objControlOutput.domEdit		= document.createElement('input');
		this.objControlOutput.domEdit.type	= 'text';
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
		
		this.objControlOutput.domView		= document.createElement('span');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
	},
	
	getElement	: function(bolRenderMode)
	{
		this.update();
		return this.objControlOutput.domElement;
	},
	
	update	: function()
	{
		// Update value
		var strValue	= this.getValue();
		
		this.objElements.domEdit.value		= strValue;
		this.objElements.domView.innerHTML	= strValue;
		
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
}
, /* extends */ Control_Field);
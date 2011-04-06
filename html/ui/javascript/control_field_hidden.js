var Control_Field_Hidden	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Create the DOM Elements
		this.oControlOutput.oEdit = $T.input({type: 'hidden'});
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		this.oControlOutput.oView = $T.span();
		
		this.validate();
	},
	
	getElementValue	: function()
	{
		return this.oControlOutput.oEdit.value;
	},
	
	setElementValue	: function(mValue)
	{
		this.oControlOutput.oEdit.value	= mValue;
	},
	
	updateElementValue	: function()
	{
		var	mValue	= this.getValue();
		this.setElementValue(mValue);
	}
});
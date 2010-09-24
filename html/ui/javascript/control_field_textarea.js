var Control_Field_Textarea	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, rows, cols)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Create the DOM Elements
	
		this.oControlOutput.oEdit		= document.createElement('textarea');
		//this.oControlOutput.oEdit.type	= 'textarea';
		this.oControlOutput.oEdit.rows	= rows;
		this.oControlOutput.oEdit.cols	= cols;
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		this._aOnChangeCallbacks	= [];
		
		this.validate();
		
		this.addEventListeners();
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
		mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= mValue;
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers					= {};
		this.aEventHandlers.fnValueChange	= this._valueChange.bind(this);
		
		this.oControlOutput.oEdit.observe('click'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('change'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('keyup'	, this.aEventHandlers.fnValueChange);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oEdit.stopObserving('click'		, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.stopObserving('change'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.stopObserving('keyup'		, this.aEventHandlers.fnValueChange);
	},
	
	addOnChangeCallback	: function(fnCallback)
	{
		this._aOnChangeCallbacks.push(fnCallback);
	},
	
	_valueChange	: function()
	{
		this.validate();
		
		for (var i = 0; i < this._aOnChangeCallbacks.length; i++)
		{
			this._aOnChangeCallbacks[i]();
		}
	},
	
	disableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oEdit.disabled	= true;
		}
	},
	
	enableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oEdit.removeAttribute('disabled');
		}
	}
});
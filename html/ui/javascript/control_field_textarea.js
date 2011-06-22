var Control_Field_Textarea	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, rows, cols, bCancelFocusShiftOnTab)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Create the DOM Elements
	
		this.oControlOutput.oEdit		= document.createElement('textarea');
		//this.oControlOutput.oEdit.type	= 'textarea';
		
		if (rows) {
			this.oControlOutput.oEdit.rows	= rows;
		}
		if (cols) {
			this.oControlOutput.oEdit.cols	= cols;
		}
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oView.setAttribute('class', 'control-field-textarea-view')
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		this._bCancelFocusShiftOnTab	= (typeof bCancelFocusShiftOnTab == 'undefined') || bCancelFocusShiftOnTab;
		
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
		var	mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= mValue.escapeHTML();
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers					= {};
		this.aEventHandlers.fnValueChange	= this._valueChange.bind(this);
		
		this.oControlOutput.oEdit.observe('click'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('change'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('keyup'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('keydown'	, this._keyDown.bind(this));
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
	
	cancelFocusShiftOnTab	: function()
	{
		this._bCancelFocusShiftOnTab	= true;
	},
	
	_valueChange	: function(oEvent)
	{
		this.validate();
		this.fire('change', oEvent);
		
		// Kept for backwards compatibility
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
	},
	
	_keyDown	: function(oEvent)
	{
		if (this._bCancelFocusShiftOnTab && oEvent.keyCode == Event.KEY_TAB)
		{
			// Get cursor position from text area
			var oTextArea	= this.oControlOutput.oEdit;
			var sTab		= "\t";
			var iStartPos 	= oTextArea.selectionStart;
	        var iEndPos 	= oTextArea.selectionEnd;
	        var iScrollTop 	= oTextArea.scrollTop;

	        // Insert the tab
	        oTextArea.value	= oTextArea.value.substring(0, iStartPos) + sTab + oTextArea.value.substring(iEndPos, oTextArea.value.length);
			
	        // Reset the cursor position
	        oTextArea.selectionStart	= iStartPos + sTab.length;
	        oTextArea.selectionEnd 		= iStartPos + sTab.length;
	        oTextArea.scrollTop 		= iScrollTop;
	        
			oEvent.stop();
		}
	}
});
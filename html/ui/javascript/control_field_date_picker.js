
var Control_Field_Date_Picker	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, sDateFormat, bTimePicker)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		this._sDateFormat	= (sDateFormat ? sDateFormat : 'Y-m-d');
		this._bTimePicker	= bTimePicker;

		// Create the DOM Elements
		this.oControlOutput.oEdit	= $T.div({class: 'date-time-picker-dynamic'});
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		// FIXME: Temporary Id
		var sHiddenId	= 'hidden_' + Math.ceil(Math.random() * (new Date()).getTime());
		
		this.oControlOutput.oHidden			= document.createElement('hidden');
		this.oControlOutput.oHidden.id		= sHiddenId;
		this.oControlOutput.oEdit.appendChild(this.oControlOutput.oHidden);
		
		this.oControlOutput.oInput				= document.createElement('input');
		this.oControlOutput.oInput.type			= 'text';
		this.oControlOutput.oInput.className	= 'date-formatted';
		this.oControlOutput.oInput.readOnly		= true;
		this.oControlOutput.oEdit.appendChild(this.oControlOutput.oInput);
		
		this.oControlOutput.oIcon			= $T.img({class: 'date-time-picker-launch-icon'});
		this.oControlOutput.oIcon.src		= '../admin/img/template/calendar_small.png';
		this.oControlOutput.oIcon.title		= 'Choose Date with Picker...';
		this.oControlOutput.oIcon.alt		= 'Choose Date';
		this.oControlOutput.oEdit.appendChild(this.oControlOutput.oIcon);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		var objDate			= new Date();
		this._oDatePicker	= 	new Component_Date_Picker(
									new Date(), 
									(bTimePicker ? true : false), 
									Control_Field_Date_Picker.YEAR_START, 
									Control_Field_Date_Picker.YEAR_END,
									this._sDateFormat, 
									this._datePickerValueChange.bind(this)
								);
		
		this._aOnChangeCallbacks	= [];
		
		this.validate();		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return this.oControlOutput.oHidden.value;
	},
	
	setElementValue	: function(mValue)
	{
		this.oControlOutput.oHidden.value	= mValue;
		this._updateFormattedInput();
		
		// Parse the formatted date string & update the Datepicker's default date
		var oDate	= Date.$parseDate(mValue, this._sDateFormat);
		if (oDate)
		{
			this._oDatePicker.setDate(oDate);
		}
	},
	
	updateElementValue	: function()
	{
		mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= this._getFormattedDate();
	},
	
	_datePickerValueChange	: function(oDate)
	{
		// Convert the date object (from the picker) to a formatted string 
		// and set this controls hidden value the update the visible input.
		var sDate							= oDate.$format(this._sDateFormat);
		this.oControlOutput.oHidden.value	= sDate;
		this._updateFormattedInput();
	},
	
	_updateFormattedInput	: function()
	{
		this.oControlOutput.oInput.value	= this._getFormattedDate();
	},
	
	_getFormattedDate	: function()
	{
		if (this.oControlOutput.oHidden.value && this.oControlOutput.oHidden.value.length)
		{
			var oDate	= Date.$parseDate(this.oControlOutput.oHidden.value, this._sDateFormat);
			
			if (oDate && oDate.$format)
			{
				if (this._bTimePicker)
				{
					return oDate.$format(Control_Field_Date_Picker.DATE_TIME_FORMAT);	
				}
				else
				{
					return oDate.$format(Control_Field_Date_Picker.DATE_FORMAT);
				}
			}
		}
		
		return "[ No date specified ]";
	},
	
	_showDatePicker	: function(oEvent)
	{
		this._oDatePicker.show(oEvent);
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers					= {};
		this.aEventHandlers.fnValueChange	= this._valueChange.bind(this);
		this.aEventHandlers.fnOpenPicker	= this._showDatePicker.bind(this);
		
		this.oControlOutput.oHidden.addEventListener('change' ,this.aEventHandlers.fnValueChange, false);
		this.oControlOutput.oHidden.addEventListener('change' ,this.aEventHandlers.fnValueChange, false);
		this.oControlOutput.oIcon.addEventListener('click' ,this.aEventHandlers.fnOpenPicker, false);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oInput.removeEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oIcon.removeEventListener('click'	, this.aEventHandlers.fnOpenPicker, false);
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
			this.oControlOutput.oInput.disabled	= true;
		}
	},
	
	enableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oInput.removeAttribute('disabled');
		}
	}
});

Control_Field_Date_Picker.DATE_FORMAT		= 'd/m/Y';
Control_Field_Date_Picker.DATE_TIME_FORMAT	= Control_Field_Date_Picker.DATE_FORMAT + ' g:i A';

Control_Field_Date_Picker.YEAR_START	= 1900;
Control_Field_Date_Picker.YEAR_END		= 2050;

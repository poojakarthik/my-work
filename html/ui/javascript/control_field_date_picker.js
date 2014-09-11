
var Control_Field_Date_Picker	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, sDateFormat, bTimePicker, iYearStart, iYearEnd)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		this._sDateFormat	= (sDateFormat ? sDateFormat : 'Y-m-d');
		this._bTimePicker	= bTimePicker;
		
		// Default value is null
		this.mValue			= null;

		// Create the DOM Elements
		this.oControlOutput.oEdit	= $T.div({class: 'date-time-picker-dynamic'});
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oHidden	= document.createElement('hidden');
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
		
		this._aOnChangeCallbacks	= [];
		var objDate					= new Date();
		this._oDatePicker			= 	new Component_Date_Picker(
											new Date(), 
											(bTimePicker ? true : false), 
											(iYearStart ? iYearStart : Control_Field_Date_Picker.YEAR_START), 
											(iYearEnd ? iYearEnd : Control_Field_Date_Picker.YEAR_END),
											this._sDateFormat, 
											this._datePickerValueChange.bind(this)
										);
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
		if (mValue != null)
		{
			var oDate	= Date.$parseDate(mValue, this._sDateFormat);
			if (oDate)
			{
				this._oDatePicker.setDate(oDate);
			}
		}
	},
	
	updateElementValue	: function()
	{
		//debugger;
		var	mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= this._getFormattedDate();
	},
	
	getElementText	: function()
	{
		return this._getFormattedDate();
	},
	
	clearValue	: function()
	{
		this._oDatePicker.clearDate();
		this.oControlOutput.oHidden.value	= null;
		this.save(true);
		this.updateElementValue();
	},
	
	_datePickerValueChange	: function(oDate)
	{
		// Convert the date object (from the picker) to a formatted string 
		// and set this controls hidden value the update the visible input.
		var sDate	= oDate.$format(this._sDateFormat);
		this.setElementValue(sDate);
		this.validate();
		this.fire('change');
		
		// Kept for backwards compatibility
		for (var i = 0; i < this._aOnChangeCallbacks.length; i++)
		{
			this._aOnChangeCallbacks[i]();
		}
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
		this.aEventHandlers.fnOpenPicker	= this._showDatePicker.bind(this);
		this.oControlOutput.oIcon.addEventListener('click' ,this.aEventHandlers.fnOpenPicker, false);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oIcon.removeEventListener('click'	, this.aEventHandlers.fnOpenPicker, false);
	},
	
	addOnChangeCallback	: function(fnCallback)
	{
		this._aOnChangeCallbacks.push(fnCallback);
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
Control_Field_Date_Picker.DATE_FORMAT_MY	= 'm/Y';
Control_Field_Date_Picker.DATE_TIME_FORMAT	= Control_Field_Date_Picker.DATE_FORMAT + ' g:i A';

Control_Field_Date_Picker.YEAR_START	= 1900;
Control_Field_Date_Picker.YEAR_END		= 2050;

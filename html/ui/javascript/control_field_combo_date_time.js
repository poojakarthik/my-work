

var Control_Field_Combo_Date_Time	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, iDateFormat, iDateMinYear, iDateMaxYear, iTimeFormat, mDateSeparatorElement, mTimeSeparatorElement)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		var fnOnUpdate	= this._childFieldUpdated.bind(this);
		this.oDate		= new Control_Field_Combo_Date('Date', '', iDateFormat, mDateSeparatorElement, fnOnUpdate);
		this.oDate.setYearRange(iDateMinYear, iDateMaxYear);
		this.oDate.disableValidationStyling();
		this.oTime		= new Control_Field_Combo_Time('Time', '', iTimeFormat, mTimeSeparatorElement, fnOnUpdate);
		this.oTime.disableValidationStyling();
		var oUL			= 	$T.ul({class: 'reset horizontal control-field-combo-date-time'},
								$T.li(this.oDate.getElement()),
								$T.li(this.oTime.getElement())
							);
		this.oControlOutput.oElement.appendChild(oUL);
	},
	
	setVisible	: function($super, bVisible)
	{
		$super(bVisible);
		this.oDate.setEditable(bVisible);
		this.oTime.setEditable(bVisible);
	},
	
	setEditable	: function($super, bEditable)
	{
		$super(bEditable);
		this.oDate.setEditable(bEditable);
		this.oTime.setEditable(bEditable);
	},
	
	setMandatory	: function($super, bMandatory)
	{
		$super(bMandatory);
		this.oDate.setMandatory(bMandatory);
		this.oTime.setMandatory(bMandatory);
	},
	
	getElement	: function()
	{
		return this.oControlOutput.oElement;
	},
	
	updateElementValue	: function()
	{
		this.oDate.updateElementValue();
		this.oTime.updateElementValue();
	},
	
	setValue	: function(mValue)
	{
		this.mValue	= mValue;
		this.setElementValue(mValue);
		this.updateElementValue(true);
		this.validate();
	},
	
	setElementValue	: function(mValue)
	{
		var aValueSplit	= (mValue ? mValue.match(Control_Field_Combo_Date_Time.VALUE_REGEX) : null);
		this.oDate.setElementValue(aValueSplit && aValueSplit[1] ? aValueSplit[1] : '');
		this.oTime.setElementValue(aValueSplit && aValueSplit[2] ? aValueSplit[2] : '');
	},
	
	getElementValue	: function()
	{
		return this.oDate.getElementValue() + ' ' + this.oTime.getElementValue();
	},
	
	setRenderMode	: function(bRenderMode)
	{
		this.bRenderMode	= bRenderMode;
		
		this.oDate.setRenderMode(bRenderMode);
		this.oTime.setRenderMode(bRenderMode);
		
		if (bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.setElementValue(this.getValue());
		}
		
		this.validate();
	},
	
	setValidateFunction	: function(fnValidate)
	{
		// Set this validate function
		this.fnValidate	= fnValidate;
		this.validate();
	},
	
	_childFieldUpdated	: function()
	{
		this.validate();
	},
	
	// Redefined, to allow formatted output in validatation error text
	validate	: function(bSilentFail)
	{
		// Default to silent fail if not specifically set to false
		if (bSilentFail !== false)
		{
			bSilentFail	= true;
		}
		
		if (this.isEditable())
		{
			// Preprocess (trim)
			this.trim();
			
			this.oControlOutput.oElement.removeClassName('invalid');
			this.oControlOutput.oElement.removeClassName('valid');
			this.oControlOutput.oElement.removeClassName('mandatory');
			
			var mElementValue	= this.getElementValue();
			
			if (mElementValue)
			{
				if (this.isValid())
				{
					if (this.bValidationStylingEnabled)
					{
						this.oControlOutput.oElement.addClassName('valid');
					}
				}
				else
				{
					if (this.bValidationStylingEnabled)
					{
						this.oControlOutput.oElement.addClassName('invalid');
					}
					
					if (bSilentFail)
					{
						return false;
					}
					else
					{
						throw "'" + this._formatElementValue(mElementValue) + "' is not a valid " + this.getLabel() + "." + this.getValidationReason();
					}
				}
			}
			else if (this.isMandatory())
			{
				if (this.bValidationStylingEnabled)
				{
					this.oControlOutput.oElement.addClassName('mandatory');
				}
				
				if (bSilentFail)
				{
					return false;
				}
				else
				{
					throw "No value supplied for mandatory field "+this.getLabel();
				}
			}
		}
		
		return true;
	},
	
	_formatElementValue	: function(mValue)
	{
		var aValueSplit	= mValue.match(Control_Field_Combo_Date_Time.VALUE_REGEX);
		var sDate		= (aValueSplit[1] ? aValueSplit[1] : '');
		var sTime		= (aValueSplit[2] ? aValueSplit[2] : '');

		return this.oDate._formatElementValue(sDate) + ' ' + this.oTime._formatElementValue(sTime);
	}
});


Control_Field_Combo_Date_Time.VALUE_REGEX	= /(\d{0,4}-\d{0,2}-\d{0,2})?\s(\d{0,2}:\d{0,2}(:\d{1,2})?(\s(AM|PM))?)?/;

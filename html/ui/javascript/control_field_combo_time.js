

var Control_Field_Combo_Time	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, iFormat, mSeparatorElement, fnOnUpdate)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		this.oHours		= new Control_Field_Select('Hours', '');
		this.oMinutes	= new Control_Field_Select('Minutes', '');
		this.oAMPM		= new Control_Field_Select('AM/PM', '');
		
		this.aControls		= [];
		this._fnOnUpdate	= fnOnUpdate;
		this.iFormat		= (iFormat ? iFormat : Control_Field_Combo_Time.FORMAT_12_HOUR);
		
		switch (this.iFormat)
		{
			case Control_Field_Combo_Time.FORMAT_12_HOUR:
				this.aControls	= [this.oHours, this.oMinutes, this.oAMPM];
				break;
			
			case Control_Field_Combo_Time.FORMAT_24_HOUR:
				this.aControls	= [this.oHours, this.oMinutes];
				break;
		}
		
		this.oHours.setPopulateFunction(this.getHourOptions.bind(this, this.iFormat), true);
		this.oMinutes.setPopulateFunction(this.getMinuteOptions.bind(this), true);
		this.oAMPM.setPopulateFunction(this.getAMPMOptions.bind(this), true);
		
		var oUL	= $T.ul({class: 'reset horizontal control-field-combo-time'});
		this.oControlOutput.oElement.appendChild(oUL);
		
		// Add css classes to the selects
		this.oHours.getElement().addClassName('control-field-combo-time-select');
		this.oMinutes.getElement().addClassName('control-field-combo-time-select');
		this.oAMPM.getElement().addClassName('control-field-combo-time-select');
		
		var fnChildSelectUpdated	= this.childSelectUpdated.bind(this);
		var oControl			 	= null;
		
		for (var i = 0; i < this.aControls.length; i++)
		{
			// Add extra events to the select
			oControl	= this.aControls[i];
			
			oControl.oControlOutput.oEdit.observe('change', fnChildSelectUpdated);
			oControl.oControlOutput.oEdit.observe('keyup', fnChildSelectUpdated);
			oControl.disableValidationStyling();
			
			// Add control inside LI
			oUL.appendChild(
				$T.li({class: (i < (this.aControls.length - 1) ? 'control-field-combo-time-pad' : '')},
					oControl.getElement()
				)
			);
			
			if (mSeparatorElement)
			{
				oUL.appendChild(
					$T.li({class: 'control-field-combo-time-select-separator'},
						$T.span(mSeparatorElement)
					)
				);
				
				// Only need one separator max
				mSeparatorElement	= null;
			}
		}
	},
	
	setVisible	: function($super, bVisible)
	{
		$super(bVisible);
		this.oHours.setEditable(bVisible);
		this.oMinutes.setEditable(bVisible);
		this.oAMPM.setEditable(bVisible);
	},
	
	setEditable	: function($super, bEditable)
	{
		$super(bEditable);
		this.oHours.setEditable(bEditable);
		this.oMinutes.setEditable(bEditable);
		this.oAMPM.setEditable(bEditable);
	},
	
	setMandatory	: function($super, bMandatory)
	{
		$super(bMandatory);
		this.oHours.setMandatory(bMandatory);
		this.oMinutes.setMandatory(bMandatory);
		this.oAMPM.setMandatory(bMandatory);
	},
	
	getElement	: function()
	{
		return this.oControlOutput.oElement;
	},
	
	updateElementValue	: function()
	{
		for (var i = 0; i < this.aControls.length; i++)
		{
			this.aControls[i].updateElementValue();
		}
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
		var aDateTime = null;
		if (mValue)
		{
			aDateTime = mValue.match(Control_Field_Combo_Time.VALUE_REGEX);
		}
		
		if (aDateTime)
		{
			var sHour	= aDateTime[1];
			var sMinute	= (aDateTime[2] ? aDateTime[2] : 0);
			var sAMPM	= (aDateTime[3] ? aDateTime[3] : null);
			
			// Special process for hours
			var fHour	= parseFloat(sHour);
			switch (this.iFormat)
			{
				case Control_Field_Combo_Time.FORMAT_12_HOUR:
					this.oHours.setValue((fHour == 0 ? 12 : (fHour > 12 ? fHour - 12 : fHour)).toString());
					this.oAMPM.setValue(sAMPM ? sAMPM : (fHour > 12 ? Control_Field_Combo_Time.AMPM_PM : Control_Field_Combo_Time.AMPM_AM));
					break;
				
				case Control_Field_Combo_Time.FORMAT_24_HOUR:
					this.oHours.setValue(fHour.toString());
					break;
			}
			
			this.oMinutes.setValue(parseFloat(sMinute).toString());
		}
		else
		{
			this.oHours.setValue(null);
			this.oMinutes.setValue(null);
			this.oAMPM.setValue(null);
		}
	},
	
	getElementValue	: function()
	{
		var mHours 		= this.oHours.getElementValue();
		var mMinutes	= this.oMinutes.getElementValue();
		if (mHours === null && mMinutes === null)
		{
			return null;
		}
		
		var sValue = Control_Field_Combo_Time.padWithZero(mHours) + Control_Field_Combo_Time.SEPARATOR + Control_Field_Combo_Time.padWithZero(mMinutes);
		if (this.iFormat == Control_Field_Combo_Time.FORMAT_12_HOUR)
		{
			sValue += 	' ' + this.oAMPM.getElementValue();
		}
		
		return sValue;
	},
	
	setRenderMode	: function(bRenderMode)
	{
		this.bRenderMode	= bRenderMode;
		
		for (var i = 0; i < this.aControls.length; i++)
		{
			this.aControls[i].setRenderMode(bRenderMode);
		}
		
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
	
	getHourOptions	: function(iFormat, fnCallback)
	{
		// Determine hour range, from format
		var iMinHour	= null;
		var iMaxHour	= null;
		switch (iFormat)
		{
			case Control_Field_Combo_Time.FORMAT_12_HOUR:
				iMinHour	= 1;
				iMaxHour	= 12;
				break;
			case Control_Field_Combo_Time.FORMAT_24_HOUR:
				iMinHour	= 0;
				iMaxHour	= 23;
				break;
		}
		
		var aOptions	= [];
		var sHour		= null;
		for (var iHour = iMinHour; iHour <= iMaxHour; iHour++)
		{
			sHour	= (iHour < 10 ? '0' + iHour : iHour);
			aOptions.push(
				$T.option({value: iHour},
					sHour
				)
			);
		}
		
		fnCallback(aOptions);
	},
	
	getMinuteOptions	: function(fnCallback)
	{
		var aOptions	= [];
		var sMinute		= null; 
		for (var iMinute = 0; iMinute <= 60; iMinute++)
		{
			sMinute	= (iMinute < 10 ? '0' + iMinute : iMinute);
			aOptions.push(
				$T.option({value: iMinute},
					sMinute
				)
			);
		}

		fnCallback(aOptions);
	},
	
	getAMPMOptions	: function(fnCallback)
	{
		var aOptions	=	[
								$T.option({value: Control_Field_Combo_Time.AMPM_AM},
									Control_Field_Combo_Time.AMPM_AM
								),
								$T.option({value: Control_Field_Combo_Time.AMPM_PM},
									Control_Field_Combo_Time.AMPM_PM
								)
		            	  	];
		fnCallback(aOptions);
	},
	
	childSelectUpdated	: function()
	{
		this.validate();
		
		if (this._fnOnUpdate)
		{
			this._fnOnUpdate();	
		}
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
		var aValueSplit	= mValue.match(Control_Field_Combo_Time.VALUE_REGEX);
		var iHours		= parseInt(aValueSplit[1]);
		var iMinutes	= parseInt(aValueSplit[2]);
		
		var sHours		= (isNaN(iHours) ? '?' : Control_Field_Combo_Time.padWithZero(iHours));
		var sMinutes	= (isNaN(iMinutes) ? '?' : Control_Field_Combo_Time.padWithZero(iMinutes));
		var sAMPM		= '';
		
		if (this.iFormat == Control_Field_Combo_Time.FORMAT_12_HOUR)
		{
			sAMPM	= (aValueSplit[3] ? aValueSplit[3] : '?');
		}
		
		return sHours + Control_Field_Combo_Time.SEPARATOR + sMinutes + ' ' + sAMPM;
	}/*,
	
	clearValue	: function()
	{
		this.oHour.setElementValue(null);
		this.oMinute.setElementValue(null);
		this.oAMPM.setElementValue(null);
	}*/
});

Control_Field_Combo_Time.padWithZero	= function(iNumber)
{
	if ((iNumber != null) && (typeof iNumber != 'undefined'))
	{
		iNumber	= parseInt(iNumber);
		return (iNumber < 10 ? '0' + iNumber : iNumber.toString());
	}
	
	return null;
}

Control_Field_Combo_Time.FORMAT_12_HOUR	= 1;
Control_Field_Combo_Time.FORMAT_24_HOUR	= 2;

Control_Field_Combo_Time.AMPM_AM		= 'AM';
Control_Field_Combo_Time.AMPM_PM		= 'PM';

Control_Field_Combo_Time.SEPARATOR		= ':';

Control_Field_Combo_Time.VALUE_REGEX	= /^(\d{2}):(\d{2})?[:\d\s]*(AM|PM)?/;

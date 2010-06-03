

var Control_Field_Combo_Time	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, iFormat)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		this.oHours		= new Control_Field_Select('Hours', '');
		this.oMinutes	= new Control_Field_Select('Minutes', '');
		this.oAMPM		= new Control_Field_Select('AM/PM', '');
		
		this.aControls	= [];
		this.iFormat	= (iFormat ? iFormat : Control_Field_Combo_Time.FORMAT_12_HOUR);
		
		switch (this.iFormat)
		{
			case Control_Field_Combo_Time.FORMAT_12_HOUR:
				this.aControls	= [this.oHours, this.oMinutes, this.oAMPM];
				break;
			
			case Control_Field_Combo_Time.FORMAT_24_HOUR:
				this.aControls	= [this.oHours, this.oMinutes];
				break;
		}
		
		var oUL	= $T.ul({class: 'reset horizontal control-field-combo-date'});
		this.oControlOutput.oElement.appendChild(oUL);
		
		// Add css classes to the selects
		this.oHours.getElement().addClassName('control-field-combo-date-day');
		this.oMinutes.getElement().addClassName('control-field-combo-date-month');
		this.oAMPM.getElement().addClassName('control-field-combo-date-year');
		
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
				$T.li(
					oControl.getElement()
				)
			);
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
		var aValueSplit	= mValue.split(Control_Field_Combo_Time.SEPARATOR);
		var iHours		= parseInt(aValueSplit[0]).toString();
		
		switch (this.iFormat)
		{
			case Control_Field_Combo_Time.FORMAT_12_HOUR:
				this.oHours.setValue(iHours == 0 ? 12 : (iHours > 12 ? iHours - 12 : iHours));
				this.oAMPM.setValue(iHours > 12 ? Control_Field_Combo_Time.AM : Control_Field_Combo_Time.PM);
				break;
			
			case Control_Field_Combo_Time.FORMAT_24_HOUR:
				this.oHours.setValue(iHours);
				break;
		}
		
		this.oMinutes.setValue(parseInt(aValueSplit[1]).toString());
	},
	
	getElementValue	: function()
	{
		var sValue = 	this.oHours.getElementValue() 		+ 
						Control_Field_Combo_Date.SEPARATOR 	+ 
						this.oMinutes.getElementValue() 	+ 
						' ' + this.oAMPM.getElementValue();
		
		if (this.iFormat == Control_Field_Combo_Time.FORMAT_24_HOUR)
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
	
	setYearRange	: function(iMinYear, iMaxYear)
	{
		this.iMinYear	= iMinYear;
		this.iMaxYear	= iMaxYear;
		
		this.oHours.setPopulateFunction(this.getHourOptions.bind(this), true);
		this.oMinutes.setPopulateFunction(this.getMinuteOptions.bind(this), true);
		this.oAMPM.setPopulateFunction(this.getAMPMOptions.bind(this), true);
	},
	
	getHourOptions	: function(fnCallback)
	{
		// Determine hour range, from format
		var iMinHour	= null;
		var iMaxHour	= null;
		switch (this.iFormat)
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
			sHour	= (iMinute < 10 ? '0' + iMinute : iMinute);
			aOptions.push(
				$T.option({value: sHour},
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
				$T.option({value: sMinute},
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
		var aValueSplit	= mValue.split(Control_Field_Combo_Time.SEPARATOR);
		var iHours		= parseInt(aValueSplit[0]);
		var iMinutes	= parseInt(aValueSplit[1]);
		
		var sHours		= (isNaN(iHours) ? '?' : iHours);
		var sMinutes	= (isNaN(iMinutes) ? '?' : iMinutes);
		var sAMPM		= '';
		
		if (this.iFormat == Control_Field_Combo_Time.FORMAT_12_HOUR)
		{
			sAMPM	= (aValueSplit[2] ? aValueSplit[2] : '?');
		}
		
		return sHours + Control_Field_Combo_Time.SEPARATOR + sMinutes + ' ' + sAMPM;
	}
});

Control_Field_Combo_Time.FORMAT_12_HOUR	= 1;
Control_Field_Combo_Time.FORMAT_24_HOUR	= 2;

Control_Field_Combo_Time.AMPM_AM	= 'AM';
Control_Field_Combo_Time.AMPM_PM	= 'PM';

Control_Field_Combo_Time.SEPARATOR		= ':';


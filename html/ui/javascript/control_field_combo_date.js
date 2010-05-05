

var Control_Field_Combo_Date	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, iFormat)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		this.oDay	= new Control_Field_Select('Day', '');
		this.oMonth	= new Control_Field_Select('Month', '');
		this.oYear	= new Control_Field_Select('Year', '');
		
		this.aControls	= [];
		this.iFormat	= (iFormat ? iFormat : Control_Field_Combo_Date.FORMAT_D_M_Y);
		
		switch (this.iFormat)
		{
			case Control_Field_Combo_Date.FORMAT_D_M_Y:
				this.aControls	= [this.oDay, this.oMonth, this.oYear];
				break;
			
			case Control_Field_Combo_Date.FORMAT_M_Y:
				this.aControls	= [this.oMonth, this.oYear];
				break;
		}
		
		var oUL	= $T.ul({class: 'reset horizontal control-field-combo-date'});
		this.oControlOutput.oElement.appendChild(oUL);
		
		// Add css classes to the selects
		this.oDay.getElement().addClassName('control-field-combo-date-day');
		this.oMonth.getElement().addClassName('control-field-combo-date-month');
		this.oYear.getElement().addClassName('control-field-combo-date-year');
		
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
		this.oDay.setEditable(bVisible);
		this.oMonth.setEditable(bVisible);
		this.oYear.setEditable(bVisible);
	},
	
	setEditable	: function($super, bEditable)
	{
		$super(bEditable);
		this.oDay.setEditable(bEditable);
		this.oMonth.setEditable(bEditable);
		this.oYear.setEditable(bEditable);
	},
	
	setMandatory	: function($super, bMandatory)
	{
		$super(bMandatory);
		this.oDay.setMandatory(bMandatory);
		this.oMonth.setMandatory(bMandatory);
		this.oYear.setMandatory(bMandatory);
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
		
		var aValueSplit	= mValue.split('-');
		this.oDay.setValue(parseInt(aValueSplit[2]).toString());
		this.oMonth.setValue(parseInt(aValueSplit[1]).toString());
		this.oYear.setValue(parseInt(aValueSplit[0]).toString());
		
		this.updateElementValue(true);
		this.validate();
	},
	
	setElementValue	: function(mValue)
	{
		var aValueSplit	= mValue.split('-');
		this.oDay.setElementValue(parseInt(aValueSplit[2]).toString());
		this.oMonth.setElementValue(parseInt(aValueSplit[1]).toString());
		this.oYear.setElementValue(parseInt(aValueSplit[0]).toString());
	},
	
	getElementValue	: function()
	{
		var aValues	= [];
		
		// Iterate in reverse to get yyyy-mm-dd, that is the value format
		var sValue		= null;
		var iValueCount	= null;
		for (var i = this.aControls.length - 1; i >= 0; i--)
		{
			sValue	= this.aControls[i].getElementValue();
			
			if (sValue)
			{
				iValueCount++;
			}
			
			aValues.push(sValue);
		}
		
		// Return empty string if there are no values
		return (iValueCount ? aValues.join(Control_Field_Combo_Date.DATE_SEPARATOR) : '');
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
		
		this.oDay.setPopulateFunction(this.getDayOptions.bind(this), true);
		this.oMonth.setPopulateFunction(this.getMonthOptions.bind(this), true);
		this.oYear.setPopulateFunction(this.getYearOptions.bind(this), true);
	},
	
	getDayOptions	: function(fnCallback)
	{
		var aOptions	= [];
		
		for (var iDay = 1; iDay <= 31; iDay++)
		{
			aOptions.push(
				$T.option({value: iDay},
					iDay
				)
			);
		}

		fnCallback(aOptions);
	},
	
	getMonthOptions	: function(fnCallback)
	{
		var aOptions	= [];
		
		for (var iMonth = 1; iMonth <= 12; iMonth++)
		{
			aOptions.push(
				$T.option({value: iMonth},
					iMonth
				)
			);
		}

		fnCallback(aOptions);
	},
	
	getYearOptions	: function(fnCallback)
	{
		var aOptions	= [];
		
		for (var iYear = this.iMinYear; iYear <= this.iMaxYear; iYear++)
		{
			aOptions.push(
				$T.option({value: iYear},
					iYear
				)
			);
		}
		
		fnCallback(aOptions);
	},
	
	childSelectUpdated	: function()
	{
		this.validate();
	},
	
	// Redefined, to allow formatted date output in validatation error text
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
		var aSplit	= mValue.split('-');
		var sDate	= mValue;
		var iMonth	= parseInt(aSplit[1]);
		var sMonth	= (iMonth < 10 ? '0' + iMonth : iMonth);
		
		switch (this.iFormat)
		{
			case Control_Field_Combo_Date.FORMAT_D_M_Y:
				var iDay	= parseInt(aSplit[2]);
				sDate		= (iDay < 10 ? '0' + iDay : iDay) + '/' + sMonth + '/' + aSplit[0];
				break;
			
			case Control_Field_Combo_Date.FORMAT_M_Y:
				sDate	= sMonth + '/' + aSplit[0];
				break;
		}
		
		return sDate;
	}
});

Control_Field_Combo_Date.FORMAT_D_M_Y	= 1;
Control_Field_Combo_Date.FORMAT_M_Y		= 2;

Control_Field_Combo_Date.DATE_SEPARATOR	= '-';


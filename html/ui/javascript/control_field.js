var Control_Field	= Class.create
({
	initialize	: function(sLabel, sLabelSeparator)
	{
		this.sLabel				= sLabel;
		this.sLabelSeparator	= (sLabelSeparator) ? sLabelSeparator : '&nbsp;:';
		
		// Set a default value (this should be overwritten pretty much immediately)
		this.mDefaultValue	= '';
		this.mValue			= this.mDefaultValue;
		
		this.sValidationReason			= null;
		this.bValidationStylingEnabled	= true;
		this.bValidateField			= false;
		
		// Create DOM Objects
		this.oControlOutput							= {};
		this.oControlOutput.oElement				= $T.div({'class':'control-field'});
		this.oControlOutput.oElement.oControlField	= this;
		this.oControlOutput.oValidationError		= {
			oElement	: $T.div({'class':'control-field-validation-error'})
		}
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oValidationError.oElement);

		this.oControlOutput.oElement.observe('mousemove', this._positionValidationError.bind(this));
		
		this.bInit	= false;
	},
	
	getElement	: function()
	{
		return this.oControlOutput.oElement;
	},
	
	updateElementValue	: function(bolUseInternalValue)
	{
		throw "OO Error: Control_Field::updateElementValue() is an unimplemented Virtual Method!";
	},
	
	disableInput	: function()
	{
		throw "OO Error: Control_Field::disableInput() is an unimplemented Virtual Method!";
	},
	
	enableInput	: function()
	{
		throw "OO Error: Control_Field::enableInput() is an unimplemented Virtual Method!";
	},
	
	update	: function()
	{
		// Update value
		this.updateElementValue();
	},
	
	// Value
	setValue	: function(mValue)
	{
		//alert(this.sLabel+" is being set to '" + mValue + "'");
		
		this.mValue	= mValue;
		this.updateElementValue(true);
		
		// Make sure we update the Control(s)
		this.validate();
	},
	
	setElementValue	: function(mValue)
	{
		throw "OO Error: Control_Field::setElementValue() is an unimplemented Virtual Method!";
	},
	
	getElementValue	: function()
	{
		throw "OO Error: Control_Field::getElementValue() is an unimplemented Virtual Method!";
	},
	
	addOnChangeCallback	: function()
	{
		throw "OO Error: Control_Field::addOnChangeCallback() is an unimplemented Virtual Method!";
	},
	
	getValue	: function(bImplicitSave)
	{
		if (bImplicitSave)
		{
			this.save(true);
		}
		return this.mValue;
	},
	
	getLabel	: function(bAppendSeparator)
	{
		return ((this.sLabel != '') ? this.sLabel + (bAppendSeparator ? this.sLabelSeparator : '') : '');
	},
	
	// Visibility
	setVisible	: function(mVisible)
	{
		this.mVisible	= mVisible;
		if (typeof this.mVisible == 'function')
		{
			// Callback
			this.mVisible()?this.getElement().style.display = '': this.getElement().style.display = 'none';
		}
		else
		{
			this.mVisible?this.getElement().style.display = '': this.getElement().style.display = 'none';
		}
	},
	
	isVisible	: function()
	{
		if (typeof this.mVisible == 'function')
		{
			// Callback
			return (this.mVisible()) ? true : false;
		}
		else
		{
			return (this.mVisible) ? true : false;
		}
	},
	
	// Max Length
	setMaxLength	: function(iMaxLength)
	{
		this.iMaxLength	= iMaxLength;
	},
	
	getMaxLength	: function()
	{
		return this.iMaxLength;
	},
	
	// Population (SELECTs only)
	setPopulateFunction	: function()
	{
		throw "OO Error: Control_Field::setPopulateFunction() is an unimplemented Virtual Method!";
	},
	
	populate	: function()
	{
		throw "OO Error: Control_Field::populate() is an unimplemented Virtual Method!";
	},
	
	setAutoTrim	: function(mAutoTrim)
	{
		this.mAutoTrim	= mAutoTrim;
		this.validate();
	},
	
	trim	: function(bReturnValue)
	{
		// Perform Trim
		var mValue			= this.getElementValue();
		var mOriginalValue	= mValue;
		if (typeof this.mAutoTrim == 'function')
		{
			// Callback
			mValue	= this.mAutoTrim(mValue);
		}
		else if (this.mAutoTrim)
		{
			mValue	= mValue.replace(/(^\s*|\s*$)/g, '');
		}
		
		// Return or set Value
		if (bReturnValue)
		{
			return mValue;
		}
		else if (mValue != mOriginalValue)
		{
			this.setElementValue(mValue);
		}
	},

	setEditable	: function(mEditable)
	{
		this.mEditable	= mEditable;
		this.validate();
	},
	
	getEditable	: function()
	{
		if (typeof this.mEditable == 'function')
		{
			// Callback
			return (this.mEditable()) ? true : false;
		}
		else
		{
			return (this.mEditable) ? true : false;
		}
	},
	
	isEditable	: function()
	{
		return (this.getRenderMode() === Control_Field.RENDER_MODE_EDIT && this.getEditable()) ? true : false;
	},

	setRenderMode	: function(bRenderMode)
	{
		this.bRenderMode	= bRenderMode;
		
		// Update Render Mode
		if (this.isEditable())
		{
			this.oControlOutput.oEdit.removeClassName('hide');
			this.oControlOutput.oView.addClassName('hide');
		}
		else
		{
			this.oControlOutput.oEdit.addClassName('hide');
			this.oControlOutput.oView.removeClassName('hide');
		}
		
		if (bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.setElementValue(this.getValue());
		}
		
		this.validate();
	},
	
	getRenderMode	: function()
	{
		return this.bRenderMode;
	},
	
	setMandatory	: function(mMandatory)
	{
		this.mMandatory	= mMandatory;
		this.validate();
	},
	
	isMandatory	: function()
	{
		if (typeof this.mMandatory == 'function')
		{
			// Callback
			return (this.mMandatory()) ? true : false;
		}
		else
		{
			return (this.mMandatory) ? true : false;
		}
	},
	
	// Validation
	setValidateFunction	: function(fnValidate, bValidateField)
	{
		this.bValidateField	= (bValidateField === true) ? true : false;
		this.fnValidate			= fnValidate;
		this.validate();
	},
	
	isValid	: function(bSuppressException)
	{
		if (typeof this.fnValidate == 'function')
		{
			// Callback
			try {
				return !!this.fnValidate(this.bValidateField ? this : this.getElementValue());
			} catch (mException) {
				if (bSuppressException === false) {
					throw mException;
				} else {
					return false;
				}
			}
		}
		else
		{
			return true;
		}
	},
	
	validate	: function(bSilentFail)
	{
		// Default to silent fail if not specifically set to false
		if (bSilentFail !== false)
		{
			bSilentFail	= true;
		}

		this.setValidationReason('');
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
				var	bValid,
					sValidationReason;
				try {
					bValid	= this.isValid(false);
				} catch (mException) {
					bValid	= false;

					if (typeof mException === 'string') {
						sValidationReason	= mException;
					} else {
						sValidationReason	= mException.toString();
					}
				}

				if (bValid)
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

					sValidationReason	= sValidationReason ? sValidationReason : this.getValidationReason();
					this.setValidationReason(sValidationReason);
					var	sValidationMessage	= "'" + mElementValue + "' is not a valid " + this.getLabel() + ". " + sValidationReason;
					if (bSilentFail)
					{
						return false;
					}
					else
					{
						throw sValidationMessage;
					}
				}
			}
			else if (this.isMandatory())
			{
				if (this.bValidationStylingEnabled)
				{
					this.oControlOutput.oElement.addClassName('mandatory');
				}

				this.setValidationReason("No value supplied for mandatory field");
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
	
	revert	: function()
	{
		this.setElementValue(this.getValue());
	},
	
	save	: function(bCommit)
	{
		if (this.validate())
		{
			if (bCommit)
			{
				this.setValue(this.getElementValue());
				return this.getValue();
			}
		}
		else
		{
			throw "Saving an invalid value!";
		}
	},
	
	generateInputTableRow	: function(bRenderMode)
	{
		if (bRenderMode !== undefined)
		{
			this.setRenderMode(bRenderMode);
		}
		
		// TODO: Replace styling with Classes
		// TODO: Redo with $T
		var oTR						= {};
		oTR.oTH						= {};
		oTR.oTD						= {};
		oTR.oTD.oOutputDIV			= {};
		oTR.oTD.oOutputDIV.oOutput	= {};
		oTR.oTD.oDescription		= {};
		
		oTR.oElement	= document.createElement('tr');
		
		oTR.oTH.oElement				= document.createElement('th');
		oTR.oTH.oElement.innerHTML	= this.getLabel(true);
		oTR.oElement.appendChild(oTR.oTH.oElement);
		
		oTR.oTD.oElement	= document.createElement('td');
		oTR.oElement.appendChild(oTR.oTD.oElement);
		
		oTR.oTD.oOutputDIV.oElement	= document.createElement('div');
		oTR.oTD.oElement.appendChild(oTR.oTD.oOutputDIV.oElement);
		
		oTR.oTD.oOutputDIV.oOutput.oElement	= this.getElement();
		oTR.oTD.oElement.appendChild(oTR.oTD.oOutputDIV.oOutput.oElement);
		
		if (this.strDescription != undefined)
		{
			oTR.oTD.oDescription.oElement					= document.createElement('div')
			oTR.oTD.oDescription.oElement.style.color		= "#666";
			oTR.oTD.oDescription.oElement.style.fontStyle	= "italic";
			oTR.oTD.oDescription.oElement.style.fontSize	= "0.8em";
			oTR.oTD.oDescription.oElement.innerHTML			= this.strDescription;
			oTR.oTD.oElement.appendChild(oTR.oTD.oDescription.oElement);
		}
		
		return oTR;
	},
	
	setValidationReason	: function(sReason)
	{
		this.sValidationReason	= String(sReason);
		this.oControlOutput.oValidationError.oElement.innerHTML	= this.sValidationReason.escapeHTML();
	},
	
	getValidationReason	: function()
	{
		if (this.sValidationReason)
		{
			return ' ' + this.sValidationReason;
		}
		
		return '';
	},

	_positionValidationError	: function (oEvent) {
		//debugger;
		if (this.sValidationReason && this.sValidationReason.length/* && oEvent.findElement() === this.oControlOutput.oElement*/) {
			//debugger;
			this.oControlOutput.oValidationError.oElement.setStyle({
				top		: oEvent.clientY+(Object.isNumber(Control_Field.VALIDATION_TOOLTIP_OFFSET_Y) ? Control_Field.VALIDATION_TOOLTIP_OFFSET_Y : 0)+'px',
				left	: oEvent.clientX+(Object.isNumber(Control_Field.VALIDATION_TOOLTIP_OFFSET_X) ? Control_Field.VALIDATION_TOOLTIP_OFFSET_X : 0)+'px'
			});
		}
	},
	
	disableValidationStyling	: function()
	{
		this.bValidationStylingEnabled	= false;
	},
	
	clearValue	: function()
	{
		// Set not value to the element
		this.setElementValue(null);
		
		// Remove all classes
		this.oControlOutput.oElement.removeClassName('invalid');
		this.oControlOutput.oElement.removeClassName('valid');
		this.oControlOutput.oElement.removeClassName('mandatory');
	}
});

// Class Constants
Control_Field.RENDER_MODE_VIEW	= false;
Control_Field.RENDER_MODE_EDIT	= true;

Control_Field.VALIDATION_TOOLTIP_OFFSET_X	= 12;
Control_Field.VALIDATION_TOOLTIP_OFFSET_Y	= 4;

Control_Field.getError	= function(oControl)
{
	try 
	{
		if (oControl.validate(false))
		{
			return null;
		}
	}
	catch(ex)
	{
		return ex;
	}
};

// Static Functions
Control_Field.factory	= function(sType, oDefinition)
{
	var oControlField;
	
	// Determine type
	switch (sType.toLowerCase().replace('-', '_'))
	{
		
		case 'checkbox':
			oControlField	= new Control_Field_Checkbox(oDefinition.sLabel);
			break;

		case 'radio':
		case 'radiobutton':
			oControlField	= new Control_Field_RadioButton(oDefinition.sLabel);
			if (oDefinition.sFieldName)
			{
				oControlField.setFieldName(oDefinition.sFieldName);
			}
			break;
		
		case 'date_picker':
			oControlField	= 	new Control_Field_Date_Picker(
									oDefinition.sLabel, 
									null, 
									oDefinition.sDateFormat, 
									oDefinition.bTimePicker,
									oDefinition.iYearStart,
									oDefinition.iYearEnd
								);
			break;
			
		case 'hidden':
			oControlField	= new Control_Field_Hidden(oDefinition.sLabel);
			break;
			
		case 'password_confirm':
		case 'password-confirm':
		case 'password confirm':
		case 'passwordconfirm':
			oControlField	= new Control_Field_Password_Confirm(oDefinition.sLabel);
			oControlField.setMaxLength(oDefinition.iMaxLength ? oDefinition.iMaxLength : false);
			break;
			
		case 'password':
			oControlField	= new Control_Field_Password(oDefinition.sLabel);
			oControlField.setMaxLength(oDefinition.iMaxLength ? oDefinition.iMaxLength : false);
			break;
			
		case 'radio-group':
		case 'radio_group':
		case 'radio group':
		case 'radiogroup':
			oControlField	= new Control_Field_RadioGroup(oDefinition.sLabel);
			oControlField.setPopulateFunction(oDefinition.fnPopulate);
			break;
		
		case 'select':
			oControlField	= new Control_Field_Select(oDefinition.sLabel);
			oControlField.setPopulateFunction(oDefinition.fnPopulate);
			break;
			
		case 'text':
			oControlField	= new Control_Field_Text(oDefinition.sLabel);
			oControlField.setMaxLength(oDefinition.iMaxLength ? oDefinition.iMaxLength : false);
			oControlField.setAutoTrim(oDefinition.mAutoTrim ? oDefinition.mAutoTrim : false);
			break;
		
		case 'hidden':
			oControlField = new Control_Field_Hidden(oDefinition.sLabel);
			break;
			
		case 'textarea':
			oControlField	= new Control_Field_Textarea(oDefinition.sLabel, oDefinition.sLabelSeparator, oDefinition.rows, oDefinition.cols);
			break;
		case 'password_change':
			oControlField	= new Control_Field_Password_Change(oDefinition.sLabel);
			oControlField.setMaxLength(oDefinition.iMaxLength ? oDefinition.iMaxLength : false);
			break;
		
		case 'combo_date':
		case 'combo-date':
			oControlField	= new Control_Field_Combo_Date(oDefinition.sLabel, null, oDefinition.iFormat, oDefinition.mSeparatorElement);
			oControlField.setYearRange(oDefinition.iMinYear, oDefinition.iMaxYear);
			break;
		
		case 'combo_time':
		case 'combo-time':
			oControlField	= new Control_Field_Combo_Time(oDefinition.sLabel, null, oDefinition.iFormat, oDefinition.mSeparatorElement);
			break;
		
		case 'text_ajax':
			oControlField	= new Control_Field_Text_AJAX(oDefinition.sLabel, null, oDefinition.oDatasetAjax, oDefinition.sDisplayValueProperty, oDefinition.oColumnProperties, oDefinition.iResultLimit, oDefinition.sResultPaneClass);
			break;

		case 'number':
			oControlField	= new Control_Field_Number(oDefinition.sLabel);
			oControlField.setMinimumValue(oDefinition.fMinimumValue);
			oControlField.setMaximumValue(oDefinition.fMaximumValue);
			oControlField.setDecimalPlaces(oDefinition.iDecimalPlaces);
			oControlField.setAutoTrim(oDefinition.mAutoTrim ? oDefinition.mAutoTrim : false);
			break;
			
		default:
			throw "'" + sType + "' is not a valid Control_Field type!";
			break;
	}
	
	// Set common properties
	oControlField.setVisible((oDefinition.mVisible === false || oDefinition.mVisible) ? oDefinition.mVisible : true);
	oControlField.setEditable(oDefinition.mEditable ? oDefinition.mEditable : false);
	oControlField.setMandatory(oDefinition.mMandatory ? oDefinition.mMandatory : false);
	
	if (oDefinition.bDisableValidationStyling)
	{
		oControlField.disableValidationStyling();
	}
	
	if (oDefinition.fnValidate)
	{
		oControlField.setValidateFunction(oDefinition.fnValidate, !!oDefinition.bValidateField);
	}
	
	if (oDefinition.sValidationReason)
	{
		oControlField.setValidationReason(oDefinition.sValidationReason);
	}

	if (typeof oDefinition.mValue !== 'undefined') {
		oControlField.setValue(oDefinition.mValue);
	}

	if (oDefinition.sExtraClass) {
		oControlField.getElement().addClassName(oDefinition.sExtraClass);
	}

	oControlField.setRenderMode(!!oDefinition.bRenderMode);
	
	return oControlField;
};
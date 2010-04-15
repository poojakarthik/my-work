var Control_Field	= Class.create
({
	initialize	: function(sLabel, sLabelSeparator)
	{
		this.sLabel				= sLabel;
		this.sLabelSeparator	= (sLabelSeparator) ? sLabelSeparator : '&nbsp;:';
		
		// Set a default value (this should be overwritten pretty much immediately)
		this.mDefaultValue	= '';
		this.mValue			= this.mDefaultValue;
		
		// Create DOM Objects
		this.oControlOutput				= {};
		this.oControlOutput.oElement	= document.createElement('div');
		
		this.bInit	= false;
	},
	
	getElement	: function()
	{
		return this.oControlOutput.oElement;
	},
	
	getElementValue	: function()
	{
		throw "OO Error: Control_Field::getElementValue() is an unimplemented Virtual Method!";
	},
	
	updateElementValue	: function(bolUseInternalValue)
	{
		throw "OO Error: Control_Field::updateElementValue() is an unimplemented Virtual Method!";
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
	
	getValue	: function(bImplicitSave)
	{
		if (bImplicitSave)
		{
			this.save();
		}
		return this.mValue;
	},
	
	getLabel	: function(bAppendSeparator)
	{
		return this.sLabel + (bAppendSeparator ? this.sLabelSeparator : '');
	},
	
	// Visibility
	setVisible	: function(mVisible)
	{
		this.mVisible	= mVisible;
		//this.updateElementValue();
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
		var mValue	= this.getElementValue();
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
		else
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
	setValidateFunction	: function(fnValidate)
	{
		this.fnValidate	= fnValidate;
		this.validate();
	},
	
	isValid	: function()
	{
		if (typeof this.fnValidate == 'function')
		{
			// Callback
			return (this.fnValidate(this.getElementValue())) ? true : false;
		}
		else
		{
			return true;
		}
	},
	
	validate	: function()
	{
		var mReturn	= false;
		
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
					bReturn	= true;
					this.oControlOutput.oElement.addClassName('valid');
				}
				else
				{
					this.oControlOutput.oElement.addClassName('invalid');
					mReturn	= "'"+mElementValue+"' is not a valid "+this.getLabel();
				}
			}
			else if (this.isMandatory())
			{
				this.oControlOutput.oElement.addClassName('mandatory');
				mReturn	= "No value supplied for mandatory field "+this.getLabel();
			}
			else
			{
				bReturn	= true;
			}
		}
		else
		{
			bReturn	= true;
		}
		
		//this.updateElementValue();
		return bReturn;
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
	}
});

// Class Constants
Control_Field.RENDER_MODE_VIEW	= false;
Control_Field.RENDER_MODE_EDIT	= true;

// Static Functions
Control_Field.factory	= function(sType, oDefinition)
{
	var oControlField;
	
	// Determine type
	switch (sType.toLowerCase())
	{
		case 'checkbox':
			oControlField	= new Control_Field_Checkbox(oDefinition.sLabel);
			break;
			
		case 'date-picker':
			oControlField	= new Control_Field_Date_Picker(oDefinition.sLabel);
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
			
		default:
			throw "'" + sType + "' is not a valid Control_Field type!";
			break;
	}
	
	// Set common properties
	oControlField.setVisible(oDefinition.mVisible ? oDefinition.mVisible : false);
	oControlField.setEditable(oDefinition.mEditable ? oDefinition.mEditable : false);
	oControlField.setMandatory(oDefinition.mMandatory ? oDefinition.mMandatory : false);
	
	if (oDefinition.fnValidate)
	{
		oControlField.setValidateFunction(oDefinition.fnValidate);
	}
	
	return oControlField;
};
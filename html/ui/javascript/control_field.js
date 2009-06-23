var Control_Field	= Class.create
({
	initialize	: function(strLabel, strLabelSeparator)
	{
		this.strLabel			= strLabel;
		this.strLabelSeparator	= (strLabelSeparator) ? strLabelSeparator : '&nbsp;:';
		
		this.mixDefaultValue	= '';
		
		// Create DOM Objects
		this.objControlOutput				= {};
		this.objControlOutput.domElement	= document.createElement('div');
		
		this.bolInit	= false;
	},
	
	getElement	: function()
	{
		return this.objControlOutput.domElement;
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
	setValue	: function(mixValue)
	{
		//alert(this.strLabel+" is being set to '" + mixValue + "'");
		
		this.mixValue			= mixValue;
		this.updateElementValue(true);
		
		// Make sure we update the Control(s)
		this.validate();
	},
	
	setElementValue	: function(mixValue)
	{
		throw "OO Error: Control_Field::setElementValue() is an unimplemented Virtual Method!";
	},
	
	getElementValue	: function()
	{
		throw "OO Error: Control_Field::getElementValue() is an unimplemented Virtual Method!";
	},
	
	getValue	: function(bolImplicitSave)
	{
		if (bolImplicitSave)
		{
			this.save();
		}
		return this.mixValue;
	},
	
	getLabel	: function(bolAppendSeparator)
	{
		return this.strLabel + (bolAppendSeparator ? this.strLabelSeparator : '');
	},
	
	// Visibility
	setVisible	: function(mixVisible)
	{
		this.mixVisible	= mixVisible;
		//this.updateElementValue();
	},
	
	isVisible	: function()
	{
		if (typeof this.mixVisible == 'function')
		{
			// Callback
			return (this.mixVisible()) ? true : false;
		}
		else
		{
			return (this.mixVisible) ? true : false;
		}
	},
	
	// Max Length
	setMaxLength	: function(intMaxLength)
	{
		this.intMaxLength	= intMaxLength;
	},
	
	getMaxLength	: function()
	{
		return this.intMaxLength;
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
	
	setAutoTrim	: function(mixAutoTrim)
	{
		this.mixAutoTrim	= mixAutoTrim;
		this.validate();
	},
	
	trim	: function(bolReturnValue)
	{
		// Perform Trim
		var mixValue	= this.getElementValue();
		if (typeof this.mixAutoTrim == 'function')
		{
			// Callback
			mixValue	= this.mixAutoTrim(mixValue);
		}
		else if (this.mixAutoTrim)
		{
			mixValue	= mixValue.replace(/(^.*|.*$)/, '');
		}
		
		// Return or set Value
		if (bolReturnValue)
		{
			return mixValue;
		}
		else
		{
			this.setElementValue(mixValue);
		}
	},

	setEditable	: function(mixEditable)
	{
		this.mixEditable	= mixEditable;
		this.validate();
	},
	
	getEditable	: function()
	{
		if (typeof this.mixEditable == 'function')
		{
			// Callback
			return (this.mixEditable()) ? true : false;
		}
		else
		{
			return (this.mixEditable) ? true : false;
		}
	},
	
	isEditable	: function()
	{
		return (this.getRenderMode() === Control_Field.RENDER_MODE_EDIT && this.getEditable()) ? true : false;
	},

	setRenderMode	: function(bolRenderMode)
	{
		this.bolRenderMode	= bolRenderMode;
		
		// Update Render Mode
		if (this.isEditable())
		{
			this.objControlOutput.domEdit.removeClassName('hide');
			this.objControlOutput.domView.addClassName('hide');
		}
		else
		{
			this.objControlOutput.domEdit.addClassName('hide');
			this.objControlOutput.domView.removeClassName('hide');
		}
		
		this.validate();
	},
	
	getRenderMode	: function()
	{
		return this.bolRenderMode;
	},
	
	setMandatory	: function(mixMandatory)
	{
		this.mixMandatory	= mixMandatory;
		this.validate();
	},
	
	isMandatory	: function()
	{
		if (typeof this.mixMandatory == 'function')
		{
			// Callback
			return (this.mixMandatory()) ? true : false;
		}
		else
		{
			return (this.mixMandatory) ? true : false;
		}
	},
	
	// Validation
	setValidateFunction	: function(fncValidate)
	{
		this.fncValidate	= fncValidate;
		this.validate();
	},
	
	isValid	: function()
	{
		if (typeof this.fncValidate == 'function')
		{
			// Callback
			return (this.fncValidate(this.getElementValue())) ? true : false;
		}
		else
		{
			return true;
		}
	},
	
	validate	: function()
	{
		var bolReturn	= false;
		if (this.isEditable())
		{
			// Preprocess (trim)
			this.trim();
			
			this.objControlOutput.domElement.removeClassName('invalid');
			this.objControlOutput.domElement.removeClassName('valid');
			this.objControlOutput.domElement.removeClassName('mandatory');
			
			if (this.getElementValue())
			{
				if (this.isValid())
				{
					bolReturn	= true;
					this.objControlOutput.domElement.addClassName('valid');
				}
				else
				{
					this.objControlOutput.domElement.addClassName('invalid');
				}
			}
			else if (this.isMandatory())
			{
				this.objControlOutput.domElement.addClassName('mandatory');
			}
			else
			{
				bolReturn	= true;
			}
		}
		else
		{
			bolReturn	= true;
		}

		//this.updateElementValue();
		return bolReturn;
	},
	
	revert	: function()
	{
		this.setElementValue(this.getValue());
	},
	
	save	: function()
	{
		if (this.validate())
		{
			this.setValue(this.getElementValue());
			return this.getValue();
		}
		else
		{
			throw "Saving an invalid value!";
		}
	},
	
	generateInputTableRow	: function(bolRenderMode)
	{
		if (bolRenderMode !== undefined)
		{
			this.setRenderMode(bolRenderMode);
		}
		
		// TODO: Replace styling with Classes
		var objTR							= {};
		objTR.objTH							= {};
		objTR.objTD							= {};
		objTR.objTD.objOutputDIV			= {};
		objTR.objTD.objOutputDIV.objOutput	= {};
		objTR.objTD.objDescription			= {};
		
		objTR.domElement		= document.createElement('tr');
		
		objTR.objTH.domElement						= document.createElement('th');
		objTR.objTH.domElement.innerHTML			= this.getLabel(true);
		objTR.domElement.appendChild(objTR.objTH.domElement);
		
		objTR.objTD.domElement	= document.createElement('td');
		objTR.domElement.appendChild(objTR.objTD.domElement);
		
		objTR.objTD.objOutputDIV.domElement	= document.createElement('div');
		objTR.objTD.domElement.appendChild(objTR.objTD.objOutputDIV.domElement);
		
		objTR.objTD.objOutputDIV.objOutput.domElement	= this.getElement();
		objTR.objTD.domElement.appendChild(objTR.objTD.objOutputDIV.objOutput.domElement);
		
		if (this.strDescription != undefined)
		{
			objTR.objTD.objDescription.domElement					= document.createElement('div')
			objTR.objTD.objDescription.domElement.style.color		= "#666";
			objTR.objTD.objDescription.domElement.style.fontStyle	= "italic";
			objTR.objTD.objDescription.domElement.style.fontSize	= "0.8em";
			objTR.objTD.objDescription.domElement.innerHTML			= this.strDescription;
			objTR.objTD.domElement.appendChild(objTR.objTD.objDescription.domElement);
		}
		
		return objTR;
	}
});

// Class Constants
Control_Field.RENDER_MODE_VIEW	= false;
Control_Field.RENDER_MODE_EDIT	= true;

// Static Functions
Control_Field.factory	= function(strType, objDefinition)
{
	var objControlField;
	
	// Determine type
	switch (strType.toLowerCase())
	{
		case 'checkbox':
			objControlField	= new Control_Field_Checkbox(objDefinition.strLabel);
			break;
			
		case 'date':
			objControlField	= new Control_Field_Date(objDefinition.strLabel);
			break;
			
		case 'hidden':
			objControlField	= new Control_Field_Hidden(objDefinition.strLabel);
			break;
			
		case 'password_confirm':
		case 'password-confirm':
		case 'password confirm':
		case 'passwordconfirm':
			objControlField	= new Control_Field_Password_Confirm(objDefinition.strLabel);
			objControlField.setMaxLength(objDefinition.intMaxLength ? objDefinition.intMaxLength : false);
			break;
			
		case 'password':
			objControlField	= new Control_Field_Password(objDefinition.strLabel);
			objControlField.setMaxLength(objDefinition.intMaxLength ? objDefinition.intMaxLength : false);
			break;
			
		case 'radio-group':
		case 'radio_group':
		case 'radio group':
		case 'radiogroup':
			objControlField	= new Control_Field_RadioGroup(objDefinition.strLabel);
			objControlField.setPopulateFunction(objDefinition.fncPopulate);
			break;
			
		case 'select':
			objControlField	= new Control_Field_Select(objDefinition.strLabel);
			objControlField.setPopulateFunction(objDefinition.fncPopulate);
			break;
			
		case 'text':
			objControlField	= new Control_Field_Text(objDefinition.strLabel);
			objControlField.setMaxLength(objDefinition.intMaxLength ? objDefinition.intMaxLength : false);
			objControlField.setAutoTrim(objDefinition.mixAutoTrim ? objDefinition.mixAutoTrim : false);
			break;
			
		default:
			throw "'" + strType + "' is not a valid Control_Field type!";
			break;
	}
	
	// Set common properties
	objControlField.setVisible(objDefinition.mixVisible ? objDefinition.mixVisible : false);
	objControlField.setEditable(objDefinition.mixEditable ? objDefinition.mixEditable : false);
	objControlField.setMandatory(objDefinition.mixMandatory ? objDefinition.mixMandatory : false);
	
	if (objDefinition.fncValidate)
	{
		objControlField.setValidateFunction(objDefinition.fncValidate);
	}
	
	return objControlField;
};
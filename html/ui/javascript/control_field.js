var Control_Field	= Class.create
({
	initialize	: function(strLabel, strLabelSeparator)
	{
		this.strLabel			= strLabel;
		this.strLabelSeparator	= (strLabelSeparator) ? strLabelSeparator : '&nbsp;:&nbsp;';
		
		// Create DOM Objects
		this.objControlOutput				= {};
		this.objControlOutput.domElement	= document.createElement('div');
	},
	
	renderElement	: function(bolRenderMode)
	{
		throw "OO Error: Control_Field::renderElement() is an unimplemented Virtual Method!";
	},
	
	// Value
	setValue	: function(mixValue)
	{
		this.mixValue	= mixValue;
		
		// Make sure we update the Control(s)
		this.update();
	},
	
	getValue	: function()
	{
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
		this.update();
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
		this.update();
	},
	
	trim	: function(bolReturnValue)
	{
		// Perform Trim
		var mixValue	= this.getValue();
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
			this.setValue(mixValue);
		}
	},

	setEditable	: function(mixEditable)
	{
		this.mixEditable	= mixEditable;
		this.update();
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
		this.update();
	},
	
	getRenderMode	: function()
	{
		return this.bolRenderMode;
	},
	
	setMandatory	: function(mixMandatory)
	{
		this.mixMandatory	= mixMandatory;
		this.update();
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
	},
	
	isValid	: function()
	{
		if (typeof this.mixMandatory == 'function')
		{
			// Callback
			return (this.mixMandatory()) ? true : false;
		}
		else
		{
			return true;
		}
	},
	
	generateInputTableRow	: function(bolRenderMode)
	{
		this.setRenderMode(bolRenderMode);
		
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
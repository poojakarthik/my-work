var Control_Field_Password	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, strLabel, strLabelSeparator)
	{
		// Parent
		$super(strLabel, strLabelSeparator);
		
		// Create the DOM Elements
		this.objControlOutput.domEdit		= document.createElement('input');
		this.objControlOutput.domEdit.type	= 'password';
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
		
		this.objControlOutput.domView		= document.createElement('span');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domView);
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return this.objControlOutput.domEdit.value;
	},
	
	setElementValue	: function(mixValue)
	{
		this.objControlOutput.domEdit.value		= mixValue;
	},
	
	updateElementValue	: function()
	{
		mixValue	= this.getValue();
		
		this.setElementValue(mixValue);
		this.objControlOutput.domView.innerHTML	= (mixValue) ? '[ Password specified ]' : '[ No password specified ]';
	},

	setRenderMode	: function($super, bolRenderMode)
	{
		$super(bolRenderMode);
		
		//if (this.getRenderMode() !== bolRenderMode && bolRenderMode == Control_Field.RENDER_MODE_EDIT)
		if (bolRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.setElementValue('');
		}
		
		this.validate();
	},
	
	setDependant	: function(objControlFieldPassword)
	{
		if (objControlField instanceof Control_Field_Password)
		{
			this.objDependantControlField	= objControlField;
		}
	},
	
	validateDependant	: function()
	{
		if (this.objDependantControlField)
		{
			this.objDependantControlField.validate();
		}
	},
	
	addEventListeners	: function()
	{
		this.arrEventHandlers						= {};
		this.arrEventHandlers.fncValidate			= this.validate.bind(this);
		this.arrEventHandlers.fncValidateDependant	= this.validateDependant.bind(this);
		
		this.objControlOutput.domEdit.addEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('keyup'	, this.arrEventHandlers.fncValidate, false);
		
		this.objControlOutput.domEdit.addEventListener('click'	, this.arrEventHandlers.fncValidateDependant, false);
		this.objControlOutput.domEdit.addEventListener('change'	, this.arrEventHandlers.fncValidateDependant, false);
		this.objControlOutput.domEdit.addEventListener('keyup'	, this.arrEventHandlers.fncValidateDependant, false);
	},
	
	removeEventListeners	: function()
	{
		this.objControlOutput.domEdit.removeEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('keyup'	, this.arrEventHandlers.fncValidate, false);
		
		this.objControlOutput.domEdit.removeEventListener('click'	, this.arrEventHandlers.fncValidateDependant, false);
		this.objControlOutput.domEdit.removeEventListener('change'	, this.arrEventHandlers.fncValidateDependant, false);
		this.objControlOutput.domEdit.removeEventListener('keyup'	, this.arrEventHandlers.fncValidateDependant, false);
	}
});
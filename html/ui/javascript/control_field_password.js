var Control_Field_Password	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Create the DOM Elements
		this.oControlOutput.oEdit		= document.createElement('input');
		this.oControlOutput.oEdit.type	= 'password';
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return this.oControlOutput.oEdit.value;
	},
	
	setElementValue	: function(mValue)
	{
		this.oControlOutput.oEdit.value		= mValue;
	},
	
	updateElementValue	: function()
	{
		mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= (mValue) ? '[ Password specified ]' : '[ No password specified ]';
	},

	setRenderMode	: function($super, oControlFieldPassword)
	{
		$super(oControlFieldPassword);
		
		//if (this.getRenderMode() !== oControlFieldPassword && oControlFieldPassword == Control_Field.RENDER_MODE_EDIT)
		if (oControlFieldPassword == Control_Field.RENDER_MODE_EDIT)
		{
			this.setElementValue('');
		}
		
		this.validate();
	},
	
	setDependant	: function(oControlFieldPassword)
	{
		if (oControlFieldPassword instanceof Control_Field_Password)
		{
			this.oDependantControlField	= oControlFieldPassword;
		}
	},
	
	validateDependant	: function()
	{
		if (this.oDependantControlField)
		{
			this.oDependantControlField.validate();
		}
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers						= {};
		this.aEventHandlers.fnValidate			= this.validate.bind(this);
		this.aEventHandlers.fnValidateDependant	= this.validateDependant.bind(this);
		
		this.oControlOutput.oEdit.addEventListener('click'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.addEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.addEventListener('keyup'	, this.aEventHandlers.fnValidate, false);
		
		this.oControlOutput.oEdit.addEventListener('click'	, this.aEventHandlers.fnValidateDependant, false);
		this.oControlOutput.oEdit.addEventListener('change'	, this.aEventHandlers.fnValidateDependant, false);
		this.oControlOutput.oEdit.addEventListener('keyup'	, this.aEventHandlers.fnValidateDependant, false);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oEdit.removeEventListener('click'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.removeEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.removeEventListener('keyup'	, this.aEventHandlers.fnValidate, false);
		
		this.oControlOutput.oEdit.removeEventListener('click'	, this.aEventHandlers.fnValidateDependant, false);
		this.oControlOutput.oEdit.removeEventListener('change'	, this.aEventHandlers.fnValidateDependant, false);
		this.oControlOutput.oEdit.removeEventListener('keyup'	, this.aEventHandlers.fnValidateDependant, false);
	}
});
var Control_Field_Date_Picker	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, strLabel, strLabelSeparator)
	{
		// Parent
		$super(strLabel, strLabelSeparator);
		
		// Create the DOM Elements
		this.objControlOutput.domEdit		= document.createElement('div');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
		
		// FIXME: Temporary Id
		var strHiddenId	= 'hidden_' + Math.ceil(Math.random() * (new Date()).getTime());
		
		this.objControlOutput.domHidden			= document.createElement('hidden');
		this.objControlOutput.domHidden.id		= strHiddenId;
		this.objControlOutput.domEdit.appendChild(this.objControlOutput.domHidden);
		
		this.objControlOutput.domInput			= document.createElement('input');
		this.objControlOutput.domInput.type		= 'text';
		this.objControlOutput.domInput.readonly	= true;
		this.objControlOutput.domEdit.appendChild(this.objControlOutput.domInput);
		
		this.objControlOutput.domIcon			= document.createElement('img');
		this.objControlOutput.domIcon.src		= '../admin/img/template/calendar_small.png';
		this.objControlOutput.domIcon.title		= 'Date Picker';
		this.objControlOutput.domIcon.alt		= 'Choose Date';
		this.objControlOutput.domEdit.appendChild(this.objControlOutput.domIcon);
		
		this.objControlOutput.domView		= document.createElement('span');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domView);
		
		var objDate	= new Date();
		this.objDatePicker	= DateChooser.factory(this.objControlOutput.domHidden.id, Control_Field_Date_Picker.YEAR_START, Control_Field_Date_Picker.YEAR_END, Control_Field_Date_Picker.DATE_FORMAT, false, true, true, objDate.getFullYear(), objDate.getMonth(), objDate.getDay());
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return this.objControlOutput.domHidden.value;
	},
	
	setElementValue	: function(mixValue)
	{
		this.objControlOutput.domHidden.value	= mixValue;
	},
	
	updateElementValue	: function()
	{
		mixValue	= this.getValue();
		
		this.setElementValue(mixValue);
		this.objControlOutput.domView.innerHTML	= mixValue;
	},
	
	_updateFormattedInput	: function()
	{
		var objDate	= Date.parseDate(this.objControlOutput.domHidden.value, 'Y-m-d');
		this.objControlOutput.domInput.value	= Date.parseDate(this.objControlOutput.domHidden.value, 'Y-m-d').dateFormat(Control_Field_Date_Picker.DATE_FORMAT);
	},
	
	addEventListeners	: function()
	{
		this.arrEventHandlers				= {};
		this.arrEventHandlers.fncValidate	= this.validate.bind(this);
		this.arrEventHandlers.fncOpenPicker	= this.objDatePicker.show.bind(this.objDatePicker);
		
		this.objControlOutput.domHidden.addEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domHidden.addEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domIcon.addEventListener('click'	, this.arrEventHandlers.fncOpenPicker, false);
	},
	
	removeEventListeners	: function()
	{
		this.objControlOutput.domInput.removeEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domIcon.removeEventListener('click'	, this.arrEventHandlers.fncOpenPicker, false);
	}
});

Control_Field_Date_Picker.DATE_FORMAT	= 'd/m/Y';

Control_Field_Date_Picker.YEAR_START	= 1900;
Control_Field_Date_Picker.YEAR_END		= 2050;
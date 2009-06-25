var Control_Field_Select	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, strLabel, strLabelSeparator)
	{
		// Parent
		$super(strLabel, strLabelSeparator);
		
		// Create the DOM Elements
		this.objControlOutput.domEdit				= document.createElement('select');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domEdit);
		this.objControlOutput.sEditDisplayDefault	= this.objControlOutput.domEdit.style.display;
		
		this.objControlOutput.domLoading			= document.createElement('span');
		this.objControlOutput.domLoading.innerHTML	= "<img src='../admin/img/template/loading.png' style='width: 16px; height: 16px; margin-right: 0.25em;' title='Loading...' alt='' />Loading...";
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domLoading);
		
		this.objControlOutput.domView		= document.createElement('span');
		this.objControlOutput.domElement.appendChild(this.objControlOutput.domView);
		
		// Not populated yet
		this.bPopulated									= false;
		this.objControlOutput.domEdit.style.display		= 'none';
		this.objControlOutput.domLoading.style.display	= 'inline';
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return (this.objControlOutput.domEdit.selectedIndex >= 0) ? this.objControlOutput.domEdit.options[this.objControlOutput.domEdit.selectedIndex].value : null;
	},
	
	setElementValue	: function(mixValue)
	{
		if (this.bPopulated)
		{
			this.objControlOutput.domEdit.selectedIndex	= this._getIndexForValue(mixValue);
		}
		else
		{
			// Not Populated yet... set the Value once populated
			this.mixValueOnPopulate	= mixValue;
		}
	},
	
	updateElementValue	: function()
	{
		mixValue	= this.getValue();
		
		this.setElementValue(mixValue);
		this.objControlOutput.domView.innerHTML	= (this.objControlOutput.domEdit.selectedIndex >= 0) ? this.objControlOutput.domEdit.options[this.objControlOutput.domEdit.selectedIndex].innerHTML : '[ None ]';
	},
	
	setPopulateFunction	: function(fPopulateFunction, bPopulateImmediately)
	{
		this.fPopulateFunction	= fPopulateFunction;
		
		// Populate
		if (bPopulateImmediately || bPopulateImmediately === undefined || bPopulateImmediately === null)
		{
			this.populate();
		}
	},
	
	populate	: function(aOptions)
	{
		if (aOptions === undefined || aOptions === null)
		{
			alert("No options -- getting list...");
			this.bPopulated									= false;
			this.objControlOutput.domEdit.style.display		= 'none';
			this.objControlOutput.domLoading.style.display	= 'inline';
			
			// Remove any existing Options
			for (var i = 0; i < this.objControlOutput.domEdit.options.length; i++)
			{
				this.objControlOutput.domEdit.remove(this.objControlOutput.domEdit.options[i]);
			}
			
			// Retrieve Options
			this.fPopulateFunction(this.populate.bind(this));
		}
		else
		{
			// Set Options
			alert("Populating Select with " + Object.keys(aOptions).length + " Options");
			for (i in aOptions)
			{
				this.objControlOutput.domEdit.add(aOptions[i], null);
			}
			
			this.bPopulated									= true;
			this.objControlOutput.domEdit.style.display		= this.objControlOutput.sEditDisplayDefault;
			this.objControlOutput.domLoading.style.display	= 'none';
			
			if (this.mixValueOnPopulate)
			{
				this.setElementValue(this.mixValueOnPopulate);
				delete this.mixValueOnPopulate;
			}
		}
	},
	
	_getIndexForValue	: function(mixValue)
	{
		for (var i = 0; i < this.objControlOutput.domEdit.options.length; i++)
		{
			if (mixValue == this.objControlOutput.domEdit.options[i])
			{
				return i;
			}
		}
	},
	
	addEventListeners	: function()
	{
		this.arrEventHandlers				= {};
		this.arrEventHandlers.fncValidate	= this.validate.bind(this);
		
		this.objControlOutput.domEdit.addEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.addEventListener('keyup'	, this.arrEventHandlers.fncValidate, false);
	},
	
	removeEventListeners	: function()
	{
		this.objControlOutput.domEdit.removeEventListener('click'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('change'	, this.arrEventHandlers.fncValidate, false);
		this.objControlOutput.domEdit.removeEventListener('keyup'	, this.arrEventHandlers.fncValidate, false);
	}
});
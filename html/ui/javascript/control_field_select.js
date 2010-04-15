var Control_Field_Select	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Create the DOM Elements
		this.oControlOutput.oEdit				= document.createElement('select');
		this.oControlOutput.sEditDisplayDefault	= this.oControlOutput.oEdit.style.display;
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oLoading			= document.createElement('span');
		this.oControlOutput.oLoading.innerHTML	= "<img src='../admin/img/template/loading.gif' style='width: 16px; height: 16px; margin-right: 0.25em;' title='Loading...' alt='' />Loading...";
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oLoading);
		
		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		this.oControlOutput.sViewDisplayDefault	= this.oControlOutput.oView.style.display;
		
		// Not populated yet
		this.bPopulated								= false;
		this.oControlOutput.oEdit.style.display		= 'none';
		this.oControlOutput.oView.style.display		= 'none';
		this.oControlOutput.oLoading.style.display	= 'inline';
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return (this.oControlOutput.oEdit.selectedIndex >= 0) ? this.oControlOutput.oEdit.options[this.oControlOutput.oEdit.selectedIndex].value : null;
	},
	
	setElementValue	: function(mValue)
	{
		//alert("Setting " + this.getLabel() + " Element Value to '" + mValue + "'");
		
		// Set if the Contents have loaded, otherwise we will auto-set on population
		if (this.bPopulated)
		{
			this.oControlOutput.oEdit.selectedIndex	= (!mValue && mValue !== 0) ? -1 : this._getIndexForValue(mValue);
		}
	},
	
	updateElementValue	: function()
	{
		mValue	= this.getValue();
		
		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= (this.oControlOutput.oEdit.selectedIndex >= 0) ? this.oControlOutput.oEdit.options[this.oControlOutput.oEdit.selectedIndex].innerHTML : '[ None ]';
	},
	
	setPopulateFunction	: function(fnPopulateFunction, bPopulateImmediately)
	{
		this.fnPopulateFunction	= fnPopulateFunction;
		
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
			//alert("No options -- getting list...");
			this.bPopulated									= false;
			this.oControlOutput.oEdit.style.display		= 'none';
			this.oControlOutput.oView.style.display		= 'none';
			this.oControlOutput.oLoading.style.display	= 'inline';
			
			// Remove any existing Options
			for (var i = 0; i < this.oControlOutput.oEdit.options.length; i++)
			{
				this.oControlOutput.oEdit.remove(this.oControlOutput.oEdit.options[i]);
			}
			
			// Retrieve Options
			this.fnPopulateFunction(this.populate.bind(this));
		}
		else
		{
			// Set Options
			//alert("Populating Select with " + aOptions.length + " Options");
			for (var i = 0; i < aOptions.length; i++)
			{
				this.oControlOutput.oEdit.add(aOptions[i], null);
			}
			
			this.bPopulated								= true;
			this.oControlOutput.oEdit.style.display		= this.oControlOutput.sEditDisplayDefault;
			this.oControlOutput.oView.style.display		= this.oControlOutput.sViewDisplayDefault;
			this.oControlOutput.oLoading.style.display	= 'none';
			
			this.updateElementValue();
		}
	},
	
	_getIndexForValue	: function(mValue)
	{
		for (var i = 0; i < this.oControlOutput.oEdit.options.length; i++)
		{
			if (mValue == this.oControlOutput.oEdit.options[i].value)
			{
				return i;
			}
		}
		//alert("No Index for '" + mValue + "'");
		return -1;
	},
	
	addEventListeners	: function()
	{
		this.aEventHandlers				= {};
		this.aEventHandlers.fnValidate	= this.validate.bind(this);
		
		//this.oControlOutput.oEdit.addEventListener('click'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.addEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.addEventListener('keyup'	, this.aEventHandlers.fnValidate, false);
	},
	
	removeEventListeners	: function()
	{
		//this.oControlOutput.oEdit.removeEventListener('click'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.removeEventListener('change'	, this.aEventHandlers.fnValidate, false);
		this.oControlOutput.oEdit.removeEventListener('keyup'	, this.aEventHandlers.fnValidate, false);
	}
});
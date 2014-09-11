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
		
		this._aOnChangeCallbacks	= [];
		
		this.validate();
		
		this.addEventListeners();
	},
	
	getElementValue	: function()
	{
		return (this.oControlOutput.oEdit.selectedIndex >= 0) ? this.oControlOutput.oEdit.options[this.oControlOutput.oEdit.selectedIndex].value : null;
	},
	
	setElementValue	: function(mValue)
	{
		// Set if the Contents have loaded, otherwise we will auto-set on population
		if (this.bPopulated)
		{
			this.oControlOutput.oEdit.selectedIndex	= (!mValue && mValue !== 0) ? -1 : this._getIndexForValue(mValue);
		}
		this.oControlOutput.oView.innerHTML	= (this.oControlOutput.oEdit.selectedIndex >= 0) ? this.oControlOutput.oEdit.options[this.oControlOutput.oEdit.selectedIndex].innerHTML : '[ None ]';
	},
	
	updateElementValue	: function()
	{
		var	mValue	= this.getValue();
		this.setElementValue(mValue);
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
			this.bPopulated								= false;
			this.oControlOutput.oEdit.style.display		= 'none';
			this.oControlOutput.oView.style.display		= 'none';
			this.oControlOutput.oLoading.style.display	= 'inline';
			
			// Remove any existing Options
			while (this.oControlOutput.oEdit.options.length)
			{
				this.oControlOutput.oEdit.options[0].remove();
			}
			
			// Retrieve Options
			this.fnPopulateFunction(this.populate.bind(this));
		}
		else
		{
			// Set Options
			for (var i = 0; i < aOptions.length; i++)
			{
				this.oControlOutput.oEdit.add(aOptions[i], null);
			}
			
			this.bPopulated								= true;
			this.oControlOutput.oEdit.style.display		= this.oControlOutput.sEditDisplayDefault;
			this.oControlOutput.oView.style.display		= this.oControlOutput.sViewDisplayDefault;
			this.oControlOutput.oLoading.style.display	= 'none';
			this.updateElementValue();
			this._valueChange();
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
		this.aEventHandlers					= {};
		this.aEventHandlers.fnValueChange	= this._valueChange.bind(this);
		this.oControlOutput.oEdit.observe('change',	this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('keyup', 	this.aEventHandlers.fnValueChange);
	},
	
	removeEventListeners	: function()
	{
		this.oControlOutput.oEdit.stopObserving('change', 	this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.stopObserving('keyup', 	this.aEventHandlers.fnValueChange);
	},
	
	addOnChangeCallback	: function(fnCallback)
	{
		return this._aOnChangeCallbacks.push(fnCallback) - 1;
	},
	
	removeOnChangeCallback	: function(iIndex)
	{
		if (this._aOnChangeCallbacks[iIndex])
		{
			this._aOnChangeCallbacks.splice(iIndex, 1);
		}
	},
	
	_valueChange	: function(oEvent)
	{
		this.validate();
		this.fire('change', oEvent);
		
		// Kept for backwards compatibility
		for (var i = 0; i < this._aOnChangeCallbacks.length; i++)
		{
			this._aOnChangeCallbacks[i]();
		}
	},
	
	getElementText	: function()
	{
		return ((this.oControlOutput.oEdit.selectedIndex >= 0) ? this.oControlOutput.oEdit.options[this.oControlOutput.oEdit.selectedIndex].innerHTML : null);
	},
	
	emptyList: function()
	{
		while (this.oControlOutput.oEdit.options.length)
			{
				this.oControlOutput.oEdit.options[0].remove();
			}
	
	}
});
FW.Package.create('FW.GUIComponent.ElementGroup',
{
	//an array of elements, both input and non-input
	aElements:null,
	//a span that contains the data value of the element group, for display only
	oDisplay:null,
	//array containing only the 'input' elements
	aInputs:null,
	//a boolean or function to indicate if this is a mandatory field
	mIsMandatory:null,
	//optional function for validation of this element group
	fnIsValidCustom: null,
	//html table row for this element
	oRow: null,
	//array of events that should trigger validation
	aValidationEvents:null,
	//type of element group
	sType:null,
	//data field in the underlying data object
	mDataField:null,
	//underlying data object
	oDataObject:null,
	
	
	initialize: function(mIsMandatory, fnIsValidCustom, aValidationEvents)
	{
		mIsMandatory	= ((mIsMandatory == undefined) ? false : mIsMandatory);
		this.mIsMandatory =  mIsMandatory;
		this.fnIsValidCustom = fnIsValidCustom;
		if (aValidationEvents == undefined)
		{
			aValidationEvents	= new Array('change', 'keyup', 'click');
		}
		
		this.aValidationEvents = aValidationEvents;
		
	
	},
	
	//binds this element group to its underlying data object and field
	bindToField: function(oObject, mField)
	{
		this.mDataField = mField;
		this.oDataObject = oObject;	
	},
	
	//updates the underlying data object with values entered by the user
	updateDataField: function()
	{
		if (typeof(this.oDataObject) == 'object' && typeof(this.mDataField) == 'string')
		{
			if (this.isValid())
			{
				this.oDataObject[this.mDataField] = this.getValue();
			}
			else
			{
				this.oDataObject[this.mDataField] = null;
			}
		}
	
	},
	
	//validates user input
	isValid: function()
	{
		// Remove any valid/invalid classes from the Inputs
		for (var i = 0; i < this.aInputs.length; i++)
		{
			this.aInputs[i].removeClassName('invalid');
			this.aInputs[i].removeClassName('valid');
		}

		// Convert Value to a string, then strip the whitespace
		mixValue	= this.getValue();
		strValue	= (mixValue === null) ? '' : String(mixValue).strip();

		// If mixIsMandatory is a function, then call it
		var bolIsMandatory;
		if ((typeof this.mIsMandatory) === 'function')
		{
			//alert('mixIsMandatory is a function!');
			bolIsMandatory	= this.mIsMandatory();
		}
		else if (this.mIsMandatory == undefined)
		{
			bolIsMandatory	= false;
		}
		else
		{
			bolIsMandatory	= this.mIsMandatory;
		}

		// Is there a validation method set?
		var bolValid	= (this.fnIsValidCustom == undefined) ? true : this.fnIsValidCustom(strValue);

		if (this.isDisabled())
		{
			// Something can't be considered manditory if it is disabled, right?
			// I can't help but think this bold generalisation will bite me in the arse
			bolIsMandatory = false;
		}

		// Mandatory?
		if (strValue.length == 0)
		{
			bolValid = !bolIsMandatory;
		}

		// Set Style
		strNewStyle	= null;
		if (strValue.length > 0 || bolIsMandatory)
		{
			if (!bolValid)
			{
				strNewStyle	= 'invalid';
			}
			else
			{
				strNewStyle	= 'valid';
			}
		}

		// Apply Styles to all inputs
		var bolDisabled	= true;
		if (strNewStyle)
		{
			for (var i = 0; i < this.aInputs.length; i++)
			{
				this.aInputs[i].addClassName(strNewStyle);
				bolDisabled	= (this.aInputs[i].disabled) ? bolDisabled : false;
			}
		}

		// If it's disabled, then it is Valid (all inputs must be disabled)
		return (bolDisabled) ? true : bolValid;
	
	},
	
	//appends this element group to the GUI html table
	appendToTable: function($table, $label)
	{
		this.oRow = $table.insertRow(-1);
		var cell = this.oRow.insertCell(-1);
		cell.appendChild(document.createTextNode($label + ":"));
		cell = this.oRow.insertCell(-1);
		this.appendToTableCell(cell);

		return this.oRow;	
	
	},
	
	//appends this element to its designated html table cell
	appendToTableCell: function($container)
	{
		for (var i = 0, l = this.aElements.length; i < l; i++)
		{
			$container.appendChild(this.aElements[i]);
			$container.appendChild(this.oDisplay);
		}	
	},
	
	// to check if this is in disabled state
	isDisabled: function()
	{
		
		for (var i=0, l=this.aInputs.length; i<l; i++)
		{
			if (!this.aInputs[i].disabled)
			{
				return false;
			}
		}
		return true;
	},
	
	
	// to empty the input elements of their values
	reset: function()
	{
		for (var i=0;i<this.aInputs.length;i++)
		{
			switch (this.aInputs[i].nodeName.toLowerCase())
			{
				case 'input':
					if (this.aInputs[i].type == 'radio' || this.aInputs[i].type == 'checkbox')
					{
						this.aInputs[i].checked = false;
					}
					else
					{
						this.aInputs[i].value = '';
					}
					break;
				case 'select':
					if (this.aInputs[i].multiple)
						this.aInputs[i].selectedIndex = -1;
					else
						this.aInputs[i].selectedIndex = null;
					break;
				case 'textarea':
					this.aInputs[i].value = '';
			}
		
		}
	
	},
	
	//to disable this element group's GUI inputs
	disable: function( bNullifyValue)
	{
				if (bNullifyValue == undefined)
				{
					blNullifyValue = false;
				}				

				for(var i=0;i<this.aInputs.length;i++)
				{
					this.aInputs[i].disabled = true;
				
				}
				
				if (bNullifyValue)
					this.reset();
			
	},
	
	//to enable this element's GUI inputs
	enable: function()
	{		
		for (var i=0, l=this.aInputs.length; i<l; i++)
		{
			this.aInputs[i].disabled = false;
		}	
		this.isValid();
		
	},
	
	//the following abstract methods must be implemented by the child classes
	
	//to get the value of the user input
	getValue: function(){},
	//to update the data display span (oDisplay)
	updateDisplay: function(){},
	

});
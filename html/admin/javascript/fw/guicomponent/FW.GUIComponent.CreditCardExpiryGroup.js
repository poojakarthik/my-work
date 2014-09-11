
FW.Package.create('FW.GUIComponent.CreditCardExpiryGroup' ,
{	
	extends: 'FW.GUIComponent.ElementGroup',
	inputs: [],
	elements: [],
	type: null,
	display: null,
	MixIsMandatory: null,

	initialize: function($super,$values, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
		// Create Inputs
		var curMonth = $values[0];
		var curYear = $values[1];

		var intYear = (new Date()).getYear() + 1900;

		var month = document.createElement('select');
		var year = document.createElement('select');
		year.className = month.className = 'data-entry';
		for (var i = 0; i <= 12; i++)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curMonth);
			option.appendChild(document.createTextNode((i == 0) ? 'Month' : i));
			month.appendChild(option);
		}
		var option = document.createElement('option');
		option.value = 0;
		option.appendChild(document.createTextNode('Year'));
		year.appendChild(option);
		for (var i = intYear; i <= (intYear + 15); i++)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curYear);
			option.appendChild(document.createTextNode(i));
			year.appendChild(option);
		}

		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';

		var wrap = document.createElement('span');
		wrap.className = 'data-entry';
		wrap.appendChild(month);
		wrap.appendChild(document.createTextNode(' / '));
		wrap.appendChild(year);

		//mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		
	
		this.aInputs =  new Array(month, year);
		this.aElements =  new Array(wrap);
		this.sType =  'credit_card_expiry';
		this.oDisplay =  disp;
		this.mIsMandatory = mixIsMandatory;

	
		for (var i = 0; i < this.aValidationEvents.length; i++)
		{
			for (t = 0; t < this.aInputs.length; t++)
			{
				strEvent	= this.aValidationEvents[i];
				Event.observe(this.aInputs[t], strEvent, this.isValid.bindAsEventListener(this));
				Event.observe(this.aInputs[t], strEvent, this.updateDataField.bindAsEventListener(this));
			}
		}

		// Give each Input a reference to it's Group
		for (var i = 0; i < this.aInputs.length; i++)
		{
			this.aInputs[i].objElementGroup	= this;
		}

		// Pre-Validate the Group
		this.isValid();

		this.updateDisplay(this);

		return this;
	},
	
	setValue: function($values)
	{
		alert("Set value for credit card expiry has not been implemented.");
	},
	
	getValue: function()
		{
			this.updateDisplay();
			if (parseInt(this.aInputs[0].options[this.aInputs[0].selectedIndex].value) && parseInt(this.aInputs[1].options[this.aInputs[1].selectedIndex].value))
				{
					return new Array(this.aInputs[0].options[this.aInputs[0].selectedIndex].value, this.aInputs[1].options[this.aInputs[1].selectedIndex].value);
				}
				return null;
		
		},
		
		updateDisplay: function()
		{
		
			this.oDisplay.innerHTML = '';
				var date = this.aInputs[0].options[this.aInputs[0].selectedIndex].value;
				date += " / ";
				date += this.aInputs[1].options[this.aInputs[1].selectedIndex].value;
				this.oDisplay.appendChild(document.createTextNode(date));
		
		},
		
	//override the following element group methods to cater for the special nature of this gui element group class
	bindToField: function(oObject, aFields)
	{
		this.mDataField1 = aFields[0];
		this.mDataField2 = aFields[1];
		this.oDataObject = oObject;	
	},
	
	//updates the underlying data object with values entered by the user
	updateDataField: function()
	{
		if (typeof(this.oDataObject) == 'object' && typeof(this.mDataField1) == 'string' &&  typeof(this.mDataField2) == 'string' && this.isValid())
		{
			this.oDataObject[this.mDataField1] = this.getValue()[0];
			this.oDataObject[this.mDataField2] = this.getValue()[1];
		}
	
	},
			
			
});
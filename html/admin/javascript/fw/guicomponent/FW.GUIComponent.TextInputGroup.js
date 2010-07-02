	FW.Package.create('FW.GUIComponent.TextInputGroup',
	{
	
		extends: 'FW.GUIComponent.ElementGroup',
		initialize: function($super,$value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
		{
			$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
			// Create the Input
			var input = document.createElement('input');
			input.type = 'text';
			input.value = $value;
			input.className = 'data-entry';

			// Create Element Group
			var disp = document.createElement('span');
			disp.className = 'data-display';

			mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
			
			this.aInputs =  new Array(input);
			this.aElements = new Array(input);
			this.sType = 'text';
			this.oDisplay =  disp;
			this.mIsMandatory =  mixIsMandatory;

			this.setValue = function($value)
			{
				this.aInputs[0].value = $value;
			}
			

		
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
		
		getValue: function()
		{
			this.updateDisplay();
			return this.aInputs[0].value;
		
		},
		
		updateDisplay: function()
		{
		
			this.oDisplay.innerHTML = '';
				this.oDisplay.appendChild(document.createTextNode(this.aInputs[0].value));
		
		}
		
	});
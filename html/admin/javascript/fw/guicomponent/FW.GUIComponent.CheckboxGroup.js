FW.Package.create('FW.GUIComponent.CheckboxGroup',
	{
		extends: 'FW.GUIComponent.ElementGroup',
		initialize: function($super,$checked, mixIsMandatory, clbValidationFunction, arrValidationEvents)
		{
			$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
			// Create Input
			var checkbox = document.createElement('input');
			checkbox.type = 'checkbox';
			checkbox.checked = $checked;
			checkbox.className = 'data-entry';

			// Create Element Group
			var disp = document.createElement('span');
			disp.className = 'data-display';

			//mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
			
			this.aInputs= new Array(checkbox);
			this.aElements= new Array(checkbox);
			this.sType= 'checkbox';
			this.oDisplay= disp;
			this.mIsMandatory= mixIsMandatory;

			this.setValue= function($value)
			{
				this.aInputs[0].checked = $value;
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
			return this.aInputs[0].checked;
		
		},
		
		updateDisplay: function()
		{
		
			this.oDisplay.innerHTML = (this.aInputs[0].checked ? 'Yes' : 'No');
		
		}
		
	});
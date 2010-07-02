FW.Package.create('FW.GUIComponent.TextareaGroup',
	{
		extends: 'FW.GUIComponent.ElementGroup',
		initialize: function($super,$value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
		{
			$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
			// Create Input
			var textarea = document.createElement('textarea');
			textarea.value = $value;
			textarea.className = 'data-entry';

			// Create Element Group
			var disp = document.createElement('textarea');
			disp.disabled = true;
			disp.style.color = '#000';
			disp.style.backgroundColor = '#fff';
			disp.className = 'data-display';

			mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
			
			this.aInputs = new Array(textarea);
			this.aElements = new Array(textarea);
			this.sType =  'textarea';
			this.oDisplay = disp;
			this.mIsMandatory = mixIsMandatory;

			this. setValue = function($value)
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
		
			this.oDisplay.value = this.aInputs[0].value;
		
		}
		
	});
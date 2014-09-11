	FW.Package.create('FW.GUIComponent.DropDown',
		{
			extends: 'FW.GUIComponent.ElementGroup',
			initialize:  function($super, $values, $labels, $value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
			{
				$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
				// Create Input
				var dropDown = document.createElement('select');
				dropDown.className = 'data-entry';
				var option = document.createElement('option');
				option.value = '';
				option.appendChild(document.createTextNode('[None]'));
				dropDown.appendChild(option);
				for (var i = 0, l = $values.length; i < l; i++)
				{
					var option = document.createElement('option');
					option.value = $values[i];
					option.selected = ($values[i] == $value);
					option.appendChild(document.createTextNode($labels[i]));
					dropDown.appendChild(option);
				}

			
				 // for (var i = 0; i < this.aValidationEvents.length; i++)
				 // {
					 // strEvent	= this.aValidationEvents[i];
					 // Event.observe(dropDown, strEvent, this.validateInput.bindAsEventListener(this));
				 // }
				// dropDown.isValid	= clbValidationFunction;

				var disp = document.createElement('span');
				disp.className = 'data-display';
			
				this.aInputs =  new Array(dropDown);
				this.aElements = new Array(dropDown);
				this.sType =  'select';
				this.oDisplay = disp;
					//this.mIsMandatory =  mixIsMandatory;

					this.setValue = function($value)
					{
						for (var i = 0, l = this.aInputs[0].options.length; i < l; i++)
						{
							this.aInputs[0].options[i].selected = (this.aInputs[0].options[i].value == $value);
							
						}
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

				this.updateDisplay();

				return this;
			},
			
			// validateInput: function(objValidator)
			// {
				// var objInput, strValue, mixIsMandatory;
				// if (objValidator == undefined || objValidator == null)
				// {
					// objInput	= this;
				// }
				// else if (typeof objValidator == 'object' && objValidator.target != undefined)
				// {
					// objInput	= objValidator.currentTarget;
				// }
				// else
				// {
					// objInput	= objValidator;
				// }

				// // Remove any valid/invalid classes from the Input
				// objInput.removeClassName('invalid');
				// objInput.removeClassName('valid');

				// // Convert Value to a string, then strip the whitespace
				// strValue	= String(objInput.value);
				// strValue.strip();

				// //alert("Validating Input with Value '"+strValue+"'");
				// mixIsMandatory	= (objInput.objElementGroup.mIsMandatory == undefined) ? false : objInput.objElementGroup.mIsMandatory;

				// // Is there a validation method set?
				// var bolValid	= (objInput.isValid == undefined) ? true : objInput.isValid(objInput.value);

				// // Mandatory?
				// if (strValue.length == 0 && mixIsMandatory)
				// {
					// bolValid	= false;
				// }

				// // Set Style
				// if (strValue.length > 0 || mixIsMandatory)
				// {
					// if (!bolValid)
					// {
						// objInput.addClassName('invalid');
					// }
					// else
					// {
						// objInput.addClassName('valid');
					// }
				// }
				// else
				// {
					// bolValid			= true;
				// }

				// return bolValid;
			// },
			
			getValue: function()
		{
			this.updateDisplay();
			for (var i = 0, l = this.aInputs[0].options.length; i < l; i++)
				{
					if (this.aInputs[0].options[i].selected)
					{
						return this.aInputs[0].options[i].value;
					}
				}
				return null;
		
		},
		
		updateDisplay: function()
		{
		
			this.oDisplay.innerHTML = '';
				for (var i = 0, l = this.aInputs[0].options.length; i < l; i++)
				{
					if (this.aInputs[0].options[i].selected)
					{
						this.oDisplay.appendChild(document.createTextNode(this.aInputs[0].options[i].textContent));
						break;
					}
				}
		
		}
			
		});
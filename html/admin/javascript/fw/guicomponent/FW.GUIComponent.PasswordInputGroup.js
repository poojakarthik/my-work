	FW.Package.create('FW.GUIComponent.PasswordInputGroup',
		{
			extends: 'FW.GUIComponent.ElementGroup',
			initialize: function($super,$value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
			{
				$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
				// Create Inputs
				var grp = document.createElement('span');
				grp.className = 'data-entry';

				var input = document.createElement('input');
				input.type = 'password';
				input.value = $value;
				grp.appendChild(input);
				grp.appendChild(document.createElement('br'));
				var txtConf = document.createTextNode('Confirm:');
				grp.appendChild(txtConf);
				grp.appendChild(document.createElement('br'));
				var conf = document.createElement('input');
				conf.type = 'password';
				conf.value = $value;
				grp.appendChild(conf);

				// Create Element Group
				var disp = document.createElement('span');
				disp.className = 'data-display';
				disp.appendChild(document.createTextNode('[hidden]'));

				mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
				
				this.aInputs= new Array(input, conf);
				this.aElements= new Array(grp);
				this.sType= 'password';
				this.oDisplay= disp;
				this.mIsMandatory= mixIsMandatory;

				this.setValue= function($value)
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
			if (this.aInputs[0].value != this.aInputs[1].value)
				{
					return null;
				}
		
		},
		
		updateDisplay: function()
		{
		
			return null;
		
		}
			
			
		});
	FW.Package.create('FW.GUIComponent.DateGroup',
	{
		extends: 'FW.GUIComponent.ElementGroup',
		initialize: function($super,$value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
		{
			$super(mixIsMandatory, clbValidationFunction, arrValidationEvents);
			// Create Inputs
			var curDay   = parseInt("1" + (($value != null && $value.length == 10) ? $value.substr(8, 2) : "00")) - 100;
			var curMonth = parseInt("1" + (($value != null && $value.length == 10) ? $value.substr(5, 2) : "00")) - 100;
			var curYear  = parseInt(($value != null && $value.length == 10) ? $value.substr(0, 4) : 0);

			var intYear = (new Date()).getYear() + 1900;

			var day = document.createElement('select');
			var month = document.createElement('select');
			var year = document.createElement('select');
			year.className = month.className = day.className = 'data-entry';
			for (var i = 0; i <= 31; i++)
			{
				var option = document.createElement('option');
				option.value = i;
				option.selected = (i == curDay);
				option.appendChild(document.createTextNode((i == 0) ? 'Day' : i));
				day.appendChild(option);
			}
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
			for (var i = (intYear - 10); i >= (intYear - 100); i--)
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
			wrap.appendChild(day);
			wrap.appendChild(document.createTextNode(' / '));
			wrap.appendChild(month);
			wrap.appendChild(document.createTextNode(' / '));
			wrap.appendChild(year);

			//mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
			// aElements:null,
					// oDisplay:null,
					// aInputs:null,
					// mIsMandatory:null,
					// fnIsValidCustom: null,
					// oRow: null,
					// aValidationEvents,
					//sType
				
				
				
			this.aInputs =  new Array(day, month, year);
			this.aElements = new Array(wrap);
			this.sType =  'date';
			this.oDisplay =  disp;
			this.mIsMandatory = mixIsMandatory;

			this.setValue = function($values)
			{
				alert("Set value for date groups has not been implemented.");
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
			var year	= this.aInputs[2].options[this.aInputs[2].selectedIndex].value;
				var month	= this.aInputs[1].options[this.aInputs[1].selectedIndex].value;
				var day		= this.aInputs[0].options[this.aInputs[0].selectedIndex].value;
				if (parseInt(year) || parseInt(month) || parseInt(day))
				{
					date = year;
					date += "-";
					if (month.length == 1) month = '0' + month;
					date += month;
					date += "-";
					if (day.length == 1) day = '0' + day;
					date += day;
					return date;
				}
				return null;
		
		},
		
		updateDisplay: function()
		{
		
			this.oDisplay.innerHTML = '';
				var date = this.aInputs[0].options[this.aInputs[0].selectedIndex].value;
				date += " / ";
				date += this.aInputs[1].options[this.aInputs[1].selectedIndex].value;
				date += " / ";
				date += this.aInputs[2].options[this.aInputs[2].selectedIndex].value;
				this.oDisplay.appendChild(document.createTextNode(date));
		
		}
			
			
	});
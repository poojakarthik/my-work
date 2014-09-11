var Class = require('fw/class'),
	GUIComponent = require('../guicomponent');

var self = new Class({
	extends : require('./elementgroup'),

	construct : function ($values, $labels, $value, mixIsMandatory, clbValidationFunction, arrValidationEvents) {
		this._super(mixIsMandatory, clbValidationFunction, arrValidationEvents);

		// Create Inputs
		var radios = [];
		var uniqueName = 'name_' + GUIComponent.unique;
		GUIComponent.unique++;
		var all = [];
		for (var i = 0, l = $values.length; i < l; i++) {
			var radio = document.createElement('input');
			
			radio.type = 'radio';
			radio.value = $values[i];
			radio.checked = ($values[i] == $value);
			//radio.setAttribute('label', $labels[i]);
			radio.label = $labels[i];
			//radio.setAttirbute('name', uniqueName);
			radio.name = uniqueName;
			radio.id = $values[i];
			
			radio.className = 'data-entry';
			
			var lab = document.createElement('label');
			lab.htmlFor = $values[i];
			//lab.id = uniqueName;
			lab.innerHTML = $labels[i];
			lab.className = 'data-entry';
			radios[i] = radio;
			all.push(radio);
			all.push(lab);
			all.push(document.createElement('br'));
		}

		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';

		//mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		
		this.aInputs= radios;
		this.aElements= all;
		this.sType= 'radio';
		this.oDisplay= disp;
		this.mIsMandatory= mixIsMandatory;

		this.setValue = function ($value) {
			for (var i = 0, l = $group.inputs.length; i < l; i++) {
				this.aInputs[i].checked = (this.aInputs[i].value == $value);
			}
		};

		var t;
		for (i = 0; i < this.aValidationEvents.length; i++) {
			for (t = 0; t < this.aInputs.length; t++) {
				strEvent = this.aValidationEvents[i];
				Event.observe(this.aInputs[t], strEvent, this.isValid.bindAsEventListener(this));
				Event.observe(this.aInputs[t], strEvent, this.updateDataField.bindAsEventListener(this));
			}
		}

		// Give each Input a reference to it's Group
		for (i = 0; i < this.aInputs.length; i++) {
			this.aInputs[i].objElementGroup	= this;
		}

		// Pre-Validate the Group
		this.isValid();

		this.updateDisplay(this);

		return this;
	},
	
	getValue : function () {
		this.updateDisplay();
		for (var i = 0, l = this.aInputs.length; i < l; i++) {
			if (this.aInputs[i].checked) {
				return this.aInputs[i].value;
			}
		}
		return null;
	},
	
	updateDisplay: function() {
		this.oDisplay.innerHTML = '';
		for (var i = 0, l = this.aInputs.length; i < l; i++) {
			if (this.aInputs[i].checked) {
				this.oDisplay.appendChild(document.createTextNode(this.aInputs[i].label));
			}
		}
	}
});

return self;
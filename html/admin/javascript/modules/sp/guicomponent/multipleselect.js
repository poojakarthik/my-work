var Class = require('fw/class');
var GUIComponent = require('fw/guicomponent');

var self = new Class({
	extends: require('./elementgroup'),

	construct : function ($values, $labels, $selectedValues, mixIsMandatory, clbValidationFunction, arrValidationEvents) {
		this._super(mixIsMandatory, clbValidationFunction, arrValidationEvents);

		// Create Input
		var option;
		var dropDown = document.createElement('select');
		dropDown.multiple = 'multiple';
		dropDown.className = 'data-entry';
		for (var i = 0, l = $values.length; i < l; i++) {
			option = document.createElement('option');
			option.value = $values[i];
			option.selected = GUIComponent.__array_contains($selectedValues, option.value);
			option.appendChild(document.createTextNode($labels[i]));
			dropDown.appendChild(option);
		}

		// Create Element Group
		var disp = document.createElement('p');
		disp.className = 'data-display';

		//mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		
		this.aInputs= new Array(dropDown);
		this.aElements= new Array(dropDown);
		this.sType= 'multiple';
		this.oDisplay= disp;
		this.mIsMandatory= mixIsMandatory;

		this.setValue = function($values) {
			for (var j = 0, k = $values.length; j < k; j++) {
				for (var i = 0, l = this.aInputs[0].options.length; i < l; i++) {
					this.aInputs[0].options[i].selected = (this.aInputs[0].options[i].value == $values[j]);
					if (this.aInputs[0].options[i].selected) break;
				}
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
		var values = [];
		for (var i = 0, l = this.aInputs[0].options.length; i < l; i++) {
			if (this.aInputs[0].options[i].selected) {
				values[values.length] = this.aInputs[0].options[i].value;
			}
		}
		return values;
	},
	
	updateDisplay : function () {
		this.oDisplay.innerHTML = '';
		var matched = false;
		for (var i = 0, l = this.aInputs[0].options.length; i < l; i++) {
			if (this.aInputs[0].options[i].selected) {
				if (matched) {
					this.oDisplay.appendChild(document.createElement('br'));
				}
				this.oDisplay.appendChild(document.createTextNode(this.aInputs[0].options[i].textContent));
				matched = true;
			}
		}
	}
});

return self;
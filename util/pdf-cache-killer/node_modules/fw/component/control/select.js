
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			fnPopulate 	: {},
			bPopulated	: {
				fnSetter : function() {
					throw new Error("bPopulated cannot be set (Select Control).");
				},
				fnGetter : function() {
					return this._oSelect && (this._oSelect.options.length > 0);
				}.bind(this)
			}
		}, this.CONFIG || {});

		this._mTempValue 	= null;
		this._bValueSet 	= false;
		
		this._super.apply(this, arguments);
		
		this.NODE.addClassName('fw-control-select');
	},
	
	_buildUI : function() {
		this._super();
		this._oSelect 	= $D.select();
		this._oView	 	= $D.span({'class': 'fw-control-select-view'});
		this.NODE.appendChild(this._oSelect);
		this.NODE.appendChild(this._oView);
		
		var fnChange = this._valueChange.bind(this);
		this._oSelect.observe('change', fnChange);
		this._oSelect.observe('keyup', fnChange);
	},
	
	_syncUI : function() {
		this._super();
		
		this._oSelect.name = this.get('sName');
		
		if (this._oSelect.options.length == 0) {
			this.populate();
		}

		if (!this._bInitialised){
			// Monitor changes
			this.NODE.observe('DOMNodeInsertedIntoDocument', function(oEvent) {
				// The control has been attached to the document, this function makes sure that the correct option is chosen (or none chosen
				// if there is no value). This has been done because chrome/webkit auto-selects the first option when a select element 
				// is appended. The value to use is the one that has been chosen from the ui or configured to the control, if no value then we
				// look at the temp value (i.e. a value set before population is complete so therefore not available through this.getValue()). 
				// If no temp value has been specified then we clear the value of the control.
				var mValueToUse = (this._bValueSet ? this.getValue() : '');
				if (mValueToUse == '') {
					if ((this._mTempValue === null) || (this._mTempValue == '')) {
						mValueToUse = null;
					} else {
						mValueToUse = this._mTempValue;
					}
				}

				if (mValueToUse === null) {
					this._clearValue();
				} else if (mValueToUse != this.getValue()) {
					this.set('mValue', mValueToUse);
					this._valueChange();
				}
			}.bind(this));
		}
		
		this.validate();
		this._onReady();
	},
	
	populate : function(aOptions) {
		if (!aOptions) {
			if (this._bPopulating) {
				return;
			}
			
			// Cache the current value so it can be reset
			if ((this._mTempValue === null) || (this._mTempValue == '')) {
				var mValue 			= this.getValue();
				this._mTempValue 	= (mValue ? mValue : null);
			}
			
			// Clear options
			this._oSelect.select('option, optgroup').each(Element.remove);
			
			// Get the options
			if (this.get('fnPopulate')) {
				this._bPopulating = true;
				this.get('fnPopulate')(this.populate.bind(this));
			}
		} else {
			// Cache the current value so it can be reset, only if there isn't already a temp
			if ((this._mTempValue === null) || (this._mTempValue == '')) {
				var mValue 			= this.getValue();
				this._mTempValue 	= (mValue ? mValue : null);
			}
			
			// Clear options
			this._oSelect.select('option, optgroup').each(Element.remove);
			
			// Got options
			aOptions.each(
				function(oOption) {
					this._oSelect.appendChild(oOption);
				}.bind(this)
			);
			
			if ((this._mTempValue !== null) && (this._mTempValue != '')) {
				// A value was set prior to population, set it now
				this._setValue(this._mTempValue);
				this._mTempValue = null;
			} else {
				this.clearValue();
			}
			
			this._bPopulating = false;
			this._valueChange();
			this.fire('populate');
		}
	},
	
	getValueText : function() {
		try {
			var oOption = this._oSelect.options[this._oSelect.selectedIndex];
		} catch (oEx) {
			var oOption = null;
		}
		return (oOption ? oOption.innerHTML : '');
	},
	
	_setEnabled : function() {
		this._oSelect.show();
		this._oSelect.enable();
		this._oView.hide();
	},
	
	_setDisabled : function() {
		this._oSelect.show();
		this._oSelect.disable();
		this._oView.hide();
	},
	
	_setReadOnly : function() {
		this._oSelect.hide();
		this._oView.show();
	},

	_setMandatory : function(bMandatory) {
		if (bMandatory) {
			this._oSelect.setAttribute('required', 'required');
		} else {
			this._oSelect.removeAttribute('required');
		}
	},
	
	_setValue : function(mValue) {
		var iIndex	= -1;
		var sView 	= '[None]';
		if (!this._oSelect.options.length) {
			// Not yet populated, cache for when it is
			this._mTempValue = mValue;
		} else if (!mValue && mValue !== 0) {
			// Populated, but NO value
		} else {
			// Populated, value
			iIndex = this._getIndexForValue(mValue);
			if (iIndex != -1) {
				sView 			= this._oSelect.options[iIndex].innerHTML;
				this._bValueSet	= true;
			}
		}
		
		this._oSelect.selectedIndex	= iIndex;
		this._oView.innerHTML 		= sView;
	},
	
	_getValue : function() {
		return this._oSelect.value;
	},
	
	_clearValue : function() {		
		this._oSelect.selectedIndex = -1;
	},
	
	_valueChange : function(oEvent) {
		try {
			var oOption = this._oSelect.options[this._oSelect.selectedIndex];
		} catch (oEx) {
			var oOption = null;
		}

		if (oEvent && oEvent.target) {
			this._bValueSet = true;
		}
		
		this._oView.innerHTML = (oOption ? oOption.innerHTML : '[None]');
		this.validate();
		this.fire('change');
	},
	
	_getIndexForValue : function(mValue) {
		for (var i = 0; i < this._oSelect.options.length; i++) {
			if (mValue == this._oSelect.options[i].value) {
				return i;
			}
		}
		return -1;
	}
});

return self;

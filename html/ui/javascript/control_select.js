
var Control_Select = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({
			fnPopulate : {}
		}, this.CONFIG || {});
		
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-select');
		
		this._mTempValue = null;
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oSelect = $T.select(),
			this._oView = $T.span()
		);
		
		var fnChange = this._valueChange.bind(this);
		this._oSelect.observe('change', fnChange);
		this._oSelect.observe('keyup', fnChange);
	},
	
	_syncUI : function($super) {
		$super();
		this._oSelect.name = this.get('sName');
		this.populate();
	},
	
	populate : function(aOptions) {
		if (!aOptions) {
			// Clear options
			for (var i = 0; i < this._oSelect.options.length; i++) {
				this._oSelect.options[i].remove();
			}
			
			// Cache the current value so it can be reset
			var mValue 			= this.getValue();
			this._mTempValue 	= (mValue ? mValue : null);
			
			// Get the options
			if (this.get('fnPopulate')) {
				this.get('fnPopulate')(this.populate.bind(this));
			}
		} else {
			// Got options
			aOptions.each(
				function(oOption) {
					this._oSelect.appendChild(oOption);
				}.bind(this)
			);
			
			if (this._mTempValue !== null) {
				// A value was set prior to population, set it now
				this._setValue(this._mTempValue);
				this._mTempValue = null;
			} else {
				this._setValue(null);
			}
			
			this._valueChange();
		}
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
	
	_setValue : function(mValue) {
		if (!this._oSelect.options.length) {
			// Not yet populated, cache for when it is
			this._mTempValue = mValue;
			return;
		}
		
		// Populated, set the value and update the view dom
		var iIndex	= -1;
		var sView 	= '[None]';
		if (!mValue && mValue !== 0) {
			// NO value
		} else {
			iIndex = this._getIndexForValue(mValue);
			if (iIndex != -1) {
				sView = this._oSelect.options[iIndex].innerHTML;
			}
		}
		this._oSelect.selectedIndex 	= iIndex;
		this._oView.innerHTML 			= sView;
	},
	
	_getValue : function() {
		return this._oSelect.value;
	},
	
	_clearValue : function() {
		this._oSelect.value = -1;
	},
	
	_valueChange : function(oEvent) {
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

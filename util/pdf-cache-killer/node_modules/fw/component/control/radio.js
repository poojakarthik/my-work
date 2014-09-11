
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			bChecked : {
				fnGetter : function(bChecked) {
					// NOTE: This is done because if the radios value changes as the result 
					// of one with the same name being selected, there is no event fired and 
					// so no way to update the bChecked property.
					return !!this._oInput.checked;
				}.bind(this),
				fnSetter : function(bChecked) {
					// NOTE: Read fnGetter comment. This is done because syncUI would need to call 
					// get('bChecked') in order to know which state to use. It would return the current 
					// checked state, which means that it would never change
					this._oInput.checked = bChecked;
				}.bind(this)
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-radio');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput = $D.input({type: 'radio'});
		this.NODE.appendChild(this._oInput);		
		this._oInput.observe('change', this._radioChange.bind(this));
	},
	
	_syncUI : function() {
		var bChecked 	= this.get('bChecked'),
			sName		= this.get('sName');
		
		this._super();
		
		// sName
		this._oInput.name = sName;
		
		this._updateViewElement();
		
		this.validate();
		this._onReady();
	},
	
	_setEnabled : function() {
		this._oInput.show();
		this._oInput.enable();
	},
	
	_setDisabled : function() {
		this._oInput.show();
		this._oInput.disable();
	},
	
	_setReadOnly : function() {
		this._setDisabled();
	},
	
	_setMandatory : function(bMandatory) {
		if (bMandatory) {
			this._oInput.setAttribute('required', 'required');
		} else {
			this._oInput.removeAttribute('required');
		}
	},
	
	_setValue : function(mValue) {
		if (mValue !== null) {
			this._oInput.value = mValue;
		}
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_getValue : function() {
		return (this._oInput.checked ? this._oInput.value : null);
	},
	
	_updateViewElement : function() {
		// Nothing to do, no view element
	},
	
	_radioChange : function(oEvent) {
		this.fire('change');
	}
});

return self;

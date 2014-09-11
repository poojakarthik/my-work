
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-hidden');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput = $D.input({type: 'hidden'});
		this.NODE.appendChild(this._oInput);
	},
	
	_syncUI : function() {
		this._super();
		this._oInput.name = this.get('sName');
		this.validate();
		this._onReady();
	},
	
	_setEnabled : function() {
		this._oInput.show();
	},
	
	_setDisabled : function() {
		this._oInput.show();
	},
	
	_setReadOnly : function() {
		this._oInput.hide();
	},

	_setMandatory : function(bMandatory) {
		if (bMandatory) {
			this._oInput.setAttribute('required', 'required');
		} else {
			this._oInput.removeAttribute('required');
		}
	},
	
	_setValue : function(mValue) {
		this._oInput.value = (mValue !== null ? mValue.toString() : '');
	},
	
	_getValue : function() {
		return this._oInput.value;
	},
	
	_clearValue : function() {
		this._oInput.value = '';
	},
	
	_valueChange : function(oEvent) {
		this.validate();
		this.fire('change');
	}
});

return self;

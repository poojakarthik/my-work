
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-password');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput = $D.input({type: 'password'});
		this.NODE.appendChild(this._oInput);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
	},
	
	_syncUI : function() {
		this._super();
		this._oInput.name = this.get('sName');
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
		var sValue			= (mValue !== null ? mValue.toString() : '');
		this._oInput.value	= sValue;
	},
	
	_getValue : function() {
		return this._oInput.value;
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_valueChange : function(oEvent) {
		this.validate();
		this.fire('change');
	}
});

return self;

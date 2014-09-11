
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			sPlaceholder : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-text');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput	= $D.input({type: 'text'});
		this._oView 	= $D.span({'class': 'fw-control-text-view'});
		this.NODE.appendChild(this._oInput);
		this.NODE.appendChild(this._oView);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', this._valueChange.bind(this));
		this._oInput.observe('keyup', this._valueChange.bind(this));
	},
	
	_syncUI : function() {
		this._super();
		
		this._oInput.name = this.get('sName');
		
		if (this.get('sPlaceholder')) {
			this._oInput.setAttribute('placeholder', this.get('sPlaceholder'));
		}
		
		this.validate();
		this._onReady();
	},
	
	_setEnabled : function() {
		this._oInput.show();
		this._oInput.enable();
		this._oView.hide();
	},
	
	_setDisabled : function() {
		this._oInput.show();
		this._oInput.disable();
		this._oView.hide();
	},
	
	_setReadOnly : function() {
		this._oInput.hide();
		this._oView.show();
	},
	
	_setMandatory : function(bMandatory) {
		if (bMandatory) {
			this._oInput.setAttribute('required', 'required');
		} else {
			this._oInput.removeAttribute('required');
		}
	},
	
	_setValue : function(mValue) {
		var sValue				= (mValue !== null ? mValue.toString() : '');
		this._oInput.value 		= sValue;
		this._oView.innerHTML 	= sValue.escapeHTML();
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_getValue : function() {
		return this._oInput.value;
	},
	
	_valueChange : function(oEvent) {
		this.validate();
		this.fire('change');
	}
});

return self;

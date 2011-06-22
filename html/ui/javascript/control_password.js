
var Control_Password = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-password');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oInput = $T.input({type: 'password'})
		);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
	},
	
	_syncUI : function($super) {
		$super();
		this._oInput.name = this.get('sName');
		this.validate();
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
	
	_setValue : function(mValue) {
		var sValue			= mValue.toString();
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


var Control_Text = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-text');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oInput = $T.input({type: 'text'}),
			this._oView = $T.span()
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
	
	_setValue : function(mValue) {
		var sValue				= mValue.toString();
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

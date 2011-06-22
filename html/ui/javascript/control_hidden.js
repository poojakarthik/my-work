
var Control_Hidden = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-hidden');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oInput = $T.input({type: 'hidden'})
		);
	},
	
	_syncUI : function($super) {
		$super();
		this._oInput.name = this.get('sName');
		this.validate();
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
	
	_setValue : function(mValue) {
		this._oInput.value = mValue.toString();
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

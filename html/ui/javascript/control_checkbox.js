
var Control_Checkbox = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({
			bChecked : {
				fnSetter : function(bChecked) {
					if (bChecked && !this._oInput.checked) {
						this._oInput.checked = true;
					} else if (!bChecked && this._oInput.checked) {
						this._oInput.checked = false;
					}
					this._updateViewElement();
					return bChecked;
				}.bind(this)
			}
		}, this.CONFIG || {});
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-checkbox');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oInput = $T.input({type: 'checkbox'}),
			this._oView = $T.span()
		);
		
		this._oInput.observe('change', this._checkboxChange.bind(this));
	},
	
	_syncUI : function($super) {
		$super();
		this._oInput.name = this.get('sName');
		if (!this.get('bChecked')) {
			this._updateViewElement();
		}
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
		if (mValue !== null) {
			this._oInput.value = mValue;
		}
	},
	
	_getValue : function() {
		return (this._oInput.checked ? this._oInput.value : null);
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_updateViewElement : function() {
		this._oView.innerHTML = (this._oInput.checked ? 'Yes' : 'No');
	},
	
	_checkboxChange : function(oEvent) {
		this.set('bChecked', !!this._oInput.checked);
		this.fire('change');
	}
});

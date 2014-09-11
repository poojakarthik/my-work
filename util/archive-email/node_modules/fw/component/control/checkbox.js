
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			bChecked : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-checkbox');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput 	= $D.input({type: 'checkbox'});
		this._oView 	= $D.span({'class': 'fw-control-checkbox-view'});
		this.NODE.appendChild(this._oInput);
		this.NODE.appendChild(this._oView);
		
		this._oInput.observe('change', this._checkboxChange.bind(this));
	},
	
	_syncUI : function() {
		var bChecked 	= this.get('bChecked'),
			sName		= this.get('sName');
		
		this._super();
		
		// sName
		this._oInput.name = sName;
		
		// bChecked
		if (bChecked != !!this._oInput.checked) {
			this._oInput.checked = bChecked;
		}
		
		this._updateViewElement();
		
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
		/*
		this._oInput.hide();
		this._oView.show();
		*/
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

return self;

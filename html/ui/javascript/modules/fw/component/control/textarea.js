
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			iRows 				: {},
			iColumns			: {},
			bAllowTabbedContent	: {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-textarea');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput 	= $D.textarea();
		this._oView 	= $D.span({'class': 'fw-control-textarea-view'});
		this.NODE.appendChild(this._oInput);
		this.NODE.appendChild(this._oView);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
		this._oInput.observe('keydown', this._keyDown.bind(this));
	},
	
	_syncUI : function() {
		this._super();
		
		this._oInput.name = this.get('sName');
		if (this.get('iRows')) {
			this._oInput.rows = this.get('iRows');
		}
		
		if (this.get('iColumns')) {
			this._oInput.cols = this.get('iColumns');
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
	
	_getValue : function() {
		return this._oInput.value;
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_valueChange : function(oEvent) {
		this.validate();
		this.fire('change');
	},
	
	_keyDown	: function(oEvent) {
		if (this.get('bAllowTabbedContent') && oEvent.keyCode == Event.KEY_TAB) {
			// Get cursor position from text area
			var oTextArea	= this._oInput;
			var sTab		= "\t";
			var iStartPos 	= oTextArea.selectionStart;
	        var iEndPos 	= oTextArea.selectionEnd;
	        var iScrollTop 	= oTextArea.scrollTop;

	        // Insert the tab
	        oTextArea.value	= oTextArea.value.substring(0, iStartPos) + sTab + oTextArea.value.substring(iEndPos, oTextArea.value.length);
			
	        // Reset the cursor position
	        oTextArea.selectionStart	= iStartPos + sTab.length;
	        oTextArea.selectionEnd 		= iStartPos + sTab.length;
	        oTextArea.scrollTop 		= iScrollTop;
	        
			oEvent.stop();
		}
	}
});

return self;

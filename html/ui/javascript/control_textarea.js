
var Control_Textarea = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({
			iRows 				: {},
			iColumns			: {},
			bAllowTabbedContent	: {}
		}, this.CONFIG || {});
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-textarea');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oInput = $T.textarea(),
			this._oView = $T.span()
		);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
		this._oInput.observe('keydown'	, this._keyDown.bind(this));
	},
	
	_syncUI : function($super) {
		$super();
		this._oInput.name = this.get('sName');
		
		if (this.get('iRows')) {
			this._oInput.rows = this.get('iRows');
		}
		
		if (this.get('iColumns')) {
			this._oInput.cols = this.get('iColumns');
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
		var sValue				= mValue.toString();
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

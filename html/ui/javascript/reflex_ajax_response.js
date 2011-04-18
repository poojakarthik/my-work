
var Reflex_AJAX_Response = Class.create({
	
	initialize	: function(oData) {
		this._oData = oData;
	},
	
	// Public
	
	hasException : function(sClass) {
		if (this._oData && this._oData.oException) {
			if (!sClass) {
				return true;
			} else {
				return !!(this._oData.oException.aClasses.indexOf(sClass) !== 1);
			}
		}
		return false;
	},
	
	getException : function() {
		return (this._oData ? this._oData.oException : null);
	},
	
	get : function(sProperty) {
		return (this._oData && !Object.isUndefined(this._oData[sProperty]) ? this._oData[sProperty] : null);
	},
	
	getData : function() {
		return this._oData;
	},
	
	getDebugLog : function() {
		return this.get('sDebug');
	}
});

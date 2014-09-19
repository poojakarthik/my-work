
var Class = require('./class');

var self = new Class({
	construct : function(oData) {
		this._oData = oData;
	},
	
	get : function(sProperty) {
		return (this._oData && !Object.isUndefined(this._oData[sProperty]) ? this._oData[sProperty] : null);
	},
	
	getData : function() {
		return this._oData;
	},

	hasException : function(sClass) {
		if (this._oData && this._oData.oException) {
			if (!sClass) {
				return true;
			} else {
				return !!(this._oData.oException.aClasses.indexOf(sClass) !== -1);
			}
		}
		return false;
	},
	
	getException : function() {
		return (this._oData ? this._oData.oException : null);
	},
});

return self;
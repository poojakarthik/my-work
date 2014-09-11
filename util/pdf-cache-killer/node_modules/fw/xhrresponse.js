
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
	}
});

return self;
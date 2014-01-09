
var	Class 	= require('../class'),
	Dataset = require('../dataset'),
	Sort 	= require('./sort');
	
var self = new Class({
	extends : Dataset,
	
	construct : function(iCacheMode, fnGetRecords, oSort, oFilter) {
		this._super(iCacheMode, oSort, oFilter);
		this._fnGetRecords = fnGetRecords;
	},
	
	_getRecordSet : function(fnCallback, bCountOnly, iLimit, iOffset) {
		if (this._fnGetRecords) {
			this._fnGetRecords(fnCallback, bCountOnly, iLimit, iOffset, this._hSort, this._hFilter);
		} else {
			fnCallback();
		}
	}
});

return self;

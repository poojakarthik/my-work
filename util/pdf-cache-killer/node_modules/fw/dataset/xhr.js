
var	Class 		= require('../class'),
	Dataset		= require('../dataset'),
	Sorter 		= require('../sorter'),
	Alert		= require('../component/popup/alert'),
	XHRRequest	= require('../xhrrequest'),
	Sort 		= require('./sort');
	
var self = new Class({
	extends : Dataset,
	
	construct : function(iCacheMode, oXHRRequest, oSort, oFilter) {
		this._super(iCacheMode, oSort, oFilter);
		if (!(oXHRRequest instanceof XHRRequest)) {
			throw "Invalid XHRRequest object supplied.";
		}
		
		this._fnRequestCallback	= null;
		this._oXHRRequest 		= oXHRRequest;
		this._oXHRRequest.observe('success', this._requestComplete.bind(this));
		this._oXHRRequest.observe('failure', this._requestComplete.bind(this));
	},
	
	_requestComplete : function(oEvent) {
		if (this._fnRequestCallback) {
			this._fnRequestCallback(oEvent);
		}
	},
	
	_getRecordSet : function(fnCallback, bCountOnly, iLimit, iOffset) {
		this._fnRequestCallback	= fnCallback;
		this._oXHRRequest.send({
			bCountOnly 	: bCountOnly, 
			iLimit 		: iLimit, 
			iOffset 	: iOffset, 
			oSort 		: this._hSort, 
			oFilter 	: this._hFilter
		});
	},
	
	_recordsLoaded	: function(iLimit, iOffset, oEvent) {
		var oResponse = (oEvent ? oEvent.getData() : null);
		if (!oResponse) {
			this._super(iLimit, iOffset);
		} else if (oResponse.hasException()) {
			// Something went wrong
			new Alert('An error has occurred retrieving data from the server.'/* + oResponse.getException().sMessage*/);
		} else {
			// Success
			this._super(iLimit, iOffset, oResponse.get('iRecordCount'), oResponse.get('aRecords'));
		}
	},
	
	_recordsCounted	: function(fnCallback, oEvent) {
		var oResponse = oEvent.getData();
		if (oResponse.hasException()) {
			// Something went wrong
			new Alert('An error has occurred retrieving data from the server. ' + (oResponse.sMessage ? oResponse.sMessage : ''));
		} else {
			// Success, call the super _recordsCounted
			this._super(fnCallback, oResponse.iRecordCount);
		}
	}
});

return self;

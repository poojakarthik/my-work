
var Reflex_AJAX_Response = Class.create({
	
	initialize	: function(oData, oRequest) {
		this._oData = oData;
		this._oRequest = oRequest;
	},
	
	// Public
	
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
	
	get : function(sProperty) {
		return (this._oData && !Object.isUndefined(this._oData[sProperty]) ? this._oData[sProperty] : null);
	},
	
	getData : function() {
		return this._oData;
	},

	getRequest : function() {
		return this._oRequest;
	},
	
	getDebugLog : function() {
		return this.get('sDebug');
	}
});

Object.extend(Reflex_AJAX_Response, {
	errorPopup : function(oResponse) {
		var oRequest = oResponse.getRequest();
		Reflex_AJAX_Request.showErrorPopup(
			'Reflex_AJAX_Request', 
			oResponse.getException().sMessage, 
			oRequest.getHandler(), 
			oRequest.getMethod(), 
			oRequest.getParameters(),
			oResponse.getException()
		);
	}
});

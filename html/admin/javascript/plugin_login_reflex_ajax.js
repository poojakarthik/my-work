
var Plugin_Login_Reflex_AJAX = Class.create(Reflex_Plugin, {
	OBSERVE : {
		beforerecovery : 'recover'
	},
	
	initialize : function($super, oHost) {
		$super(oHost);
		this._oRequest = null;
	},
	
	setReflexAJAXRequest : function(oRequest) {
		this._oRequest = oRequest;
	},
	
	recover : function(oEvent) {
		this._oRequest.send.apply(this._oRequest, this._oHost._aParameters);
		oEvent.stop();
	}
});

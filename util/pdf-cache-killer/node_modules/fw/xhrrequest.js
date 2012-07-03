var Class 		= require('./class'),
	XHRResponse	= require('./xhrresponse'),
	Alert 		= require('./component/popup/alert');

var self = new Class({
	implements : [require('./observable')],
	
	construct : function(sURL, fnOnSuccess, fnOnError) {
		this._sURL = sURL;
		
		if (fnOnSuccess) {
			this.observe('success', fnOnSuccess);
			this.observe('failure', fnOnError ? fnOnError : fnOnSuccess);
		}
	},
	
	send : function(oData) {
		// Make the request
		new Ajax.Request(
			this._sURL,
			{
				contentType	: 'application/json',
				parameters	: Object.toJSON(oData),
				onSuccess	: this._response.bind(this, 'success'),
				onFailure	: this._response.bind(this, 'failure'),
				onException : this._exception.bind(this)
			}
		);
	},
	
	_response : function(sEventType, oResponse) {
		// Decode & store the response
		var oResponseData = JSON.parse(oResponse.responseText);
		if (!oResponseData) {
			new Alert({sTitle : 'Error'},
				'There was an error accessing the server.'
			);
			return;
		}
		
		// Create response object
		var oResponse = new XHRResponse(oResponseData);
		
		// Invoke all of the registered callbacks
		this.fire(sEventType, oResponse);
	},
	
	_exception : function(oRequest, oException) {
		// Caught exception, let it through to the console
		throw new Error(oException.message || oException);
	}
});

return self;
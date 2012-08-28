
var Reflex_AJAX_Request = Class.create({
	
	initialize : function(sHandler, sMethod, fnOnSuccess, fnOnError) {
		this._sHandler = sHandler;
		this._sMethod = sMethod;
		this._sURL = 'reflex_json.php/' + this._sHandler + '/' + this._sMethod + '/';
		this._hEventCallbacks = {};
		this._aParameters = null;
		
		if (fnOnSuccess) {
			this.observe('success', fnOnSuccess);
			this.observe('error', fnOnError ? fnOnError : fnOnSuccess);
		}
	},
	
	// Public
	
	getHandler : function() {
		return this._sHandler;
	},

	getMethod : function() {
		return this._sHandler;
	},

	getParameters : function() {
		return this._aParameters;
	},

	getURL : function() {
		return this._sURL;
	},

	observe : function(sEvent, fnCallback) {
		if (!this._hEventCallbacks[sEvent]) {
			this._hEventCallbacks[sEvent] = [];
		}
		this._hEventCallbacks[sEvent].push(fnCallback);
	},
	
	stopObserving : function(sEvent, fnCallback) {
		if (!this._hEventCallbacks[sEvent]) {
			return;
		}
		
		if (fnCallback) {
			// Remove the specific callback
			var aEventCallbacks = this._hEventCallbacks[sEventType];
			var iIndex			= aEventCallbacks.indexOf(fnCallback);
			if (iIndex !== -1) {
				aEventCallbacks.splice(iIndex, 1);
			}
		} else {
			// Remove all callbacks for that event
			delete this._hEventCallbacks[sEvent];
		}
	},
	
	send : function() {
		// Extract method parameters
		this._aParameters = $A(arguments);
		
		// Make the request
		new Ajax.Request(
			this._sURL,
			{
				contentType	: 'application/x-www-form-urlencoded',
				parameters	: {json: Object.toJSON(this._aParameters)},
				onComplete	: this._response.bind(this, 'success'),
				onFailure	: this._response.bind(this, 'error')
			}
		);
	},
	
	// Private
	
	_response : function(sEventType, oResponse) {
		// Decode & store the response
		var oResponseData = oResponse.responseText.evalJSON();
		if (!oResponseData || oResponseData.ERROR) {
			if (oResponseData.ERROR == 'LOGIN') {
				// Authentication error, special case due to backwards compatibility with jQuery.json.jsonFunction
				JsAutoLoader.loadScript(
					['javascript/popup_login.js', 
					'javascript/plugin_login_reflex_ajax.js'], 
					function() {
						var oPopup = new Popup_Login(oResponseData.sHandler, oResponseData.sMethod, oResponseData.aParameters);
						oPopup.plug('reflex_ajax', Plugin_Login_Reflex_AJAX);
						oPopup.plugin('reflex_ajax').setReflexAJAXRequest(this);
					}.bind(this)
				);
			} else {
				Reflex_Popup.alert(
					'There was an error accessing the server. Please contact YBS for assistance.', 
					{
						sTitle			: 'Error', 
						sDebugContent	: (oResponseData.ERROR || oResponse.responseText)
					}
				);
			}
			return;
		}
		
		// Create response object
		var oResponse = new Reflex_AJAX_Response(oResponseData, this);
		
		// Add the debug log to the global debug log catcher
		try {
			Component_Debug_Log.extractLogStringFromJSONResponse(oResponse);
		} catch (oException) {
			// Component_Debug_Log must not be defined
		}
		
		// Invoke all of the registered callbacks
		try {
			var aEventCallbacks = this._hEventCallbacks[sEventType];
			if (aEventCallbacks) {
				for (var i = 0; i < aEventCallbacks.length; i++) {
					aEventCallbacks[i](oResponse);
				}
			}
		} catch (oEx) {
			Reflex_Popup.alert(oEx.message, {sTitle: 'Error'});
		}
	}
});

Object.extend(Reflex_AJAX_Request, {
	showErrorPopup : function(sRequestType, sMessage, sHandler, sMethod, aParameters, oOther, sPopupMessage, fnOnClose) {
		return Reflex_Popup.yesNoCancel(
			(sPopupMessage ? sPopupMessage : 'An error has occured'), 
			{
				iWidth : 20,
				sTitle : 'Error',
				sYesLabel : 'Report Error',
				sNoLabel : 'Close',
				fnOnYes : function(fnOnClose) {
					var sTitle = "XHR Error in Flex (" + document.domain + ")";
					var aParameterStrings = [];
					if (aParameters) {
						for (var i = 0; i < aParameters.length; i++) {
							aParameterStrings[i] = Object.toJSON(aParameters[i]);
						}
					}

					var aLines = [
						'Request Method: ' + sRequestType,
						'Message: ' + sMessage,
						'JSON Handler: ' + sHandler,
						'Function: ' + sMethod,
						'Arguments: ' + aParameterStrings.join(', ')
					];

					if (oOther) {
						for (var sLabel in oOther) {
							aLines.push(sLabel + ': ' + Object.toJSON(oOther[sLabel]));
						}
					}

					window.location = 'mailto:ybs-admin@ybs.net.au?subject=' + escape(sTitle) + '&body=' + escape(aLines.join("\n"));

					if (fnOnClose) {
						fnOnClose();
					}
				}.curry(fnOnClose),
				fnOnNo : fnOnClose
			}
		);
	}
});
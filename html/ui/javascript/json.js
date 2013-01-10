// This code relies on the jquery library

jQuery.json = {

	postJSON: function( url, data, callback ) {
		return jQuery.post(url, data, callback, "json");
	},

	escapable: {
			'\b': '\\b',
			'\t': '\\t',
			'\n': '\\n',
			'\f': '\\f',
			'\r': '\\r',
			'"' : '\\"',
			'\\': '\\\\'
		},

	encoder: {
		'boolean': function (x) {
			return String(x);
		},
		number: function (x,fmt,tb) {
			return isFinite(x) ? String(x) : 'null';
		},
		string: function (x) {
			if (/["\\\x00-\x1f]/.test(x)) {
				x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
					var c = jQuery.json.escapable[b];
					if (c) {
						return c;
					}
					c = b.charCodeAt();
					return '\\u00' +
						Math.floor(c / 16).toString(16) +
						(c % 16).toString(16);
				});
			}
			return '"' + x + '"';
		},
		object: function (x,fmt, tb) {
			if (x) {
				if (fmt == true)
				{
					var tb;
					if (typeof(tb) == 'undefined')
					{
							tb = "";
					}
					var cr = "\n";
					var ntb = tb + "\t";
				}
				else
				{
					var cr = '';
					var tb = '';
					var ntb = '';
				}
				var a = [], b, f, i, l, v;
				if (x instanceof Array) {
					a[0] = cr + tb + '[' + cr;
					l = x.length;
					for (i = 0; i < l; i += 1) {
						v = x[i];
						f = jQuery.json.encoder[typeof v];
						if (f) {
							v = f(v, fmt, ntb);
							if (typeof v == 'string') {
								if (b) {
									a[a.length] = ',' + cr;
								}
								a[a.length] = v;
								b = true;
							}
						}
					}
					a[a.length] = cr + tb + ']' + cr;
				} else if (x instanceof Object) {
					a[0] = cr + tb + '{' + cr;
					for (i in x) {
						v = x[i];
						f = jQuery.json.encoder[typeof v];
						if (f) {
							v = f(v, fmt, ntb);
							if (typeof v == 'string') {
								if (b) {
									a[a.length] = ','  + cr;
								}
								a.push(ntb + jQuery.json.encoder.string(i), ':', v);
								b = true;
							}
						}
					}
					a[a.length] = cr + tb + '}';
				} else {
					return;
				}
				return a.join('');
			}
			return 'null';
		}
	},
		
	//------------------------------------------------------------------------//
	// encode
	//------------------------------------------------------------------------//
	/**
	 * encode()
	 *
	 * convert a Javascript value to a JSON string
	 *
	 * convert a Javascript value to a JSON string
	 *
	 * @param	mixed	value	any Javascript value
	 * @return	string			JSON string
	 *
	 * @method
	 * @see		jQuery.json.prettyEncode()
	 * @see		jQuery.json.decode()
	 */
	encode: function (v) {
		var f = jQuery.json.encoder[typeof v];
		if (f) {
			v = f(v, false);
			if (typeof v == 'string') {
				return v;
			}
		}
		return null;
	},

	//------------------------------------------------------------------------//
	// prettyEncode
	//------------------------------------------------------------------------//
	/**
	 * prettyEncode()
	 *
	 * convert a Javascript value to a JSON string
	 *
	 * convert a Javascript value to a JSON string with formating (tabs and newlines)
	 * to make the output string easier to read. The output of fstringify is slightly
	 * larger then the output of stringify but is much easier to read.
	 *
	 * @param	mixed	value	any Javascript value
	 * @return	string			formated JSON string
	 *
	 * @method
	 * @see		jQuery.json.encode()
	 * @see		jQuery.json.decode()
	 */
	prettyEncode: function (v) {
		var f = jQuery.json.encoder[typeof v];
		if (f) {
			v = f(v,true);
			if (typeof v == 'string') {
				return v;
			}
		}
		return null;
	},

	//------------------------------------------------------------------------//
	// decode
	//------------------------------------------------------------------------//
	/**
	 * decode()
	 *
	 * Decode a JSON string into a Javascript value.
	 *
	 * Decode a JSON string into Javascript value(s).
	 *
	 * @param	string	text	JSON string to be decoded
	 * @return	bool	returns FALSE on error
	 *
	 * @method
	 * @see		jQuery.json.encode()
	 * @see		jQuery.json.prettyEncode()
	 */
	decode: function (text) {
		try {
			return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(
					text.replace(/"(\\.|[^"\\])*"/g, ''))) &&
				eval('(' + text + ')');
		} catch (e) {
			return false;
		}
	},

	jsonFunctionHelper: {

		callPostJson: function()
		{
			this.localFunc.funcArgs = $A(arguments);
			this.localFunc.requestFunction = this;
			
			var data = {
				json: jQuery.json.encode(this.localFunc.funcArgs)
			};

			jQuery.json.postJSON('reflex_json.php/' + this.funcClass + '/' + this.funcName, data, this.localFunc);
		},

		validateJsonResponse: function()
		{
			if (typeof Vixen != 'undefined')
			{
				Vixen.Popup.ClosePageLoadingSplash();
			}
			var success = true;
			
			if (arguments.length > 0 && arguments[1] != 'success')
			{
				success = false;
				arguments[0]['ERROR'] = 'An ajax communication error occurred.';
			}
			if (arguments.length > 0 && success && arguments[0] != null && arguments[0]['ERROR'] != undefined && arguments[0]['ERROR'] != null && arguments[0]['ERROR'] != '')
			{
				if (arguments[0]['ERROR'] == 'LOGIN')
				{
					// Prompt user to extend session or logout
					// Get the username and password from them.
					// Then invoke the callPostJson function.
					// TODO WIP Implement ajax login here and then uncomment the following line (or do something cleverer)
					//this.funcRemote.apply(null, this.funcArgs);
					// ... and get rid of the next line
					//arguments[0]['ERROR'] = "Your session has timed out. Please log in to continue.";
					jQuery.json.showLoginPopup(arguments[0], this.onSuccess, this.onFailure);
					return;
				}
				else if (arguments[0]['ERROR'] == 'PERMISSIONS')
				{
					// Inform the user that they do not have the required permissions for this action
					$Alert("You do not have permission to access this functionality", 'medium', 'PermissionInsufficient', null, "Insufficient Permissions");
					return;
				}

				success = false;
			}

			var oResponse = arguments[0];
			try {
				Component_Debug_Log.extractLogStringFromJSONResponse(oResponse);
			} catch (oException) {
				// Component_Debug_Log must not be defined
			}
					
			var argsArray = [oResponse];

			if (!success)
			{
				this.onFailure.apply(null, argsArray);
			}
			else
			{
				this.onSuccess.apply(null, argsArray);
			}
		},

		defaultErrorHandler: function(oResponse) {
			// Close the Splash, if it is open
			if (window.Vixen && window.Vixen.Popup) {
				window.Vixen.Popup.ClosePageLoadingSplash();
			}
			
			jQuery.json.errorPopup(oResponse);
		}
	},

	jsonFunction: function(onSuccess, onFailure, remoteClass, remoteMethod) {
		if (onSuccess == undefined || onSuccess == null)
		{
			onSuccess = function(){}
		}

		if (onFailure == undefined || onFailure == null)
		{
			onFailure = jQuery.json.jsonFunctionHelper.defaultErrorHandler;
		}

		var responseHandler = jQuery.json.jsonFunctionHelper.validateJsonResponse.bind({
			onSuccess: onSuccess,
			onFailure: onFailure,
			funcRemote: null,
			funcArgs: null
		}); 
		
		responseHandler.funcRemote = jQuery.json.jsonFunctionHelper.callPostJson.bind({
			funcClass: remoteClass,
			funcName: remoteMethod,
			localFunc: responseHandler
		});

		return responseHandler.funcRemote;
	},
	
	errorPopup : function(oResponse, sPopupMessage, fnOnClose) {
		// Check if it was a DatabaseAccess lock timeout or deadlock exception 
		if (oResponse.sExceptionClass) {
			switch (oResponse.sExceptionClass) {
				case 'Exception_Database_LockTimeout':
				case 'Exception_Database_Deadlock':
					Reflex_Popup.alert('This action could not be completed because the Flex server is currently busy.\nPlease wait a moment and then try again.');
					return;
			}
		}

		// Find the function that represented the response handler (NOTE: The max distance is arbitrary but there to prevent infinitum)
		var fnParent = arguments.callee;
		var iDistance = 0;
		while (fnParent && !fnParent.requestFunction && (iDistance < 20)) {
			fnParent = fnParent.caller;
			iDistance++;
		}

		var oResponseFunction = (fnParent.requestFunction ? fnParent : null);

		// Extract the message
		var sMessage = '-';
		if (oResponse.ERROR) {
			sMessage = oResponse.ERROR;
		} else if (oResponse.sMessage) {
			sMessage = oResponse.sMessage;
		} else if (oResponse.Message) {
			sMessage = oResponse.Message;
		} else if (oResponse.message) {
			sMessage = oResponse.message;
		} else if (oResponse.errorMessage) {
			sMessage = oResponse.errorMessage;
		} else if (oResponse.ErrorMessage) {
			sMessage = oResponse.ErrorMessage;
		}

		return Reflex_AJAX_Request.showErrorPopup(
			'jQuery.json.jsonFunction', 
			sMessage,
			(oResponseFunction ? oResponseFunction.requestFunction.funcClass : 'Unknown'), 
			(oResponseFunction ? oResponseFunction.requestFunction.funcName : 'Unknown'), 
			(oResponseFunction ? oResponseFunction.funcArgs : 'Unknown'),
			{'Response' : oResponse}, 
			sPopupMessage, 
			fnOnClose
		);
	},

	// Iframe-basd AJAX
	jsonIframeFormSubmit	: function(elmForm, funcResponseHandler)
	{
		// Create a hidden IFrame
		var	strIframeId			= elmForm.id + "_iframe";
		
		var elmDiv				= document.createElement('div');
		elmDiv.id				= strIframeId + '_div';
		elmDiv.style.visibility	= 'hidden';
		document.body.appendChild(elmDiv);
		
		var elmIframe				= document.createElement('iframe');
		elmIframe.id				= strIframeId;
		elmIframe.name				= strIframeId;
		elmIframe.setAttribute('onload', 'jQuery.json.jsonIframeFormLoaded(this)');
		elmIframe.style.visibility	= 'hidden';
		elmDiv.appendChild(elmIframe);
		
		// Attach a Response Handler function
		if (typeof(funcResponseHandler) == 'function')
		{
			elmIframe.funcResponseHandler	= funcResponseHandler;
		}
		
		// Add a target to the form
		elmForm.target			= elmIframe.id;
		//elmForm.target			= '_blank';
		
		return true;
	},
	
	jsonIframeFormLoaded	: function(elmIframe)
	{
		var objIframeDocument	= (elmIframe.contentDocument) ? elmIframe.contentDocument : (elmIframe.contentWindow) ? elmIframe.contentWindow.document : window.frames[elmIframe.id].document;
		
		try
		{
			// Parse Iframe contents for response data (JSON'd PHP Array)
			var sIframeContent		= objIframeDocument.body.innerHTML,
				objResponse;
		}
		catch (mError)
		{
			Reflex_Popup.alert(mError);
		}
		
		try
		{
			objResponse	= sIframeContent.unescapeHTML().evalJSON();
		}
		catch (mException)
		{
			objResponse	= {Message: sIframeContent};
		}
		
		// Call the Handler Function (if one was supplied)
		if (elmIframe.funcResponseHandler != undefined)
		{
			elmIframe.funcResponseHandler(objResponse);
		}
		
		// Schedule Iframe Cleanup
		setTimeout(this._jsonIframeCleanup.bind(this, elmIframe), 100);
		
		elmIframe.bolLoaded	= true;
	},
	
	_jsonIframeCleanup		: function(elmIframe)
	{
		// If the IFrame exists and is loaded, then remove it
		if ($ID(elmIframe.id) && elmIframe.bolLoaded)
		{
			// Destroy the Div and Iframe
			//$Alert("Cleaning up IFrame with Id '"+elmIframe.id+"' and contents '"+$ID(elmIframe.id + '_div').innerHTML+"'");
			document.body.removeChild($ID(elmIframe.id + '_div'));
		}
		else
		{
			// Otherwise schedule another cleanup
			setTimeout(this._jsonIframeCleanup.bind(this, elmIframe), 100);
		}
	},
	
	// handleResponse()	: Generic Response Handler
	handleResponse : function(fnCallback, oResponse) {
		if (oResponse) {
			if (oResponse.Success || oResponse.bSuccess) {
				fnCallback(oResponse);
				return true;
			}
		}

		jQuery.json.errorPopup(oResponse);
	},
	
	arrayAsObject	: function(mArray)
	{
		var oReturn	= {};
		if (Object.isArray(mArray))
		{
			// Array -- convert to an Object
			for (var i = 0, j = mArray.length; i < j; i++)
			{
				oReturn[i]	= mArray[i];
			}
		}
		else
		{
			// Assume it was already an Object
			oReturn	= mArray;
		}
		
		return oReturn;
	},
	
	showLoginPopup	: function(oResponse, fnOnSuccess, fnOnFailure)
	{
		var fnShowPopup	= function(sHandler, sMethod, aParameters, fnOnSuccess, fnOnFailure)
		{
			var oPopup	= new Popup_Login(sHandler, sMethod, aParameters, fnOnSuccess, fnOnFailure);
		}
		
		JsAutoLoader.loadScript(
			'javascript/popup_login.js', 
			fnShowPopup.bind(this, oResponse.sHandler, oResponse.sMethod, oResponse.aParameters, fnOnSuccess, fnOnFailure)
		);
	}
};

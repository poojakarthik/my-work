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
			if (arguments[1] != 'success')
			{
				success = false;
				arguments[0]['ERROR'] = 'An ajax communication error occurred.';
			}
			if (success && arguments[0]['ERROR'] != undefined && arguments[0]['ERROR'] != null && arguments[0]['ERROR'] != '')
			{
				if (arguments[0]['ERROR'] == 'LOGIN')
				{
					// Prompt user to extend session or logout
					// Get the username and password from them.
					// Then invoke the callPostJson function.
					// TODO WIP Implement ajax login here and then uncomment the following line (or do something cleverer)
					//this.funcRemote.apply(null, this.funcArgs);
					// ... and get rid of the next line
					arguments[0]['ERROR'] = "Your session has timed out. Please log out and then log back in again to continue.";
				}

				success = false;
			}

			var argsArray = [ arguments[0] ];

			if (!success)
			{
				this.onFailure.apply(null, argsArray);
			}
			else
			{
				this.onSuccess.apply(null, argsArray);
			}
		},

		defaultErrorHandler: function(error)
		{
			// Close the Splash, if it is open
			if (window.Vixen && window.Vixen.Popup)
			{
				window.Vixen.Popup.ClosePageLoadingSplash();
			}
			
			alert('An error occurred when communicating with the server.\n\nIf this continues, please contact your system administrator with the following details:\n\n' + error['ERROR']);
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
	}

};

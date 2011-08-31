
//debugger;

// CommonJS Modules/2.0d8 Browser Module Loader Plugin for Flex
(function (NS) {
	var	_global	= window || global;

	var	MODULE_URL_BASE	= '../admin/reflex_json.php/Javascript_Module/get';

	var	SOURCES		= {},
		OBSERVERS	= {};

	var	_undefined,
		_aXHRFactories	= [
			function(){return new XMLHttpRequest()},
			function(){return new ActiveXObject("Msxml2.XMLHTTP")},
			function(){return new ActiveXObject("Msxml3.XMLHTTP")},
			function(){return new ActiveXObject("Microsoft.XMLHTTP")}
		],
		XHR_READYSTATES	= {
			UNSENT				: 0,
			OPENED				: 1,
			HEADERS_RECEIVED	: 2,
			LOADING				: 3,
			DONE				: 4
		},
		// _XHR(): Cross-browser(?) XMLHttpRequest implementation
		_XHR	= function (sURL, oConfig) {
			// Config
			var	_oConfig	= {
				fnCallback		: oConfig.fnCallback || null,
				sHTTPMethod		: (['GET','POST'].indexOf(oConfig.sHTTPMethod.toUpperCase()) > -1) ? oConfig.sHTTPMethod.toUpperCase() : 'GET',
				bAsync			: (oConfig.bAsync === false) ? false : true,
				sContentType	: oConfig.sContentType || 'application/x-www-form-urlencoded',
				sContent		: (typeof oConfig.sContent !== 'undefined' && oConfig.sContent !== null) ? oConfig.sContent : null
			};
			
			// XMLHTTPRequest
			var	oRequest;
			for (var i=0, l=_aXHRFactories.length; i < l; i++) {
				try {
					oRequest	= _aXHRFactories[i]();
				} catch (mException) {
					continue;
				}
				break;
			}
			if (!oRequest) {
				throw new Error("No XMLHttpRequest implementation detected");
			}

			// Configure
			oRequest.open(_oConfig.sHTTPMethod, sURL, _oConfig.bAsync);
			//oRequest.setRequestHeader('User-Agent', 'XMLHTTP/1.0');
			if (_oConfig.sContent !== null) {
				oRequest.setRequestHeader('Content-type', _oConfig.sContentType);
			}

			// Monitor updates
			oRequest.onreadystatechange	= function () {
				if (oRequest.readyState != XHR_READYSTATES.DONE) {
					return;
				}
				if (typeof _oConfig.fnCallback === 'function') {
					_oConfig.fnCallback({
						// TODO: We might do some fanciness here
						oRequest	: oRequest,
						sContent	: oRequest.responseText
					});
				}
			};

			// Dispatch
			oRequest.send(_oConfig.sContent);
		},
		// _moduleIdentifierToURL(): Converts a Module Identifier to a URL that the server can handle
		_moduleIdentifierToURL	= function (sModuleIdentifier) {
			return sModuleIdentifier+'.js';
		},
		_isModules2	= function (sSource) {
			// Not completely correct, but probably good enough
			return !!sSource.trim().match(/^module\.declare\(/);
		},
		_wrapModules1	= function(sSource) {
			//return sSource;
			//return "debugger;module.declare(function(require, exports, module) {debugger;});";
			return	"module.declare(function(require, exports, module) {\n/* START MODULE SECTION */\n"+sSource.trim()+"\n/* END MODULE SECTION */\n});";
		},
		_observeModuleProvided	= function (sModuleId, fnCallback) {
			if (!OBSERVERS[sModuleId]) {
				OBSERVERS[sModuleId]	= [];
			}
			OBSERVERS[sModuleId].push(fnCallback);

			// If we have already been loaded, invoke the callback now(ish)
			//debugger;
			if (require.isMemoized(sModuleId)) {
				module.eventually(fnCallback);
			}
		};

	var	_oDeclaringModule;

	//debugger;

	// module.eventually()
	module.constructor.prototype.eventually	= function (fnCallback) {
		setTimeout(fnCallback, 0);
	};

	// module.declare()
	module.constructor.prototype.declare	= function (aDependencies, fnFactory) {
		if (typeof aDependencies === 'function') {
			fnFactory		= aDependencies;
			aDependencies	= [];
		}

		//debugger;
		if (!_oDeclaringModule) {
			// Probably a mischievous module.declare() statement somewhere it shouldn't be
			throw new Error("There is no module being declared (most likely an out-of-place module.declare())");
		}
		var oDeclaringModule	= _oDeclaringModule;
		_oDeclaringModule		= _undefined;

		// Link the provided callback to our module
		_observeModuleProvided(oDeclaringModule.sModuleId, oDeclaringModule.fnCallback);

		// Load the dependencies using module.prototype.provide() (or any override)
		this.provide(aDependencies, function () {
			//debugger;

			// Memoise the Module
			require.memoize(oDeclaringModule.sModuleId, aDependencies, fnFactory);
			
			// Invoke our observers
			for (var i=0, l=OBSERVERS[oDeclaringModule.sModuleId].length; i < l; i++) {
				module.eventually(OBSERVERS[oDeclaringModule.sModuleId][i]);
			}
		});
	};

	// module.provide()
	module.constructor.prototype.provide	= function (aDependencies, fnCallback) {
		debugger;
		var	_thisModule				= this,
			aNormalisedDependencies	= require.normaliseDependencies(aDependencies),
			oPending				= {},
			fnOnLoad				= function (sModuleId) {
				delete oPending[sModuleId];
				if (!Object.keys(oPending).length) {
					// Everything is loaded
					if (fnCallback) {
						_thisModule.eventually(fnCallback);
					}
				}
			};
		
		var	sModuleId,
			sModuleIdentifier,
			iPendingModules	= 0;
		if (aNormalisedDependencies.length) {
			for (var i=0, l=aNormalisedDependencies.length; i < l; i++) {
				// Load if it is yet to be provided to the environment
				sModuleIdentifier	=
				sModuleId	= require.id(aNormalisedDependencies[i]);
				if (!require.isMemoized(sModuleId)) {
					iPendingModules++;
					if (!SOURCES[aNormalisedDependencies[i]]) {
						// Not memoised or currently loading -- load
						this.load(sModuleId, fnOnLoad);
					} else {
						// Waiting for it to load -- notify us later
						_observeModuleProvided(sModuleId, fnOnLoad);
					}
				}
			}
		}

		if (!iPendingModules) {
			// Not waiting on any Modules
			_thisModule.eventually(fnCallback);
		}
	};

	// module.load()
	module.constructor.prototype.load	= function (sModuleIdentifier, fnCallback) {
		var	sModuleId	= require.id(sModuleIdentifier);

		// Load via XHR
		_XHR(MODULE_URL_BASE, {
			sHTTPMethod		: 'POST',
			sContent		: "json="+encodeURIComponent(JSON.stringify([[sModuleIdentifier]])),

			fnCallback	: function (oResponse) {
				//debugger;
				if (_oDeclaringModule) {
					throw new Error("There is already a module being loaded: '"+_oDeclaringModule.sModuleIdentifier+"'");
				}

				// Expecting a JSON object in the form of {IDENTIFIER: SOURCE}
				// This is because the Server tries to load our dependencies
				var	oSources	= JSON.parse(oResponse.sContent),
					sSource;

				var	_onAfterModuleDeclared	= function (sDeclaredIdentifier) {
					delete oSources[sDeclaredIdentifier];
					// If we have no more modules to declare, invoke our callback
					if (!Object.keys(oSources).length) {
						fnCallback();
					}
				};

				var	sLoadedIdentifier,
					sLoadedId;
				for (sLoadedIdentifier in oSources) {
					if (oSources.hasOwnProperty(sLoadedIdentifier)) {
						sLoadedId	= require.id(sLoadedIdentifier);

						sSource	= oSources[sLoadedId];

						// Allow Modules/1.* modules as well
						if (!_isModules2(sSource)) {
							sSource	= _wrapModules1(sSource);
						}

						// Add to our list of loaded sources
						SOURCES[sLoadedId]	= sSource;
					}
				}

				for (sLoadedIdentifier in oSources) {
					if (oSources.hasOwnProperty(sLoadedIdentifier)) {
						sSource	= SOURCES[sLoadedIdentifier];

						// Invoke module.declare
						_oDeclaringModule	= {
							sModuleId			: sModuleId,
							sModuleIdentifier	: sModuleIdentifier,
							fnCallback			: function () {_onAfterModuleDeclared(sModuleIdentifier);}
						};
						
						// FIXME: We should probably sanitise the source prior to eval()
						// Run in the global scope (don't want it polluting our local vars through closure)
						//eval(sSource);
						var	fnSandbox	= new Function(sSource);
						fnSandbox();
					}
				}
			}
		});
	};

	// Define the Main Module (empty, as much of Flex exists in the extra-module environment)
	module.declare(function () {
		//debugger;
		// TODO: We can probably remove this logging at some stage.  It doesn't really serve any purpose.  Still need to keep the function, though.
		if ('console' in _global && typeof console.log === 'function') {
			console.log("Flex's Main Module invoked");
		}
	});

})(window);

//debugger;

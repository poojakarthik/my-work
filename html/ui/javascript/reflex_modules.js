
//debugger;

// CommonJS Modules/2.0d8 Browser Module Loader Plugin for Flex
// We assume that Prototype has been loaded
(function (NS) {
	var	MODULE_URL_BASE	= 'reflex_json.php/Javascript_Module/get';

	var	SOURCES		= {},
		OBSERVERS	= {};

	var	_undefined,
		_log	= function () {
			//debugger;
			var	aArgs	= $A(arguments);
			if (console && 'log' in console && 'apply' in console.log) {
				console.log.apply(console, aArgs);
			} else {
				alert(aArgs.join("\n"));
			}
		},
		// _moduleIdToURL(): Converts a Module Id to a URL that the server can handle
		_moduleIdToURL	= function (sModuleId) {
			return MODULE_URL_BASE;
		},
		_isModules2	= function (sSource) {
			try {
				// Not completely correct, but probably good enough
				return !!sSource.trim().match(/^module\.declare\(/);
			} catch (oEx) {
				_log('Error: _isModules2 : ' + oEx.message);
			}
		},
		_wrapModules1	= function(sSource) {
			try {
				//return sSource;
				//return "debugger;module.declare(function(require, exports, module) {debugger;});";
				return	"//debugger;\n" +
						"module.declare(function(require, exports, module) {\n" +
							"//debugger;\n" +
							"/* START MODULE SECTION */\n" +
							sSource.trim() + "\n/" +
							"* END MODULE SECTION */\n" +
						"});";
			} catch (oEx) {
				_log('Error: _isModules2 : ' + oEx.message);
			}
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

			// Ensure we still haven't been provided/memoised through some kind of async behaviour
			if (!require.isMemoized(oDeclaringModule.sModuleId)) {
				// Memoise the Module
				require.memoize(oDeclaringModule.sModuleId, aDependencies, fnFactory);
			}
			
			// Invoke our observers
			for (var i=0, l=OBSERVERS[oDeclaringModule.sModuleId].length; i < l; i++) {
				module.eventually(OBSERVERS[oDeclaringModule.sModuleId][i]);
			}
		});
	};

	// module.provide()
	module.constructor.prototype.provide	= function (aDependencies, fnCallback) {
		if (this !== window.module) {
			//debugger;
		}

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
				sModuleIdentifier	= require.realIdentifier(aNormalisedDependencies[i], this.id ? this.id+'/../' : '');
				sModuleId			= require.id(sModuleIdentifier);
				if (!require.isMemoized(sModuleId)) {
					iPendingModules++;
					if (!SOURCES[sModuleId]) {
						// Not memoised or currently loading -- load
						SOURCES[sModuleId]	= {};
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
		new Ajax.Request(_moduleIdToURL(sModuleId), {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json=" + encodeURIComponent(JSON.stringify([
				[sModuleIdentifier],
				true,
				Object.keys(SOURCES).reject(function(sId){return sModuleId == sId;})
			])),

			onException	: function (oEvent, oException) {
				_log(oException);
				throw oException;
			},

			onFailure	: function (oAJAXResponse) {
				_log("Unable to load Module '"+sModuleIdentifier+"' ("+oAJAXResponse.status+": "+oAJAXResponse.statusText+").");
				throw new Error("Unable to load Module '"+sModuleIdentifier+"' ("+oAJAXResponse.status+": "+oAJAXResponse.statusText+").");
			},
			
			onSuccess	: function (oAJAXResponse) {
				//debugger;
				if (_oDeclaringModule) {
					_log("There is already a module being loaded: '"+_oDeclaringModule.sModuleIdentifier+"'");
					throw new Error("There is already a module being loaded: '"+_oDeclaringModule.sModuleIdentifier+"'");
				}

				// Expecting a JSON object in the form of {IDENTIFIER: SOURCE}
				// This is because the Server tries to load our dependencies
				var	oSources = oAJAXResponse.responseJSON || (oAJAXResponse.responseText && oAJAXResponse.responseText.evalJSON()),
					sSource;
				//debugger;

				if (!oSources) {
					_log("Unable to load module '"+sModuleIdentifier+"': response data is invalid", oAJAXResponse);
					throw new Error("Unable to load module '"+sModuleIdentifier+"': response data is invalid");
				}

				var	_onAfterModuleDeclared	= function (sDeclaredIdentifier) {
					delete oSources[sDeclaredIdentifier];
					var	aRemainingDeclarations	= Object.keys(oSources);
					_log("Module '"+sDeclaredIdentifier+"' has been declared/provided."+(aRemainingDeclarations.length ? " Still waiting on: "+aRemainingDeclarations.join(';') : ''));
					// If we have no more modules to declare, invoke our callback
					if (!aRemainingDeclarations.length) {
						_log("All pending modules declared/provided -- invoking callback...");
						//debugger;
						fnCallback();
					}
				};

				var	sLoadedIdentifier,
					sLoadedId;
				for (sLoadedIdentifier in oSources) {
					if (oSources.hasOwnProperty(sLoadedIdentifier)) {
						sLoadedId	= require.id(sLoadedIdentifier);
						sSource		= oSources[sLoadedId];

						// Allow Modules/1.* modules as well
						if (!_isModules2(sSource)) {
							sSource	= _wrapModules1(sSource);
						}

						// Add to our list of loaded sources
						//debugger;
						SOURCES[sLoadedId]	= sSource;
					}
				}
				//debugger;
				for (sLoadedIdentifier in oSources) {
					if (oSources.hasOwnProperty(sLoadedIdentifier)) {
						sLoadedId	= require.id(sLoadedIdentifier);
						sSource		= SOURCES[sLoadedId];
						//debugger;

						// Invoke module.declare
						_oDeclaringModule	= {
							sModuleId			: sLoadedId,
							sModuleIdentifier	: sLoadedIdentifier,
							fnCallback			: (function (sLoadedIdentifier) {_onAfterModuleDeclared(sLoadedIdentifier);}).curry(sLoadedIdentifier)
						};
						//debugger;
						// FIXME: We should probably sanitise the source prior to eval()
						// Run in the global scope (don't want it polluting our local vars through closure)
						//eval(sSource);
						try {
							// The `//@ sourceURL=[IDENTIFIER]` hack allows Firebug and Chrome Developer Tools to give a "name" to the eval'd code
							var	fnSandbox	= new Function("//@ sourceURL=module://"+sLoadedIdentifier+"\n"+sSource);
							fnSandbox();
						} catch (mException) {
							//debugger;
							_log("Unable to evaluate source of Module '"+sLoadedIdentifier+"'", mException, sSource);
							throw mException;
						}
						//debugger;
					}
				}
			}
		});
	};

	// Define the Main Module (empty, as much of Flex exists in the extra-module environment)
	module.declare(function () {
		//debugger;
		// TODO: We can probably remove this logging at some stage.  It doesn't really serve any purpose.  Still need to keep the function, though.
		if (typeof console == "undefined" || typeof console.log == "undefined") {
			console.log("Flex's Main Module invoked");
		}
	});

})(window);

//debugger;

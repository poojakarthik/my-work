// SOURCE: https://svn.yellowbilling.com.au/js-framework/trunk/src/modules.js

//debugger;

(function (NS) {
	// CommonJS Modules/2.0d8 implementation (http://www.page.ca/~wes/CommonJS/modules-2.0-draft8/commonjs%20modules%202.0-8(2).pdf)

	var	_undefined,
		_log	= function () {
			var	aArgs	= Array.prototype.slice.apply(arguments);
			if (console && 'log' in console && 'apply' in console.log) {
				console.log.apply(console, aArgs);
			} else {
				alert(aArgs.join("\n"));
			}
		},
		_extend	= function (oTo, oFrom) {
			for (var sProperty in oFrom) {
				if (oFrom.hasOwnProperty(sProperty)) {
					oTo[sProperty]	= oFrom[sProperty];
				}
			}
		};

	var	MODULES	= {},	// Normal Modules
		MODULES_MAIN,	// Main Module
		MODULES_EME;	// Extra-module environment

	var	MODULE_IDENTIFIER_TERM_PARENT		= '..',
		MODULE_IDENTIFIER_TERM_CURRENT		= '.',
		MODULE_IDENTIFIER_TERM_NAME_VALID	= /^[a-z0-9\_\-\.]+$/;
	
	// MODULE NAMESPACE	[§4]
	var	_resolveModuleIdentifier	= function (sModuleIdentifier, sBasePath) {
			// FIXME: Is this good enough?
			return Require.prototype.realIdentifier(sModuleIdentifier, sBasePath);
		},
		_getExports	= function (sModuleId) {
			if (sModuleId && !MODULES.hasOwnProperty(sModuleId)) {
				throw new Error("Unknown Module Id '"+sModuleId+"'");
			}
			var	oModule	= (sModuleId) ? MODULES[sModuleId] : MODULES_MAIN;
			
			if (!oModule.hasOwnProperty('mExports')) {
				var	mExports	= {};

				oModule.mExports	= mExports;

				mReturned	= oModule.fnFactory(new Require(), mExports, new Module());
				if (mReturned) {
					oModule.oModule.setExports(mReturned);
				}
			}
			oModule.bExportsRetrieved	= true;
			return oModule.mExports;
		},
		
		// Module constructor function
		Module	= function (sModuleId, aDependencies) {
			//if (!(this instanceof Module)) return new Module();

			// module.id	[§4.3]
			if (sModuleId === _undefined) {
				// Extra-module environment: No Id or Dependencies
				this.dependencies	= _undefined;
			} else {
				// Normal Module (main or otherwise)
				this.id	= sModuleId;
				
				// module.dependencies	[§4.5]
				this.dependencies	= aDependencies || [];

				// module.uri	[§4.9; deprecated]
				//this.uri	= 'todo://TODO';
			}

			// module.main	[§4.4]
			// FIXME: This may break when initialising the main module or extra-module environment
			// TODO: Reinstate later
			//this.main	= _getExports('');
		};
	//debugger;
	_extend(Module.prototype, {
		// module.prototype.declare()	[§4.1]
		declare	: function (aDependencies, fnFactory) {
			throw new Error("Default module.prototype.declare has no implementation");
		},

		// module.prototype.provide()	[§4.2]
		provide	: function (aDependencies, fnCallback) {
			throw new Error("Default module.prototype.provide has no implementation");
		},

		// module.prototype.load()	[§4.6]
		load	: function (sModuleIdentifier, fnCallback) {
			throw new Error("Default module.prototype.load has no implementation");
		},

		// module.prototype.eventually	[§4.7]
		eventually	: function (fnCallback) {
			throw new Error("Default module.prototype.eventually has no implementation");
		},

		// Works similar to the functionality described at (http://wiki.commonjs.org/wiki/Modules/SetExports)
		setExports	: function (mExports) {
			if (!(mExports instanceof Object || typeof mExports === 'function')) {
				throw new Error("Exports must be an Object or Function");
			} else if (MODULES[this.id].bRequired) {
				throw new Error("Module '"+this.id+"' has already been require()'d, cannot replace exports");
			}
		},

		// module.main	[§4.4]
		main	: _undefined
	});
	//debugger;

	// REQUIRE NAMESPACE	[§5]
	var	// Factory/pseudo-constructor for Module require() functions
		Require	= function (aDependencies, sBasePath) {
			//debugger;
			this._sBasePath;

			var	oLabelledDependencies	= this.extractLabelledDependencies(aDependencies);

			// require()	[§5.1]
			var	fnRequire	= function (sModuleIdentifier) {
				//debugger;
				// Dereference the Module Identifier
				var	sModuleId	= require.id(sModuleIdentifier);

				// Check if we have a labelled dependency by this name
				if (oLabelledDependencies.hasOwnProperty(sModuleIdentifier)) {
					// Return this instead of the resolved module id
					return _getExports(require.id(oLabelledDependencies[sModuleIdentifier]));
				}

				// Return the Module's Exports
				return _getExports(sModuleId);
			};

			// require.id()	[§5.3]
			fnRequire.id	= function (sModuleIdentifier) {
				return _resolveModuleIdentifier(sModuleIdentifier, sBasePath);
			};

			// require.main	[§5.7; deprecated]
			// TODO: Maybe later
			//fnRequire.main	= _getExports('');

			fnRequire.constructor	= Require;
			fnRequire.__proto__		= Require.prototype;
			return fnRequire;
		};
	
	_extend(Require.prototype, {
		// require.memoize()	[§5.5]
		memoize	: function (sModuleId, aDependencies, fnFactory) {
			//debugger;
			// Disallow re-memoising	[§5.5.2]
			if (require.isMemoized(sModuleId)) {
				throw new Error((sModuleId ? 'Module "'+sModuleId+'"' : 'Main Module')+" has already been provided to the environment");
			}
			
			// Add to our memoisation structure(s), but don't invoke the factory function until require()'d
			var	oModule	= {
					//mExports		: undefined,
					aDependencies	: aDependencies,
					fnFactory		: fnFactory
			};
			if (sModuleId) {
				// Regular Module
				MODULES[sModuleId]	= oModule;
			} else {
				// Main Module
				MODULES_MAIN	= oModule;
			}
		},

		// require.isMemoized()	[§5.6]
		isMemoized	: function (sModuleId) {
			return (sModuleId) ? !!MODULES[sModuleId] : !!MODULES_MAIN;
		},

		// require.uri()	[§5.4]
		uri	: function (sModuleIdentifier) {
			// TODO
		},

		// ADDITIONAL METHODS PROVIDED BY YBS

		// realIdentifier(): Normalises/realpath()'s a Module Identifier
		realIdentifier	: function (sModuleIdentifier, sBasePath) {
			var	sAbsolutePath		= (sBasePath || '') + '/';
			
			if (sModuleIdentifier.charAt(0) === '.') {
				// Relative Path
				sAbsolutePath	+= sModuleIdentifier;
			} else {
				// Absolute Path
				sAbsolutePath	= sModuleIdentifier;
			}

			// Convert into a "realpath"
			var	aTerms				= (sModuleIdentifier).toLowerCase().split('/'),
				aNormalisedTerms	= [];
			for (var i=0, l=aTerms.length; i < l; i++) {
				switch (aTerms[i]) {
					case '':
					case MODULE_IDENTIFIER_TERM_CURRENT:
						// Ignore
						break;
					case MODULE_IDENTIFIER_TERM_PARENT:
						if (!aNormalisedTerms.length) {
							throw new Error("Module Identifier '"+sModuleIdentifier+"' exceeds the top-level directory");
						}
						aNormalisedTerms.pop();
						break;
					default:
						if (!MODULE_IDENTIFIER_TERM_NAME_VALID.test(aTerms[i])) {
							throw new Error("Module Identifier '"+sModuleIdentifier+"' includes invalid terms");
						}
						aNormalisedTerms.push(aTerms[i]);
						break;
				}
			}
			return aNormalisedTerms.join('/');
		},

		// normaliseDependencies(): Normalises a [§3.5] Dependency Array to a flat array of Module Identifiers
		normaliseDependencies	: function (aDependencies) {
			var	aModuleIdentifiers	= [];
			
			// Process the dependencies
			aDependencies	= aDependencies || [];
			for (i=0, l=aDependencies.length; i < l; i++) {
				if (typeof aDependencies[i] === 'string') {
					// String: unlabelled Dependency
					aModuleIdentifiers.push(aDependencies[i]);
				} else {
					// Object: labelled Dependencies
					for (sLabel in aDependencies[i]) {
						if (aDependencies[i].hasOwnProperty(sLabel)) {
							// Labelled Dependency
							aModuleIdentifiers.push(aDependencies[i][sLabel]);
						}
					}
				}
			}

			return aModuleIdentifiers;
		},

		// extractLabelledDependencies(): Extracts all labelled dependencies to a single hash in the form of {LABEL:MODULE_IDENTIFIER}
		extractLabelledDependencies	: function (aDependencies) {
			var	oLabels	= {};

			aDependencies	= aDependencies || [];
			
			for (i=0, l=aDependencies.length; i < l; i++) {
				if (typeof aDependencies[i] === 'string') {
					// String: unlabelled Dependency
					// Ignore
				} else {
					// Object: labelled Dependencies
					for (sLabel in aDependencies[i]) {
						if (aDependencies[i].hasOwnProperty(sLabel)) {
							// Labelled Dependency
							oLabels[sLabel]	= aDependencies[i][sLabel];
						}
					}
				}
			}

			return oLabels;
		}
	});

	// Set up our extra-module environment variables
	NS.module	= new Module();
	NS.require	= new Require();

	// We have a special implementation of module.declare() for when we init the main module
	NS.module.declare	= function (aDependencies, fnFactory) {
		//debugger;
		if (typeof aDependencies === 'function') {
			fnFactory		= aDependencies;
			aDependencies	= [];
		}
		
		// Remove our special case
		delete module.declare;

		// Provide any dependencies
		module.provide(aDependencies, function () {
			// Memoize & invoke the main module
			require.memoize('', aDependencies, fnFactory);
			_getExports('');
		});
	};

})(window);

//debugger;

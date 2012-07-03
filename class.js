
require('./function');

var	_constructorExtensible	= function (fnFunction) {
		// NOTE: Ideally, we would limit ourselves to 
		//return !!(fnFunction instanceof Class);
		return !!(typeof fnFunction == 'function');
	},
	_isPlainObject	= function (mObject) {
		return mObject && mObject.constructor.prototype === Object.prototype;
	},
	_isArray	= function (mObject) {
		return mObject && mObject.constructor.prototype === Array.prototype;
	},
	_clone	= function (oObject, aParentObjects) {
		aParentObjects	= (aParentObjects || []);

		// If recursion is detected, return the object uncloned
		if (aParentObjects.indexOf(oObject) != -1) {
			return oObject;
		}

		// Copy "own" properties to a new object
		var	oCloned	= {};
		for (var sProperty in oObject) {
			if (oObject.hasOwnProperty(sProperty)) {
				if (_isArray(oObject[sProperty])) {
					// Array, clone it
					oCloned[sProperty] = oObject[sProperty].slice(0);
				} else if (_isPlainObject(oObject[sProperty])) {
					// Plain object, special clone
					oCloned[sProperty] = _clone(oObject[sProperty], aParentObjects.slice(0).push(oObject));
				} else {
					// Primitive
					oCloned[sProperty] = oObject[sProperty];
				}
			}
		}
		return oCloned;
	},
	_clonePrototypeObjectProperties	= function (oSubject) {
		//debugger;
		var	aKeys			= Object.keys(oSubject),
			oPrototype		= Object.getPrototypeOf(oSubject);
		
		for (var sProperty in oPrototype) {
			if (oPrototype.hasOwnProperty(sProperty)) {
				// Clone "plain" objects or arrays recursively
				if (_isPlainObject(oPrototype[sProperty])) {
					// Plain object, special clone
					oSubject[sProperty]	= _clone(oPrototype[sProperty]);
				} else if (_isArray(oPrototype[sProperty])) {
					// Array, clone it
					oSubject[sProperty] = oPrototype[sProperty].slice(0);
				}
			}
		}
	},
	_mixin	= function (oTarget, oSource, bAllowConstructor) {
		var	mProperty,
			fnExisting;

		for (var sProperty in oSource) {
			// Only allow `constructor` to be added if explicitly allowed
			if (oSource.hasOwnProperty(sProperty) && (bAllowConstructor || sProperty != 'constructor')) {
				if (typeof oSource[sProperty] == 'function' && typeof oTarget[sProperty] == 'function') {
					// Methods that override need to be wrapped to support the `this._super()` call
					oTarget[sProperty]	= (function (fnOverride, fnSuper) {
						//debugger;
						// FIXME: I don't think we need `_this`.  Can probably remove
						var	_this	= this;
						return	function () {
							//debugger;
							// We need to temporarily replace `this._super` with a higher level one
							var	fnOldSuper	= this._super,
								mReturn;
							
							this._super	= fnSuper;
							mReturn 		= fnOverride.apply(this, arguments);
							this._super	= fnOldSuper;

							return mReturn;
						};
					}).call(oTarget, oSource[sProperty], (typeof oTarget[sProperty] == 'function') ? oTarget[sProperty] : null);
				} else {
					// Everything else (include non-overriding methods) should just be copied in the default manner
					oTarget[sProperty]	= oSource[sProperty];
				}
			}
		}

		// Allow chaining on Target
		return oTarget;
	},
	_bConstructing,
	_BaseClass;

// Constructor
var	Class	= function (oDefinition) {
	//debugger;

	// If our constructor is accidentally called as a function (i.e. without 'new'), then autocorrect
	if (!(this instanceof Class)) return Class.applyAsConstructor(arguments);

	var	i;
	
	var fnConstructor	= function () {
		// If our constructor is accidentally called as a function (i.e. without 'new'), then autocorrect
		if (!(this instanceof fnConstructor)) return fnConstructor.applyAsConstructor(arguments);

		// Copy any object properties from oDefinition to our instance (so that they don't modify the prototype's version)
		_clonePrototypeObjectProperties(this);
		
		// Call `construct` implementation if we are not in "constructing" mode (i.e. initialising to create a prototype for a subclass)
		if (this.construct && !_bConstructing) {
			this.construct.apply(this, arguments);
		}
	};

	// INHERITANCE (extends)
	// `extends` must be a function (ideally, instanceof Class)
	var	fnParentConstructor;
	if (_constructorExtensible(oDefinition.extends)) {
		fnConstructor.$PARENT	= oDefinition.extends;

		// Our prototype is an instance of our parent class
		// Setting `_bConstructing` to `true` allows us to suppress the user-supplied implementation for `construct` for the purposes of generating a prototype
		_bConstructing			= true;
		fnConstructor.prototype	= new (fnConstructor.$PARENT)();
		_bConstructing			= false;
	} else {
		// Parent is the "BaseClass" class
		fnConstructor.prototype	= new _BaseClass();
	}
	delete oDefinition.extends;

	// MIXINS (implements)
	fnConstructor.$MIXINS	= [];
	var	aMixins	= (typeof oDefinition.mixes == 'string') ? [oDefinition.implements] : oDefinition.implements;
	if (Array.isArray(aMixins) && aMixins.length) {
		for (i = 0; i < aMixins.length; i++) {
			_mixin(fnConstructor.prototype, aMixins[i], false);
			fnConstructor.$MIXINS.push(aMixins[i]);
		}
	}
	delete oDefinition.implements;

	// STATICS (statics)
	if (_isPlainObject(oDefinition.statics)) {
		// Mix these in to the constructor funciton ("Class"), allowing them to be accessed in the form [CLASS].[STATIC]
		_mixin(fnConstructor, oDefinition.statics, false);
	}
	delete oDefinition.statics;

	// INSTANCE (everything else)
	// Instance methods & properties
	_mixin(fnConstructor.prototype, oDefinition, true);

	// Other fixes to ensure our prototype chain is as we expect it
	fnConstructor.constructor	= Class;
	//fnConstructor.__proto__		= Class.prototype;

	//debugger;
	if (Class.constructor !== Function) {
		console.log("CULPRIT FOUND");
	}
	
	// Add a reference to this class from the prototype (for inheritance reflection)
	fnConstructor.prototype.$CLASS = fnConstructor;
	
	return fnConstructor;
};

// Prototype Methods
Class.prototype.implements = function (oMixin) {
	// Checks to see if the Class implements the provided Mixin
	if (this.$MIXINS.indexOf(oMixin)) {
		// We implement this mixin directly
		return true;
	} else if (!this.$MIXINS.indexOf(oMixin) || (implements in this.$PARENT && $MIXINS in this.$PARENT)) {
		// Check if the parent class implements this mixin
		return this.$PARENT.implements(oMixin);
	}
	// We don't implement this mixin
	return false;
};

// Emulate Prototype's Class factory signature
Class.create	= function (oParent, oDefinition) {
	// oParent parameter is optional
	if (!oDefinition) {
		oDefinition	= oParent;
		oParent		= null;
	} else {
		oDefinition.extends	= oParent;
	}

	return new Class(oDefinition);
};

// Create the "base" Class
_BaseClass	= function(){};

// Return our API
return (module.exports = Class);

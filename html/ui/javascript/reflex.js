
/* Reflex JS 'Namespace' */
Reflex = {
	// mixin: Mixes the properties of the mixer object (oMixer) into the mixee (oMixee) by copying them to the mixee's prototype.
	mixin : function(oMixee, oMixer) {
		var aMixerProperties 			= Object.keys(oMixer);
		var hInitialisationProperties	= {};
		for (var i = 0; i < aMixerProperties.length; i++) {
			var sProperty 	= aMixerProperties[i];
			var mValue		= oMixer[sProperty];
			if (!oMixee.prototype[sProperty]) {
				if (typeof mValue == 'function') {
					// The property is a function, just add it to the prototype
					oMixee.prototype[sProperty] = mValue;
				} else {
					// The property is an object or a primitive, add it as an initialisation property
					hInitialisationProperties[sProperty] = mValue;
				}
			}
		}
		
		// Make sure the initialisation properties are added to a new instance of the mixee
		var aPropertyNames = Object.keys(hInitialisationProperties);
		if (aPropertyNames.length > 0) {
			var fnInitialize = oMixee.prototype.initialize.wrap(
				function(fnInitialize) {
					for (var i = 0; i < aPropertyNames.length; i++) {
						var sProperty 	= aPropertyNames[i];
						var mValue		= hInitialisationProperties[sProperty];
						this[sProperty]	= Reflex.cloneDataMember(mValue, oMixer, sProperty);
					}

					var aArgs = $A(arguments).slice(1, $A(arguments).length);
					return fnInitialize.apply(this, aArgs);
				}
			);

			oMixee.prototype.initialize = fnInitialize;
		}
	},
	
	// cloneDataMember:	Method to prepare a member instance of a data value, based on a prototype value (represented by parameter mValue).
	// 					This method clones mValue and returns the cloned value.
	// 					if mValue is a primitive datatype it is returned without being cloned.
	// 					if mValue is a function or 'undefined', an error is thrown because these are not allowed in valid class definitions
	// 					if mValue is an object, a deep clone is created. If there are circular references in the cloning process, an error 
	//					will be thrown
	cloneDataMember : function(mValue, oSource, sProperty, aEnclosingObjects) {
		if (typeof aEnclosingObjects == 'undefined') {
			aEnclosingObjects = new Array();
		}
		
		try {
			if (typeof mValue == 'object') {
				// Object or Array
				var oNewObj = (mValue instanceof Array) ? [] : {};
				if (mValue instanceof Array) {
					// An array
					for (var i = 0; i < mValue.length; i++) {
						if (!Reflex.inObjects(mValue[i], aEnclosingObjects)) {
							var aCopy = aEnclosingObjects.clone();
							aCopy.push(mValue);
							oNewObj[i] = Reflex.cloneDataMember(mValue[i], oSource, sProperty, aCopy);
						} else {
							throw "'" + oSource.__sPackageName + "." + sProperty + "' contains a circular reference, which makes it impossible for it to be cloned";
						}
					}
				} else {
					// An object
					for (var i in mValue) {
						if (!Reflex.inObjects(mValue[i], aEnclosingObjects)) {
							var aCopy = aEnclosingObjects.clone();
							aCopy.push(mValue);
							oNewObj[i] = Reflex.cloneDataMember(mValue[i], oSource, sProperty, aCopy);
						} else {
							throw "'" + oSource.__sPackageName +  "." + sProperty + "' contains a circular reference, which makes it impossible for it to be cloned";
						}
					}
				}
				return oNewObj;
			} else if (typeof mValue == 'function' || typeof mValue == 'undefined') {
				// Function, not allowed to clone it
				throw "'" + oSource.__sPackageName + "." + sProperty + "' is or contains a value that is not allowed: only primitive datatypes, arrays, and objects are allowed.";
			} else {
				// Primitive
				return mValue;
			}
		} catch (oException) {
			throw "An error has occurred cloning a mixin data member: " + oException;
		}
	},

	// inObjects: A utility method to detect circular references. It checks whether mValue exists in aObjects.
	inObjects: function (mValue, aObjects) {
		if (mValue == null || typeof mValue !='object') {
			// We dont need to do this for primitive values
			return false;
		}
		
		for (var i = 0; i < aObjects.length; i++) {
			var aProperties = Object.keys(aObjects[i]);
			for (var z = 0; z < aProperties.length; z++) {
				if (aObjects[i][aProperties[z]] == mValue) {
					return true;
				}
			}
		}
		
		return false;
	}
};

// Function.construct
/*Function.construct	= function (fnConstructor, aArguments) {
	var	fnFakeConstructor	= function(){
			fnConstructor.apply(this, $A(aArguments))
		};
	fnFakeConstructor.prototype	= fnConstructor.prototype;
	return new fnFakeConstructor();
};*/

Function.prototype.construct	= function () {
	var	self				= this,
		aArguments			= $A(arguments),
		fnFakeConstructor	= function () {
			self.apply(this, aArguments)
		};
	fnFakeConstructor.prototype	= this.prototype;
	return new fnFakeConstructor();
};

Function.prototype.constructApply	= function (aArguments) {
	return this.construct.apply(this, aArguments);
};

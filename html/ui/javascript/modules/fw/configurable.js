
var	_undefined;

return {
	// setConfig(): Public API to set the Config of this Component
	setConfig	: function (oConfig) {
		this.set(oConfig);
		/*this.CONFIG = this.CONFIG || {};
		if (oConfig) {
			for (var sAttribute in oConfig) {
				if (oConfig.hasOwnProperty(sAttribute)) {
					this.set(sAttribute, oConfig[sAttribute]);
				}
			}
		}*/
	},

	// set(): Sets Config options
	set	: function (sAttribute, mValue) {
		var	oConfig;

		// Setting a single property
		if (typeof sAttribute == 'string') {
			oConfig	= {};
			oConfig[sAttribute]	= mValue;
			return this.set(oConfig);
		}

		// Setting multiple properties
		this.CONFIG = this.CONFIG || {};
		oConfig		= sAttribute;
		if (oConfig) {
			// Update each property
			for (var sAttribute in oConfig) {
				mValue	= oConfig[sAttribute];

				if (sAttribute in this.CONFIG) {
					// Configurable attribute
					mValue	= (typeof this.CONFIG[sAttribute].fnSetter === 'function') ? this.CONFIG[sAttribute].fnSetter(mValue) : mValue;
					if (typeof mValue !== 'undefined') {
						this.CONFIG[sAttribute].mValue	= mValue;
					}
				} else if ('observe' in this) {
					// Check for an event handler
					var aMatches = sAttribute.match(/on([a-z_]+)/);
					if (aMatches && Object.isFunction(mValue)) {
						// It is an event handler, set up the observer
						var sEventName = aMatches[1];
						if (typeof this.observe == 'function') {
							this.observe(sEventName, mValue);
						}
					}
				} else {
					throw new Error("'"+sAttribute+"' is not a recognised configuration property");
				}
			}
		}
	},

	// set(): Gets an individual Config record
	get	: function (sAttribute) {
		if (sAttribute in this.CONFIG) {
			return (typeof this.CONFIG[sAttribute].fnGetter === 'function') ? this.CONFIG[sAttribute].fnGetter(this.CONFIG[sAttribute].mValue) : this.CONFIG[sAttribute].mValue;
		}
		throw new Error("'"+sAttribute+"' is not a recognised configuration property");
	}
};

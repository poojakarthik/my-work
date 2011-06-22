
var Configurable = {
	// setConfig(): Public API to set the Config of this Component
	setConfig	: function (oConfig) {
		this.CONFIG = this.CONFIG || {};
		for (var sAttribute in oConfig) {
			if (oConfig.hasOwnProperty(sAttribute)) {
				this.set(sAttribute, oConfig[sAttribute]);
			}
		}
	},

	// set(): Sets an individual Config record
	set	: function (sAttribute, mValue) {
		if (sAttribute in this.CONFIG) {
			// Configurable attribute
			mValue	= (typeof this.CONFIG[sAttribute].fnSetter === 'function') ? this.CONFIG[sAttribute].fnSetter(mValue) : mValue;
			if (typeof mValue !== 'undefined') {
				this.CONFIG[sAttribute].mValue	= mValue;
			}
		} else {
			// Check for an event handler
			var aMatches = sAttribute.match(/on([a-z_]+)/);
			if (aMatches && Object.isFunction(mValue)) {
				// It is an event handler, set up the observer
				var sEventName = aMatches[1];
				if (typeof this.observe == 'function') {
					this.observe(sEventName, mValue);
				}
			}
		}
	},

	// set(): Gets an individual Config record
	get	: function (sAttribute) {
		if (sAttribute in this.CONFIG) {
			return (typeof this.CONFIG[sAttribute].fnGetter === 'function') ? this.CONFIG[sAttribute].fnGetter(this.CONFIG[sAttribute].mValue) : this.CONFIG[sAttribute].mValue;
		}
		return null;
	}
};

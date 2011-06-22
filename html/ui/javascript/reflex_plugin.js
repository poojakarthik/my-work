
var Reflex_Plugin = Class.create({
	OBSERVE : {},
	
	initialize : function(oHost) {
		this._oHost = oHost;
		
		Object.keys(this.OBSERVE).each(
			function(sEventName) {
				var mHandler = this.OBSERVE[sEventName];
				if (typeof mHandler == 'string') {
					if (!Object.isUndefined(this[mHandler])) {
						// String: function name
						this._oHost.observe(sEventName, this[mHandler].bind(this));
					}
				} else {
					// Function reference
					this._oHost.observe(sEventName, mHandler.bind(this));
				}
			}.bind(this)
		);
	}
});

Reflex.mixin(Reflex_Plugin, Observable);

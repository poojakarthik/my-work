
var Class = require('./class');

var self = new Class({
	implements	: [require('./observable')],
	
	EXTEND : {},
	
	construct : function(oHost) {
		this._oHost = oHost;
		
		Object.keys(this.EXTEND).each(
			function(sFunction) {
				// Find if the hosts or its parents allow this extension point
				var oClass 					= this._oHost.__proto__;
				var bValidExtensionPoint	= false;
				
				while (oClass) {
					if ((typeof oClass.$CLASS.EXTENSION_POINTS != 'undefined') && (oClass.$CLASS.EXTENSION_POINTS.indexOf(sFunction) != -1)) {
						// Found one, valid extension point
						bValidExtensionPoint = true;
						break;
					}
					
					oClass = oClass.__proto__;
				}
				
				if (!bValidExtensionPoint) {
					throw new Error(sFunction + " is not a valid plugin extension point");
				}
				
				var fnHostFunction 	= this._oHost[sFunction].bind(this._oHost);
				var mHandler 		= this.EXTEND[sFunction];
				if (typeof mHandler == 'string') {
					if (!Object.isUndefined(this[mHandler])) {
						// String: function name
						this._oHost[sFunction] = fnHostFunction.wrap(this[mHandler].bind(this));
					}
				} else {
					// Function reference
					this._oHost[sFunction] = fnHostFunction.wrap(mHandler.bind(this));
				}
			}.bind(this)
		);
	}
});

return self;

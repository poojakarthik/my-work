
var	Event	= require('../event'),
	Class	= require('../class');

return new Class({
	// Defaults
	bCancelled	: false,
	
	construct	: function(oEvent, mData) {
		if (!(oEvent instanceof Event)) {
			throw "oEvent is not an FW/Event!";
		}
		
		// Members
		this.oEvent		= oEvent;
		this.bCancelled	= false;
		this.iTimestamp	= (new Date()).getTime();
		this.mData		= (typeof mData === 'undefined') ? null : mData;
		
		// Fire!
		for (var aListeners = this.oEvent.getListeners(), i = 0, l = aListeners.length; i < l; i++) {
			// Allow Listeners to return false to cancel the default action
			if (aListeners[i](this) === false) {
				this.cancel();
			}
		}
	},
	
	cancel	: function() {
		this.bCancelled	= (this.oEvent.isCancellable()) ? true : false;
		return this;
	},
	
	stop	: function() {
		return this.cancel();
	},
	
	isCancelled	: function() {
		return this.bCancelled;
	},
	
	getData	: function() {
		return this.mData;
	},
	
	getTarget	: function() {
		return this.oEvent.getTarget();
	}
});

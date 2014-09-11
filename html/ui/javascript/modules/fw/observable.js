
var	Event	= require('./event');

return {
	// Events that this supports
	oEvents : {},
	
	observe : function(sEventName, cEventHandler) {
		sEventName = Event.sanitiseName(sEventName);
		this._registerEvent(sEventName);
		this.oEvents[sEventName].observe(cEventHandler);
		
		return this;
	},
	
	stopObserving : function(sEventName, cEventHandler) {
		sEventName = Event.sanitiseName(sEventName);
		if (typeof this.oEvents[sEventName] !== 'undefined') {
			this.oEvents[sEventName].stopObserving(cEventHandler);
		}
		return this;
	},
	
	fire : function(sEventName, mData) {
		sEventName = Event.sanitiseName(sEventName);
		this._registerEvent(sEventName);
		
		return this.oEvents[sEventName].fire(mData);
	},
	
	_registerEvent : function(sEventName, bCancellable) {
		sEventName = Event.sanitiseName(sEventName);
		if (typeof this.oEvents[sEventName] === 'undefined') {
			this.oEvents[sEventName] = new Event(this, sEventName, bCancellable);
		} else {
			// Allow 'redefinition', by redefining the Cancellable property
			// setCancellable reverts to default/previous value if undefined is passed through
			this.oEvents[sEventName].setCancellable(bCancellable);
		}		
		return this;
	}
};

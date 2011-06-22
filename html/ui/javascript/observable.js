// Mixin
Observable = {
	// Events that this supports
	oEvents : {},
	
	observe : function(sEventName, cEventHandler) {
		sEventName = Reflex_Event.sanitiseName(sEventName);
		this._registerEvent(sEventName);
		this.oEvents[sEventName].observe(cEventHandler);
		
		return this;
	},
	
	stopObserving : function(sEventName, cEventHandler) {
		sEventName = Reflex_Event.sanitiseName(sEventName);
		if (typeof this.oEvents[sEventName] !== 'undefined') {
			this.oEvents[sEventName].stopObserving(cEventHandler);
		}
		return this;
	},
	
	fire : function(sEventName, mData) {
		sEventName = Reflex_Event.sanitiseName(sEventName);
		this._registerEvent(sEventName);
		
		return this.oEvents[sEventName].fire(mData);
	},
	
	_registerEvent : function(sEventName, bCancellable) {
		sEventName = Reflex_Event.sanitiseName(sEventName);
		if (typeof this.oEvents[sEventName] === 'undefined') {
			this.oEvents[sEventName] = new Reflex_Event(this, sEventName, bCancellable);
		} else {
			// Allow 'redefinition', by redefining the Cancellable property
			// setCancellable reverts to default/previous value if undefined is passed through
			this.oEvents[sEventName].setCancellable(bCancellable);
		}		
		return this;
	}
};
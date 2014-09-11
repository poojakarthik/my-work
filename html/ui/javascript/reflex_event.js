
var Reflex_Event = Class.create({
	/**
	 * initialize()
	 * 
	 * Constructor
	 * 
	 * @param	oTarget		Target Object
	 * @param	sType		Event Name (e.g. 'click')
	 * @param	bCancellable	
	 */
	initialize	: function(oTarget, sType, bCancellable) {
		this.oTarget		= oTarget;
		this.sType			= String(sType).strip();
		
		this.setCancellable(Reflex_Event.CANCELLABLE_DEFAULT);
		this.setCancellable(bCancellable);
		
		this._aListeners	= [];
	},
	
	isCancellable	: function() {
		return this._bCancellable;
	},
	
	setCancellable	: function(bCancellable) {
		this._bCancellable	= (typeof bCancellable === 'undefined') ? this._bCancellable : !!bCancellable;
	},
	
	fire	: function(mData) {
		// Return the Event Instance that is created
		return (new Reflex_Event_Instance(this, mData));
	},
	
	observe	: function(cCallback) {
		if (this._aListeners.indexOf(cCallback) === -1){
			// Only attach if we aren't already attached
			this._aListeners.push(cCallback);
		}
		return this;
	},
	
	stopObserving	: function(cCallback) {
		var	iListenerIndex	= this._aListeners.indexOf(cCallback);
		if (iListenerIndex !== -1) {
			// Only detach if we aren't already attached
			this._aListeners.splice(cCallback, 1);
		}
		return this;
	},
	
	getListeners	: function() {
		// Return a shallow copy, so that onlookers can't manipulate the result
		return $A(this._aListeners);
	},
	
	getType	: function() {
		return this.sType;
	},
	
	getTarget	: function() {
		return this.oTarget;
	}
});

Object.extend(Reflex_Event, {
	CANCELLABLE_DEFAULT	: false,
	
	sanitiseName	: function(sEventName) {
		return sEventName.strip();
	}
});
